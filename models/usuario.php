<?php
include 'conexao.php';


function obterUsuario($usuario, $senha) {
    $consulta = "SELECT * FROM contrato_login WHERE usuario='".$usuario."' AND senha='".$senha."';";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function obterContratosUsuario($id_pessoa) {
    $consulta = "SELECT * FROM `contrato` where id_pessoa='".$id_pessoa."';";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();
    $contratos = array();
    foreach ($res as $r) {
        array_push($contratos, $r['id']);
    }
    return $contratos;
}

function obterNomeCliente($id_contrato) {
    $consulta = "SELECT * FROM contrato WHERE id='".$id_contrato."'";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();
    $id_tipo = $res[0]['id_tipo'];
    $id_pessoa = $res[0]['id_pessoa'];
    if ($id_tipo==1) {
        $consulta = "SELECT * FROM contrato_outsourcing where id_contrato='$id_contrato'";
        $dbh = getConexao();
        $sth = $dbh->prepare($consulta);
        $sth->execute();
        $res2 = $sth->fetchAll()[0];
        return $res2['nome'];
    } else {
        $consulta = "SELECT nome_pessoa FROM pessoa WHERE id='".$id_pessoa."';";
        $dbh = getConexao();
        $sth = $dbh->prepare($consulta);
        $sth->execute();
        $res3 = $sth->fetchAll();
        return $res3['nome_pessoa'];
    }
}

function obterNome2($id) {
    $consulta = "SELECT nome_pessoa FROM pessoa WHERE id='".$id."';";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res3 = $sth->fetchAll()[0];
    return $res3['nome_pessoa'];
}