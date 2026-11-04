<?php
// ============================================
// Conversa do chamado pelo link do e-mail (sem login): id + código de segurança.
// Leitura, envio e anexos ficam no componente views/partials/conversa.php (rota /conversa).
// ============================================
ini_set('display_errors', '0');
require_once __DIR__ . '/../../models/erp_api.php';

$idChamado = (int)($_GET['id'] ?? 0);
$seguranca = substr(trim((string)($_GET['seguranca'] ?? '')), 0, 128);

$erroVisitante = null;
$chamado = null;
if ($idChamado <= 0 || $seguranca === '') {
    $erroVisitante = 'Link inválido. Use o link enviado no e-mail do chamado.';
} else {
    try {
        $chamado = erp_api('chamado.obter_seguro', ['id' => $idChamado, 'seguranca' => $seguranca]);
        if (!$chamado) {
            $erroVisitante = 'Chamado não encontrado. Confira o link enviado por e-mail.';
        }
    } catch (ErpApiErro $e) {
        error_log('Interação visitante, chamado ' . $idChamado . ': ' . $e->getMessage());
        $erroVisitante = 'Não foi possível abrir o chamado agora. Tente novamente em instantes.';
    }
}

if ($erroVisitante !== null) {
    http_response_code(404);
}

$conversa_id = $idChamado;
$conversa_seguranca = $seguranca;
$conversa_token = '';

include __DIR__ . '/../../views/visitante/chamados_interacoes.php';
