<?php
require_once __DIR__ . '/erp_api.php';
require_once __DIR__ . '/email.php';

const CHAMADO_STATUS_RESP_CLIENTE = 6;

function chamado_ids_contratos($contratos): array {
    return array_values(array_unique(array_filter(array_map('intval', (array)$contratos), static function ($id) { return $id > 0; })));
}

function obterTiposSolicitacao() {
    return erp_api('chamado.tipos_solicitacao') ?: [];
}

function obterUrgenciaSolicitacao() {
    return erp_api('chamado.urgencias') ?: [];
}

function obterUltimosChamados($contratos, $qtd, $filtro='todos') {
    return erp_api('chamado.ultimos', [
        'contratos' => chamado_ids_contratos($contratos),
        'qtd' => (int)$qtd,
        'filtro' => (string)$filtro,
    ]) ?: [];
}

/** Chamados em aberto com solicitante, equipamento e última interação (uma chamada à API). */
function obterChamadosAbertosLista($contratos) {
    return erp_api('chamado.abertos_lista', ['contratos' => chamado_ids_contratos($contratos)]) ?: [];
}

/** Página de chamados fechados no mesmo formato da lista de abertos (solicitante e detalhes). */
function obterChamadosFechadosLista($contratos, $limite, $offset) {
    return erp_api('chamado.fechados_lista', [
        'contratos' => chamado_ids_contratos($contratos),
        'limite' => (int)$limite,
        'offset' => (int)$offset,
    ]) ?: [];
}

/** Encerramento pelo cliente logado (situação 7 + evento "Chamado encerrado pelo usuário"). */
function encerrarChamadoCliente($id_chamado, $contratos) {
    try {
        return (bool)erp_api('chamado.encerrar_cliente', [
            'id' => (int)$id_chamado,
            'contratos' => chamado_ids_contratos($contratos),
        ]);
    } catch (ErpApiErro $erro) {
        error_log('Falha ao encerrar chamado ' . (int)$id_chamado . ': ' . $erro->getMessage());
        return false;
    }
}

function contarChamadosFechados($contratos) {
    return (int)erp_api('chamado.contar_fechados', ['contratos' => chamado_ids_contratos($contratos)]);
}

function obterChamadosFechadosPagina($contratos, $limite, $offset) {
    return erp_api('chamado.fechados_pagina', [
        'contratos' => chamado_ids_contratos($contratos),
        'limite' => (int)$limite,
        'offset' => (int)$offset,
    ]) ?: [];
}

function obterUltimosChamadosDashboard($contratos) {
    return erp_api('chamado.ultimos_dashboard', ['contratos' => chamado_ids_contratos($contratos)]) ?: [];
}

function obterInteracaoPorTecnico($contratos, $mes_atual, $ano_atual) {
    return erp_api('chamado.interacao_por_tecnico', [
        'contratos' => chamado_ids_contratos($contratos),
        'mes' => (int)$mes_atual,
        'ano' => (int)$ano_atual,
    ]) ?: [];
}

function obterQtdChamados($mes_atual, $ano_atual, $contratos) {
    return (int)erp_api('chamado.quantidade', [
        'contratos' => chamado_ids_contratos($contratos),
        'mes' => (int)$mes_atual,
        'ano' => (int)$ano_atual,
    ]);
}

function obterQtdChamadosAbertos($mes_atual, $ano_atual, $contratos) {
    return (int)erp_api('chamado.quantidade', [
        'contratos' => chamado_ids_contratos($contratos),
        'mes' => (int)$mes_atual,
        'ano' => (int)$ano_atual,
        'somente_abertos' => true,
    ]);
}

