<?php

require_once __DIR__ . '/erp_api.php';
require_once __DIR__ . '/equipamentoConstantes.php';

// O parâmetro $dbh das funções abaixo é mantido só por compatibilidade com os controllers; os dados vêm da API do ERP.

// Um contrato só dá acesso à documentação se estiver ativo e pertencer ao usuário.
function obterContratoHelpdesk($contratos, $dbh = null)
{
    $ids = array_values(array_unique(array_filter(array_map('intval', (array)$contratos), static fn($id) => $id > 0)));
    if (!$ids) {
        return null;
    }
    $id = erp_api('documentacao.contrato_helpdesk', ['contratos' => $ids]);
    return $id === null ? null : (int)$id;
}

function documentacaoSecao($secao, $idContrato)
{
    return erp_api('documentacao.secao', ['secao' => $secao, 'id_contrato' => (int)$idContrato]) ?: [];
}

function obterDadosEmpresa($idPessoa, $dbh = null)
{
    return erp_api('documentacao.empresa', ['id_pessoa' => (int)$idPessoa]) ?: [];
}

function obterContratoOutsourcingPorIdContrato($idContrato, $dbh = null)
{
    return documentacaoSecao('outsourcing', $idContrato);
}

function obterEquipamentosDocumentacao($idContrato, $dbh = null)
{
    $porCategoria = array();
    // Converte os IDs reais dos tipos cadastrados nas categorias usadas pelo PDF.
    $categoriasPorTipo = array(
        1 => EQP_CATEGORIA_DESKTOP_WORKSTATION,
        2 => EQP_CATEGORIA_NOTEBOOK,
        3 => EQP_CATEGORIA_NOTEBOOK,
        4 => EQP_CATEGORIA_SERVIDOR_FISICO,
        9 => EQP_CATEGORIA_APS,
        11 => EQP_CATEGORIA_STORAGE,
        13 => EQP_CATEGORIA_NOBREAK,
        19 => EQP_CATEGORIA_SERVIDOR_VIRTUAL,
        20 => EQP_CATEGORIA_SERVIDOR_VIRTUAL,
        25 => EQP_CATEGORIA_SERVIDOR_NUVEM
    );

    foreach (documentacaoSecao('equipamentos', $idContrato) as $equipamento) {
        $tipoId = (int)$equipamento['tipo_id'];
        // Tipos sem mapeamento ficam em "Outros", evitando excluí-los da documentação.
        $categoria = $categoriasPorTipo[$tipoId] ?? EQP_CATEGORIA_OUTROS;
        $equipamento['categoria'] = $categoria;
        unset($equipamento['tipo_id']);
        $porCategoria[$equipamento['status']][$categoria][] = $equipamento;
    }

    return $porCategoria;
}

function obterIntroducao($idContrato, $dbh = null)
{
    return documentacaoSecao('introducao', $idContrato);
}

function obterRede($idContrato, $dbh = null)
{
    return documentacaoSecao('rede', $idContrato);
}

function obterRede_ipv4_exclusao($idContrato, $dbh = null)
{
    return documentacaoSecao('rede_exclusao_ipv4', $idContrato);
}

function obterRede_ssid($idContrato, $dbh = null)
{
    return documentacaoSecao('rede_wifi', $idContrato);
}

function obterLicencasPorCategorias($idContrato, $categorias, $dbh = null)
{
    $categorias = array_values(array_unique(array_filter(array_map('intval', (array)$categorias), static fn($categoria) => $categoria > 0)));
    if (!$categorias) {
        return array();
    }

    $licencas = erp_api('documentacao.licencas', ['id_contrato' => (int)$idContrato, 'categorias' => $categorias]) ?: [];
    $porCategoria = array();
    foreach ($licencas as $licenca) {
        $porCategoria[(int)$licenca['categoria_id']][] = $licenca;
    }
    return $porCategoria;
}

function obterBackup($idContrato, $dbh = null)
{
    return documentacaoSecao('backup', $idContrato);
}

function obterVolumetria($idContrato, $dbh = null)
{
    return documentacaoSecao('volumetria', $idContrato);
}

function obterContratosInternet($idContrato, $dbh = null)
{
    return documentacaoSecao('contratos_internet', $idContrato);
}

function obterContratosSistemas($idContrato, $dbh = null)
{
    return documentacaoSecao('contratos_sistemas', $idContrato);
}

function obterInventarioDesktop($idContrato, $dbh = null)
{
    return documentacaoSecao('inventario_desktop', $idContrato);
}

function obterInventarioNotebook($idContrato, $dbh = null)
{
    return documentacaoSecao('inventario_notebook', $idContrato);
}

function obterInventarioSeguranca($idContrato, $dbh = null)
{
    return documentacaoSecao('inventario_seguranca', $idContrato);
}

function obterNotaSeguranca($idContrato, $dbh = null)
{
    return documentacaoSecao('nota_seguranca', $idContrato);
}
