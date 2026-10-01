<?php

require_once __DIR__ . '/../../helpers/httpHelpers.php';
require_once __DIR__ . '/../../helpers/contratoHelpers.php';
require_once __DIR__ . '/../../helpers/arrayHelpers.php';

require_once __DIR__ . '/../../models/contrato.php';
require_once __DIR__ . '/../../models/chamado.php';

require_once __DIR__ . '/../../services/uploadFileService.php';
require_once __DIR__ . '/../../services/chamadoInteracoesService.php';

$url401 = "/401";
$url404 = "/404";

if (!isXhr() || !isPOST()) {
    error_log('Invalid request');
    echo renderRedirectPage('/401');
    exit;
}

try {

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


} catch (\Throwable $th) {
    error_log($th->getMessage());
    echo renderRedirectPage('/401');
    exit;
}

$formName = 'form_nova_msg';
$formHash = '';

$xhrRespData = [
    'tipoMensagem' => null,
    'mensagem' => null
];

$postCsrfHash = safeHttpPostVar('csrf');

if (!$postCsrfHash || !isValidFormCSRF($formName, $postCsrfHash)) {
    $xhrRespData = [
        'tipoMensagem' => 'Error',
        'mensagem' => 'Falha na validacao de seguranca do formulario!'
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

$idChamado = safeHttpPostVar('id_chamado', 0);
$seguranca = safeHttpPostVar('seguranca', 0);
$operacao = safeHttpPostVar('operacao', 0);
$idContatoCliente = safeHttpPostVar('id_contato', 0);;
$idEqpto = safeHttpPostVar('id_equipamento', 0);

if (
    $id != $idChamado
    || $segur != $seguranca
    || ($operacao !== 'mensagem' && $operacao !== 'arquivo')
) {
    $xhrRespData = [
        'tipoMensagem' => 'Error',
        'mensagem' => 'Param. inválidos!'
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

$interacoes = comporChamadoEventosComPessoas($idChamado);

$ultimoEvento = $interacoes[array_key_last($interacoes)];
$estaFinalizado = (
    $ultimoEvento
    && (
        (arrayGet($ultimoEvento, 'id_situacao') == CHAMADO_SITUACAO_FINALIZADO)
        || (arrayGet($ultimoEvento, 'id_status') == CHAMADO_EVENTO_STATUS_FINALIZADO)
    )
);

// Sinaliza finalizacao do Chamado e envia as ultimas mensages na fila.
if ($estaFinalizado) {

    $urlAvaliacao = sprintf('%s/avaliar-chamado?id=%s&seguranca=%s', APP_BASE_URL, $idChamado, $seguranca);

    $xhrRespData = [
        'tipoMensagem' => 'Finalizado',
        'mensagem' => $interacoes,
        'urlRedirect' => $urlAvaliacao
    ];

    xhrJsonResponse($xhrRespData, 201);
    exit(0);

// Processa envio mensagem do cliente ao Suporte
} elseif ($operacao === 'mensagem') {

    $novaMsg = safeHttpPostVar('nova-msg');
    if ($operacao === 'mensagem' && empty($novaMsg)) {
        $xhrRespData = [
            'tipoMensagem' => 'Error',
            'mensagem' => 'Param. inválidos!'
        ];
        xhrJsonResponse($xhrRespData, 201);
        exit;
    }

    $eventoData = [
        'idChamado' => $idChamado,
        'idPessoa' => 0,
        'idContato' => $idContatoCliente,
        'idEqpto' => $idEqpto,
        'descricao' => addslashes($novaMsg),
        'idStatus' => CHAMADO_EVENTO_STATUS_RESP_CLIENTE,
        'link' => CHAMADO_EVENTO_LINK_OFF
    ];
    $res = criarEventoChamadoComUpdate($eventoData);
    if (!$res) {
        $xhrRespData = [
            'tipoMensagem' => 'Error',
            'mensagem' => 'Falha ao tentar registrar dados da mensagem!'
        ];
        xhrJsonResponse($xhrRespData, 201);
        exit;
    }

    $interacoes = comporChamadoEventosComPessoas($idChamado);

// Processa upload de arquivo anexo
} else if ($operacao === 'arquivo' && $_FILES['arquivo']['size'] > 0) {
    $linkUrl = salvarArquivoRetornaLink($idChamado);

    if (! isValidString($linkUrl)
        || (strpos($linkUrl, 'http') === false)
    ) {
        $xhrRespData = [
            'tipoMensagem' => 'Error',
            'mensagem' => 'Falha ao anexar arquivo inválido ou não permitido!'
        ];
        xhrJsonResponse($xhrRespData, 201);
        exit;
    }

    $eventoData = [
        'idChamado' => $idChamado,
        'idPessoa' => 0,
        'idContato' => $idContatoCliente,
        'idEqpto' => $idEqpto,
        'descricao' => $linkUrl,
        'idStatus' => CHAMADO_EVENTO_STATUS_RESP_CLIENTE,
        'link' => CHAMADO_EVENTO_LINK_ON
    ];
    $res = criarEventoChamadoComUpdate($eventoData);
    if (!$res) {
        $xhrRespData = [
            'tipoMensagem' => 'Error',
            'mensagem' => 'Falha ao tentar registrar dados do arquivo anexo!'
        ];
        xhrJsonResponse($xhrRespData, 201);
        exit;
    }

    $interacoes = comporChamadoEventosComPessoas($idChamado);

} else {
    $xhrRespData = [
        'tipoMensagem' => 'Error',
        'mensagem' => 'Param. inválidos!'
    ];
    xhrJsonResponse($xhrRespData, 201);
    exit;
}

//
unset($_POST['nova-msg']);
unset($_POST['id_chamado']);
unset($_POST['seguranca']);
//

$xhrRespData = [
    'tipoMensagem' => 'Data',
    'mensagem' => $interacoes
];
xhrJsonResponse($xhrRespData, 200);
exit(0);
