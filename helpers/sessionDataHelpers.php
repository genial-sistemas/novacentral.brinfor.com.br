<?php
require_once __DIR__ . '/typeHelpers.php';
require_once __DIR__ . '/loggerFunctions.php';
require_once __DIR__ . '/../models/pessoa.php';

function getCurrSessionUser() {
    $idPessoa = getSafeArrayKeyValue($_SESSION, 'id_pessoa');

    $pgData = isValidInteger($idPessoa)
        ? obterPessoaGenericaPorIdPessoa($idPessoa)
        : null;

    return $pgData && array_key_exists('nome', $pgData)
        ? $pgData
        : [ 'nome' => 'anonimo', 'email' => null, 'doc' => null];
}

/**
 * makeSessionKeyName function
 *
 * @param string $keyName
 * @param string $suffix
 * @param string $strCase
 * @return string|null
 */
function makeSessionKeyName(string $keyName, ?string $suffix, string $strCase='upper'): ?string
{

    if (!isValidString($keyName)) { return null; }

    $keyName = \trim($keyName);
    $nameOut = '';

    if (isValidString($suffix)) {
        $suffix = \trim($suffix);
        $nameOut = sprintf('%s_%s', $keyName, $suffix);
    }

    if (strtolower($strCase) == 'lower') {
        $nameOut = strtolower($nameOut);
    } else {
        $nameOut = strtoupper($nameOut);
    }

    return $nameOut;
}

function makeFormCSRF($formName='', $expireTime=0) {
    if (!isValidString($formName)) { return null; }

    if (!isValidInteger($expireTime)) {
        $expireTime = defined('MAKE_CSRF_EXPIRE_TIME') ? MAKE_CSRF_EXPIRE_TIME : ((3600 * 60) * 48); // 02 dias
    }

    $formHashKey = makeSessionKeyName($formName, 'HASH');
    $formHash = bin2hex(random_bytes(32));

    $_SESSION[$formHashKey] = $formHash;

    if ($expireTime > 0) {
        $formExpireKey = makeSessionKeyName($formName, 'EXPIRE_TIME');
        $_SESSION[$formExpireKey] = intval(time() + $expireTime);
    }

    return $formHash;
}

function clearFormCSRFData($formName='') {
    if (!isValidString($formName) ) { return; }

    $formHashKey = makeSessionKeyName($formName, 'HASH');
    $formExpireKey = makeSessionKeyName($formName, 'EXPIRE_TIME');

    if (array_key_exists($formHashKey, $_SESSION)) {
        unset($_SESSION[$formHashKey]);
    }

    if (array_key_exists($formExpireKey, $_SESSION)) {
        unset($_SESSION[$formExpireKey]);
    }
}

function isValidFormCSRF($formName='', $csrfHash='', bool $enableExpireTime = false) {
    if (!isValidString($formName) || !isValidString($csrfHash)) { return false; }

    $formHashKey = makeSessionKeyName($formName, 'HASH');
    $formExpireKey = makeSessionKeyName($formName, 'EXPIRE_TIME');

    if (!isValidString($_SESSION[$formHashKey])) { return false; }

    // verifica a validade do hash comparando com valor armazenado na sessao
    if (strval($_SESSION[$formHashKey]) != \trim($csrfHash)) {
        phpLogError(
            'Form CSRF hash inválido!',
            [
                'form_name' => $formName,
                'form_hash' => \trim($csrfHash),
                'session_form_hash_key' => $formHashKey,
                'session_hash' => $_SESSION[$formHashKey]
            ]
        );
        clearFormCSRFData($formName);
        return false;
    }

    if (! $enableExpireTime || ! isValidInteger($_SESSION[$formExpireKey])) { return true; }

    // verifica se o tempo para uso do hash expirou
    if (time() > intval($_SESSION[$formExpireKey])) {
        phpLogError(
            'Form CSRF hash expirado!',
            [
                'form_name' => $formName,
                'form_hash' => \trim($csrfHash),
                'session_form_hash_key' => $formHashKey,
                'session_hash' => $_SESSION[$formHashKey],
                'session_exp_time_key' => $formExpireKey,
                'expiration_time' => intval($_SESSION[$formExpireKey])
            ]
        );
        clearFormCSRFData($formName);
        return false;
    }

    return true;
}
