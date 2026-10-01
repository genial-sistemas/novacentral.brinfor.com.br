<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = '';

if (isset($_POST['nova-msg'])) {
    criarInteracao($_POST['chamado_id'], 0, $_POST['id_equipamento'], $_POST['nova-msg'], 1);
    unset($_POST['nova-msg']);
}

$chamado = obterChamado($_POST['chamado_id'])[0];
$interacoes = obterInteracoesPorChamados($_POST['chamado_id']);

$ult_index = sizeof($interacoes)-1;

$ultima_interacao_tecnico = $interacoes[$ult_index]['nome_pessoa'];
$ultima_interacao_data_hora = data_brasil($interacoes[$ult_index]['data'])." às ".$interacoes[$ult_index]['hora'];
$ultima_interacao_equipamento = $interacoes[$ult_index]['id_equipamento'];

include 'views/chamados_interacoes.php';