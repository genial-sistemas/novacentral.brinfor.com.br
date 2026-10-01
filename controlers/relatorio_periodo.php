<?php

include 'models/chamado.php';
include 'models/contrato.php';

$menu   = 'Relatórios';
$pagina = 'Por período';

$contratos = obterContratos($_SESSION['contratos']);
$chamados = array();


if (isset($_POST['acao']) && $_POST['acao'] == 'filtrar') {
    $data_inicial = data_reversa($_POST['data_inicial']);
    $data_final = data_reversa($_POST['data_final']);
    $chamados = obterChamadosPorPeriodo($_POST['contrato'], $data_inicial, $data_final);

    $chamados_primeira_interacao =  array();
    foreach($chamados as $k => $ca) {
        $interacoes = obterInteracoesPorChamados($ca['id']);
        $chamados_primeira_interacao[$k] = $interacoes[0];
    }
}

include 'views/relatorio_periodo.php';
