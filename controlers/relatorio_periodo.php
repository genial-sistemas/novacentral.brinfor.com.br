<?php

include 'models/chamado.php';
include 'models/contrato.php';

$menu   = 'Relatórios';
$pagina = 'Por período';

$contratos = obterContratos($_SESSION['contratos']);
$chamados = array();
$chamados_primeira_interacao = array();
$erro_relatorio = null;

if (isset($_POST['acao']) && $_POST['acao'] === 'filtrar') {
    $data_inicial_texto = trim($_POST['data_inicial'] ?? '');
    $data_final_texto = trim($_POST['data_final'] ?? '');
    $contrato_id = filter_var($_POST['contrato'] ?? null, FILTER_VALIDATE_INT);
    $contratos_ids = array_map('intval', $_SESSION['contratos'] ?? array());
    $parse_data = static function ($valor) {
        $data = DateTimeImmutable::createFromFormat('!d/m/Y', $valor);
        $erros = DateTimeImmutable::getLastErrors();
        if ($data === false || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0)) || $data->format('d/m/Y') !== $valor) {
            return null;
        }
        return $data;
    };

    $data_inicial = $parse_data($data_inicial_texto);
    $data_final = $parse_data($data_final_texto);

    if ($data_inicial === null || $data_final === null) {
        $erro_relatorio = 'Informe as duas datas no formato DD/MM/AAAA.';
    } elseif ($data_inicial > $data_final) {
        $erro_relatorio = 'A data inicial deve ser anterior ou igual à data final.';
    } elseif ($contrato_id === false || !in_array($contrato_id, $contratos_ids, true)) {
        $erro_relatorio = 'Selecione um contrato válido.';
    } else {
        $chamados = obterChamadosPorPeriodo(
            $contrato_id,
            $data_inicial->format('Y-m-d'),
            $data_final->format('Y-m-d')
        );

        foreach ($chamados as $k => $ca) {
            $interacoes = obterInteracoesPorChamados($ca['id']);
            $chamados_primeira_interacao[$k] = $interacoes[0] ?? array();
        }
    }
}

include 'views/relatorio_periodo.php';
