<?php
require_once 'arrayHelpers.php';

function httpGetVar($varName='', $defValue=null, $filterId=FILTER_DEFAULT) {
    $value = filter_input(INPUT_GET, $varName, $filterId);
    return $value ? $value : $defValue;
}

function safeHttpGetVar($varName='', $defValue=null, $filterId=FILTER_DEFAULT) {
    return httpGetVar($varName, $defValue, $filterId);
}

function httpPostVar($varName='', $defValue=null, $filterId=FILTER_DEFAULT) {
    $value = filter_input(INPUT_POST, $varName, $filterId);
    return $value ? $value : $defValue;
}

function safeHttpPostVar($varName='', $defValue=null, $filterId=FILTER_DEFAULT) {
    return httpPostVar($varName, $defValue, $filterId);
}

function getCurrRequestUri() {
    return getSafeArrayKeyValue($_SERVER, 'REQUEST_URI', '');
}

function getCurrentBrowser() {
    return getSafeArrayKeyValue($_SERVER, 'HTTP_USER_AGENT', '');
}

function getHttpHost() {
    return getSafeArrayKeyValue($_SERVER, 'HTTP_HOST', '');
}

function getHttpReferer() {
    return getSafeArrayKeyValue($_SERVER, 'HTTP_REFERER', '');
}

function getRemoteIpAddr() {
    return getSafeArrayKeyValue($_SERVER, 'REMOTE_ADDR', '');
}

function getRemotePort() {
    return getSafeArrayKeyValue($_SERVER, 'REMOTE_PORT', '');
}

function getRemoteIpPort() {
    try {
        $ipAddr = getSafeArrayKeyValue($_SERVER, 'REMOTE_ADDR', '');
        $ipPort = getSafeArrayKeyValue($_SERVER, 'REMOTE_PORT', '');
        return sprintf('%s:%s', trim($ipAddr), trim($ipPort));
    } catch (Throwable $th) {
        return '';
    }
}

function getCurrRequestUrl() {
    if (!isset($_SERVER['HTTP_HOST']) || !isset($_SERVER['REQUEST_URI'])) {
        return '';
    }
    return (
        isset($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] === 'on'
            ? "https"
            : "http"
    ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function isGET() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function isPOST() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function isXhr() {
    $headers = apache_request_headers();
    return array_key_exists('X-Requested-With', $headers)
        && strtolower($headers['X-Requested-With']) == strtolower('XMLHttpRequest');
}

function xhrRespErrorCode(int $errorCode = 500) {
    if ($errorCode < 400 || $errorCode > 500) { return; }
    http_response_code($errorCode);
    header('Connection: close');
    header('Content-Type: text/html');
    die();
}

function xhrJsonResponse(mixed $xhrResponse, int $httpCode = 200): void {
    if ($httpCode >= 400) { xhrRespErrorCode($httpCode); return; }
    if ($httpCode >= 300) { return; }
    http_response_code($httpCode);
    header('Content-Type: application/json');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    die(json_encode($xhrResponse));
}

function xhrHtmlResponse(string $xhrResponse, int $httpCode): void {
    if ($httpCode >= 400) { xhrRespErrorCode($httpCode);  return; }
    if ($httpCode >= 300) { return; }
    http_response_code($httpCode);
    header('Content-Type: text/html');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    die($xhrResponse);
}

function httpRedirect($uri) {
    if (is_string($uri) && strlen($uri) > 0 && strpos($uri, '/') !== false) {
        flush();
        ob_flush();
        header('Location: ' . $uri, true, 302);
        die();
    }
}

function httpRedirectBack() {
    httpRedirect(getHttpReferer());
}

function renderRedirectPage(string $url): string {
    $html = <<<EOT
        <!DOCTYPE html>
        <html>
            <head>
                <meta name="color-scheme" content="dark">
                <meta charset="utf-8">
            </head>
            <body>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        window.location.replace('');
                        window.location.assign('${url}');
                    });
                </script>
            </body>
        </html>
    EOT;
    return $html;
}

function renderPhp(string $viewPath, array $viewData): string {
    extract($viewData);
    ob_start();
    include($viewPath);
    $content = ob_get_contents();
    ob_end_clean();
    return $content !== false ? $content : '';
}

?>