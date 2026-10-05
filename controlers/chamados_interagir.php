<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = '';

$id_chamado = (int)($_GET['id'] ?? $_POST['chamado_id'] ?? 0);
$contratos = array_map('intval', $_SESSION['contratos'] ?? []);
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
    ? data_brasil($ultima_interacao['data']) . ' às ' . $ultima_interacao['hora']
    : '';
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
// Eu mostro só a data de abertura no resumo do chamado.
$data_abertura = date('d/m/Y', strtotime($chamado['data_abertura']));

if (($_GET['atualizar'] ?? '') === '1') {
    // Eu entrego o estado mais recente para atualizar a conversa sem recarregar a página.
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    $eventos_json = array_map(static function ($interacao) {
        return array(
            'id_evento' => (int)$interacao['id_evento'],
            'id_pessoa' => (int)$interacao['id_pessoa'],
            'nome_pessoa' => $interacao['nome_pessoa'] ?? '',
            'data' => $interacao['data'],
            'hora' => $interacao['hora'],
            'descricao' => $interacao['descricao']
        );
    }, $interacoes);

    echo json_encode(array(
        'situacao' => $chamado['situacao'],
        'id_situacao' => (int)$chamado['id_situacao'],
        'ultima_interacao' => $ultima_interacao_data_hora,
        'tecnico' => $ultima_interacao_tecnico,
        'equipamento' => $equipamento_rotulo,
        'eventos' => $eventos_json
    ), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova-msg'])) {
    $nova_mensagem = trim($_POST['nova-msg']);
    if ($nova_mensagem !== '' && (int)$chamado['id_situacao'] !== 7) {
        if (criarInteracao($id_chamado, 0, $ultima_interacao_equipamento, $nova_mensagem, 1, CHAMADO_STATUS_RESP_CLIENTE)) {
            header('Location: /chamados_interagir?id=' . $id_chamado);
            exit;
        }
    }
    $erro_interacao = 'Não foi possível enviar a interação. Verifique a mensagem e tente novamente.';
}

include 'views/chamados_interacoes.php';
?>