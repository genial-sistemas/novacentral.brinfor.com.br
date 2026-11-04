<?php

require "models/chamado.php";

$menu   = 'Chamados';
$pagina = '';

// Aceita o id pela URL ou pelo formulário das listas de chamados.
$id_chamado = (int)($_GET['id'] ?? $_POST['chamado_id'] ?? 0);
$contratos = array_map('intval', $_SESSION['contratos'] ?? []);
$chamado = $id_chamado > 0 ? (obterChamado($id_chamado)[0] ?? null) : null;

// O chamado precisa ser de um contrato do cliente logado.
if (!$chamado || !in_array((int)$chamado['id_contrato'], $contratos, true)) {
    if (!headers_sent()) {
        http_response_code(404);
    }
    include 'views/404.php';
    exit;
}

// A conversa (leitura, envio e anexos) é carregada pelo componente views/partials/conversa.php via /conversa.
include 'views/chamados_interacoes.php';
