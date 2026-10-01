<?php
// api/index.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Se for OPTIONS (preflight do CORS), retorna 200 e sai
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Pega o endpoint da URL
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Rotas da API
switch ($endpoint) {
    case 'eventos':
        include __DIR__ . '/buscar-eventos-chamado.php';
        break;
    case 'info':
        include __DIR__ . '/buscar-info-chamado.php';
        break;
    case 'enviar':
        include __DIR__ . '/enviar-evento-chamado.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint não encontrado']);
        break;
}
?>