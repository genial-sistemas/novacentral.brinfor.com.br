<?php
// Limpa os dados, expira o cookie da sessão e destrói a sessão no servidor.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $parametros['path'],
        'domain' => $parametros['domain'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
session_destroy();
header('location:login');
exit;
