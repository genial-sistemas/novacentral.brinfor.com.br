<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Listar Abertos';

$contratos = array_map('intval', $_SESSION['contratos'] ?? []);
if (empty($_SESSION['csrf_chamados'])) {
    $_SESSION['csrf_chamados'] = bin2hex(random_bytes(32));
}
$token_chamados = $_SESSION['csrf_chamados'];

// Encerramento pelo usuário (POST com token; a API confere se o chamado é dos contratos da sessão).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'encerrar') {
    $id_encerrar = (int)($_POST['chamado_id'] ?? 0);
    $token_valido = hash_equals($token_chamados, (string)($_POST['token'] ?? ''));
    $ok = $token_valido && $id_encerrar > 0 && encerrarChamadoCliente($id_encerrar, $contratos);
    header('Location: /chamados_listar_abertos?' . ($ok ? 'encerrado=' . $id_encerrar : 'erro_encerrar=1'));
    exit;
}

$chamado_reaberto = ($_GET['reaberto'] ?? '') === '1';
$email_reabertura_falhou = ($_GET['email'] ?? '') === 'failed';
$chamado_encerrado = (int)($_GET['encerrado'] ?? 0);
$erro_encerrar = ($_GET['erro_encerrar'] ?? '') === '1';

$chamados_abertos = obterChamadosAbertosLista($contratos);

include 'views/chamados_listar_abertos.php';
