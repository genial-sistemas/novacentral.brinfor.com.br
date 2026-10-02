<?php
require "models/contrato.php";
require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Abrir';

$contratos_hospedagem = array();
$contratos_backup = array();
$frm_tipo_contrato = 0;
$frm_codigo_equipamento = 0;
$frm_etiqueta_codigo_equipamento = '';
$frm_nome = '';
$frm_email = '';
$frm_telefone = '';
$frm_tipo_solicitacao = 0;
$frm_descricao_solicitacao = '';

$contratosAtivos = obterContratosAtivos($_SESSION['contratos']);
$contratos_outsourcing_equipamentos = array();
foreach ($contratosAtivos as $c) {
    switch($c['id_tipo']) {
        case '1':
            foreach(obterContratoOutsourcingEquipamentosaAtivos($c['id']) as $e) {
                array_push($contratos_outsourcing_equipamentos, $e);
            }
            $etiqueta = obterEtiqueta($c['id']);
            $etiqueta = str_pad($etiqueta, 2, "0", STR_PAD_LEFT); 
            break;
        case '2':
            array_push($contratos_hospedagem, obterContratoHospedagem($c['id']));
            break;
        case '5':
            array_push($contratos_backup, obterContratoBackup($c['id']));
            break;
    }
}
$tipos_solicitacao = obterTiposSolicitacao();

if (isset($_POST['acao']) && $_POST['acao'] === 'abrir_chamado') {
    $frm_tipo_contrato = $_POST['id_tipo_contrato'];
    $frm_codigo_equipamento = $_POST['codigo_equipamento'];
    $frm_etiqueta_codigo_equipamento = $_POST['etiqueta_codigo_equipamento'];
    $frm_nome = htmlspecialchars($_POST['nome']);
    $frm_email = htmlspecialchars($_POST['email']);
    $frm_telefone = htmlspecialchars($_POST['telefone']);
    $frm_tipo_solicitacao = htmlspecialchars($_POST['tipo_solicitacao']);
    $frm_descricao_solicitacao = htmlspecialchars($_POST['descricao_solicitacao']);    
    if($frm_tipo_contrato==0){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>É obrigatório selecionar o tipo de contrato!</p>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }elseif(($frm_tipo_contrato==1) && ($frm_codigo_equipamento)==0){
        $mensagem = '<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
                        <p>O código do equipamento é obrigatório para o tipo de contrato Helpdesk! Favor selecionar um equipamento!</p>
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
    }elseif ($frm_email == false){
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
        $contrato_id = 0;
        if ($frm_tipo_contrato == 1) {
            $equipamento = obterContratoOutsourcingPorId($frm_codigo_equipamento);
            $contrato_id = $equipamento['contrato'] ?? 0;
        }

        $retorno = abrirChamado($contrato_id, $frm_tipo_contrato, $frm_tipo_solicitacao, $frm_nome, $frm_email, $frm_telefone, $frm_codigo_equipamento, $frm_descricao_solicitacao);
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