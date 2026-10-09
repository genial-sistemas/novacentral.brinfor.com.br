<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/equipamentoConstantes.php';

function obterEquipamentosCliente($idPessoa, $contratos, $status, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));

    if (!$idPessoa || !$idsContratos || !in_array($status, [EQP_STATUS_ATIVO, EQP_STATUS_INATIVO], true)) {
        return [];
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }

    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $consulta = $dbh->prepare("
        SELECT equipamento.id,
               equipamento.contrato AS id_contrato,
               equipamento.codigo,
               equipamento.sn,
               equipamento.descricao,
               equipamento.modelo,
               equipamento.usuario,
               equipamento.status,
               tipo.tipo AS nome_tipo,
               contato.nome AS nome_contato,
               EXISTS (
                   SELECT 1
                   FROM contrato_outsourcing_equipamento_desativacao solicitacao
                   WHERE solicitacao.id_equipamento = equipamento.id
                     AND solicitacao.data_avaliacao IS NULL
               ) AS desativacao_pendente
        FROM contrato_outsourcing_equipamentos equipamento
        JOIN contrato
          ON contrato.id = equipamento.contrato
        LEFT JOIN contrato_outsourcing_equipamentos_tipo tipo
          ON tipo.id = equipamento.tipo
        LEFT JOIN pessoa_juridica_contatos contato
          ON contato.id = equipamento.contato
        WHERE contrato.id_pessoa = ?
          AND contrato.id_tipo = ?
          AND contrato.status = ?
          AND contrato.id IN ($marcadores)
          AND equipamento.status = ?
        ORDER BY equipamento.codigo
    ");

    $consulta->execute(array_merge(
        [(int)$idPessoa, CONTRATO_HELPDESK_ID, CONTRATO_STATUS_ATIVO],
        $idsContratos,
        [$status]
    ));

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

function obterEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));

    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return null;
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }

    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $consulta = $dbh->prepare("
        SELECT equipamento.id, equipamento.contrato AS id_contrato,
               equipamento.codigo, equipamento.contato, equipamento.usuario,
               equipamento.descricao AS nome_rede, equipamento.modelo,
               equipamento.valor_equipamento,
               tipo.tipo AS nome_tipo,
               contrato_tipo.descricao AS tipo_contrato
        FROM contrato_outsourcing_equipamentos equipamento
        JOIN contrato ON contrato.id = equipamento.contrato
        LEFT JOIN contrato_outsourcing_equipamentos_tipo tipo
          ON tipo.id = equipamento.tipo
        LEFT JOIN contrato_outsourcing_equipamentos_contrato_tipo contrato_tipo
          ON contrato_tipo.id = equipamento.id_tipo_contrato
        WHERE equipamento.id = ?
          AND contrato.id_pessoa = ?
          AND contrato.id_tipo = ?
          AND contrato.status = ?
          AND contrato.id IN ($marcadores)
          AND equipamento.status = ?
        LIMIT 1
    ");
    $consulta->execute(array_merge(
        [(int)$idEquipamento, (int)$idPessoa, CONTRATO_HELPDESK_ID, CONTRATO_STATUS_ATIVO],
        $idsContratos,
        [EQP_STATUS_ATIVO]
    ));

    return $consulta->fetch(PDO::FETCH_ASSOC) ?: null;
}

