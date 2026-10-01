<?php
require_once __DIR__ . '/../config/systemConfig.php';

const MAP_NUM_MES_ABREV = [
    1 => 'Jan',
    2 => 'Fev',
    3 => 'Mar',
    4 => 'Abr',
    5 => 'Mai',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Ago',
    9 => 'Set',
    10 => 'Out',
    11 => 'Nov',
    12 => 'Dez'
];

function isValidStrDate($strDate, $format='Y-m-d') {
    if (!isValidString($strDate)) { return false; }
    try {
        $dto = \DateTime::createFromFormat($format, $strDate);
        return $dto && ($dto->format($format) == $strDate);
    } catch (\Throwable $th) {
        //throw $th;
        return false;
    }
}

function getTodayDate($format='Y-m-d') {
    try {
        date_default_timezone_set(DEFAULT_SYSTEM_TZ);
        return date($format);
    } catch (\Throwable $th) {
        //throw $th;
        return null;
    }
}

function convDbStrDateToFormat($dbStrDate, $outFormat='d/m/y') {
    try {
        date_default_timezone_set(DEFAULT_SYSTEM_TZ);
        $dto = \DateTime::createFromFormat(DB_DATE_FORMAT, $dbStrDate);
        return $dto->format($outFormat);
    } catch (\Throwable $th) {
        //throw $th;
        return null;
    }
}

function convDbStrDateTimeToFormat($dbStrDateTime, $outFormat='d/m/y') {
    try {
        date_default_timezone_set(DEFAULT_SYSTEM_TZ);
        $dto = \DateTime::createFromFormat(DB_DATETIME_FORMAT, $dbStrDateTime);
        return $dto->format($outFormat);
    } catch (\Throwable $th) {
        //throw $th;
        return null;
    }
}

function convBrStrDateToFormat($brStrDate, $outFormat='Y-m-d') {
    try {
        date_default_timezone_set(DEFAULT_SYSTEM_TZ);
        $dto = \DateTime::createFromFormat(BR_INPUT_DATE_FORMAT, $brStrDate);
        return $dto->format($outFormat);
    } catch (\Throwable $th) {
        //throw $th;
        return null;
    }
}

function convNumToMonthNameAbrev($numValue=null) {
    if (!is_numeric($numValue) || $numValue <= 0) { return ''; }
    return MAP_NUM_MES_ABREV[intval($numValue)];
}