<?php

require_once __DIR__ . '/../../helpers/arrayHelpers.php';
require_once __DIR__ . '/../../helpers/httpHelpers.php';
require_once __DIR__ . '/../../helpers/sessionDataHelpers.php';

require_once __DIR__ . '/../../models/conexao.php';
require_once __DIR__ . '/../../models/contrato.php';
require_once __DIR__ . '/../../models/chamado.php';

require_once __DIR__ . '/../../services/chamadoInteracoesService.php';
require_once __DIR__ . '/../../services/sseMessageService.php';

$url401 = "/401";
$url404 = "/404";

try {

    if (!isXhr() || !isPOST()) { throw new \RuntimeException('Invalid request', 401); }

    $urlParts = parse_url($_SERVER['REQUEST_URI']);
    $queryPrms = [];
    parse_str($urlParts['query'], $queryPrms);

    $id = getSafeArrayKeyValue($queryPrms, 'id', '');
    $segur = getSafeArrayKeyValue($queryPrms, 'seguranca', '');

    if (
        empty($id)
        || empty($segur)
    ) {
        throw new \RuntimeException('Invalid request', 401);
    }

    $dbh = getConexao();
    $chamado = obterChamadoPorIdEhCodSeguranca($id, $segur, $dbh);

} catch (\Throwable $th) {
    error_log($th->getMessage());
    echo renderRedirectPage('/401');
    exit;
}

if (!isset($chamado) || !arrayHasKeys($chamado, ['id', 'seguranca', 'id_situacao'])) {
    $xhrRespData = [
        'tipoMensagem' => 'redirect',
        'mensagem' => 'Dados inválidos!',
        'url' => '/401'
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

if ($chamado['id_situacao'] == CHAMADO_EVENTO_STATUS_FINALIZADO) {
    $xhrRespData = [
        'tipoMensagem' => 'redirect',
        'mensagem' => 'Este chamado está com status concluido!',
        'url' => \sprintf('/avaliar-chamado=%s', $id, $segur)
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

$idChamado = $chamado['id'] ?? 0;
$interacoes = comporChamadoEventosComPessoas($idChamado) ?? [];
if (empty($interacoes)) {
    $xhrRespData = [
        'tipoMensagem' => 'info',
        'mensagem' => 'Não existem eventos registrados neste chamado'
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

$jsonData = json_encode($interacoes);
if (empty($jsonData)) {
    $xhrRespData = [
        'tipoMensagem' => 'error',
        'mensagem' => 'Falha ao tentar enviar mensagem existem eventos registrados neste chamado'
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

$xhrRespData = [
    'tipoMensagem' => 'interacoes',
    'mensagem' => $jsonData
];
xhrJsonResponse($xhrRespData, 200);
exit;
