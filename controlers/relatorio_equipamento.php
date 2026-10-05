<?php

require 'models/chamado.php';
require 'models/contrato.php';

$menu = 'Relatórios';
$pagina = 'Por equipamento';

$data_inicial_texto = $_POST['data_inicial'] ?? '01/01/' . date('Y');
$data_final_texto = $_POST['data_final'] ?? date('d/m/Y');
$referencia = trim($_POST['equipamento'] ?? 'todos');
$ids_contratos = array_map('intval', $_SESSION['contratos'] ?? array());
$contratos = $ids_contratos ? obterContratosAtivos($ids_contratos) : array();
$equipamentos = obterEquipamentosParaRelatorio($contratos);
$equipamentos_por_referencia = array();
foreach ($equipamentos as $equipamento) {
    $equipamentos_por_referencia[$equipamento['referencia']] = $equipamento;
}
$chamados = array();
$chamados_primeira_interacao = array();
$equipamento_selecionado = $equipamentos_por_referencia[$referencia] ?? null;
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

if (isset($_POST['acao']) && $_POST['acao'] === 'filtrar') {
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
        if ($referencia === 'todos') {
            $contratos_com_equipamentos = array();
            foreach ($contratos as $contrato) {
                if (in_array((int)$contrato['id_tipo'], array(1, 7), true)) {
                    $contratos_com_equipamentos[] = (int)$contrato['id'];
                }
            }
            $chamados = obterChamadosTodosEquipamentosPeriodo(
                $contratos_com_equipamentos,
                $data_inicial->format('Y-m-d'),
                $data_final->format('Y-m-d')
            );
            foreach ($chamados as &$chamado) {
                $referencia_chamado = (int)$chamado['id_contrato'] . ':' . (int)$chamado['id_equipamento'];
                $chamado['equipamento_rotulo'] = $equipamentos_por_referencia[$referencia_chamado]['rotulo'] ?? 'Equipamento ' . (int)$chamado['id_equipamento'];
            }
            unset($chamado);
        } else {
            $chamados = obterChamadosPorEquipamentoPeriodo(
                $equipamento_selecionado['id_contrato'],
                $equipamento_selecionado['id'],
                $data_inicial->format('Y-m-d'),
                $data_final->format('Y-m-d')
            );
            foreach ($chamados as &$chamado) {
                $chamado['equipamento_rotulo'] = $equipamento_selecionado['rotulo'];
            }
            unset($chamado);
        }

        foreach ($chamados as $indice => $chamado) {
            $interacoes = obterInteracoesPorChamados($chamado['id']);
            $chamados_primeira_interacao[$indice] = $interacoes[0] ?? array();
        }
    }
}

include 'views/relatorio_equipamento.php';
