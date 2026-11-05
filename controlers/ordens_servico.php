<?php
// Ordens de serviço do cliente logado: lista (abertas/fechadas, 20 por página) e detalhe (?id=).
require_once __DIR__ . '/../models/ordem_servico.php';

$menu = 'Ordens de Serviço';
$idPessoa = (int)($_SESSION['id_pessoa'] ?? 0);
$filtro = ($_GET['filtro'] ?? '') === 'fechadas' ? 'fechadas' : 'abertas';
$pagina = $filtro === 'fechadas' ? 'OS Fechadas' : 'OS Abertas';

$idOs = (int)($_GET['id'] ?? 0);
if ($idOs > 0) {
    $detalhe = obterOrdemServicoDetalhe($idOs, $idPessoa);
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
$resultado = obterOrdensServico($idPessoa, $filtro, $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
$total_os = (int)$resultado['total'];
$total_paginas = max(1, (int)ceil($total_os / $itens_por_pagina));
if ($pagina_atual > $total_paginas) {
    $pagina_atual = $total_paginas;
    $resultado = obterOrdensServico($idPessoa, $filtro, $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
}
$ordens = $resultado['itens'];

include 'views/ordens_servico.php';
