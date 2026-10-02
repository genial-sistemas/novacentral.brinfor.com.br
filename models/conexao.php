<?php
date_default_timezone_set('America/Sao_Paulo');

function getConexao(){
    $dsn  = 'mysql:host=localhost;dbname=bhcloud;charset=utf8';
    $user = 'root';
    $pass = '';

    try{
        $pdo = new PDO($dsn, $user, $pass);
        return $pdo;

    } catch (PDOException $ex) {
        echo 'Error: '.$ex->getMessage();
    }

}