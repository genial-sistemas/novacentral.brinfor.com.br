<?php
require_once "typeHelpers.php";

/**
 * cleanFormatOfPhoneNumber function
 *
 * @param string $phoneNumber
 * @return string
 */
function cleanFormatOfPhoneNumber($phoneNumber='') {
    if (!isValidString($phoneNumber)) { return ''; }
    return preg_replace('/[^0-9]/', '', \trim($phoneNumber));
}

/**
 * removeFmtTelefone function
 *
 * @param string $phoneNumber
 * @return string
 */
function removeFmtTelefone($phoneNumber='') {
    return cleanFormatOfPhoneNumber($phoneNumber);
}

/**
 * fmtTelefone function
 *
 * @param string $phoneNumber
 * @return string
 */
function fmtTelefone($phoneNumber='') {
    if (!isValidString($phoneNumber)) {
        return $phoneNumber;
    }

    $phoneNumber = strval($phoneNumber);

    $rawPhoneNumber = cleanFormatOfPhoneNumber($phoneNumber);

    $matches = [];
    preg_match('/^([0-9]{2})([0-9]{4,5})([0-9]{4})$/', $rawPhoneNumber, $matches);

    if ($matches) {
        return '('.$matches[1].') '.$matches[2].'-'.$matches[3];
    }

    if (strlen($phoneNumber) == 8) {
        return substr($phoneNumber, -8, 4) . "-" . substr($phoneNumber, -4, 4);
    }

    return $phoneNumber;
}

/**
 * fmtStrQtdWords function
 *
 * @param string $strValue
 * @param integer $qtdWords
 * @param string $strSep
 * @return string
 */
function fmtStrQtdWords($strValue='', $qtdWords=1, $strSep=' ') {
    if (!isValidString($strValue) || !isValidInteger($qtdWords) || !isValidString($strSep)) { return ''; }

    $strSlices = explode(' ', \trim($strValue));
    $qtdSlices = \count($strSlices);

    $strWords = [];

    if ($qtdSlices >= $qtdWords) {
        for ($i=0; $i < $qtdWords; $i++) {
            $strWords[] = $strSlices[$i];
        }
    }

    return join($strSep, $strWords);
}

/**
 * fmtNomeSobrenome function
 *
 * @param string $nomeCompleto
 * @return string
 */
function fmtNomeSobrenome($nomeCompleto='') {
    if (!isValidString($nomeCompleto)) { return ''; }
    $strSlices = explode(' ', \trim($nomeCompleto));
    $qtdSlices = \count($strSlices);
    if ($qtdSlices > 1) {
        return sprintf('%s %s', $strSlices[0], $strSlices[$qtdSlices - 1]);
    } else {
        return $strSlices[0];
    }
}

function fmtDataBrasil($data) {
    $parte = explode("-", $data);
    $dia   = $parte[2];
    $mes   = $parte[1];
    $ano   = $parte[0];

    return "$dia/$mes/$ano";
}

function fmtDataHoraBrasil($dataHora) {
    if (empty($dataHora) || $dataHora == " ") {
        return 'Sem hora';
    }

    $partesDataHora = explode(" ", $dataHora);

    $parte_data = explode("-", $partesDataHora[0]);
    $dia   = $parte_data[2];
    $mes   = $parte_data[1];
    $ano   = $parte_data[0];

    $parte_hora = explode(":", $partesDataHora[1]);
    $hora = $parte_hora[0];
    $minuto = $parte_hora[1];
    $segundo = $parte_hora[2];

    $dataHoraFormatado = "$dia/$mes/$ano às $hora:$minuto:$segundo";

    return $dataHoraFormatado;
}

/**
 * fmtMoedaBrasil function
 *
 * @param string $numero
 * @param bool $comSimbolo
 * @return string
 */
function fmtMoedaBrasil(?string $numero, bool $comSimbolo=false): string
{
    if (empty($numero) || !is_numeric($numero)) { return '0,00'; }

    $numero = number_format($numero, 2, ',', '.');
    $real = sprintf('%s', $numero);

    if ($comSimbolo === true) {
        $real = sprintf('R$ %s', $real);
    }

    return $real;
}

/**
 * fmtNumPad function
 *
 * @param string|int $numberValue
 * @param integer $qtdPosition
 * @param string $padStr
 * @param int $padType
 * @return string
 */
function fmtNumPad(string|int $numberValue, int $qtdPosition=2, string $padStr='0', int $padType=STR_PAD_LEFT): string
{
    if (! is_numeric($numberValue)) { return ''; }

    $numberValue = is_string($numberValue) ? \trim($numberValue) : strval($numberValue);

    return str_pad($numberValue, $qtdPosition, $padStr, $padType);
}
