<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Listar Abertos';

$chamados_abertos = obterUltimosChamados($_SESSION['contratos'], 0, 'aberto');

$chamados_abertos_ultima_interacao =  array();
foreach($chamados_abertos as $ca) {
    $interacoes = obterInteracoesPorChamados($ca['id']);
    $chamados_abertos_ultima_interacao[$ca['id']] = $interacoes[sizeof($interacoes)-1];
}

include 'views/chamados_listar_abertos.php';