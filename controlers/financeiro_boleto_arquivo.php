<?php
// Download do PDF do boleto. A API só entrega se o boleto for do cliente logado.
require_once __DIR__ . '/../models/financeiro.php';

$idBoleto = (int)($_GET['id'] ?? 0);
try {
    if ($idBoleto <= 0) {
        throw new ErpApiErro('Boleto inválido.');
    }
    $pdf = obterPdfBoleto($idBoleto, $_SESSION['id_pessoa']);
} catch (ErpApiErro $e) {
    error_log('PDF do boleto ' . $idBoleto . ': ' . $e->getMessage());
    header('Location: /financeiro_boletos?erro=arquivo');
    exit;
}

header('Content-Type: application/pdf');
header("Content-Disposition: inline; filename*=UTF-8''" . rawurlencode('Boleto BRInfor ' . $idBoleto . '.pdf'));
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
echo $pdf;
exit;
