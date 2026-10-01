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

$idChamado = $_GET['id'] ?? 0;
$seguranca = $_GET['seguranca'] ?? 0;

// $formName = 'form_interacao';
// $formHash = '';

$formName = 'form_nova_msg';
$formHash = makeFormCSRF($formName);

$viewData = [
    'idChamado' => 0,
    'seguranca' => 0,
    'idTipoContrato' => 0,
    'idContatoCliente' => 0,
    'idEqpto' => 0,
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

    $idChamado = $chamado['id'] ?? 0;
    $viewData['idChamado'] = $idChamado;
    $viewData['seguranca'] = $chamado['seguranca'] ?? 0;

    $viewData['idTipoContrato'] = $chamado['id_tipo_contrato'] ?? 0;
    $viewData['tipoContrato'] = $chamado['tipo_contrato'] ?? '';

    if ($chamado['id_situacao'] == CHAMADO_EVENTO_STATUS_FINALIZADO) {
        $viewData['error'] = 'Esse chamado ja foi finalizado.';
        $viewData['situacao'] = 'finalizado';
        return $viewData;
    }

    $viewData['situacao'] = $chamado['situacao'] ?? '';

    $interacoes = comporChamadoEventosComPessoas($idChamado);
    $viewData['interacoes'] = $interacoes ?? [];

    $primeiraInteracao = $interacoes[0];
    $ultimaInteracao = $interacoes[sizeof($interacoes) - 1];

    $viewData['idEqpto'] = $primeiraInteracao['id_equipamento'] ?? 0;

    $idRespAbertura = $chamado['resp_abertura'] ?? 0;

    $idContato = $primeiraInteracao['id_contato'] ?? 0;
    $viewData['idContatoCliente'] = isValidInteger($idContato) ? $idContato : $idRespAbertura;
    if (empty($viewData['idContatoCliente'])) {
        $viewData['idContatoCliente'] = $primeiraInteracao['id_pessoa_contato'] ?? ($primeiraInteracao['id_pessoa_cliente'] ?? 0);
    }

    $idContrato = getSafeArrayKeyValue($primeiraInteracao, 'id_contrato');
    $idTipoContrato = getSafeArrayKeyValue($primeiraInteracao, 'id_tipo_contrato');

    $viewData['dataAbertura'] = data_brasil(substr($chamado['data_abertura'], 0, 10));

    $dadosTecnicoSuporte = obterDadosTecnicoSuporteDeChamadoEventos($interacoes, $dbh);

    $viewData['ultEventoNomeTecnico'] = $dadosTecnicoSuporte
        ? $dadosTecnicoSuporte['nome_pessoa']
        : '';

    $viewData['ultEventoDataEhHora'] = data_brasil($ultimaInteracao['data']) . " às " . $ultimaInteracao['hora'];
    $viewData['ultEventoDescrEqpto'] = '';

    $idEqpto = 0;

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

$viewData = loadViewData($idChamado, $seguranca, $viewData);
extract($viewData);

header('Cache-Control: max-age=3600, must-revalidate');
header('Expires: Fri, 30 Oct 1998 14:19:41 GMT');
header('Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT');

include __DIR__ . '/../../views/visitante/chamados_interacoes.php';
