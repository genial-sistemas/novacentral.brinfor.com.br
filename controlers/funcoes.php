<?php

if (!function_exists('htmlDataJsonEncode')) {
    function htmlDataJsonEncode($data) {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
}

function data_brasil($data)
{
    $parte = explode("-", $data);
    $dia   = $parte[2];
    $mes   = $parte[1];
    $ano   = $parte[0];

    return "$dia/$mes/$ano";
}

function data_brasil_datetime($data_hora)
{
    $parte_data_hora = explode(" ", $data_hora);

    $parte_data = explode("-", $parte_data_hora[0]);
    $dia   = $parte_data[2];
    $mes   = $parte_data[1];
    $ano   = $parte_data[0];

    $parte_hora = explode(":", $parte_data_hora[1]);
    $hora = $parte_hora[0];
    $minuto = $parte_hora[1];
    $segundo = $parte_hora[2];

    $data_hora_formatado = "$dia/$mes/$ano às $hora:$minuto:$segundo";

    return $data_hora_formatado;
}

function data_reversa($data)
{
    $parte = explode("/", $data);
    $dia   = $parte[0];
    $mes   = $parte[1];
    $ano   = $parte[2];

    return "$ano-$mes-$dia";
}

function moeda_brasil($numero)
{
    if ($numero!='') {
        $dinheiro = number_format($numero, 2, ',', '.');
        $real = "$dinheiro";
    } else {
        $real = "0,00";
    }

    return $real;
}

function formata_telefone($phone)
{
    $formatedPhone = preg_replace('/[^0-9]/', '', $phone);
    $matches = [];
    preg_match('/^([0-9]{2})([0-9]{4,5})([0-9]{4})$/', $formatedPhone, $matches);
    if ($matches) {
        return '('.$matches[1].') '.$matches[2].'-'.$matches[3];
    }

    if (strlen($phone)==8) {
        $phone = substr($phone, -8, 4) . "-" . substr($phone, -4, 4);
    }

    return $phone;
}

function isValidString($value) {
    try {
        return isset($value) && is_string($value) && strlen($value) > 0;
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

