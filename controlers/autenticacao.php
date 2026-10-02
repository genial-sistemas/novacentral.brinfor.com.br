<?php

include_once 'models/usuario.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_POST['acao']) && $_POST['acao'] == 'logar') {
    // Efetua o logon do usuário no sistema
    $usuario = htmlspecialchars($_POST['usuario']);
    $senha = htmlspecialchars($_POST['senha']);

    $res = obterUsuario($usuario, $senha);

    if (sizeof($res)) {
        $_SESSION['id_pessoa'] = $res[0]['id_pessoa'];
        $_SESSION['nome_cliente'] = obterNome2($_SESSION['id_pessoa']);
        $_SESSION['contratos'] = obterContratosUsuario($_SESSION['id_pessoa']);
        //$_SESSION['nome_cliente'] = obterNomeCliente($_SESSION['contratos'][0]);
        header('location:dashboard');
        exit;
    } else {
        unset($_SESSION['id_pessoa']);
        unset($_SESSION['contratos']);
        unset($_SESSION['nome_cliente']);
        header('location:login?erro=1');
        exit;
    }
} else {
    // Verifica se o usuário está logado
    if (!isset($_SESSION['id_pessoa'])) {
        $pagina = 'login';
    }
}
