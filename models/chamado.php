<?php
require_once 'conexao.php';

const CHAMADO_STATUS_RESP_CLIENTE = 6;

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
    $contratos = array_values(array_unique(array_filter(array_map('intval', $contratos))));
    if (!$contratos) {
        return array();
    }

    $placeholders = implode(', ', array_fill(0, count($contratos), '?'));
    $condicoes = array("chamado.id_contrato IN ($placeholders)");
    $parametros = $contratos;

    if ((int)$ano_atual !== 0) {
        if ((int)$mes_atual !== 0) {
            $condicoes[] = 'MONTH(evento.data) = ?';
            $parametros[] = (int)$mes_atual;
        }
        $condicoes[] = 'YEAR(evento.data) = ?';
        $parametros[] = (int)$ano_atual;
    }

    // Eu conto pela data da interação e mantenho técnicos que atuaram antes de ficarem inativos.
    $consulta = "SELECT pessoa.nome_pessoa AS tecnico, COUNT(*) AS total
    FROM contrato_chamado AS chamado
    INNER JOIN contrato_chamado_eventos AS evento ON evento.id_chamado = chamado.id
    INNER JOIN funcionario ON funcionario.id_pessoa = evento.id_pessoa
    INNER JOIN pessoa ON pessoa.id = funcionario.id_pessoa
    WHERE " . implode(' AND ', $condicoes) . "
    GROUP BY funcionario.id_pessoa, pessoa.nome_pessoa
    ORDER BY total DESC, pessoa.nome_pessoa";

    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute($parametros);
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

