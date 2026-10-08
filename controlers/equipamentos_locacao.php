<?php
require_once __DIR__ . '/../models/conexao.php';
require_once __DIR__ . '/../models/equipamentos_cliente.php';

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: /login');
    exit;
}

$dbh = getConexao();
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

$menu = 'Equipamentos';
$pagina = 'Locação';
$equipamentos = obterEquipamentosLocacaoCliente(
    $_SESSION['id_pessoa'],
    $contratosLocacao,
    $dbh
);

include __DIR__ . '/../views/equipamentos_locacao.php';
