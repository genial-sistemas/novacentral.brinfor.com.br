<?php

require_once __DIR__ . '/../../helpers/httpHelpers.php';
require_once __DIR__ . '/../../helpers/contratoHelpers.php';
require_once __DIR__ . '/../../helpers/sessionDataHelpers.php';

require_once __DIR__ . '/../../models/contrato.php';
require_once __DIR__ . '/../../models/chamado.php';

require_once __DIR__ . '/../../services/uploadFileService.php';
require_once __DIR__ . '/../../services/chamadoInteracoesService.php';

$menu   = 'Chamados';
$pagina = '';

$chamado = null;

$idChamado = 0;
$seguranca = '';

$formName = 'form_interacao';
$formHash = '';

// $interacoes = [];

// $ultEventoNomeTecnico = null;
// $ultEventoDataEhHora = null;
// $ultEventoDescrEqpto = null;

// $mensagem = null;
// $tipoMensagem = null;

$viewData = [
    'idChamado' => 0,
    'seguranca' => 0,
    'idTipoContrato' => 0,
    'tipoContrato' => '',
    'situacao' => '',
    'interacoes' => [],
    'ultEventoNomeTecnico' => null,
    'ultEventoDataEhHora' => null,
    'ultEventoDescrEqpto' => null,
    'mensagem' => null,
    'tipoMensagem' => null,
    'maxUploadFileSize' => UPLOAD_FILE_MAX_SIZE,
    'acceptsUploadFileExts' => json_encode(UPLOAD_FILE_ACCEPT_EXTENSIONS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
];


function loadViewData($id, $seguranca, $viewData, $dbh=null) {

    if (!isset($id) || !isset($seguranca)) {
        $viewData['error'] = 'Parametros inválidos.';
        include 'views/visitante/chamados_interacoes.php';
        exit;
    }
    
    $chamado = obterChamadoPorIdEhCodSeguranca($id, $seguranca);

    if (!isset($chamado) || !arrayHasKeys($chamado, ['id', 'seguranca', 'id_situacao'])) {
        $viewData['error'] = 'Chamado inválido.';
        return $viewData;
    } 

    if ($chamado['id_situacao'] == 7) {
        $viewData['error'] = 'Esse chamado ja foi finalizado.';
        return $viewData;
    }

    $idChamado = $chamado['id'] ?? 0;
    $viewData['idChamado'] = $idChamado;
    $viewData['seguranca'] = $chamado['seguranca'] ?? 0;
   
    $viewData['idTipoContrato'] = $chamado['id_tipo_contrato'] ?? 0;
    $viewData['tipoContrato'] = $chamado['tipo_contrato'] ?? '';
    $viewData['situacao'] = $chamado['situacao'] ?? '';


    $interacoes = comporChamadoEventosComPessoas($idChamado);
    $viewData['interacoes'] = $interacoes ?? [];

    $primeiraInteracao = $interacoes[0]; 
    $ultimaInteracao = $interacoes[sizeof($interacoes) - 1];

    $viewData['idEqpto'] = $primeiraInteracao['id_equipamento'] ?? 0;

    $idContato = $primeiraInteracao['id_contato'] ?? 0;
    $idRespAbertura = $chamado['resp_abertura'] ?? 0;

    $idContrato = getSafeArrayKeyValue($primeiraInteracao, 'id_contrato');
    $idTipoContrato = getSafeArrayKeyValue($primeiraInteracao, 'id_tipo_contrato');

    $viewData['idContatoCliente'] = isValidInteger($idContato) ? $idContato : $idRespAbertura;
  
    $viewData['dataAbertura'] = data_brasil(substr($chamado['data_abertura'], 0, 10));
    
    $dadosTecnicoSuporte = obterDadosTecnicoSuporteDeChamadoEventos($interacoes, $dbh);

    $viewData['ultEventoNomeTecnico'] = $dadosTecnicoSuporte 
        ? $dadosTecnicoSuporte['nome_pessoa'] 
        : '';

    $viewData['ultEventoDataEhHora'] = data_brasil($ultimaInteracao['data']) . " às " . $ultimaInteracao['hora'];
    $viewData['ultEventoDescrEqpto'] = '';

    if (isContratoTipoOutsourcing($idTipoContrato)) {
        $idEqpto = $primeiraInteracao['id_equipamento'] ?? 0;

        $eqptoData = obterContratoOutsrcEqptoPorIdEqptoEhIdContrato($idEqpto, $idContrato);

        $idPessoaContato = getSafeArrayKeyValue($eqptoData, 'id_pessoa_contato', 0);
        $tipoEqpto = getSafeArrayKeyValue($eqptoData, 'tipo_equipamento');
        
        $etiquetaCodigoContrato = getSafeArrayKeyValue($eqptoData, 'etiqueta_codigo_contrato', 0);
        $etiquetaCodigoEqpto = getSafeArrayKeyValue($eqptoData, 'etiqueta_codigo_equipamento', 0);

        $codigoEtiqueta = sprintf('[%s|%s]', 
            str_pad($etiquetaCodigoContrato, 2, '0', STR_PAD_LEFT),
            str_pad($etiquetaCodigoEqpto, 4, '0', STR_PAD_LEFT)
        );

        $pessoaContatoEqpto = obterPerfilPessoaJuridicaContato($idPessoaContato);
        $pessoaContatoEqptoNome = getSafeArrayKeyValue($pessoaContatoEqpto, 'nome_pessoa');

        $viewData['ultEventoDescrEqpto'] = sprintf('%s %s - %s', $tipoEqpto, $codigoEtiqueta, $pessoaContatoEqptoNome);
    }

    return $viewData;
}

// if ($_SERVER['REQUEST_METHOD'] == 'GET') {
//     $viewData = loadViewData($_GET['id'], $_GET['seguranca'], $viewData);
//     include __DIR__ . '/../views/visitante/chamados_interacoes.php';
//     exit;
// }

if (isPOST()) {
    $idChamado = safeHttpPostVar('id_chamado', 0);
    $seguranca = safeHttpPostVar('seguranca', 0);

    $postCsrfHash = safeHttpPostVar('csrf');

    if (!$postCsrfHash || !isValidFormCSRF($formName, $postCsrfHash)) {
        $xhrRespData = [
            'tipoMensagem' => 'Error',
            'mensagem' => 'Falha na validacao de seguranca do formulario!'
        ];
        xhrJsonResponse($xhrRespData);
    }

    if (isset($_POST['nova-msg']) && $idChamado && $seguranca) {
        $xhrRespData = [
            'tipoMensagem' => null,
            'mensagem' => null
        ];

        $idContatoCliente = safeHttpPostVar('id_contato', 0);;
        $idEqpto = safeHttpPostVar('id_equipamento', 0);

        $novaMsg = safeHttpPostVar('nova-msg');
        if (isValidString($novaMsg)) {
            $eventoData = [
                'idChamado' => safeHttpPostVar('id_chamado', 0),
                'idPessoa' => 0,
                'idContato' => $idContatoCliente,
                'idEqpto' => $idEqpto,
                'descricao' => addslashes($novaMsg),
                'idStatus' => CHAMADO_EVENTO_STATUS_RESP_CLIENTE,
                'link' => CHAMADO_EVENTO_LINK_OFF
            ];
            $res = criarEventoChamadoComUpdate($eventoData); 
        }

        if ($_FILES['arquivo']['size'] > 0) {
            $linkUrl = salvarArquivoRetornaLink($idChamado);
            if (isValidString($linkUrl) && (strpos($linkUrl, 'http') !== false)) {
                $eventoData = [
                    'idChamado' => safeHttpPostVar('id_chamado', 0),
                    'idPessoa' => 0,
                    'idContato' => $idContatoCliente,
                    'idEqpto' => $idEqpto,
                    'descricao' => $linkUrl,
                    'idStatus' => CHAMADO_EVENTO_STATUS_RESP_CLIENTE,
                    'link' => CHAMADO_EVENTO_LINK_ON
                ];
                $res = criarEventoChamadoComUpdate($eventoData);

            } else {
                $xhrRespData = [
                    'tipoMensagem' => 'Error',
                    'mensagem' => 'Falha ao anexar arquivo inválido ou não permitido!'
                ];
            }
        }
        //
        unset($_POST['nova-msg']);
        unset($_POST['id_chamado']);
        unset($_POST['seguranca']);
        //
        clearFormCSRFData($formName);
        //
        //$viewData = loadViewData($idChamado, $seguranca, $viewData);
        xhrJsonResponse($xhrRespData);
    }

    
    //header("Location: " . $_SERVER['REQUEST_URI']);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $idChamado = $_GET['id'] ?? 0;
    $seguranca = $_GET['seguranca'] ?? 0;
    if (!isXhr()) {
        $formHash = makeFormCSRF($formName);
    } else {
        $formHashKey = makeSessionKeyName($formName, 'HASH'); 
        $formHash = $_SESSION[$formHashKey];
    }

}

$viewData = loadViewData($idChamado, $seguranca, $viewData);
extract($viewData);

header('Cache-Control: max-age=3600, must-revalidate');
header('Expires: Fri, 30 Oct 1998 14:19:41 GMT');
header('Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT');

include __DIR__ . '/../../views/visitante/chamados_interacoes.php';
