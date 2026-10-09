<?php
require_once __DIR__ . '/../models/conexao.php';
require_once __DIR__ . '/../models/documentacao_cliente.php';
require_once __DIR__ . '/../models/equipamentos_cliente.php';

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: /login');
    exit;
}

$dbh = getConexao();
if (obterContratoHelpdesk($_SESSION['contratos'], $dbh) === null) {
    http_response_code(403);
    include __DIR__ . '/../views/404.php';
    exit;
}

$menu = 'Equipamentos';
$pagina = 'Inativos';
$csrfToken = tokenCsrfEquipamentos();
$mensagemEquipamento = $_SESSION['mensagem_equipamentos'] ?? null;
unset($_SESSION['mensagem_equipamentos']);
$equipamentos = obterEquipamentosCliente(
    $_SESSION['id_pessoa'],
    $_SESSION['contratos'],
    EQP_STATUS_INATIVO,
    $dbh
);

include __DIR__ . '/../views/equipamentos_listar_inativos.php';