function obterQtdChamados($mes_atual, $ano_atual, $contratos) {
    $contrato_string = implode(", ", $contratos);
    $options = "id_contrato IN (".$contrato_string.")";
    if ($ano_atual != 0) {
        if($mes_atual!=0){
            $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }else{
            $options = "YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }
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
    $options = "id_contrato IN (".$contrato_string.") AND id_situacao <= 6";
    if ($ano_atual != 0) {
        if($mes_atual!=0){
            $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }else{
            $options = "YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }
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
    $options = "id_contrato IN (".$contrato_string.")";
    if ($ano_atual != 0) {
        if($mes_atual!=0){
            $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }else{
            $options = "YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }
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
    $options = "id_contrato IN (".$contrato_string.")";
    if ($ano_atual != 0) {
        if($mes_atual!=0){
            $options = "MONTH(data_abertura) = '".$mes_atual."' AND YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }else{
            $options = "YEAR(data_abertura) = '".$ano_atual."' AND ".$options;
        }
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

function obterChamadosPorSituacao($mes_atual, $ano_atual, $contratos) {
    $contratos = array_values(array_unique(array_filter(array_map('intval', $contratos))));
    if (!$contratos) {
        return array();
    }

    $placeholders = implode(', ', array_fill(0, count($contratos), '?'));
    $condicoes = array('chamado.id_contrato IN (' . $placeholders . ')');
    $parametros = $contratos;

    if ((int)$ano_atual !== 0) {
        if ((int)$mes_atual !== 0) {
            $condicoes[] = 'MONTH(chamado.data_abertura) = ?';
            $parametros[] = (int)$mes_atual;
        }
        $condicoes[] = 'YEAR(chamado.data_abertura) = ?';
        $parametros[] = (int)$ano_atual;
    }

    // Eu agrupo os chamados pelo status e aplico o mesmo período selecionado no dashboard.
    $consulta = "SELECT status.status AS situacao, COUNT(*) AS total
    FROM contrato_chamado chamado
    JOIN contrato_chamado_status status ON status.id = chamado.id_situacao
    WHERE " . implode(' AND ', $condicoes) . "
    GROUP BY status.id, status.status
    ORDER BY status.id";

    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute($parametros);
    return $sth->fetchAll(PDO::FETCH_ASSOC);
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
    ORDER BY a.data ASC, a.hora ASC, a.id_evento ASC";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll();
}

function obterRotuloEquipamentoChamado($id_tipo_contrato, $id_contrato, $id_equipamento) {
    $id_tipo_contrato = (int)$id_tipo_contrato;
    $id_contrato = (int)$id_contrato;
    $id_equipamento = (int)$id_equipamento;
    if ($id_equipamento <= 0) {
        return 'Não informado';
    }

    $dbh = getConexao();
    if ($id_tipo_contrato === 1) {
        $consulta = $dbh->prepare('SELECT equipamento.codigo, tipo.tipo AS tipo_equipamento, contato.nome, contrato.etiqueta
            FROM contrato_outsourcing_equipamentos equipamento
            LEFT JOIN contrato_outsourcing_equipamentos_tipo tipo ON tipo.id = equipamento.tipo
            LEFT JOIN pessoa_juridica_contatos contato ON contato.id = equipamento.contato
            LEFT JOIN contrato_outsourcing contrato ON contrato.id_contrato = equipamento.contrato
            WHERE equipamento.id = :id AND equipamento.contrato = :contrato');
        $consulta->execute(array(':id' => $id_equipamento, ':contrato' => $id_contrato));
        $equipamento = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($equipamento) {
            // Eu monto a identificação do equipamento com tipo, etiqueta, código e contato.
            $tipo = trim((string)($equipamento['tipo_equipamento'] ?? 'Equipamento'));
            $etiqueta = str_pad(trim((string)($equipamento['etiqueta'] ?? '')), 2, '0', STR_PAD_LEFT);
            $codigo = str_pad((string)$equipamento['codigo'], 4, '0', STR_PAD_LEFT);
            $contato = trim((string)($equipamento['nome'] ?? ''));
            return $tipo . ' [' . $etiqueta . '|' . $codigo . ']' . ($contato !== '' ? ' - ' . $contato : '');
        }
    } elseif ($id_tipo_contrato === 7) {
        $consulta = $dbh->prepare('SELECT equipamento.descricao, equipamento.patrimonio
            FROM contrato_locacao_equipamentos_locados locado
            JOIN contrato_locacao_equipamentos equipamento ON equipamento.id = locado.id_equipamento
            WHERE locado.id_contrato = :contrato AND equipamento.id = :id_equipamento
            LIMIT 1');
        $consulta->execute(array(':contrato' => $id_contrato, ':id_equipamento' => $id_equipamento));
        $equipamento = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($equipamento) {
            $patrimonio = trim((string)($equipamento['patrimonio'] ?? ''));
            return ($patrimonio !== '' ? $patrimonio . ' - ' : '') . $equipamento['descricao'];
        }
    }

    return 'Equipamento ' . $id_equipamento;
}

function obterUltimasInteracoesPorChamados($chamados) {
    $ids_chamados = array();
    foreach ($chamados as $chamado) {
        $id_chamado = (int)($chamado['id'] ?? 0);
        if ($id_chamado > 0) {
            $ids_chamados[] = $id_chamado;
        }
    }

    $ids_chamados = array_values(array_unique($ids_chamados));
    if (!$ids_chamados) {
        return array();
    }

    $placeholders = implode(', ', array_fill(0, count($ids_chamados), '?'));
    $consulta = "SELECT ultima.id_chamado, ultima.id_pessoa, ultima.id_equipamento,
                        ultima.data, ultima.hora, pessoa.nome_pessoa
    FROM (
        SELECT evento.id_chamado, evento.id_pessoa, evento.id_equipamento,
               evento.data, evento.hora,
               ROW_NUMBER() OVER (
                   PARTITION BY evento.id_chamado
                   ORDER BY evento.data DESC, evento.hora DESC, evento.id_evento DESC
               ) AS ordem
        FROM contrato_chamado_eventos evento
        WHERE evento.id_chamado IN ($placeholders)
    ) ultima
    LEFT JOIN pessoa ON pessoa.id = ultima.id_pessoa
    WHERE ultima.ordem = 1";

    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute($ids_chamados);

    $interacoes = array();
    foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $interacao) {
        $interacoes[(int)$interacao['id_chamado']] = $interacao;
    }
    return $interacoes;
}

function criarInteracao($chamado_id, $id_pessoa, $id_equipamento, $descricao, $status, $situacao_chamado = null) {
    $data = date('Y-m-d');
    $hora = date('H:i:s');
    $dbh = getConexao();
    try {
        $dbh->beginTransaction();
        $consulta = $dbh->prepare('INSERT INTO contrato_chamado_eventos(id_chamado, id_pessoa, id_equipamento, data, hora, descricao, status)
            VALUES (:chamado, :pessoa, :equipamento, :data, :hora, :descricao, :status)');
        $consulta->execute(array(
            ':chamado' => (int)$chamado_id,
            ':pessoa' => (int)$id_pessoa,
            ':equipamento' => (int)$id_equipamento,
            ':data' => $data,
            ':hora' => $hora,
            ':descricao' => $descricao,
            ':status' => (int)$status
        ));

        if ($situacao_chamado !== null) {
            // Eu registro a resposta e devolvo o chamado ao cliente na mesma transação.
            $atualizacao = $dbh->prepare('UPDATE contrato_chamado SET id_situacao = :situacao WHERE id = :chamado AND id_situacao <> 7');
            $atualizacao->execute(array(':situacao' => (int)$situacao_chamado, ':chamado' => (int)$chamado_id));
        }

        $dbh->commit();
        return true;
    } catch (Throwable $erro) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        return false;
    }
}

function abrirChamado($contrato_id, $id_tipo_contrato,  $tipo_solicitacao, $frm_nome, $frm_email, $frm_telefone, $frm_codigo_equipamento, $frm_descricao_solicitacao) {
    $data_abertura = date('Y-m-d H:i:s');
    $id_situacao = 1;
    $tipo_abertura = 2;
    $tipo_atendimento = 2;
    $resp_abertura = 0;
    $urgencia = 4;
    $seguranca = random_bytes(13);
    // Eu gravo a hora de abertura em data_saida e deixo data_entrada vazia.
    $consulta = "INSERT INTO contrato_chamado(id_contrato, id_tipo_contrato, resp_abertura, tipo_abertura, tipo_solicitacao, tipo_atendimento, urgencia, data_abertura, data_entrada, data_saida, id_situacao, seguranca, nome, email, telefone)
    VALUES('$contrato_id', '$id_tipo_contrato', '$resp_abertura', '$tipo_abertura', '$tipo_solicitacao', '$tipo_atendimento', '$urgencia', '$data_abertura', NULL, '$data_abertura', '$id_situacao', '$seguranca', '$frm_nome', '$frm_email', '$frm_telefone')";
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

        $interacaoCriada = criarInteracao($id_chamado, 0, $frm_codigo_equipamento, $frm_descricao_solicitacao, 1);
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
    WHERE a.id_contrato = :contrato
      AND a.data_abertura >= :data_inicial
      AND a.data_abertura < DATE_ADD(:data_final, INTERVAL 1 DAY)";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(
        ':contrato' => (int)$contrato,
        ':data_inicial' => $data_inicial,
        ':data_final' => $data_final
    ));
    return $sth->fetchAll();
}

function obterChamadosPorEquipamentoPeriodo($contrato, $equipamento, $data_inicial, $data_final) {
    $consulta = "SELECT chamado.*, pessoa.nome_pessoa
    FROM contrato_chamado chamado
    JOIN pessoa ON pessoa.id = chamado.resp_abertura
    WHERE chamado.id_contrato = :contrato
      AND chamado.data_abertura >= :data_inicial
      AND chamado.data_abertura < DATE_ADD(:data_final, INTERVAL 1 DAY)
      AND EXISTS (
          SELECT 1
          FROM contrato_chamado_eventos evento
          WHERE evento.id_chamado = chamado.id
            AND evento.id_equipamento = :equipamento
      )
    ORDER BY chamado.data_abertura DESC";

    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(
        ':contrato' => (int)$contrato,
        ':equipamento' => (int)$equipamento,
        ':data_inicial' => $data_inicial,
        ':data_final' => $data_final
    ));
    return $sth->fetchAll();
}

function obterChamadosTodosEquipamentosPeriodo($contratos, $data_inicial, $data_final) {
    $contratos = array_values(array_unique(array_map('intval', $contratos)));
    if (!$contratos) {
        return array();
    }

    $placeholders = implode(', ', array_fill(0, count($contratos), '?'));
    $consulta = "SELECT DISTINCT chamado.*, pessoa.nome_pessoa, evento.id_equipamento
    FROM contrato_chamado chamado
    JOIN pessoa ON pessoa.id = chamado.resp_abertura
    JOIN contrato_chamado_eventos evento ON evento.id_chamado = chamado.id
    WHERE chamado.id_contrato IN ($placeholders)
      AND chamado.data_abertura >= ?
      AND chamado.data_abertura < DATE_ADD(?, INTERVAL 1 DAY)
      AND evento.id_equipamento > 0
    ORDER BY chamado.data_abertura DESC";

    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array_merge($contratos, array($data_inicial, $data_final)));
    return $sth->fetchAll();
}