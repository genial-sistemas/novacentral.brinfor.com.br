<?php
require "models/pessoa.php";
require "models/contrato.php";
require "models/chamado.php";

$menu   = 'Dashboard';
$pagina = 'Início';

$mes_atual = date('m');
$ano_atual = date('Y');

$selectPeriodo = isset($_POST['periodo']) ? $_POST['periodo'] : null;

if (empty($selectPeriodo)){
    $selectPeriodo = $mes_atual-$ano_atual;
    $mes_select = $mes_atual;
    $ano_select = $ano_atual;
}else{
    $periodo = explode("-", $selectPeriodo);
    if(count($periodo)>1){
        $mes_select = $periodo[0];
        $ano_select = $periodo[1];
    }else{
        $ano_select = $selectPeriodo;
        $mes_select = 0;
    }
}

for ($x=1; $x<=12; $x++){
    if($mes_atual!=0){
        $mes_atual = str_pad($mes_atual, 2, "0", STR_PAD_LEFT);    
        $periodo_mes = "$mes_atual-$ano_atual";
        $peridoDados[$x] = ['valor' => $periodo_mes, 'descricao' => $mes_atual.'/'.$ano_atual];
        $mes_atual = $mes_atual-1;
    }else{
        $peridoDados[$x] = ['valor' => $ano_atual, 'descricao' => "Ano $ano_atual"];
        $ano_atual = $ano_atual-1;
        $mes_atual = 12;
    }
}


$chamados_total = obterQtdChamados($mes_select, $ano_select, $_SESSION['contratos']);
$chamados_total_aberto = obterQtdChamadosAbertos($mes_select, $ano_select, $_SESSION['contratos']);
$maquinas_ativas = obterMaquinasAtivas($_SESSION['contratos']);
$maquinas_inativas = obterMaquinasInativas($_SESSION['contratos']);
$horas_trabalhadas = obterHorasTrabalhadas($mes_select, $ano_select, $_SESSION['contratos']);
$resultado_pesquisa = obterResultadoPesquisa($mes_select, $ano_select, $_SESSION['contratos']);
$interacao_tecnico = obterInteracaoPorTecnico($_SESSION['contratos'], $mes_select, $ano_select);
$etiqueta = obterEtiqueta($_SESSION['contratos']);
$contratosAtivos = obterContratosAtivos($_SESSION['contratos']);
$obterUltimosChamados = obterUltimosChamadosDashboard($_SESSION['contratos']);

include 'views/dashboard.php';