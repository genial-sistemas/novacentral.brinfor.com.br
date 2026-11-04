<?php
require_once __DIR__ . '/erp_api.php';
require_once __DIR__ . '/equipamentoConstantes.php';

// O parâmetro $dbh das funções abaixo é mantido só por compatibilidade com os controllers; os dados vêm da API do ERP.

function equipamentosIdsContratos($contratos)
{
    return array_values(array_unique(array_filter(
        array_map('intval', (array)$contratos),
        static fn($id) => $id > 0
    )));
}

function obterEquipamentosCliente($idPessoa, $contratos, $status, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idPessoa || !$idsContratos || !in_array($status, [EQP_STATUS_ATIVO, EQP_STATUS_INATIVO], true)) {
        return [];
    }

    return erp_api('equipamento.listar', [
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
        'status' => $status,
    ]) ?: [];
}

function obterEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return null;
    }

    return erp_api('equipamento.obter', [
        'id' => (int)$idEquipamento,
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
    ]) ?: null;
}

function obterContatosAtivosEquipamentos($idPessoa, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    if (!$idPessoa) {
        return [];
    }
    return erp_api('contato.listar_ativos_simples', ['id_cliente' => (int)$idPessoa]) ?: [];
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
    $idContato = $idContato === null ? null : filter_var($idContato, FILTER_VALIDATE_INT);
    if ($idContato === false || ($idContato !== null && $idContato <= 0)) {
        return false;
    }
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return false;
    }

    return (bool)erp_api('equipamento.atualizar_contato', [
        'id' => (int)$idEquipamento,
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
        'id_contato' => $idContato,
    ]);
}

function reativarEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return false;
    }

    return (bool)erp_api('equipamento.reativar', [
        'id' => (int)$idEquipamento,
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
    ]);
}

/**
 * Cria a solicitação de desativação. Retorna null (não permitido), ['pendente' => true]
 * ou ['pendente' => false, 'token', 'email_gerente', 'equipamento'].
 */
function criarSolicitacaoDesativacaoEquipamentoHelpdeskCliente($idEquipamento, $idPessoa, $contratos, $dbh = null)
{
    $idEquipamento = filter_var($idEquipamento, FILTER_VALIDATE_INT);
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idEquipamento || !$idPessoa || !$idsContratos) {
        return null;
    }

    return erp_api('equipamento.solicitar_desativacao', [
        'id' => (int)$idEquipamento,
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
    ]) ?: null;
}

function cancelarSolicitacaoDesativacaoEquipamentoCliente($token, $dbh = null)
{
    return (bool)erp_api('desativacao.cancelar', ['token' => (string)$token]);
}

function obterSolicitacaoDesativacaoEquipamentoCliente($token, $dbh = null)
{
    if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/i', $token)) {
        return null;
    }
    return erp_api('desativacao.obter', ['token' => $token]) ?: null;
}

function resolverSolicitacaoDesativacaoEquipamentoCliente($token, $aprovado, $dbh = null)
{
    if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/i', $token) || !is_bool($aprovado)) {
        return false;
    }
    return (bool)erp_api('desativacao.resolver', ['token' => $token, 'aprovado' => $aprovado]);
}

function obterContratosLocacaoCliente($idPessoa, $contratos, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idPessoa || !$idsContratos) {
        return [];
    }
    return array_map('intval', erp_api('locacao.contratos', [
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
    ]) ?: []);
}

function obterEquipamentosLocacaoCliente($idPessoa, $contratos, $dbh = null)
{
    $idPessoa = filter_var($idPessoa, FILTER_VALIDATE_INT);
    $idsContratos = equipamentosIdsContratos($contratos);
    if (!$idPessoa || !$idsContratos) {
        return [];
    }
    return erp_api('locacao.equipamentos', [
        'id_pessoa' => (int)$idPessoa,
        'contratos' => $idsContratos,
    ]) ?: [];
}
