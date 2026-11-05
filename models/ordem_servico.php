<?php
require_once __DIR__ . '/erp_api.php';

/** Página de ordens de serviço do cliente logado: ['total' => n, 'itens' => [...]]. Filtro: abertas | fechadas. */
function obterOrdensServico($idPessoa, $filtro, $limite, $offset) {
    $retorno = erp_api('os.listar', [
        'id_pessoa' => (int)$idPessoa,
        'filtro' => $filtro === 'fechadas' ? 'fechadas' : 'abertas',
        'limite' => (int)$limite,
        'offset' => (int)$offset,
    ]);
    return is_array($retorno) ? $retorno + ['total' => 0, 'itens' => []] : ['total' => 0, 'itens' => []];
}

/** Detalhe de uma OS do cliente (null se não existir ou não for dele). */
function obterOrdemServicoDetalhe($id, $idPessoa) {
    try {
        $retorno = erp_api('os.detalhe', ['id' => (int)$id, 'id_pessoa' => (int)$idPessoa]);
        return is_array($retorno) && !empty($retorno['os']) ? $retorno : null;
    } catch (ErpApiErro $erro) {
        if (strpos($erro->getMessage(), 'não encontrada') !== false) {
            return null;
        }
        throw $erro;
    }
}
