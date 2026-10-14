<?php

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(array('erro' => 'Método não permitido'));
    exit;
}

$tamanho = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($tamanho <= 0 || $tamanho > 8192) {
    http_response_code(413);
    echo json_encode(array('erro' => 'Tamanho de registro inválido'));
    exit;
}

$entrada = json_decode(file_get_contents('php://input'), true);
$origem = is_array($entrada) ? ($entrada['origem'] ?? '') : '';
$mensagem = is_array($entrada) ? ($entrada['mensagem'] ?? '') : '';
$rota = is_array($entrada) ? ($entrada['rota'] ?? '') : '';

$origensPermitidas = array('browser.error', 'browser.rejection', 'browser.console');
if (
    !is_string($origem)
    || !in_array($origem, $origensPermitidas, true)
    || !is_string($mensagem)
    || trim($mensagem) === ''
) {
    http_response_code(400);
    echo json_encode(array('erro' => 'Registro de erro inválido'));
    exit;
}

$gravado = registrarErroSistema($origem, $mensagem, array(
    'rota_cliente' => is_string($rota) ? parse_url($rota, PHP_URL_PATH) : ''
));

if (!$gravado) {
    http_response_code(500);
    echo json_encode(array('erro' => 'Não foi possível gravar o registro'));
    exit;
}

http_response_code(204);
