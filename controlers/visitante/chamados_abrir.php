<?php
require "models/contrato.php";
require "models/chamado.php";

$contratosTipos = obterContratosTiposAtivos();
$frm_tipo_contrato = isset($_GET["tipo_contrato"]) ? $_GET["tipo_contrato"] : null;

/*
if ($_GET!="") {
    
    $contratos_outsourcing_equipamentos = array();
    foreach ($contratosTipos as $c) {
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
        $tipos_solicitacao = obterTiposSolicitacao();
    }
}

if(!isset($_POST['acao'])) {
    $contratos = obterContratos($_SESSION['contratos']);
    $contratos_backup = array();
    $contratos_outsourcing_equipamentos = array();
    $contratos_hospedagem = array();
    $contratos_bhclouderp = array();
    foreach ($contratos as $c) {
        switch($c['id_tipo']) {
            case '1':
                foreach(obterContratoOutsourcingEquipamentos($c['id']) as $e) {
                    array_push($contratos_outsourcing_equipamentos, $e);
                }
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
    $urgencia_solicitacao = obterUrgenciaSolicitacao();
} elseif($_POST['acao']=="abrir_chamado") {
    $contrato_id = 0;
    switch ($_POST['tipo_chamado']) {
        case '1':
            $contrato_id = obterContratoOutsourcingPorId($_POST['codigo_equipamento'])['contrato'];
            break;
        case '2':
            $contrato_id = obterContratoHospedagemPorId($_POST['dominio'])['id_contrato'];
            break;
        case '5':
            $contrato_id = obterContratoBackupPorId($_POST['backup_plano'])['id_contrato'];
            break;
        default:
            foreach(obterContratosAtivos($_SESSION['contratos']) as $c) {
                if($c['id_tipo']==6) {
                    $contrato_id = $c['id'];
                }
            }
    }
    $retorno = abrirChamado($contrato_id, $_POST['tipo_chamado'], 0, $_POST['tipo_solicitacao'], $_POST['urgencia'], $_POST['observacao']);
}*/

include 'views/visitante/chamados_abrir.php';