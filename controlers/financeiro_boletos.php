<?php
require_once __DIR__ . '/../models/financeiro.php';

$menu   = 'Financeiro';
$pagina = 'Boletos';

$itens_por_pagina = 20;
$pagina_atual = max(1, (int)filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT));
$erro_boletos = '';

try {
    $resultado = obterBoletos($_SESSION['id_pessoa'], $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
} catch (ErpApiErro $e) {
    error_log('Boletos: ' . $e->getMessage());
    $resultado = ['total' => 0, 'itens' => []];
    $erro_boletos = 'Não foi possível carregar os boletos agora. Tente novamente em instantes.';
}

$boletos = $resultado['itens'];
$total_boletos = $resultado['total'];
$total_paginas = max(1, (int)ceil($total_boletos / $itens_por_pagina));
$erro_arquivo = ($_GET['erro'] ?? '') === 'arquivo';

include 'views/financeiro_boletos.php';
