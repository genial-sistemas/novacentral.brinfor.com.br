<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/equipamentoConstantes.php';

// Um contrato só dá acesso à documentação se estiver ativo e pertencer ao usuário.
function obterContratoHelpdesk($contratos, $dbh = null)
{
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$contratos), static function ($id) {
        return $id > 0;
    })));

    if (!$ids) {
        return null;
    }

    $dbh = $dbh ?: getConexao();
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $consulta = $dbh->prepare("
        SELECT c.id
        FROM contrato c
        WHERE c.id IN ($marcadores)
          AND c.id_tipo = " . CONTRATO_HELPDESK_ID . "
          AND c.status = " . CONTRATO_STATUS_ATIVO . "
        ORDER BY c.id
        LIMIT 1
    ");
    $consulta->execute($ids);
    $id = $consulta->fetchColumn();

    return $id === false ? null : (int)$id;
}

function obterDadosEmpresa($idPessoa, $dbh = null)
{
    $dbh = $dbh ?: getConexao();
    $consulta = $dbh->prepare("
        SELECT p.nome_pessoa, j.cnpj, j.razao_social,
               e.rua, e.numero, e.complemento, e.bairro, e.cep, e.cidade, e.estado
        FROM pessoa p
        JOIN pessoa_juridica j ON j.id_pessoa = p.id
        JOIN pessoa_endereco e ON e.id_pessoa = p.id AND e.principal = 's'
        WHERE p.id = :id_pessoa
        LIMIT 1
    ");
    $consulta->execute(array(':id_pessoa' => (int)$idPessoa));
    return $consulta->fetch(PDO::FETCH_ASSOC) ?: array();
}

function obterContratoOutsourcingPorIdContrato($idContrato, $dbh = null)
{
    $dbh = $dbh ?: getConexao();
    $consulta = $dbh->prepare("
        SELECT o.*, tipo.tipo AS tipo
        FROM contrato_outsourcing o
        JOIN contrato c ON c.id = o.id_contrato
        LEFT JOIN contrato_outsourcing_tipo tipo ON tipo.id = o.id_tipo_contrato
        WHERE o.id_contrato = :id_contrato
          AND c.id_tipo = " . CONTRATO_HELPDESK_ID . "
          AND c.status = " . CONTRATO_STATUS_ATIVO . "
        LIMIT 1
    ");
    $consulta->execute(array(':id_contrato' => (int)$idContrato));
    return $consulta->fetch(PDO::FETCH_ASSOC) ?: array();
}

function obterEquipamentosDocumentacao($idContrato, $dbh = null)
{
    $dbh = $dbh ?: getConexao();
    $consulta = $dbh->prepare("
        SELECT e.codigo, t.tipo AS equipamento, e.descricao, e.modelo, p.nome,
               e.tipo AS categoria, e.status
        FROM contrato_outsourcing_equipamentos e
        LEFT JOIN contrato_outsourcing_equipamentos_tipo t ON t.id = e.tipo
        LEFT JOIN pessoa_juridica_contatos p ON p.id = e.contato
        WHERE e.contrato = :id_contrato
          AND e.tipo IN (1, 2, 3, 4, 5, 6, 7, 9, 25)
        ORDER BY e.tipo, e.status, e.codigo
    ");
    $consulta->execute(array(':id_contrato' => (int)$idContrato));
    $porCategoria = array();

    foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $equipamento) {
        $porCategoria[$equipamento['status']][(int)$equipamento['categoria']][] = $equipamento;
    }

    return $porCategoria;
}

function documentacaoBuscar($sql, $parametros, $dbh, $varios = true)
{
    $consulta = $dbh->prepare($sql);
    $consulta->execute($parametros);
    return $varios
        ? $consulta->fetchAll(PDO::FETCH_ASSOC)
        : ($consulta->fetch(PDO::FETCH_ASSOC) ?: array());
}

function obterIntroducao($idContrato, $dbh = null)
{
    $dbh = $dbh ?: getConexao();
    return documentacaoBuscar(
        'SELECT * FROM contrato_outsourcing_documentacao WHERE id_contrato = ?',
        array((int)$idContrato),
        $dbh,
        false
    );
}

function obterRede($idContrato, $dbh = null)
{
    $dbh = $dbh ?: getConexao();
    return documentacaoBuscar(
        'SELECT * FROM contrato_outsourcing_documentacao_rede WHERE id_contrato = ?',
        array((int)$idContrato),
        $dbh,
        false
    );
}

function obterRede_ipv4_exclusao($idContrato, $dbh = null)
{
    return documentacaoBuscar(
        'SELECT * FROM contrato_outsourcing_documentacao_rede_exclusao_ipv4 WHERE id_contrato = ?',
        array((int)$idContrato),
        $dbh ?: getConexao()
    );
}

function obterRede_ssid($idContrato, $dbh = null)
{
    return documentacaoBuscar(
        'SELECT * FROM contrato_outsourcing_documentacao_rede_wifi WHERE id_contrato = ?',
        array((int)$idContrato),
        $dbh ?: getConexao()
    );
}

function obterLicencas($idContrato, $categoria, $dbh = null)
{
    return documentacaoBuscar("
        SELECT c.id, c.quantidade, c.vencimento, l.nome, ct.categoria, t.tipo,
               (SELECT COUNT(*) FROM contrato_outsourcing_equipamentos_licenca el
                WHERE el.id_licenca = c.id) AS soma
        FROM contrato_outsourcing_licenca_cliente c
        JOIN contrato_outsourcing_licencas l ON l.id = c.id_licenca
        JOIN contrato_outsourcing_licencas_categorias ct ON ct.id = l.categoria
        JOIN contrato_outsourcing_licencas_tipo t ON t.id = c.id_tipo
        WHERE c.id_contrato = ? AND l.categoria = ?
        ORDER BY l.nome
    ", array((int)$idContrato, (int)$categoria), $dbh ?: getConexao());
}

function obterLicencasPorCategorias($idContrato, $categorias, $dbh = null)
{
    $categorias = array_values(array_unique(array_filter(array_map('intval', (array)$categorias), static function ($categoria) {
        return $categoria > 0;
    })));
    if (!$categorias) {
        return array();
    }

    $marcadores = implode(', ', array_fill(0, count($categorias), '?'));
    $licencas = documentacaoBuscar("
        SELECT c.id, c.quantidade, c.vencimento, l.nome, ct.categoria, t.tipo,
               (SELECT COUNT(*) FROM contrato_outsourcing_equipamentos_licenca el
                WHERE el.id_licenca = c.id) AS soma,
               l.categoria AS categoria_id
        FROM contrato_outsourcing_licenca_cliente c
        JOIN contrato_outsourcing_licencas l ON l.id = c.id_licenca
        JOIN contrato_outsourcing_licencas_categorias ct ON ct.id = l.categoria
        JOIN contrato_outsourcing_licencas_tipo t ON t.id = c.id_tipo
        WHERE c.id_contrato = ? AND l.categoria IN ($marcadores)
        ORDER BY l.nome
    ", array_merge(array((int)$idContrato), $categorias), $dbh ?: getConexao());

    $porCategoria = array();
    foreach ($licencas as $licenca) {
        $porCategoria[(int)$licenca['categoria_id']][] = $licenca;
    }
    return $porCategoria;
}

function obterBackup($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT b.id, b.titulo, loc.descricao AS local, sof.descricao AS software,
               rec.descricao AS recorrencia, b.horario, des.descricao AS destino,
               b.retencao, b.descricao
        FROM contrato_outsourcing_documentacao_backup b
        JOIN contrato_outsourcing_documentacao_backup_destino des ON des.id = b.destino
        JOIN contrato_outsourcing_documentacao_backup_recorrencia rec ON rec.id = b.recorrencia
        JOIN contrato_outsourcing_documentacao_backup_software sof ON sof.id = b.software
        JOIN contrato_outsourcing_documentacao_backup_local loc ON loc.id = b.local
        WHERE b.status = 1 AND b.id_contrato = ?
        ORDER BY b.titulo
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterVolumetria($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT id, titulo, volumetria_usada, volumetria_existente,
               volumetria_existente - volumetria_usada AS volumetria_disponivel
        FROM contrato_outsourcing_documentacao_backup
        WHERE status = 1 AND id_contrato = ?
        ORDER BY titulo
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterContratosInternet($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT c.id, c.titulo, c.tipo, i.nome_empresa, i.codigo_contrato,
               i.telefone_suporte, t.descricao, i.upload, i.download, i.obs, i.valor_mensal
        FROM contrato_outsourcing_documentacao_contratos c
        JOIN contrato_outsourcing_documentacao_contratos_internet i ON i.id_doc_contrato = c.id
        JOIN contrato_outsourcing_documentacao_contratos_internet_tipo t ON t.id = i.tipo_internet
        WHERE c.status = 1 AND c.id_contrato = ?
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterContratosSistemas($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT c.id, c.titulo, c.tipo, i.nome_empresa, i.nome_sistema,
               i.contato_suporte, i.codigo_contrato, i.telefone_suporte,
               i.email_suporte, i.valor_mensal
        FROM contrato_outsourcing_documentacao_contratos c
        JOIN contrato_outsourcing_documentacao_contratos_sistemas i ON i.id_doc_contrato = c.id
        WHERE c.status = 1 AND c.id_contrato = ?
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterInventarioDesktop($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT e.id, e.descricao, c.cpu, c.memoria, c.hd
        FROM contrato_outsourcing_equipamentos e
        LEFT JOIN contrato_outsourcing_equipamentos_computador c ON c.codigo_equipamento = e.id
        WHERE e.status = 'a' AND e.contrato = ? AND e.tipo = 1
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterInventarioNotebook($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT e.id, e.descricao, c.cpu, c.memoria, c.hd
        FROM contrato_outsourcing_equipamentos e
        LEFT JOIN contrato_outsourcing_equipamentos_computador c ON c.codigo_equipamento = e.id
        WHERE e.status = 'a' AND e.contrato = ? AND e.tipo = 2
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterInventarioSeguranca($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT c.id AS item, s.descricao, c.descricao AS categoria, s.valor
        FROM contrato_outsourcing_documentacao_seguranca d
        JOIN contrato_outsourcing_documentacao_seguranca_servicos s ON s.id = d.id_servico
        JOIN contrato_outsourcing_documentacao_seguranca_servicos_categorias c ON c.id = s.id_categoria
        WHERE d.id_contrato = ?
        ORDER BY c.id
    ", array((int)$idContrato), $dbh ?: getConexao());
}

function obterNotaSeguranca($idContrato, $dbh = null)
{
    return documentacaoBuscar("
        SELECT SUM(s.valor) AS total
        FROM contrato_outsourcing_documentacao_seguranca d
        JOIN contrato_outsourcing_documentacao_seguranca_servicos s ON s.id = d.id_servico
        WHERE d.id_contrato = ?
    ", array((int)$idContrato), $dbh ?: getConexao(), false);
}
