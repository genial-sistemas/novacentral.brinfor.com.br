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
$ultima_interacao_tecnico = $ultima_interacao['nome_pessoa'] ?? 'Não informado';
$ultima_interacao_data_hora = isset($ultima_interacao['data'], $ultima_interacao['hora'])
    ? data_brasil($ultima_interacao['data']) . ' às ' . $ultima_interacao['hora']
    : '';
$ultima_interacao_equipamento = (int)($ultima_interacao['id_equipamento'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova-msg'])) {
    $nova_mensagem = trim($_POST['nova-msg']);
    if ($nova_mensagem !== '' && (int)$chamado['id_situacao'] !== 7) {
        if (criarInteracao($id_chamado, 0, $ultima_interacao_equipamento, addslashes($nova_mensagem), 1)) {
            header('Location: /chamados_interagir?id=' . $id_chamado);
            exit;
        }
    }
    $erro_interacao = 'Não foi possível enviar a interação. Verifique a mensagem e tente novamente.';
}

include 'views/chamados_interacoes.php';
?>