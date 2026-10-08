<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = 'Listar Fechados';

$contratos = array_map('intval', $_SESSION['contratos'] ?? []);
$erro_reabrir = '';

if (isset($_POST['acao']) && $_POST['acao'] === 'reabrir_chamado') {
    $id_chamado = (int)($_POST['chamado_id'] ?? 0);
    $chamado = $id_chamado > 0 ? (obterChamado($id_chamado)[0] ?? null) : null;

    if ($chamado && in_array((int)$chamado['id_contrato'], $contratos, true) && (int)$chamado['id_situacao'] === 7 && reabrirChamado($id_chamado)) {
        $resultados_email = notificarReaberturaChamado($chamado);
        $email_reabertura_falhou = count(array_filter($resultados_email, static function ($resultado) {
            return empty($resultado['enviado']);
        })) > 0;
        header('Location: /chamados_listar_abertos?reaberto=1' . ($email_reabertura_falhou ? '&email=failed' : ''));
        exit;
    }

    $erro_reabrir = 'Não foi possível reabrir o chamado. Atualize a página e tente novamente.';
}

if (isset($_POST['acao']) && $_POST['acao'] === 'detalhes') {
    $id_chamado = (int)($_POST['chamado_id'] ?? 0);
    $chamado = $id_chamado > 0 ? (obterChamado($id_chamado)[0] ?? null) : null;
    if (!$chamado || !in_array((int)$chamado['id_contrato'], $contratos, true)) {
        http_response_code(404);
        include 'views/404.php';
        exit;
    }
	$interacoes = obterInteracoesPorChamados($id_chamado);
    $ultima_interacao = $interacoes ? $interacoes[count($interacoes) - 1] : [];
	$ultima_interacao_tecnico = 'Não informado';
    foreach (array_reverse($interacoes) as $interacao) {
        if ((int)($interacao['id_pessoa'] ?? 0) > 0 && !empty($interacao['nome_pessoa'])) {
            $ultima_interacao_tecnico = $interacao['nome_pessoa'];
            break;
        }
    }
	$ultima_interacao_data_hora = isset($ultima_interacao['data'], $ultima_interacao['hora'])
        ? data_brasil($ultima_interacao['data'])." às ".$ultima_interacao['hora']
        : '-';
	$ultima_interacao_equipamento = 0;
    foreach (array_reverse($interacoes) as $interacao) {
        if ((int)($interacao['id_equipamento'] ?? 0) > 0) {
            $ultima_interacao_equipamento = (int)$interacao['id_equipamento'];
            break;
        }
    }
    $equipamento_rotulo = obterRotuloEquipamentoChamado(
        $chamado['id_tipo_contrato'],
        $chamado['id_contrato'],
        $ultima_interacao_equipamento
    );
    $data_abertura = date('d/m/Y', strtotime($chamado['data_abertura']));

	include 'views/chamados_interacoes.php';

}else{
    $itens_por_pagina = 30;
    $total_chamados = contarChamadosFechados($contratos);
    $total_paginas = max(1, (int)ceil($total_chamados / $itens_por_pagina));
    $pagina_atual = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
    $pagina_atual = max(1, min($total_paginas, $pagina_atual ?: 1));

	$chamados_fechados = obterChamadosFechadosPagina(
        $contratos,
        $itens_por_pagina,
        ($pagina_atual - 1) * $itens_por_pagina
    );
	$chamados_fechados_ultima_interacao = obterUltimasInteracoesPorChamados($chamados_fechados);

	include 'views/chamados_listar_fechados.php';
}
