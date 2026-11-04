<?php

include_once 'models/usuario.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Cookie de sessão só por HTTPS, inacessível ao JavaScript e não enviado por outros sites.
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Limite de tentativas de login por IP: 10 falhas em 15 minutos bloqueiam por 15 minutos.
 * Guarda os contadores em arquivo fora da pasta pública (pasta temporária do sistema).
 */
function loginArquivoTentativas() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'novacentral_login_' . sha1($ip);
}

function loginBloqueado() {
    $dados = @json_decode((string)@file_get_contents(loginArquivoTentativas()), true);
    return is_array($dados) && ($dados['falhas'] ?? 0) >= 10 && time() - ($dados['inicio'] ?? 0) < 900;
}

function loginRegistrarFalha() {
    $arquivo = loginArquivoTentativas();
    $dados = @json_decode((string)@file_get_contents($arquivo), true);
    if (!is_array($dados) || time() - ($dados['inicio'] ?? 0) >= 900) {
        $dados = ['inicio' => time(), 'falhas' => 0];
    }
    $dados['falhas']++;
    @file_put_contents($arquivo, json_encode($dados), LOCK_EX);
}

if (isset($_POST['acao']) && $_POST['acao'] == 'logar') {
    if (loginBloqueado()) {
        header('location:login?erro=bloqueado');
        exit;
    }

    // Credenciais exatamente como digitadas (htmlspecialchars alterava senhas com & < > ").
    $usuario = trim((string)($_POST['usuario'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');

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
        // O detalhe técnico fica só no log (pode conter endereços internos).
        header('location:login?erro=api');
        exit;
    }

    if (sizeof($res)) {
        // Novo id de sessão no login (evita fixação de sessão).
        session_regenerate_id(true);
        @unlink(loginArquivoTentativas());
        $_SESSION['id_pessoa'] = $idPessoa;
        $_SESSION['nome_cliente'] = $nomeCliente;
        $_SESSION['contratos'] = $contratosUsuario;
        //$_SESSION['nome_cliente'] = obterNomeCliente($_SESSION['contratos'][0]);
        header('location:dashboard');
        exit;
    } else {
        loginRegistrarFalha();
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
