<?php
require_once __DIR__ . '/../config/systemConfig.php';

function makeRandomNumber() {
    return rand(123456789,987654321);
}

function makeDateTimeSeq($dtSeq= 'YmdHis') {
    date_default_timezone_set(DEFAULT_SYSTEM_TZ);
    return date('YmdHis');
}

function makeRandomHashCode($strSeed=null) {
    $rndNumbers = makeRandomNumber();
    $datacod = date('hYsHdim');
    $str = sprintf('%s%s%s', $rndNumbers, $datacod, $strSeed ?? '');
    return sha1($str);
}
