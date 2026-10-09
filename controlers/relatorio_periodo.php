<?php

include 'models/chamado.php';
include 'models/contrato.php';

$menu   = 'Relatórios';
$pagina = 'Por período';

$contratos = obterContratos($_SESSION['contratos']);
$primeiro_dia_mes = new DateTimeImmutable('first day of this month');
$data_inicial_texto = trim($_POST['data_inicial'] ?? $primeiro_dia_mes->format('d/m/Y'));
$data_final_texto = trim($_POST['data_final'] ?? $primeiro_dia_mes->modify('last day of this month')->format('d/m/Y'));
$contrato_id_texto = $_POST['contrato'] ?? '';
$chamados = array();
$chamados_primeira_interacao = array();
$erro_relatorio = null;
$relatorio_executado = false;

if (isset($_POST['acao']) && $_POST['acao'] === 'detalhes') {
    $id_chamado = filter_var($_POST['chamado_id'] ?? null, FILTER_VALIDATE_INT);
    $contratos_ids = array_map('intval', $_SESSION['contratos'] ?? array());
    $chamado = $id_chamado && $id_chamado > 0 ? (obterChamado($id_chamado)[0] ?? null) : null;

    if (
        !$chamado
        || !in_array((int)$chamado['id_contrato'], $contratos_ids, true)
        || (int)$chamado['id_situacao'] !== 7
    ) {
        http_response_code(404);
        include 'views/404.php';
        exit;
    }

    $interacoes = obterInteracoesPorChamados($id_chamado);
    $ultima_interacao = $interacoes ? $interacoes[count($interacoes) - 1] : array();
    $ultima_interacao_tecnico = 'Não informado';
    foreach (array_reverse($interacoes) as $interacao) {
        if ((int)($interacao['id_pessoa'] ?? 0) > 0 && !empty($interacao['nome_pessoa'])) {
            $ultima_interacao_tecnico = $interacao['nome_pessoa'];
            break;
        }
    }
    $ultima_interacao_data_hora = isset($ultima_interacao['data'], $ultima_interacao['hora'])
        ? data_brasil($ultima_interacao['data']) . ' às ' . $ultima_interacao['hora']
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
    exit;
}

if (isset($_POST['acao']) && $_POST['acao'] === 'filtrar') {
    $contrato_id = filter_var($contrato_id_texto, FILTER_VALIDATE_INT);
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
        $relatorio_executado = true;
        $chamados = obterChamadosFechadosPorPeriodo(
            $contrato_id,
            $data_inicial->format('Y-m-d'),
            $data_final->format('Y-m-d')
        );

        foreach ($chamados as $k => $ca) {
            $segundos = (int)$ca['horas_totais_segundos'];
            $chamados[$k]['horas_totais'] = sprintf(
                '%d:%02d:%02d',
                intdiv($segundos, 3600),
                intdiv($segundos % 3600, 60),
                $segundos % 60
            );
            $interacoes = obterInteracoesPorChamados($ca['id']);
            $chamados_primeira_interacao[$k] = $interacoes[0] ?? array();
        }
    }
}

include 'views/relatorio_periodo.php';
