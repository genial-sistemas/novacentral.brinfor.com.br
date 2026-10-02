<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Listar Fechados';

/*
if (isset($_POST['acao']) && $_POST['acao']=="reabrir_chamado") {
    reabrirChamado($_POST['chamado_id']);
}
*/

if (isset($_POST['chamado_id'])) {
	
	$chamado = obterChamado($_POST['chamado_id'])[0];
	$interacoes = obterInteracoesPorChamados($_POST['chamado_id']);

	$ult_index = sizeof($interacoes)-1;

	$ultima_interacao_tecnico = $interacoes[$ult_index]['nome_pessoa'];
	$ultima_interacao_data_hora = data_brasil($interacoes[$ult_index]['data'])." às ".$interacoes[$ult_index]['hora'];
	$ultima_interacao_equipamento = $interacoes[$ult_index]['id_equipamento'];

	include 'views/chamados_interacoes.php';

}else{

	$chamados_fechados = obterUltimosChamados($_SESSION['contratos'], 200, 'fechado');
	$chamados_fechados_ultima_interacao = obterUltimasInteracoesPorChamados($chamados_fechados);

	include 'views/chamados_listar_fechados.php';
}
