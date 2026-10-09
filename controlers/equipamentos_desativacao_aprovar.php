<?php
require_once __DIR__ . '/../models/conexao.php';
require_once __DIR__ . '/../models/equipamentos_cliente.php';

$token = (string)($_POST['token'] ?? $_GET['token'] ?? '');
$mensagemAprovacao = '';
$resultado = (string)($_GET['resultado'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    $acao = (string)($_POST['acao'] ?? '');
    if (!hash_equals(tokenCsrfEquipamentos(), $csrf)) {
        http_response_code(403);
        $mensagemAprovacao = 'A sessão expirou. Reabra o link recebido por e-mail.';
    } elseif (!in_array($acao, ['aprovar', 'rejeitar'], true)) {
        http_response_code(400);
        $mensagemAprovacao = 'Ação inválida.';
    } else {
        try {
            $resolvido = resolverSolicitacaoDesativacaoEquipamentoCliente(
                $token,
                $acao === 'aprovar'
            );
            if ($resolvido) {
                header('Location: /equipamentos_desativacao_aprovar?token=' . rawurlencode($token)
                    . '&resultado=' . ($acao === 'aprovar' ? 'aprovado' : 'rejeitado'));
                exit;
            }
            $mensagemAprovacao = 'A solicitação já foi processada ou não pode mais ser aprovada.';
        } catch (Throwable $erro) {
            error_log('Falha ao processar aprovação de desativação de equipamento: ' . $erro->getMessage());
            http_response_code(500);
            $mensagemAprovacao = 'Não foi possível processar a solicitação. Tente novamente mais tarde.';
        }
    }
}

$solicitacao = obterSolicitacaoDesativacaoEquipamentoCliente($token);
if (!$solicitacao && $mensagemAprovacao === '') {
    http_response_code(404);
    $mensagemAprovacao = 'Solicitação não encontrada. Verifique se o link está correto.';
}

$csrfToken = tokenCsrfEquipamentos();
include __DIR__ . '/../views/equipamentos_desativacao_aprovar.php';
