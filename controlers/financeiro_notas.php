<?php
require_once __DIR__ . '/../models/financeiro.php';

$menu   = 'Financeiro';
$pagina = 'Notas Fiscais';

$itens_por_pagina = 20;
$pagina_atual = max(1, (int)filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT));
$erro_notas = '';

try {
    $resultado = obterNotasFiscais($_SESSION['id_pessoa'], $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
} catch (ErpApiErro $e) {
    error_log('Notas fiscais: ' . $e->getMessage());
    $resultado = ['total' => 0, 'itens' => []];
    $erro_notas = 'Não foi possível carregar as notas fiscais agora. Tente novamente em instantes.';
}

$notas = $resultado['itens'];
$total_notas = $resultado['total'];
$total_paginas = max(1, (int)ceil($total_notas / $itens_por_pagina));
$erro_arquivo = ($_GET['erro'] ?? '') === 'arquivo';

include 'views/financeiro_notas.php';
