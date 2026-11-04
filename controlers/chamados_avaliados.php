<?php
// Chamados avaliados pelo cliente (pesquisa de satisfação), 20 por página.
require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Avaliados';

$contratos = array_map('intval', $_SESSION['contratos'] ?? []);
$itens_por_pagina = 20;
$pagina_atual = max(1, (int)($_GET['pagina'] ?? 1));

$resultado = obterChamadosAvaliados($contratos, $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
$total_chamados = (int)$resultado['total'];
$total_paginas = max(1, (int)ceil($total_chamados / $itens_por_pagina));
if ($pagina_atual > $total_paginas) {
    $pagina_atual = $total_paginas;
    $resultado = obterChamadosAvaliados($contratos, $itens_por_pagina, ($pagina_atual - 1) * $itens_por_pagina);
}
$chamados_avaliados = $resultado['itens'];

include 'views/chamados_avaliados.php';