function obterHorasTrabalhadas($mes_atual, $ano_atual, $contratos) {
    // Soma em segundos para preservar totais acima de 24 horas.
    $segundos = (int)erp_api('chamado.horas_segundos', [
        'contratos' => chamado_ids_contratos($contratos),
        'mes' => (int)$mes_atual,
        'ano' => (int)$ano_atual,
    ]);

    return sprintf('%d:%02d:%02d', intdiv($segundos, 3600), intdiv($segundos % 3600, 60), $segundos % 60);
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

    $notas = erp_api('chamado.notas_pesquisa', [
        'contratos' => chamado_ids_contratos($contratos),
        'mes' => (int)$mes_atual,
        'ano' => (int)$ano_atual,
    ]) ?: [];

    foreach ($notas as $nota) {
        if ($nota == 3) $resultado['muito_satisfeito']++;
        elseif ($nota == 2) $resultado['satisfeito']++;
        elseif ($nota == 1) $resultado['nao_satisfeito']++;
        elseif (empty($nota)) $resultado['nao_avaliados']++;
    }
    $resultado['total_avaliados'] = $resultado['muito_satisfeito'] + $resultado['satisfeito'] + $resultado['nao_satisfeito'];

    return $resultado;
}

function obterChamadosPorSituacao($mes_atual, $ano_atual, $contratos) {
    return erp_api('chamado.por_situacao', [
        'contratos' => chamado_ids_contratos($contratos),
        'mes' => (int)$mes_atual,
        'ano' => (int)$ano_atual,
    ]) ?: [];
}

function obterChamado($id_chamado) {
    return erp_api('chamado.obter', ['id' => (int)$id_chamado]) ?: [];
}

function obterUltimoChamado() {
    return erp_api('chamado.ultimo') ?: false;
}

function obterInteracoesPorChamados($id_chamado) {
    return erp_api('chamado.interacoes', ['id' => (int)$id_chamado]) ?: [];
}

