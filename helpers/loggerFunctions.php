<?php

require_once(__DIR__ . '/../models/conexao.php');
require_once(__DIR__ . '/typeHelpers.php');
require_once(__DIR__ . '/httpHelpers.php');
require_once(__DIR__ . '/sessionDataHelpers.php');

const LOG_ERROR_TIPO_GENERIC = 'generic_error';
const LOG_ERROR_TIPO_DATABASE = 'database_error';
const LOG_ERROR_TIPO_SEND_MAIL = 'send_email_error';
const LOG_ERROR_TIPO_UPLOAD_FILE = 'upload_file_error';

const LOG_ERROR_TIPOS = [
    LOG_ERROR_TIPO_GENERIC,
    LOG_ERROR_TIPO_DATABASE,
    LOG_ERROR_TIPO_SEND_MAIL,
    LOG_ERROR_TIPO_UPLOAD_FILE
];

const LOG_ERROR_TABLE = 'log_erros';

function phpLogError(?string $descricao, ?array $contexto)
{
    try {
        if (empty($descricao)){ return; }
        if (empty($contexto)) { $contexto = []; }

        $contexto['remoteIp'] = getRemoteIpAddr();
        $contexto['remotePort'] = getRemotePort();
        $contexto['browserAgent'] = getCurrentBrowser();
        $contexto['httpHost'] = getHttpHost();
        $contexto['httpReferer'] = getHttpReferer();

        error_log(serialize([
            'descricao' => $descricao,
            'contexto' => $contexto
        ]));
    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
    }
}

function logDbError(array $params)
{
    try {
        if (\count($params) < 5) { return false; }

        $sqlStr = \trim("
            INSERT INTO `%s` (`data`, `ip_remoto`, `browser_agent`, `tipo_erro`, `descricao`, `contexto`)
                VALUES (:now, :ipRemoto, :browserAgent, :tipoError, :descricao, :contextSerialized);
        ");

        $tableName = LOG_ERROR_TABLE;
        $sqlStr = sprintf($sqlStr, $tableName);

        $dbh = getConexao();
        $sth = $dbh->prepare($sqlStr);

        return $sth->execute($params);

    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
        return false;
    }

}

function logError(
    $tipoError = LOG_ERROR_TIPO_GENERIC,
    $descricao = "falha do sistema",
    $contexto = null
) {

    phpLogError($descricao, $contexto);

    try {
        if (!isValidArray($contexto)) {
            $contexto = [];
        }

        $contexto['remoteIp'] = getRemoteIpAddr();
        $contexto['remotePort'] = getRemotePort();
        $contexto['browserAgent'] = getCurrentBrowser();
        $contexto['httpHost'] = getHttpHost();
        $contexto['httpReferer'] = getHttpReferer();

        $contexto['sessionData'] = $_SESSION;

        //$contextSerialized = base64_encode(serialize($contexto));
        $contextSerialized = serialize($contexto);

        $now = date('Y-m-d H:i:s', time());

        $params = [
            ':now' => $now,
            ':ipRemoto' => sprintf('ip[%s]:port[%s]', $contexto['remoteIp'], $contexto['remotePort']),
            ':browserAgent' => $contexto['browserAgent'],
            ':tipoError' => $tipoError,
            ':descricao' => $descricao,
            ':contextSerialized' => $contextSerialized
        ];

        return logDbError($params);

    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
        return false;
    }
}

function logErrorGeneric($errorMessage = "", $errorContextData = [])
{
    try {
        return logError(LOG_ERROR_TIPO_GENERIC, $errorMessage, $errorContextData);
    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
        return false;
    }
}

function logErrorOnSaveDbData($errorContextData = [])
{
    try {
        $currentUser = getCurrSessionUser()['nome'];
        $currentUrl = getCurrRequestUrl();
        $remoteIpPort = getRemoteIpPort();
        $currentBrowser = getCurrentBrowser();

        $errorMessage = "
         Erro ao tentar salvar ou atualizar dados no DB,
         gerado pelo Usuario: $currentUser,
         com acesso via URL: $currentUrl,
         e pelo Ip:Port remoto: $remoteIpPort,
         usando o Browser: $currentBrowser
        ";

        return logError(LOG_ERROR_TIPO_DATABASE, $errorMessage, $errorContextData);
    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
        return false;
    }
}

function logErrorOnSendMail($errorContextData = [])
{
    try {
        $currentUser = getCurrSessionUser()['nome'];
        $currentUrl = getCurrRequestUrl();
        $remoteIpPort = getRemoteIpPort();
        $currentBrowser = getCurrentBrowser();
        $to = $errorContextData['to'];

        $errorMessage = "
         Erro ao enviar EmailChamado para: $to,
         gerado pelo Usuario: $currentUser,
         com acesso via URL: $currentUrl,
         e pelo Ip:Port remoto: $remoteIpPort,
         usando o Browser: $currentBrowser
        ";

        return logError(LOG_ERROR_TIPO_SEND_MAIL, $errorMessage, $errorContextData);
    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
        return false;
    }
}

function logErrorUploadFile($errorMessage = "", $errorContextData = [])
{
    try {
        return logError(LOG_ERROR_TIPO_UPLOAD_FILE, $errorMessage, $errorContextData);
    } catch (\Throwable $th) {
        //throw $th;
        error_log($th->getMessage());
        return false;
    }
}
