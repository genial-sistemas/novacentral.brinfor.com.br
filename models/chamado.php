<?php
require_once 'conexao.php';
require_once __DIR__ . '/email.php';

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
    $dbh = getConexao();
    if ($id_tipo_contrato === 1) {
        $sql = 'SELECT equipamento.id, equipamento.codigo, tipo.tipo AS tipo_equipamento, contato.nome, contrato.etiqueta
            FROM contrato_outsourcing_equipamentos equipamento
            LEFT JOIN contrato_outsourcing_equipamentos_tipo tipo ON tipo.id = equipamento.tipo
            LEFT JOIN pessoa_juridica_contatos contato ON contato.id = equipamento.contato
            LEFT JOIN contrato_outsourcing contrato ON contrato.id_contrato = equipamento.contrato
            WHERE equipamento.contrato = :contrato';
        if ($id_equipamento > 0) {
            $sql .= ' AND equipamento.id = :id';
        } else {
            $sql .= " AND equipamento.status = 'a'";
        }
        $sql .= ' ORDER BY equipamento.codigo';
        $consulta = $dbh->prepare($sql);
        $parametros = array(':contrato' => $id_contrato);
        if ($id_equipamento > 0) {
            $parametros[':id'] = $id_equipamento;
        }
        $consulta->execute($parametros);
        $equipamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        if ($equipamentos) {
            $rotulos = array();
            foreach ($equipamentos as $equipamento) {
                $tipo = trim((string)($equipamento['tipo_equipamento'] ?? 'Equipamento'));
                $etiqueta = str_pad(trim((string)($equipamento['etiqueta'] ?? '')), 2, '0', STR_PAD_LEFT);
                $codigo = str_pad((string)$equipamento['codigo'], 4, '0', STR_PAD_LEFT);
                $contato = trim((string)($equipamento['nome'] ?? ''));
                $rotulos[] = $tipo . ' [' . $etiqueta . '|' . $codigo . ']' . ($contato !== '' ? ' - ' . $contato : '');
            }
            return 'Equipamento: ' . implode('; ', $rotulos);
        }
    } elseif ($id_tipo_contrato === 2) {
        $sql = 'SELECT dominio
            FROM contrato_hosting
            WHERE id_contrato = :contrato';
        if ($id_equipamento > 0) {
            $sql .= ' AND id = :id';
        }
        $consulta = $dbh->prepare($sql);
        $parametros = array(':contrato' => $id_contrato);
        if ($id_equipamento > 0) {
            $parametros[':id'] = $id_equipamento;
        }
        $consulta->execute($parametros);
        $sites = array_column($consulta->fetchAll(PDO::FETCH_ASSOC), 'dominio');
        $sites = array_values(array_filter(array_map('trim', $sites)));
        if ($sites) {
            return 'Site: ' . implode('; ', $sites);
        }
    } elseif ($id_tipo_contrato === 4) {
        $sql = 'SELECT dominio
            FROM contrato_dominio
            WHERE id_contrato = :contrato';
        if ($id_equipamento > 0) {
            $sql .= ' AND id = :id';
        }
        $consulta = $dbh->prepare($sql);
        $parametros = array(':contrato' => $id_contrato);
        if ($id_equipamento > 0) {
            $parametros[':id'] = $id_equipamento;
        }
        $consulta->execute($parametros);
        $dominios = array_column($consulta->fetchAll(PDO::FETCH_ASSOC), 'dominio');
        $dominios = array_values(array_filter(array_map('trim', $dominios)));
        if ($dominios) {
            return 'Domínio: ' . implode('; ', $dominios);
        }
    } elseif ($id_tipo_contrato === 5) {
        $sql = 'SELECT plano.plano
            FROM contrato_backup backup
            JOIN contrato_backup_planos plano ON plano.id = backup.id_plano
            WHERE backup.id_contrato = :contrato';
        if ($id_equipamento > 0) {
            $sql .= ' AND backup.id = :id';
        }
        $consulta = $dbh->prepare($sql);
        $parametros = array(':contrato' => $id_contrato);
        if ($id_equipamento > 0) {
            $parametros[':id'] = $id_equipamento;
        }
        $consulta->execute($parametros);
        $planos = array_column($consulta->fetchAll(PDO::FETCH_ASSOC), 'plano');
        $planos = array_values(array_filter(array_map('trim', $planos)));
        if ($planos) {
            return 'Plano de backup: ' . implode('; ', $planos);
        }
    } elseif ($id_tipo_contrato === 6) {
        $sql = 'SELECT produto.descricao
            FROM contrato_suporte suporte
            JOIN contrato_suporte_produto produto ON produto.id = suporte.id_produto
            WHERE suporte.id_contrato = :contrato';
        if ($id_equipamento > 0) {
            $sql .= ' AND produto.id = :id';
        }
        $consulta = $dbh->prepare($sql);
        $parametros = array(':contrato' => $id_contrato);
        if ($id_equipamento > 0) {
            $parametros[':id'] = $id_equipamento;
        }
        $consulta->execute($parametros);
        $produtos = array_column($consulta->fetchAll(PDO::FETCH_ASSOC), 'descricao');
        $produtos = array_values(array_filter(array_map('trim', $produtos)));
        if ($produtos) {
            return 'Produto/serviço: ' . implode('; ', array_unique($produtos));
        }
    } elseif ($id_tipo_contrato === 7) {
        $sql = 'SELECT equipamento.descricao, equipamento.patrimonio
            FROM contrato_locacao_equipamentos_locados locado
            JOIN contrato_locacao_equipamentos equipamento ON equipamento.id = locado.id_equipamento
            WHERE locado.id_contrato = :contrato';
        if ($id_equipamento > 0) {
            $sql .= ' AND equipamento.id = :id_equipamento';
        }
        $consulta = $dbh->prepare($sql);
        $parametros = array(':contrato' => $id_contrato);
        if ($id_equipamento > 0) {
            $parametros[':id_equipamento'] = $id_equipamento;
        }
        $consulta->execute($parametros);
        $equipamentos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        if ($equipamentos) {
            $rotulos = array();
            foreach ($equipamentos as $equipamento) {
                $patrimonio = trim((string)($equipamento['patrimonio'] ?? ''));
                $rotulos[] = ($patrimonio !== '' ? $patrimonio . ' - ' : '') . $equipamento['descricao'];
            }
            return 'Equipamento: ' . implode('; ', $rotulos);
        }
    }

    return $id_equipamento > 0 ? 'Item #' . $id_equipamento : 'Não informado';
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
    $dbh = getConexao();
    try {
        $dbh->beginTransaction();
        $atualizacao = $dbh->prepare('UPDATE contrato_chamado
            SET id_situacao = 1
            WHERE id = :id_chamado AND id_situacao = 7');
        $atualizacao->execute(array(':id_chamado' => (int)$id_chamado));
        if ($atualizacao->rowCount() === 0) {
            $dbh->rollBack();
            return false;
        }

        $consultaEquipamento = $dbh->prepare('SELECT id_equipamento
            FROM contrato_chamado_eventos
            WHERE id_chamado = :id_chamado
            ORDER BY data DESC, hora DESC, id_evento DESC
            LIMIT 1');
        $consultaEquipamento->execute(array(':id_chamado' => (int)$id_chamado));
        $idEquipamento = (int)($consultaEquipamento->fetchColumn() ?: 0);

        $evento = $dbh->prepare('INSERT INTO contrato_chamado_eventos
            (id_chamado, id_pessoa, id_equipamento, data, hora, descricao, status)
            VALUES (:id_chamado, 0, :id_equipamento, :data, :hora, :descricao, 3)');
        $evento->execute(array(
            ':id_chamado' => (int)$id_chamado,
            ':id_equipamento' => $idEquipamento,
            ':data' => date('Y-m-d'),
            ':hora' => date('H:i:s'),
            ':descricao' => 'Chamado foi reaberto pelo Usuário'
        ));

        $dbh->commit();
        return true;
    } catch (Throwable $erro) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        error_log('Falha ao reabrir chamado ' . (int)$id_chamado . ': ' . $erro->getMessage());
        return false;
    }
}

function notificarReaberturaChamado($chamado) {
    $emailGerente = '';
    try {
        $dbh = getConexao();
        $consulta = $dbh->prepare('SELECT email_gerente FROM contrato WHERE id = :id_contrato');
        $consulta->execute(array(':id_contrato' => (int)$chamado['id_contrato']));
        $emailGerente = trim((string)$consulta->fetchColumn());
    } catch (Throwable $erro) {
        error_log('Falha ao buscar o e-mail do gerente do contrato do chamado ' . (int)$chamado['id'] . ': ' . $erro->getMessage());
    }

    $destinatarios = array(
        'gerente do contrato' => $emailGerente,
        'gerência de TI' => 'gerenciati@brinfor.com.br',
        'usuário solicitante' => trim((string)($chamado['email'] ?? ''))
    );
    $idChamado = (int)$chamado['id'];
    $assunto = 'Re-abertura de chamado #' . $idChamado;
    $escapar = static function ($valor) {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    };
    $nomeSolicitante = trim((string)($chamado['nome'] ?? 'Usuário'));
    if ($nomeSolicitante === '') {
        $nomeSolicitante = 'Usuário';
    }
    $textoTipo = trim((string)($chamado['tipo_contrato'] ?? 'Não informado'));
    $emailSolicitante = trim((string)($chamado['email'] ?? ''));
    $telefoneSolicitante = trim((string)($chamado['telefone'] ?? ''));
    $dataAbertura = !empty($chamado['data_abertura'])
        ? date('d/m/Y H:i:s', strtotime($chamado['data_abertura']))
        : 'Não informada';
    $dataReabertura = date('d/m/Y H:i:s');
    $eventos = obterInteracoesPorChamados($idChamado);
    $idEquipamento = 0;
    foreach (array_reverse($eventos) as $evento) {
        if ((int)($evento['id_equipamento'] ?? 0) > 0) {
            $idEquipamento = (int)$evento['id_equipamento'];
            break;
        }
    }
    $recursoChamado = obterRotuloEquipamentoChamado(
        $chamado['id_tipo_contrato'] ?? 0,
        $chamado['id_contrato'] ?? 0,
        $idEquipamento
    );
    $urlInteracao = 'https://central.brinfor.com.br/interagir-chamado?id=' . $idChamado
        . '&seguranca=' . rawurlencode((string)($chamado['seguranca'] ?? ''));

    $linhasEventos = '';
    foreach ($eventos as $evento) {
        $nomeEvento = (int)($evento['id_pessoa'] ?? 0) === 0
            ? 'Usuário'
            : (trim((string)($evento['nome_pessoa'] ?? '')) ?: 'Técnico');
        $dataHoraEvento = trim((string)($evento['data'] ?? '') . ' ' . (string)($evento['hora'] ?? ''));
        $dataHoraFormatada = $dataHoraEvento !== '' ? date('d/m/Y H:i:s', strtotime($dataHoraEvento)) : '-';
        $descricaoEvento = stripslashes((string)($evento['descricao'] ?? ''));
        $linhasEventos .= '<tr>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #dee2e6;vertical-align:top">' . $escapar($dataHoraFormatada) . '</td>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #dee2e6;vertical-align:top">' . $escapar($nomeEvento) . '</td>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #dee2e6;vertical-align:top;word-break:break-word">' . nl2br($escapar($descricaoEvento)) . '</td>'
            . '<td style="padding:10px 12px;border-bottom:1px solid #dee2e6;vertical-align:top">' . $escapar($evento['status'] ?? '-') . '</td>'
            . '</tr>';
    }
    if ($linhasEventos === '') {
        $linhasEventos = '<tr><td colspan="4" style="padding:12px;border-bottom:1px solid #dee2e6">Nenhum evento registrado.</td></tr>';
    }

    $html = '<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="x-apple-disable-message-reformatting"><title>Re-abertura de chamado</title></head>'
        . '<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,sans-serif">'
        . '<table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#f5f5f5"><tr><td align="center" style="padding:0">'
        . '<table role="presentation" style="width:100%;max-width:600px;border-collapse:collapse;border:1px solid #fa9758;background:#fff;text-align:left">'
        . '<tr><td style="padding:16px;background:#fa9758;text-align:center">'
        . '<img src="https://central.brinfor.com.br/views/img/brInfor_logo_mini.png" alt="BRInfor" width="200" style="height:auto;max-width:100%;display:block;margin:0 auto">'
        . '</td></tr><tr><td style="padding:30px;color:#153643">'
        . '<h1 style="font-size:24px;line-height:30px;margin:0 0 20px;text-align:center">Re-abertura de chamado #' . $idChamado . '</h1>'
        . '<p style="margin:0 0 12px;font-size:16px;line-height:24px"><strong>O chamado foi reaberto pelo usuário.</strong></p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Contrato:</strong> ' . $escapar($textoTipo) . ' (ID: ' . $escapar($chamado['id_contrato'] ?? '') . ')</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Equipamento / site / serviço:</strong> ' . $escapar($recursoChamado) . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Responsável Abertura:</strong> ' . $escapar($nomeSolicitante) . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Email Abertura:</strong> ' . $escapar($emailSolicitante !== '' ? $emailSolicitante : 'Não informado') . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Telefone Abertura:</strong> ' . $escapar($telefoneSolicitante !== '' ? $telefoneSolicitante : 'Não informado') . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Tipo Solicitação:</strong> ' . $escapar($chamado['tipo_solicitacao'] ?? 'Não informado') . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Urgência:</strong> ' . $escapar($chamado['urgencia'] ?? 'Não informada') . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Data/Hora Abertura:</strong> ' . $escapar($dataAbertura) . '</p>'
        . '<p style="margin:0 0 10px;font-size:15px;line-height:22px"><strong>Data/Hora Re-abertura:</strong> ' . $escapar($dataReabertura) . '</p>'
        . '<div style="margin:24px 0;text-align:right"><a href="' . $escapar($urlInteracao) . '" style="display:inline-block;padding:12px 22px;background:#dd9933;color:#fff;text-decoration:none;font-weight:bold">Nova Interação</a></div>'
        . '</td></tr><tr><td style="padding:0 24px 28px">'
        . '<h2 style="font-size:21px;margin:8px 0 16px;text-align:center;color:#153643">Eventos</h2>'
        . '<table role="presentation" style="width:100%;border-collapse:collapse;font-size:13px;color:#343640">'
        . '<thead><tr>'
        . '<th align="left" style="padding:8px 12px;border-bottom:2px solid #dee2e6;color:#70737c;font-size:11px;text-transform:uppercase">Data - Hora</th>'
        . '<th align="left" style="padding:8px 12px;border-bottom:2px solid #dee2e6;color:#70737c;font-size:11px;text-transform:uppercase">Pessoa Responsável</th>'
        . '<th align="left" style="padding:8px 12px;border-bottom:2px solid #dee2e6;color:#70737c;font-size:11px;text-transform:uppercase">Descrição</th>'
        . '<th align="left" style="padding:8px 12px;border-bottom:2px solid #dee2e6;color:#70737c;font-size:11px;text-transform:uppercase">Status</th>'
        . '</tr></thead><tbody>' . $linhasEventos . '</tbody></table>'
        . '</td></tr><tr><td style="padding:14px;background:#fa9758;text-align:center;color:#fff;font-size:12px">BRInfor Solu&#231;&#245;es em TI</td></tr>'
        . '</table></td></tr></table></body></html>';
    $texto = "Re-abertura de chamado #$idChamado\n"
        . "O chamado foi reaberto pelo usuário.\n"
        . "Contrato: $textoTipo (ID: " . ($chamado['id_contrato'] ?? '') . ")\n"
        . "Equipamento / site / serviço: $recursoChamado\n"
        . "Responsável pela abertura: $nomeSolicitante\n"
        . "E-mail: " . ($emailSolicitante !== '' ? $emailSolicitante : 'Não informado') . "\n"
        . "Telefone: " . ($telefoneSolicitante !== '' ? $telefoneSolicitante : 'Não informado') . "\n"
        . "Data/Hora abertura: $dataAbertura\n"
        . "Data/Hora reabertura: $dataReabertura\n"
        . "Acesse: $urlInteracao";
    $resultados = array();
    $enderecosEnviados = array();

    foreach ($destinatarios as $rotulo => $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resultados[] = array(
                'destinatario' => $rotulo,
                'enviado' => false,
                'erro' => 'Endereço de e-mail ausente ou inválido.'
            );
            error_log('Não foi possível notificar a ' . $rotulo . ' da reabertura do chamado ' . $idChamado . ': endereço ausente ou inválido.');
            continue;
        }

        $chave = strtolower($email);
        if (isset($enderecosEnviados[$chave])) {
            continue;
        }
        $enderecosEnviados[$chave] = true;
        $resultado = enviarEmailHtml($email, $assunto, $html, $texto);
        $resultado['destinatario'] = $rotulo;
        $resultados[] = $resultado;
    }

    return $resultados;
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