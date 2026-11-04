<?php
// Download do PDF/XML da nota fiscal. A API só entrega se a nota for do cliente logado.
require_once __DIR__ . '/../models/financeiro.php';

$idNota = (int)($_GET['id'] ?? 0);
$formato = ($_GET['formato'] ?? '') === 'xml' ? 'xml' : 'pdf';

try {
    if ($idNota <= 0) {
        throw new ErpApiErro('Nota inválida.');
    }
    $arquivo = obterArquivoNotaFiscal($idNota, $_SESSION['id_pessoa'], $formato);
} catch (ErpApiErro $e) {
    error_log('Arquivo da nota ' . $idNota . ': ' . $e->getMessage());
    header('Location: /financeiro_notas?erro=arquivo');
    exit;
}

$nome = 'Nota Fiscal BRInfor ' . $idNota . '.' . $formato;
header('Content-Type: ' . ($formato === 'xml' ? 'application/xml' : 'application/pdf'));
header('Content-Disposition: ' . ($formato === 'xml' ? 'attachment' : 'inline') . "; filename*=UTF-8''" . rawurlencode($nome));
header('Content-Length: ' . strlen($arquivo['conteudo']));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
echo $arquivo['conteudo'];
exit;
