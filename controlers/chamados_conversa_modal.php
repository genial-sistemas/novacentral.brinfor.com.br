<?php
// Conversa do chamado sem menu/cabeçalho, para abrir dentro de um modal (iframe) no relatório por equipamento.
require "models/chamado.php";

$id_chamado = (int)($_GET['id'] ?? 0);
$contratos = array_map('intval', $_SESSION['contratos'] ?? []);
$chamado = $id_chamado > 0 ? (obterChamado($id_chamado)[0] ?? null) : null;

// O chamado precisa ser de um contrato do cliente logado.
if (!$chamado || !in_array((int)$chamado['id_contrato'], $contratos, true)) {
    http_response_code(404);
    exit('Chamado não encontrado.');
}
// Só chamados encerrados (situação 7): no modal é apenas leitura, sem envio de novas interações.
if ((int)$chamado['id_situacao'] !== 7) {
    exit('Este chamado não está encerrado. Abra-o pela lista de chamados abertos para interagir.');
}

$conversa_id = (int)$chamado['id'];
$conversa_seguranca = '';
if (empty($_SESSION['csrf_conversa'])) {
    $_SESSION['csrf_conversa'] = bin2hex(random_bytes(32));
}
$conversa_token = $_SESSION['csrf_conversa'];
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chamado <?=(int)$conversa_id?></title>
    <style>
        html, body { height: 100%; margin: 0; font-family: Arial, sans-serif; }
        .conversa { height: 100%; min-height: 0; border: 0; border-radius: 0; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }
    </style>
</head>
<body>
<?php include 'views/partials/conversa.php'; ?>
</body>
</html>