function obterRotuloEquipamentoChamado($id_tipo_contrato, $id_contrato, $id_equipamento) {
    $id_tipo_contrato = (int)$id_tipo_contrato;
    $id_equipamento = (int)$id_equipamento;
    $linhas = erp_api('chamado.rotulo_equipamento', [
        'id_tipo_contrato' => $id_tipo_contrato,
        'id_contrato' => (int)$id_contrato,
        'id_equipamento' => $id_equipamento,
    ]) ?: [];

    $coluna = static function ($nome) use ($linhas) { return array_values(array_filter(array_map('trim', array_column($linhas, $nome)))); };

    if ($id_tipo_contrato === 1 && $linhas) {
        $rotulos = array();
        foreach ($linhas as $equipamento) {
            $tipo = trim((string)($equipamento['tipo_equipamento'] ?? 'Equipamento'));
            $etiqueta = str_pad(trim((string)($equipamento['etiqueta'] ?? '')), 2, '0', STR_PAD_LEFT);
            $codigo = str_pad((string)$equipamento['codigo'], 4, '0', STR_PAD_LEFT);
            $contato = trim((string)($equipamento['nome'] ?? ''));
            $rotulos[] = $tipo . ' [' . $etiqueta . '|' . $codigo . ']' . ($contato !== '' ? ' - ' . $contato : '');
        }
        return 'Equipamento: ' . implode('; ', $rotulos);
    } elseif ($id_tipo_contrato === 2 && ($sites = $coluna('dominio'))) {
        return 'Site: ' . implode('; ', $sites);
    } elseif ($id_tipo_contrato === 4 && ($dominios = $coluna('dominio'))) {
        return 'Domínio: ' . implode('; ', $dominios);
    } elseif ($id_tipo_contrato === 5 && ($planos = $coluna('plano'))) {
        return 'Plano de backup: ' . implode('; ', $planos);
    } elseif ($id_tipo_contrato === 6 && ($produtos = $coluna('descricao'))) {
        return 'Produto/serviço: ' . implode('; ', array_unique($produtos));
    } elseif ($id_tipo_contrato === 7 && $linhas) {
        $rotulos = array();
        foreach ($linhas as $equipamento) {
            $patrimonio = trim((string)($equipamento['patrimonio'] ?? ''));
            $rotulos[] = ($patrimonio !== '' ? $patrimonio . ' - ' : '') . $equipamento['descricao'];
        }
        return 'Equipamento: ' . implode('; ', $rotulos);
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

    $interacoes = array();
    foreach (erp_api('chamado.ultimas_interacoes', ['ids' => $ids_chamados]) ?: [] as $interacao) {
        $interacoes[(int)$interacao['id_chamado']] = $interacao;
    }
    return $interacoes;
}

function criarInteracao($chamado_id, $id_pessoa, $id_equipamento, $descricao, $status, $situacao_chamado = null) {
    try {
        return (bool)erp_api('chamado.criar_interacao', [
            'id_chamado' => (int)$chamado_id,
            'id_pessoa' => (int)$id_pessoa,
            'id_equipamento' => (int)$id_equipamento,
            'descricao' => (string)$descricao,
            'status' => (int)$status,
            'situacao_chamado' => $situacao_chamado === null ? null : (int)$situacao_chamado,
        ]);
    } catch (ErpApiErro $erro) {
        return false;
    }
}

/**
 * Abre o chamado e registra a primeira interação na mesma transação (no ERP).
 * Retorna ['id' => ..., 'seguranca' => ...] ou false.
 */
function abrirChamado($contrato_id, $id_tipo_contrato,  $tipo_solicitacao, $frm_nome, $frm_email, $frm_telefone, $frm_codigo_equipamento, $frm_descricao_solicitacao) {
    try {
        $chamado = erp_api('chamado.abrir', [
            'id_contrato' => (int)$contrato_id,
            'id_tipo_contrato' => (int)$id_tipo_contrato,
            'tipo_solicitacao' => (int)$tipo_solicitacao,
            'nome' => (string)$frm_nome,
            'email' => (string)$frm_email,
            'telefone' => (string)$frm_telefone,
            'id_equipamento' => (int)$frm_codigo_equipamento,
            'descricao' => (string)$frm_descricao_solicitacao,
        ]);
        return is_array($chamado) && !empty($chamado['id']) ? $chamado : false;
    } catch (ErpApiErro $erro) {
        return false;
    }
}

function reabrirChamado($id_chamado) {
    try {
        return (bool)erp_api('chamado.reabrir', ['id' => (int)$id_chamado]);
    } catch (ErpApiErro $erro) {
        error_log('Falha ao reabrir chamado ' . (int)$id_chamado . ': ' . $erro->getMessage());
        return false;
    }
}

function notificarReaberturaChamado($chamado) {
    $emailGerente = '';
    try {
        $emailGerente = trim((string)erp_api('contrato.email_gerente', ['id' => (int)$chamado['id_contrato']]));
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
    return erp_api('chamado.por_periodo', [
        'contrato' => (int)$contrato,
        'data_inicial' => (string)$data_inicial,
        'data_final' => (string)$data_final,
    ]) ?: [];
}

function obterChamadosFechadosPorPeriodo($contrato, $data_inicial, $data_final) {
    return erp_api('chamado.fechados_por_periodo', [
        'contrato' => (int)$contrato,
        'data_inicial' => (string)$data_inicial,
        'data_final' => (string)$data_final,
    ]) ?: [];
}

function obterChamadosPorEquipamentoPeriodo($contrato, $equipamento, $data_inicial, $data_final) {
    return erp_api('chamado.por_equipamento_periodo', [
        'contrato' => (int)$contrato,
        'equipamento' => (int)$equipamento,
        'data_inicial' => (string)$data_inicial,
        'data_final' => (string)$data_final,
    ]) ?: [];
}

function obterChamadosTodosEquipamentosPeriodo($contratos, $data_inicial, $data_final) {
    return erp_api('chamado.todos_equipamentos_periodo', [
        'contratos' => chamado_ids_contratos($contratos),
        'data_inicial' => (string)$data_inicial,
        'data_final' => (string)$data_final,
    ]) ?: [];
}
