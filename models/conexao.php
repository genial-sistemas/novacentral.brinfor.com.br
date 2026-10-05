<?php
date_default_timezone_set('America/Sao_Paulo');

function getConexao(){
    $dsn = getenv('BHCLOUD_DSN');
    $user = getenv('BHCLOUD_USER');
    $pass = getenv('BHCLOUD_PASSWORD');

    if ($dsn === false || $user === false || $pass === false) {
        throw new RuntimeException('Configure as variáveis de conexão do banco bhcloud.');
    }

    return new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}