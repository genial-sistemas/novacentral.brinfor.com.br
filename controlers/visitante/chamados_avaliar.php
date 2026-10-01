<?php
require_once __DIR__ . '/../../helpers/sessionDataHelpers.php';
require_once __DIR__ . '/../../helpers/arrayHelpers.php';
require_once __DIR__ . '/../../models/chamado.php';

$formName = 'form_avaliar';
$formHash = '';

$tipo_mensagem = 'Success';

$dbh = getConexao();

// Se os parametros da URL sao invalidos
if (!isset($_GET['id']) || !isset($_GET['seguranca'])) {
    $formHash = makeFormCSRF($formName);
    $tipo_mensagem = 'Error';
    $mensagem = 'Parametros inválidos.';
    include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
    exit;
} 

$chamado = obterChamadoPorIdEhCodSeguranca($_GET['id'], $_GET['seguranca'], $dbh);

$idChamado = getSafeArrayKeyValue($chamado, 'id', null);

// Se o Chamado nao pode ser localizado
if (!is_numeric($idChamado) || $idChamado <= 0) {
    $tipo_mensagem = 'Error';
    $mensagem = 'Dados dos Chamado inválidos!.';
    include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
    exit;
}

$chamadoAvaliacoes = obterAvaliacoesPorChamado($idChamado, $dbh);

// Se Chamado ja foi avaliado
if (is_array($chamadoAvaliacoes) && \count($chamadoAvaliacoes) > 0) {
    $tipo_mensagem = 'Error';
    $mensagem = 'Chamado já foi avaliado anteriormente.';
    include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
    exit;
}

if (isGET()) {
    $formHashKey = makeSessionKeyName($formName, 'HASH'); 
    
    if (!isXhr()) {
        $formHash = makeFormCSRF($formName);
    } else if (array_key_exists($formHashKey, $_SESSION)) {
        $formHash = $_SESSION[$formHashKey];
    } else {
        $formHash = '';
    }
}

if (isPOST() && isset($_POST['acao']) && $_POST['acao'] == 'avaliar_chamado') {

    $postCsrfHash = httpPostVar('csrf');

    if (!$postCsrfHash || !isValidFormCSRF($formName, $postCsrfHash)) {
        $xhrRespData = [
            'tipoMensagem' => 'Error',
            'mensagem' => 'Falha na validação de seguranca do formulário!'
        ];
        xhrJsonResponse($xhrRespData);
    }
    
    $dadosAvaliacao = [
        'idChamado' => $idChamado,
        'data' => getTodayDate("Y-m-d"),
        'atendido' => httpPostVar('atendido', 0),
        'nota' => httpPostVar('nota', ''),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'descricao' => httpPostVar('descricao', '')
    ];

    if (!registrarAvaliacaoChamado($dadosAvaliacao, $dbh)) {
        $tipo_mensagem = 'Error';
        $mensagem = 'Falha ao tentar registrar dados da Avaliacao! Favor tentar novamente ou informar esta falha ao suporte.';
        include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
        exit;
    }

    // Se chamado foi avaliado como "não atendido" 
    if ($dadosAvaliacao['atendido'] == 'n') {

        $data_atua = getTodayDate("Y-m-d");
        $hora_atua = getTodayDate("G:i:s");
        $descricao = "Chamado foi reaberto pelo Usuário";
        $status = CHAMADO_EVENTO_STATUS_ABERTO;
        $data_fim = date('Y/m/d H:i:s');
        $func_resp = 0;

        $interacoes = obterChamadoEventosPorIdChamado($idChamado, $dbh);
        $idEquipamento = $interacoes[count($interacoes) - 1]['id_equipamento'];

        if (!isset($interacoes) || !isset($idEquipamento)) {
            $tipo_mensagem = 'Error';
            $mensagem = 'Falha ao tentar obter dados do Chamado! Favor tentar novamente ou informar esta falha ao suporte.';
            include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
            exit;
        }

        // Inserir novo Evento Chamado 
        $dadosEvento = [
            'idChamado' => $idChamado,
            'idPessoa' => $func_resp, // ID "PessoaEmpresa"
            'idEqpto' => $idEquipamento, // ID Eqpto, se contrato Outsourcing 
            'idContato' => 0, // Sem contato
            'data' => $data_atua,
            'hora' => $hora_atua,
            'descricao' => $descricao, // Descricao da solicitacao no Chamado
            'idStatus' => $status, // status do Chamado
        ];
       
        // Gera erro se nao consegue criar evento
        if (! criarEventoChamado($dadosEvento, $dbh)) {
            $tipo_mensagem = 'Error';
            $mensagem = 'Falha ao tentar atualizar dados do Chamado! Favor tentar novamente ou informar esta falha ao suporte.';
            include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
            exit;
        }

        // Gera erro se nao consegue atualizar situacao do chamado
        if (! atualizaChamadoSituacao($idChamado, CHAMADO_SITUACAO_ABERTO)) {
            $tipo_mensagem = 'Error';
            $mensagem = 'Falha ao tentar reabrir o Chamado! Favor tentar novamente ou informar esta falha ao suporte.';
            include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
            exit;
        }

        $tipo_mensagem = 'Info';
        $mensagem = 'Agradecemos sua avaliação, o chamado foi reaberto e entraremos em contato para continuar o atendimento.';
        include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
        exit;
    }

    $tipo_mensagem = 'Success';
    $mensagem = 'Agradecemos sua avaliação, e a utilizaremos para melhorar nossos serviços.';
}

header('Cache-Control: max-age=3600, must-revalidate');
header('Expires: Fri, 30 Oct 1998 14:19:41 GMT');
header('Last-Modified: Mon, 29 Jun 1998 02:28:12 GMT');

include __DIR__ . '/../../views/visitante/chamados_avaliar.php';
