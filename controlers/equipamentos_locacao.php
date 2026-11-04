<?php
require_once __DIR__ . '/../models/equipamentos_cliente.php';

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: /login');
    exit;
}

$dbh = null; // dados via API do ERP (models/erp_api.php)
$contratosLocacao = obterContratosLocacaoCliente(
    $_SESSION['id_pessoa'],
    $_SESSION['contratos'],
    $dbh
);

if (!$contratosLocacao) {
    http_response_code(403);
    include __DIR__ . '/../views/404.php';
    exit;
}

$menu = 'Locação';
$pagina = 'Ativos';
$equipamentos = obterEquipamentosLocacaoCliente(
    $_SESSION['id_pessoa'],
    $contratosLocacao,
    $dbh
);

include __DIR__ . '/../views/equipamentos_locacao.php';
