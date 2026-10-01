<?php
require_once 'conexao.php';

function obterTiposSolicitacao() {
    $consulta = "SELECT * FROM `contrato_chamado_tipo_solicitacao`";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}


function obterUrgenciaSolicitacao() {
    $consulta = "SELECT * FROM `contrato_chamado_tipo_urgencia`";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

// Para revisar

function obterUltimosChamados($contratos, $qtd, $filtro='todos') {
    $limit = "";
    $condicao1 = "";

    if ($qtd>0) $limit = "LIMIT ".$qtd;
    if ($filtro=='aberto') $condicao1 = "AND id_situacao <= 6";
    elseif ($filtro=='fechado') $condicao1 = "AND id_situacao = 7";

    $contrato_string = implode(", ", $contratos);
    $consulta = "SELECT a.*, b.status as 'situacao', c.descricao as 'tipo_contrato'
    FROM contrato_chamado a
    JOIN contrato_chamado_status b ON a.id_situacao = b.id
    JOIN contrato_tipo c ON a.id_tipo_contrato=c.id
    WHERE id_contrato IN (".$contrato_string.")
    ".$condicao1."
    ORDER BY data_abertura DESC
    ".$limit.";";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function obterUltimosChamadosDashboard($contratos) {
    $contrato_string = implode(", ", $contratos);
    $consulta = "SELECT a.id, a.data_abertura, 'finalizado' as situacao, t.descricao as tipo_contrato
    FROM contrato_chamado a 
    LEFT JOIN contrato_tipo as t ON a.id_tipo_contrato=t.id
    WHERE id_contrato IN ($contrato_string) AND id_situacao=7
    ORDER BY data_abertura DESC
    LIMIT 10;";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();

    $consulta = "SELECT b.id, b.data_abertura, 'aberto' as situacao, t.descricao as tipo_contrato
    FROM contrato_chamado b
    LEFT JOIN contrato_tipo t ON b.id_tipo_contrato = t.id
    WHERE id_contrato IN ($contrato_string) AND id_situacao<7
    ORDER BY b.data_saida DESC
    LIMIT 10;";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res2 = $sth->fetchAll();

    $array_merged = array_merge($res2, $res);
    return array_slice($array_merged, 0, 8);
}

function obterInteracaoPorTecnico($contratos, $mes_atual, $ano_atual) {
    $consulta = "SELECT * FROM funcionario WHERE id_cargo >=5 AND id_cargo <=7 AND status = 'a'";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();

    $contrato_string = implode(", ", $contratos);
    if($mes_atual!=0){
        $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }else{
        $options = "YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }
    $interacoes = array();

    foreach ($res as $r) {
        $id_funcionario = $r['id_pessoa'];

        $consulta2 = "SELECT count(*) as total                       
        FROM contrato_chamado as c
        INNER JOIN contrato_chamado_eventos as e
        on c.id = e.id_chamado
        WHERE  $options and id_pessoa=$id_funcionario";
        $dbh = getConexao();
        $sth = $dbh->prepare($consulta2);
        $sth->execute();
        $res2 = $sth->fetchAll()[0];
        array_push($interacoes, array(
            "tecnico" => obterNome($id_funcionario),
            "total" => $res2['total']
        ));
    }
    return $interacoes;
}

function obterQtdChamados($mes_atual, $ano_atual, $contratos) {
    $contrato_string = implode(", ", $contratos);
    if($mes_atual!=0){
        $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }else{
        $options = "YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }
    $consulta = "SELECT id FROM contrato_chamado WHERE $options";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();
    return sizeof($res);
}

function obterQtdChamadosAbertos($mes_atual, $ano_atual, $contratos) {
    $contrato_string = implode(", ", $contratos);
    if($mes_atual!=0){
        $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.") AND id_situacao <= 6;";
    }else{
        $options = "YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.") AND id_situacao <= 6;";
    }
    $consulta = "SELECT id FROM contrato_chamado WHERE $options";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();
    return sizeof($res);
}

function obterHorasTrabalhadas($mes_atual, $ano_atual, $contratos) {
    $thoras = array();
    $segundos = 0;
    $contrato_string = implode(", ", $contratos);
    if($mes_atual!=0){
        $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }else{
        $options = "YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }
    $consulta = "SELECT id FROM contrato_chamado WHERE $options";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();

    foreach($res as $r) {
        $id_chamado = $r['id'];
        $consulta2 = "SELECT horas_total FROM contrato_chamado_eventos WHERE id_chamado=$id_chamado AND horas_total IS NOT NULL";
        $sth = $dbh->prepare($consulta2);
        $sth->execute();
        $res2 = $sth->fetchAll();
        foreach($res2 as $r2) {
            $horas = $r2['horas_total'];
            $thoras[] = $horas;
        }
    }

    foreach($thoras as $tempo) { //percorre o array $tempo
        list ($h,$m,$s) = explode( ':', $tempo ); //explode a variavel tempo e coloca as horas em $h, minutos em $m, e os segundos em $s

        if(!empty($h)){
            $segundos += $h * 3600;
        }
        $segundos += $m * 60;
        $segundos += $s;
    }
    
    $horas = floor( $segundos / 3600 ); //converte os segundos em horas e arredonda caso nescessario
    $segundos %= 3600;                  // pega o restante dos segundos subtraidos das horas
    $minutos = floor( $segundos / 60 ); //converte os segundos em minutos e arredonda caso nescessario
    $segundos %= 60;                    // pega o restante dos segundos subtraidos dos minutos
    
    return "$horas:$minutos:$segundos";
}

/**
 * Obter resultado de pesquisa de satisfação
 */
function obterResultadoPesquisa($mes_atual, $ano_atual, $contratos) {
    $resultado = array(
        "muito_satisfeito" => 0,
        "satisfeito" => 0,
        "nao_satisfeito" => 0,
        "nao_avaliados" => 0,
        "total_avaliados" => 0
    );

    $contrato_string = implode(", ", $contratos);
    if($mes_atual!=0){
        $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }else{
        $options = "YEAR(data_abertura) = '".$ano_atual."' AND id_contrato IN (".$contrato_string.")";
    }

    $consulta = "SELECT *
    FROM contrato_chamado as chamado LEFT JOIN contrato_chamado_avaliacao ON chamado.id = id_chamado WHERE id_situacao=7 AND $options";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    $res = $sth->fetchAll();

    foreach($res as $r) {
        $nota = $r['nota'];

        if($nota==3) $resultado['muito_satisfeito']++;
        elseif($nota==2) $resultado['satisfeito']++;
        elseif($nota==1) $resultado['nao_satisfeito']++;
        elseif(empty($nota)) $resultado['nao_avaliados']++;
    }
    $resultado['total_avaliados'] = $resultado['muito_satisfeito'] + $resultado['satisfeito'] + $resultado['nao_satisfeito'];

    return $resultado;
}

function obterChamado($id_chamado) {
    $consulta = "SELECT a.*, b.status as 'situacao', c.descricao as 'tipo_contrato'
    FROM contrato_chamado a
    JOIN contrato_chamado_status b ON a.id_situacao = b.id
    JOIN contrato_tipo c ON a.id_tipo_contrato=c.id
    WHERE a.id = $id_chamado";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function obterUltimoChamado() {
    $consulta = "SELECT id, seguranca 
    FROM contrato_chamado
    ORDER BY id DESC LIMIT 1";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();

    return $sth->fetch();
}

function obterInteracoesPorChamados($id_chamado) {
    $consulta = "SELECT a.*, b.nome_pessoa
    FROM contrato_chamado_eventos a
    LEFT JOIN pessoa b ON a.id_pessoa=b.id
    WHERE a.id_chamado=$id_chamado
    ORDER BY a.data ASC, a.hora ASC";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function criarInteracao($chamado_id, $id_pessoa, $id_equipamento, $descricao, $status) {
    $data = date('Y-m-d');
    $hora = date('H:i:s');
    $consulta = "INSERT INTO contrato_chamado_eventos(id_chamado, id_pessoa, id_equipamento, data, hora, descricao, status)
    VALUES ('$chamado_id', '$id_pessoa', '$id_equipamento', '$data', '$hora', '$descricao', '$status');";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    return $sth->execute();
}

function abrirChamado($contrato_id, $id_tipo_contrato,  $tipo_solicitacao, $frm_nome, $frm_email, $frm_telefone, $frm_codigo_equipamento, $frm_descricao_solicitacao) {
    $data_abertura = date('Y-m-d H:i:s');
    $id_situacao = 1;
    $tipo_abertura = 2;
    $tipo_atendimento = 2;
    $resp_abertura = 0;
    $urgencia = 4;
    $seguranca = random_bytes(13);
    $consulta = "INSERT INTO contrato_chamado(id_contrato, id_tipo_contrato, resp_abertura, tipo_abertura, tipo_solicitacao, tipo_atendimento, urgencia, data_abertura, id_situacao, seguranca, nome, email, telefone)
    VALUES('$contrato_id', '$id_tipo_contrato', '$resp_abertura', '$tipo_abertura', '$tipo_solicitacao', '$tipo_atendimento', '$urgencia', '$data_abertura', '$id_situacao', '$seguranca', '$frm_nome', '$frm_email', '$frm_telefone')";  
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();

    $interacaoCriada = false;
    if($sth->rowCount()>0) {    
        $consulta = "SELECT id
        FROM contrato_chamado
        ORDER BY id DESC
        LIMIT 1";
        $dbh = getConexao();
        $sth = $dbh->prepare($consulta);
        $sth->execute();
        $id_chamado = $sth->fetchAll()[0]['id'];

        $interacaoCriada = criarInteracao($id_chamado, 0, $_POST['codigo_equipamento'], $_POST['solicitacao'], 1);
    }
    return $interacaoCriada;
}

function reabrirChamado($id_chamado) {
    $consulta = "UPDATE contrato_chamado
    SET id_situacao=1
    WHERE id=$id_chamado;";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
}

function obterChamadosPorPeriodo($contrato, $data_inicial, $data_final) {
    $consulta = "SELECT a.*, b.nome_pessoa
    FROM contrato_chamado a
    JOIN pessoa b ON a.resp_abertura=b.id
    WHERE a.id_contrato=$contrato AND a.data_abertura BETWEEN '$data_inicial' AND '$data_final';";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}