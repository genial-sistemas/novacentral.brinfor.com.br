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
               equipamento.descricao,
               equipamento.modelo,
               equipamento.usuario,
               equipamento.status,
               tipo.tipo AS nome_tipo,
               contato.nome AS nome_contato
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

function desativarEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $equipamento = obterEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh);
    if (!$equipamento) {
        return false;
    }

    $dbh = $dbh ?: getConexao();
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
        SET equipamento.status = ?
        WHERE equipamento.id = ?
          AND equipamento.status = ?
          AND contrato.id_pessoa = ?
          AND contrato.id_tipo = ?
          AND contrato.status = ?
          AND contrato.id IN ($marcadores)
    ");
    $atualizar->execute(array_merge(
        [EQP_STATUS_INATIVO, (int)$idEquipamento, EQP_STATUS_ATIVO, (int)$idPessoa,
            CONTRATO_HELPDESK_ID, CONTRATO_STATUS_ATIVO],
        $idsContratos
    ));

    return $atualizar->rowCount() === 1;
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
