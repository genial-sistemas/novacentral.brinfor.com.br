<?php
date_default_timezone_set('America/Sao_Paulo');

function getConexao(){
    $dsn  = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
    $user = 'bhcloud_admin';
    $pass = '$Qnv3hf@BeBL';

    try{
        $pdo = new PDO($dsn, $user, $pass);
        return $pdo;

    } catch (PDOException $ex) {
        echo 'Error: '.$ex->getMessage();
    }

}