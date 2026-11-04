<?php

require 'models/chamado.php';
require 'models/contrato.php';

$menu = 'Relatórios';
$pagina = 'Por equipamento';

// Filtros pela URL (GET) para a paginação manter a pesquisa.
$data_inicial_texto = (string)($_GET['data_inicial'] ?? '01/01/' . date('Y'));
$data_final_texto = (string)($_GET['data_final'] ?? date('d/m/Y'));
$referencia = trim((string)($_GET['equipamento'] ?? 'todos'));
$itens_por_pagina = 20;
$pagina_atual = max(1, (int)($_GET['pagina'] ?? 1));
$total_chamados = 0;
$total_paginas = 1;

$ids_contratos = array_map('intval', $_SESSION['contratos'] ?? array());
$contratos = $ids_contratos ? obterContratosAtivos($ids_contratos) : array();
$equipamentos = obterEquipamentosParaRelatorio($contratos);
$equipamentos_por_referencia = array();
foreach ($equipamentos as $equipamento) {
    $equipamentos_por_referencia[$equipamento['referencia']] = $equipamento;
}
// Filtro Helpdesk/Locação: só aparece quando o cliente tem contrato ativo dos dois tipos.
$tipos_com_equipamento = array();
foreach ($contratos as $contrato) {
    if (in_array((int)$contrato['id_tipo'], array(1, 7), true)) {
        $tipos_com_equipamento[(int)$contrato['id_tipo']] = true;
    }
}
$mostrar_filtro_tipo = count($tipos_com_equipamento) > 1;
$tipo_filtro = (int)($_GET['tipo_contrato'] ?? 0);
if (!$mostrar_filtro_tipo || !in_array($tipo_filtro, array(1, 7), true)) {
    $tipo_filtro = 0; // 0 = todos os tipos
}

$chamados = array();
$equipamento_selecionado = $equipamentos_por_referencia[$referencia] ?? null;
// O equipamento escolhido precisa ser do tipo filtrado.
if ($equipamento_selecionado && $tipo_filtro && (int)$equipamento_selecionado['id_tipo'] !== $tipo_filtro) {
    $equipamento_selecionado = null;
}
$erro_relatorio = null;
$relatorio_executado = false;

$parse_data = static function ($valor) {
    $data = DateTimeImmutable::createFromFormat('!d/m/Y', $valor);
    $erros = DateTimeImmutable::getLastErrors();
    if ($data === false || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0)) || $data->format('d/m/Y') !== $valor) {
        return null;
    }
    return $data;
};

$data_inicial = null;
$data_final = null;

if (($_GET['acao'] ?? '') === 'filtrar') {
    $data_inicial = $parse_data($data_inicial_texto);
    $data_final = $parse_data($data_final_texto);

    if ($data_inicial === null || $data_final === null) {
        $erro_relatorio = 'Informe as duas datas no formato DD/MM/AAAA.';
    } elseif ($data_inicial > $data_final) {
        $erro_relatorio = 'A data inicial deve ser anterior ou igual à data final.';
    } elseif ($referencia !== 'todos' && $equipamento_selecionado === null) {
        $erro_relatorio = 'Selecione um equipamento ativo vinculado à sua conta.';
    } else {
        $relatorio_executado = true;
        // Contratos com equipamento (Helpdesk/Locação), respeitando o filtro de tipo.
        $contratos_com_equipamentos = array();
        foreach ($contratos as $contrato) {
            $tipo_contrato = (int)$contrato['id_tipo'];
            if (in_array($tipo_contrato, array(1, 7), true) && (!$tipo_filtro || $tipo_contrato === $tipo_filtro)) {
                $contratos_com_equipamentos[] = (int)$contrato['id'];
            }
        }
        $buscar_pagina = static function ($pagina) use ($contratos_com_equipamentos, $equipamento_selecionado, $data_inicial, $data_final, $itens_por_pagina) {
            return obterRelatorioEquipamentoPagina(
                $contratos_com_equipamentos,
                $equipamento_selecionado['id_contrato'] ?? 0,
                $equipamento_selecionado['id'] ?? 0,
                $data_inicial->format('Y-m-d'),
                $data_final->format('Y-m-d'),
                $itens_por_pagina,
                ($pagina - 1) * $itens_por_pagina
            );
        };
        $resultado = $buscar_pagina($pagina_atual);
        $total_chamados = (int)$resultado['total'];
        $total_paginas = max(1, (int)ceil($total_chamados / $itens_por_pagina));
        if ($pagina_atual > $total_paginas) {
            $pagina_atual = $total_paginas;
            $resultado = $buscar_pagina($pagina_atual);
        }
        $chamados = $resultado['itens'];
        foreach ($chamados as &$chamado) {
            $referencia_chamado = (int)$chamado['id_contrato'] . ':' . (int)$chamado['id_equipamento'];
            $chamado['equipamento_rotulo'] = $equipamentos_por_referencia[$referencia_chamado]['rotulo']
                ?? 'Equipamento ' . (int)$chamado['id_equipamento'];
        }
        unset($chamado);
    }
}

// Link de página mantendo os filtros.
$link_pagina = static function ($n) {
    $parametros = $_GET;
    unset($parametros['page']);
    $parametros['pagina'] = (int)$n;
    return '/relatorio_equipamento?' . http_build_query($parametros);
};

include 'views/relatorio_equipamento.php';
