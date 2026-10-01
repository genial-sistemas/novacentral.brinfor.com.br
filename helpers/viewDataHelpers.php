<?php
require_once "helpers/codecsHelpers.php";

function makeEqptoOptionLabel($coEqpData) {
    if (!$coEqpData['codigo']) { return null; }

    $label = sprintf('%04d', $coEqpData['codigo']);

    if (isset($coEqpData['descricao'])) {
        $label = sprintf('%s - %s', $label, trim($coEqpData['descricao']));
    }

    /*if (isset($coEqpData['modelo'])) {
        $label = sprintf('%s - %s', $label, $coEqpData['modelo']);
    }*/

    if (isset($coEqpData['pessoa_contato_nome'])) {
        $label = sprintf('%s - %s', $label, $coEqpData['pessoa_contato_nome']);
    }else{
        $label = sprintf('%s - %s', $label, 'Sem contato Vinculado');
    }

    return $label;
}

function makeHtmlDataEqptoContato($ctEqpContato) {
    $nome = $ctEqpContato['pessoa_contato_nome']
        ? sprintf('%s (%s)', $ctEqpContato['pessoa_contato_nome'], $ctEqpContato['pessoa_contato_cargo'])
        : null;
    return htmlDataJsonEncode([
        'nome' => $nome,
        'email' => $ctEqpContato['pessoa_contato_email'],
        'telefone' => formata_telefone($ctEqpContato['pessoa_contato_telefone']),
        'celular' => formata_telefone($ctEqpContato['pessoa_contato_celular']),
        'eqp_etiqueta' => sprintf('%02d', $ctEqpContato['etiqueta']),
        'eqp_codigo' => sprintf('%04d', $ctEqpContato['codigo'])
    ], true);
}
