<?php
require_once 'conexao.php';

function obterNome($id){
    $consulta = "SELECT nome_pessoa FROM pessoa WHERE id=$id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll()[0];
    return $res['nome_pessoa'];
}