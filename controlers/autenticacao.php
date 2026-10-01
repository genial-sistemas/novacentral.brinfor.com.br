<?php

include_once 'models/usuario.php';

if (isset($_POST['acao']) && $_POST['acao'] == 'logar') {
    // Efetua o logon do usuário no sistema
    if(!isset($_SESSION)) session_start();

    $usuario = htmlspecialchars($_POST['usuario']);
    $senha = htmlspecialchars($_POST['senha']);

    $res = obterUsuario($usuario, $senha);

    if (sizeof($res)) {
        $_SESSION['id_pessoa'] = $res[0]['id_pessoa'];
        $_SESSION['nome_cliente'] = obterNome2($_SESSION['id_pessoa']);
        $_SESSION['contratos'] = obterContratosUsuario($_SESSION['id_pessoa']);
        //$_SESSION['nome_cliente'] = obterNomeCliente($_SESSION['contratos'][0]);
        header('location:dashboard');
    } else {
        unset($_SESSION['id_pessoa']);
        unset($_SESSION['contratos']);
        unset($_SESSION['nome_cliente']);
        $_SESSION['erro_login'] = true;
        header('location:login');
    }
} else {
    // Verifica se o usuário está logado
    if(!isset($_SESSION)) session_start();
    if (!isset($_SESSION['id_pessoa'])) {
        $pagina = 'login';
    }
}
