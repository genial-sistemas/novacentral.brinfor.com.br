<?php
// ============================================================
// CAPTURA DE PARÂMETROS COM FALLBACK
// ============================================================

// Tenta capturar de $_GET primeiro
$id_chamado = $_GET['id'] ?? null;
$id_seguranca = $_GET['seguranca'] ?? null;

// Se falhou, tenta da query string manualmente
if (!$id_chamado || !$id_seguranca) {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
    $id_chamado = $params['id'] ?? $id_chamado;
    $id_seguranca = $params['seguranca'] ?? $id_seguranca;
}

// Se ainda falhou, tenta extrair da REQUEST_URI com regex
if (!$id_chamado || !$id_seguranca) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('/[?&]id=([^&]+)/', $uri, $matches)) {
        $id_chamado = $matches[1];
    }
    if (preg_match('/[?&]seguranca=([^&]+)/', $uri, $matches)) {
        $id_seguranca = $matches[1];
    }
}

// Validação final
if (!$id_chamado || !$id_seguranca) {
    // Em vez de die(), mostra uma mensagem mais informativa
    echo '<h3>Erro: Parâmetros não encontrados</h3>';
    echo '<p>URL acessada: ' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . '</p>';
    echo '<p>QUERY_STRING: ' . htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') . '</p>';
    echo '<p>$_GET: </p><pre>';
    var_dump($_GET);
    echo '</pre>';
    exit;
}

// (Opcional) Salva na sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['id_chamado'] = $id_chamado;
$_SESSION['id_seguranca'] = $id_seguranca;

$urlInteragir = sprintf('/chamados_interagir?id=%s&seguranca=%s', $id_chamado, $id_seguranca);
include 'header.php';
?>
<body class="az-body az-body-sidebar az-light">
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
    </div>
</div>
    <div id="successContainer" class="az-content-body">
        <div class="row">
            <div class="col-md-12">
                <p class="mb-5 mt-3" style="font-size: 30px;">Chamado <b id="chamadoIdText"><?= htmlspecialchars($id_chamado) ?></b> aberto com sucesso!!</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='<?= htmlspecialchars($urlInteragir) ?>'">Interagir com Chamado</button>
                <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='chamados_abrir'">Novo Chamado</button>
            </div>
        </div>
    </div>
<?php
    include 'footer.php';
?>