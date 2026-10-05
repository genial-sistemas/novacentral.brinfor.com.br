<?php
require "models/contrato.php";
require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Abrir';

$tipos_contrato_visitante = array(
    1 => '1 - Helpdesk (Outsourcing)',
    2 => '2 - Hospedagem',
    4 => '4 - Domínio',
    5 => '5 - Backup',
    6 => '6 - Suporte Produtos',
    7 => '7 - Locação'
);
$frm_tipo_contrato = 0;
$frm_contrato_id = 0;
$frm_codigo_equipamento = 0;
$frm_etiqueta_codigo_equipamento = '';
$frm_nome = '';
$frm_email = '';
$frm_telefone = '';
$frm_tipo_solicitacao = 0;
$frm_descricao_solicitacao = '';

$contratosAtivos = obterContratosAtivos($_SESSION['contratos']);
$contratos_outsourcing_equipamentos = array();
$tipos_contrato_habilitados = array();
foreach ($contratosAtivos as $c) {
    $tipo_contrato = (int)$c['id_tipo'];
    if (array_key_exists($tipo_contrato, $tipos_contrato_visitante)) {
        $tipos_contrato_habilitados[] = $tipo_contrato;
    }
    if ($tipo_contrato === 1) {
        foreach (obterContratoOutsourcingEquipamentosaAtivos($c['id']) as $e) {
            $contratos_outsourcing_equipamentos[] = $e;
        }
        $etiqueta = obterEtiqueta($c['id']);
        $etiqueta = str_pad($etiqueta, 2, "0", STR_PAD_LEFT);
    }
}
$tipos_contrato_habilitados = array_values(array_unique($tipos_contrato_habilitados));
$tipos_solicitacao = obterTiposSolicitacao();

if (isset($_POST['acao']) && $_POST['acao'] === 'abrir_chamado') {
    $frm_tipo_contrato = $_POST['id_tipo_contrato'];
    $frm_contrato_id = (int)($_POST['contrato_id'] ?? 0);
    $frm_codigo_equipamento = $_POST['codigo_equipamento'];
    $frm_etiqueta_codigo_equipamento = $_POST['etiqueta_codigo_equipamento'];
    $frm_nome = htmlspecialchars($_POST['nome']);
    $frm_email = htmlspecialchars($_POST['email']);
    $frm_telefone = htmlspecialchars($_POST['telefone']);
    $frm_tipo_solicitacao = htmlspecialchars($_POST['tipo_solicitacao']);
    $frm_descricao_solicitacao = htmlspecialchars($_POST['descricao_solicitacao']);
    $tipo_contrato_id = (int)$frm_tipo_contrato;
    $contrato_id = 0;
    $contrato_valido = false;

    if ($tipo_contrato_id === 1 && (int)$frm_codigo_equipamento > 0) {
        $equipamento = obterContratoOutsourcingPorId($frm_codigo_equipamento);
        $contrato_id = (int)($equipamento['contrato'] ?? 0);
        foreach ($contratosAtivos as $contrato) {
            if ((int)$contrato['id'] === $contrato_id && (int)$contrato['id_tipo'] === 1) {
                $contrato_valido = true;
                break;
            }
        }
    } elseif ($tipo_contrato_id !== 1) {
        foreach ($contratosAtivos as $contrato) {
            if ((int)$contrato['id'] === $frm_contrato_id && (int)$contrato['id_tipo'] === $tipo_contrato_id) {
                $contrato_id = $frm_contrato_id;
                $contrato_valido = true;
                break;
            }
        }
    }

    if($tipo_contrato_id===0){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>É obrigatório selecionar o tipo de contrato!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }elseif (!array_key_exists($tipo_contrato_id, $tipos_contrato_visitante)) {
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>Selecione um tipo de contrato válido!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }elseif(($tipo_contrato_id===1) && (int)$frm_codigo_equipamento===0){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>O código do equipamento é obrigatório para o tipo de contrato Helpdesk! Favor selecionar um equipamento!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }elseif (!$contrato_valido) {
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>Selecione um contrato ativo da sua conta para esse tipo!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }elseif (strlen($frm_nome)<= 3) {
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>Favor digitar nome do Resposável pela abertura!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';  
    }elseif (!filter_var(trim((string)$frm_email), FILTER_VALIDATE_EMAIL)) {
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>O email informado parece inválido! Favor digitar novamente!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';         
    }elseif ($frm_telefone == false){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>O telefone informado parece inválido! Favor digitar novamente!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>'; 
    }elseif ($frm_tipo_solicitacao == 0){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>Selecione o tipo de solicitação!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';    
    }elseif (strlen($frm_descricao_solicitacao)<= 10){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>Faça um descrição de pelo menos 10 caracteres!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';        
    }else{
        $retorno = abrirChamado($contrato_id, $tipo_contrato_id, $frm_tipo_solicitacao, $frm_nome, $frm_email, $frm_telefone, $frm_codigo_equipamento, $frm_descricao_solicitacao);
        if($retorno){
            $mensagem = '<div class="alert alert-success alert-dismissible fade show floating-alert" role="alert">
                            <p>Chamado Aberto com sucesso! '.$contrato_id.', '.$frm_tipo_contrato.', '.$frm_tipo_solicitacao.', '.$frm_nome.', '.$frm_email.', '.$frm_telefone.', '.$frm_codigo_equipamento.', '.$frm_descricao_solicitacao.'</p>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            $_SESSION['id_chamado'] = obterUltimoChamado()["id"];
            $_SESSION['id_seguranca'] = obterUltimoChamado()["seguranca"];
            header('Location: chamados_abrir-resultado');
            exit;
        }else{
            $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                            <p>Ocorreu um erro ao abrir chamado! Favor tentar novamente!</p>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                    </div>';
        }
    }
}

function validaNomeContato($contato_nome) {
    
    if($contato_nome == NULL){
        $contato_nome = "Sem Contrato Vinculado";
    }
    
    return $contato_nome;

}

include 'views/chamados_abrir.php';