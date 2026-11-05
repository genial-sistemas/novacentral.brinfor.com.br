<?php
// Ordens de serviço do cliente logado: lista (abertas/fechadas, 20 por página) e detalhe (?id=).
require_once __DIR__ . '/../models/ordem_servico.php';

$menu = 'Ordens de Serviço';
$idPessoa = (int)($_SESSION['id_pessoa'] ?? 0);
$filtro = ($_GET['filtro'] ?? '') === 'fechadas' ? 'fechadas' : 'abertas';
$pagina = $filtro === 'fechadas' ? 'OS Fechadas' : 'OS Abertas';

$idOs = (int)($_GET['id'] ?? 0);
$erro_os = null;
if ($idOs > 0) {
    try {
        $detalhe = obterOrdemServicoDetalhe($idOs, $idPessoa);
    } catch (ErpApiErro $erro) {
        error_log('Ordem de serviço ' . $idOs . ': ' . $erro->getMessage());
        $detalhe = null;
        $erro_os = 'Não foi possível carregar a ordem de serviço agora. Tente novamente em instantes.';
    }
    if ($erro_os !== null) {
        $ordens = [];
        $total_os = 0;
        $total_paginas = 1;
        $pagina_atual = 1;
        include 'views/ordens_servico.php';
        return;
    }
    if ($detalhe === null) {
        http_response_code(404);
        include 'views/404.php';
        exit;
    }
    // Volta para a lista de onde veio (aberta/fechada conforme o status da OS).
    $filtro = (int)$detalhe['os']['id_status'] >= 5 ? 'fechadas' : 'abertas';
    $pagina = $filtro === 'fechadas' ? 'OS Fechadas' : 'OS Abertas';
    $pagina_retorno = max(1, (int)($_GET['pagina'] ?? 1));
    include 'views/ordem_servico_detalhe.php';
    return;
}

$itens_por_pagina = 20;
$pagina_atual = max(1, (int)($_GET['pagina'] ?? 1));
try {
    $resultado = obterOrdensServico($idPessoa, $filtro, $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
} catch (ErpApiErro $erro) {
    // Ex.: API sem a ação os.listar (acoes/ordem_servico.php não publicado). Detalhe técnico só no log.
    error_log('Ordens de serviço: ' . $erro->getMessage());
    $resultado = ['total' => 0, 'itens' => []];
    $erro_os = 'Não foi possível carregar as ordens de serviço agora. Tente novamente em instantes.';
}
$total_os = (int)$resultado['total'];
$total_paginas = max(1, (int)ceil($total_os / $itens_por_pagina));
if ($pagina_atual > $total_paginas) {
    $pagina_atual = $total_paginas;
    $resultado = obterOrdensServico($idPessoa, $filtro, $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
}
$ordens = $resultado['itens'];

include 'views/ordens_servico.php';
