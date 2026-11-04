<?php

include_once 'models/usuario.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_POST['acao']) && $_POST['acao'] == 'logar') {
    // Efetua o logon do usuário no sistema
    $usuario = htmlspecialchars($_POST['usuario']);
    $senha = htmlspecialchars($_POST['senha']);

    try {
        $res = obterUsuario($usuario, $senha);
        if (sizeof($res)) {
            $idPessoa = $res[0]['id_pessoa'];
            $nomeCliente = obterNome2($idPessoa);
            $contratosUsuario = obterContratosUsuario($idPessoa);
        }
    } catch (Throwable $e) {
        // A API do ERP falhou: mostra o motivo (sem dados sensíveis) em vez de página em branco.
        error_log('Login: falha ao consultar a API do ERP: ' . get_class($e) . ': ' . $e->getMessage());
        $_SESSION['erro_api_login'] = $e instanceof ErpApiErro ? $e->getMessage() : 'erro interno (' . get_class($e) . ')';
        header('location:login?erro=api');
        exit;
    }

    if (sizeof($res)) {
        $_SESSION['id_pessoa'] = $idPessoa;
        $_SESSION['nome_cliente'] = $nomeCliente;
        $_SESSION['contratos'] = $contratosUsuario;
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
    $rotaPublica = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/') === 'equipamentos_desativacao_aprovar';
    if (!isset($_SESSION['id_pessoa']) && !$rotaPublica) {
        $pagina = 'login';
    }
}