function obterContatosAtivosEquipamentos($idPessoa, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    if (!$idPessoa) {
        return [];
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }

    $consulta = $dbh->prepare("
        SELECT id, nome
        FROM pessoa_juridica_contatos
        WHERE id_cliente = ? AND status = 'a'
        ORDER BY nome
    ");
    $consulta->execute([(int)$idPessoa]);
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

function tokenCsrfEquipamentos()
{
    if (empty($_SESSION['csrf_equipamentos'])) {
        $_SESSION['csrf_equipamentos'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_equipamentos'];
}

function atualizarContatoEquipamentoCliente($idEquipamento, $idPessoa, $contratos, $idContato, $dbh = null)
{
    $equipamento = obterEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh);
    if (!$equipamento) {
        return false;
    }

    $idContato = $idContato === null ? null : filter_var($idContato, FILTER_VALIDATE_INT);
    if ($idContato === false || ($idContato !== null && $idContato <= 0)) {
        return false;
    }

    $dbh = $dbh ?: getConexao();
    if ($idContato !== null) {
        $contato = $dbh->prepare("
            SELECT id
            FROM pessoa_juridica_contatos
            WHERE id = ? AND id_cliente = ? AND status = 'a'
            LIMIT 1
        ");
        $contato->execute([(int)$idContato, (int)$idPessoa]);
        if (!$contato->fetchColumn()) {
            return false;
        }
    }

    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));
    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $atualizar = $dbh->prepare("
        UPDATE contrato_outsourcing_equipamentos equipamento
        JOIN contrato ON contrato.id = equipamento.contrato
        SET equipamento.contato = ?
        WHERE equipamento.id = ?
          AND contrato.id_pessoa = ?
          AND contrato.id_tipo = ?
          AND contrato.status = ?
          AND equipamento.status = ?
          AND equipamento.contrato IN ($marcadores)
    ");
    $atualizar->execute(array_merge(
        [$idContato === null ? null : (int)$idContato, (int)$idEquipamento, (int)$idPessoa,
            CONTRATO_HELPDESK_ID, CONTRATO_STATUS_ATIVO, EQP_STATUS_ATIVO],
        $idsContratos
    ));

    return $atualizar->rowCount() > 0 || (string)($equipamento['contato'] ?? '') === (string)($idContato ?? '');
}

function reativarEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));
    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return false;
    }

    $dbh = $dbh ?: getConexao();
    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $atualizar = $dbh->prepare("
        UPDATE contrato_outsourcing_equipamentos equipamento
        JOIN contrato ON contrato.id = equipamento.contrato
        SET equipamento.status = ?
        WHERE equipamento.id = ?
          AND equipamento.status = ?
          AND contrato.id_pessoa = ?
          AND contrato.id_tipo = ?
          AND contrato.status = ?
          AND contrato.id IN ($marcadores)
    ");
    $atualizar->execute(array_merge(
        [EQP_STATUS_ATIVO, (int)$idEquipamento, EQP_STATUS_INATIVO, (int)$idPessoa,
            CONTRATO_HELPDESK_ID, CONTRATO_STATUS_ATIVO],
        $idsContratos
    ));

    return $atualizar->rowCount() === 1;
}

function criarSolicitacaoDesativacaoEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));
    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return null;
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }
    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $dbh->beginTransaction();
    try {
        $consulta = $dbh->prepare("
            SELECT equipamento.id, equipamento.contrato AS id_contrato,
                   equipamento.contato AS id_contato, equipamento.usuario,
                   equipamento.codigo, equipamento.sn, equipamento.descricao AS nome_rede,
                   equipamento.modelo, equipamento.data_ativado, equipamento.valor_equipamento,
                   tipo.tipo AS nome_equipamento,
                   contato.nome AS nome_contato, contato.email AS email_contato,
                   contrato.id_pessoa AS id_pessoa_empresa,
                   contrato.vendedor AS id_gerente_contas,
                   contrato.email_gerente AS email_gerente,
                   outsourcing.nome AS nome_contrato_outsourcing,
                   tipo_contrato.descricao AS nome_tipo_contrato_outsourcing
            FROM contrato_outsourcing_equipamentos equipamento
            JOIN contrato
              ON contrato.id = equipamento.contrato
            LEFT JOIN contrato_outsourcing outsourcing
              ON outsourcing.id_contrato = contrato.id
            LEFT JOIN contrato_outsourcing_equipamentos_tipo tipo
              ON tipo.id = equipamento.tipo
            LEFT JOIN contrato_outsourcing_equipamentos_contrato_tipo tipo_contrato
              ON tipo_contrato.id = equipamento.id_tipo_contrato
            LEFT JOIN pessoa_juridica_contatos contato
              ON contato.id = equipamento.contato
            WHERE equipamento.id = ?
              AND contrato.id_pessoa = ?
              AND contrato.id_tipo = ?
              AND contrato.status = ?
              AND contrato.id IN ($marcadores)
              AND equipamento.status = ?
            LIMIT 1
            FOR UPDATE
        ");
        $consulta->execute(array_merge(
            [(int)$idEquipamento, (int)$idPessoa, CONTRATO_HELPDESK_ID, CONTRATO_STATUS_ATIVO],
            $idsContratos,
            [EQP_STATUS_ATIVO]
        ));
        $equipamento = $consulta->fetch(PDO::FETCH_ASSOC);
        if (!$equipamento || !filter_var(trim((string)$equipamento['email_gerente']), FILTER_VALIDATE_EMAIL)) {
            $dbh->rollBack();
            return null;
        }

        $pendente = $dbh->prepare("
            SELECT id
            FROM contrato_outsourcing_equipamento_desativacao
            WHERE id_equipamento = ? AND data_avaliacao IS NULL
            LIMIT 1
        ");
        $pendente->execute([(int)$idEquipamento]);
        if ($pendente->fetchColumn()) {
            $dbh->rollBack();
            return ['pendente' => true];
        }

        $token = bin2hex(random_bytes(32));
        $inserir = $dbh->prepare("
            INSERT INTO contrato_outsourcing_equipamento_desativacao (
                id_equipamento, id_contrato, id_chamado, id_contato_equipamento,
                id_pessoa_empresa, id_gerente_contas, data_ativacao_equipamento,
                data_solicitacao, nome_contrato_outsourcing, nome_tipo_contrato_outsourcing,
                codigo_etiqueta, nome_rede, nome_equipamento, modelo_equipamento,
                valor_equipamento, nome_contato, email_contato, nome_gerente_contas,
                email_gerente_contas, motivacao, token_avaliacao, data_avaliacao, aprovado
            ) VALUES (
                ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, 0
            )
        ");
        $inserir->execute([
            (int)$equipamento['id'],
            (int)$equipamento['id_contrato'],
            !empty($equipamento['id_contato']) ? (int)$equipamento['id_contato'] : null,
            (int)$equipamento['id_pessoa_empresa'],
            !empty($equipamento['id_gerente_contas']) ? (int)$equipamento['id_gerente_contas'] : null,
            $equipamento['data_ativado'] ?: null,
            date('Y-m-d H:i:s'),
            $equipamento['nome_contrato_outsourcing'] ?? '',
            $equipamento['nome_tipo_contrato_outsourcing'] ?? '',
            $equipamento['codigo'] ?? '',
            $equipamento['nome_rede'] ?? '',
            $equipamento['nome_equipamento'] ?? '',
            $equipamento['modelo'] ?? '',
            $equipamento['valor_equipamento'] ?? 0,
            $equipamento['nome_contato'] ?? ($equipamento['usuario'] ?? ''),
            $equipamento['email_contato'] ?? '',
            'Gerente do contrato',
            trim((string)$equipamento['email_gerente']),
            'Solicitação de desativação enviada pelo portal do cliente.',
            $token
        ]);
        $dbh->commit();

        return [
            'pendente' => false,
            'token' => $token,
            'email_gerente' => trim((string)$equipamento['email_gerente']),
            'equipamento' => $equipamento
        ];
    } catch (Throwable $erro) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        throw $erro;
    }
}

function cancelarSolicitacaoDesativacaoEquipamentoCliente($token, $dbh = null)
{
    $dbh = $dbh ?: getConexao();
    $consulta = $dbh->prepare("
        DELETE FROM contrato_outsourcing_equipamento_desativacao
        WHERE token_avaliacao = ? AND data_avaliacao IS NULL
    ");
    $consulta->execute([(string)$token]);
    return $consulta->rowCount() === 1;
}

function obterSolicitacaoDesativacaoEquipamentoCliente($token, $dbh = null)
{
    if (!is_string($token) || !preg_match('/\\A[a-f0-9]{64}\\z/i', $token)) {
        return null;
    }

    $dbh = $dbh ?: getConexao();
    $consulta = $dbh->prepare("
        SELECT id, id_equipamento, id_contrato, codigo_etiqueta, nome_contrato_outsourcing,
               nome_tipo_contrato_outsourcing, nome_equipamento, nome_rede,
               modelo_equipamento, nome_contato, email_gerente_contas,
               token_avaliacao, data_solicitacao, data_avaliacao, aprovado
        FROM contrato_outsourcing_equipamento_desativacao
        WHERE token_avaliacao = ?
        LIMIT 1
    ");
    $consulta->execute([$token]);
    return $consulta->fetch(PDO::FETCH_ASSOC) ?: null;
}

function resolverSolicitacaoDesativacaoEquipamentoCliente($token, $aprovado, $dbh = null)
{
    if (!is_string($token) || !preg_match('/\\A[a-f0-9]{64}\\z/i', $token) || !is_bool($aprovado)) {
        return false;
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }
    $dbh->beginTransaction();
    try {
        $consulta = $dbh->prepare("
            SELECT id, id_equipamento
            FROM contrato_outsourcing_equipamento_desativacao
            WHERE token_avaliacao = ? AND data_avaliacao IS NULL
            LIMIT 1
            FOR UPDATE
        ");
        $consulta->execute([$token]);
        $solicitacao = $consulta->fetch(PDO::FETCH_ASSOC);
        if (!$solicitacao) {
            $dbh->rollBack();
            return false;
        }

        if ($aprovado) {
            $atualizarEquipamento = $dbh->prepare("
                UPDATE contrato_outsourcing_equipamentos
                SET status = ?
                WHERE id = ? AND status = ?
            ");
            $atualizarEquipamento->execute([
                EQP_STATUS_INATIVO,
                (int)$solicitacao['id_equipamento'],
                EQP_STATUS_ATIVO
            ]);
            if ($atualizarEquipamento->rowCount() !== 1) {
                $dbh->rollBack();
                return false;
            }
        }

        $atualizarSolicitacao = $dbh->prepare("
            UPDATE contrato_outsourcing_equipamento_desativacao
            SET aprovado = ?, data_avaliacao = ?
            WHERE id = ? AND data_avaliacao IS NULL
        ");
        $atualizarSolicitacao->execute([
            $aprovado ? 1 : 0,
            date('Y-m-d H:i:s'),
            (int)$solicitacao['id']
        ]);
        if ($atualizarSolicitacao->rowCount() !== 1) {
            $dbh->rollBack();
            return false;
        }

        $dbh->commit();
        return true;
    } catch (Throwable $erro) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        throw $erro;
    }
}

function obterContratosLocacaoCliente($idPessoa, $contratos, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));

    if (!$idPessoa || !$idsContratos) {
        return [];
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }

    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $consulta = $dbh->prepare("
        SELECT id
        FROM contrato
        WHERE id_pessoa = ?
          AND id_tipo = ?
          AND status = ?
          AND id IN ($marcadores)
        ORDER BY id
    ");
    $consulta->execute(array_merge(
        [(int)$idPessoa, CONTRATO_LOCACAO_ID, CONTRATO_STATUS_ATIVO],
        $idsContratos
    ));

    return array_map('intval', $consulta->fetchAll(PDO::FETCH_COLUMN));
}

function obterEquipamentosLocacaoCliente($idPessoa, $contratos, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static function ($id) {
            return $id > 0;
        }
    )));

    if (!$idPessoa || !$idsContratos) {
        return [];
    }

    $dbh = $dbh ?: getConexao();
    if (!$dbh instanceof PDO) {
        throw new RuntimeException('Não foi possível conectar ao banco de dados.');
    }

    $marcadores = implode(', ', array_fill(0, count($idsContratos), '?'));
    $consulta = $dbh->prepare("
        SELECT locado.id AS id_locacao,
               contrato.id AS id_contrato,
               equipamento.descricao,
               equipamento.patrimonio,
               equipamento.status,
               contato.nome AS nome_contato,
               contato.email,
               contato.cel AS telefone
        FROM contrato_locacao_equipamentos_locados locado
        JOIN contrato
          ON contrato.id = locado.id_contrato
        JOIN contrato_locacao_equipamentos equipamento
          ON equipamento.id = locado.id_equipamento
        LEFT JOIN pessoa_juridica_contatos contato
          ON contato.id = locado.id_contato
        WHERE contrato.id_pessoa = ?
          AND contrato.id_tipo = ?
          AND contrato.status = ?
          AND contrato.id IN ($marcadores)
          AND equipamento.status = 1
        ORDER BY contrato.id, equipamento.descricao
    ");
    $consulta->execute(array_merge(
        [(int)$idPessoa, CONTRATO_LOCACAO_ID, CONTRATO_STATUS_ATIVO],
        $idsContratos
    ));

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}
