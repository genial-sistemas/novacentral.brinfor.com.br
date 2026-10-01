<?php
require_once 'conexao.php';

function obterContratosTiposAtivos() {
    $consulta = "SELECT id, descricao FROM contrato_tipo WHERE status='a'";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function obterContratosAtivos($ids_contatos) {

    $contratos_string = implode(", ", $ids_contatos);
    $consulta = "SELECT c.id, p.nome_pessoa as gerente, t.id as id_tipo, t.descricao as tipo_contrato, c.dia_vencimento, c.valor FROM contrato as c
    LEFT JOIN contrato_tipo as t ON c.id_tipo=t.id
    JOIN pessoa as p ON c.vendedor=p.id
    WHERE c.id IN ($contratos_string) AND c.status=2";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $contratosAtivos = $sth->fetchAll();

    return $contratosAtivos;
}

function obterContratoOutsourcingEquipamentosaAtivos($id_contato) {
    $consulta = "SELECT * FROM contrato_outsourcing_equipamentos 
    WHERE contrato=$id_contato
    AND status='a'
    ORDER BY codigo ASC";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function obterNomeContato($id_contato) {
    $consulta = "SELECT * FROM pessoa_juridica_contatos 
    WHERE id=$id_contato";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $resultado = $sth->fetch();

    return $resultado;
}

function obterEtiqueta($id_contato) {
    $consulta = "SELECT etiqueta FROM contrato_outsourcing WHERE id_contrato=$id_contato";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $resultado = $sth->fetch();
    return $resultado['etiqueta'];
}


function obterContratos($contratos_ids) {
    $contratos_string = implode(", ", $contratos_ids);
    $consulta = "SELECT a.*, b.descricao AS 'tipo_nome'
    FROM contrato a
    JOIN contrato_tipo b ON a.id_tipo=b.id
    WHERE a.id IN ($contratos_string)";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}


function obterContratoOutsourcingPorId($id) {
    $consulta = "SELECT * FROM contrato_outsourcing_equipamentos WHERE id=$id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll()[0];
}




function obterMaquinasAtivas($contratos) {
    $contrato_string = implode(", ", $contratos);
    $consulta = "SELECT id,contrato,status,codigo,usuario, patrimonio 
    FROM contrato_outsourcing_equipamentos 
    WHERE contrato IN (".$contrato_string.")
    AND status='a'";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();
    return sizeof($res);
}

function obterMaquinasInativas($contratos) {
    $contrato_string = implode(", ", $contratos);
    $consulta = "SELECT id,contrato,status,codigo,usuario, patrimonio 
    FROM contrato_outsourcing_equipamentos 
    WHERE contrato IN (".$contrato_string.")
    AND status='i'";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();
    return sizeof($res);
}


function obterContratoHospedagem($contrato_id) {
    $consulta = "SELECT *
    FROM contrato_hosting
    WHERE id_contrato=$contrato_id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll()[0];
}

function obterContratoHospedagemPorId($id) {
    $consulta = "SELECT *
    FROM contrato_hosting
    WHERE id=$id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll()[0];
}


function obterContratoBackup($contrato_id) {
    $consulta = "SELECT a.*, b.plano
    FROM contrato_backup a
    JOIN contrato_backup_planos b ON a.id_plano=b.id
    WHERE a.id_contrato = $contrato_id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll()[0];
}


function obterContratoBackupPorId($id) {
    $consulta = "SELECT *
    FROM contrato_backup
    WHERE id=$id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll()[0];
}
?>