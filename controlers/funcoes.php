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

// (31) 3333-4444, (31) 98888-7777, 3333-4444 ou 0800 123 4567. Aceita DDD com 0 e +55. Outros formatos voltam como vieram.
function formata_telefone($phone)
{
    $digitos = preg_replace('/\D/', '', (string)$phone);
    if (preg_match('/^0?(800|300|303|400|500|900)(\d{3})(\d{4})$/', $digitos, $m)) {
        return '0' . $m[1] . ' ' . $m[2] . ' ' . $m[3];
    }
    if (strlen($digitos) >= 12 && strpos($digitos, '55') === 0) {
        $digitos = substr($digitos, 2);
    }
    if (strlen($digitos) >= 11 && $digitos[0] === '0') {
        $digitos = substr($digitos, 1);
    }
    if (preg_match('/^(\d{2})(\d{4,5})(\d{4})$/', $digitos, $m)) {
        return '(' . $m[1] . ') ' . $m[2] . '-' . $m[3];
    }
    if (preg_match('/^(\d{4,5})(\d{4})$/', $digitos, $m)) {
        return $m[1] . '-' . $m[2];
    }
    return trim((string)$phone);
}

function isValidString($value) {
    try {
        return isset($value) && is_string($value) && strlen($value) > 0;
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

