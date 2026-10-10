<?php
require_once __DIR__ . '/../models/conexao.php';
require_once __DIR__ . '/../models/documentacao_cliente.php';
require_once __DIR__ . '/../models/equipamentos_cliente.php';
require_once __DIR__ . '/../models/email.php';

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: /login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

$csrf = (string)($_POST['csrf'] ?? '');
if (!hash_equals(tokenCsrfEquipamentos(), $csrf)) {
    http_response_code(403);
    exit('A sessão expirou. Atualize a página e tente novamente.');
}

$idEquipamento = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$acao = (string)($_POST['acao'] ?? '');
if (!$idEquipamento || $idEquipamento <= 0 || !in_array($acao, ['solicitar_desativacao', 'reativar'], true)) {
    http_response_code(400);
    exit('Solicitação inválida.');
}

$dbh = getConexao();
$autorizado = obterContratoHelpdesk($_SESSION['contratos'], $dbh) !== null;
$sucesso = false;
$destino = '/equipamentos_listar';
$texto = 'Não foi possível concluir a solicitação. Atualize a lista e tente novamente.';

if ($autorizado && $acao === 'reativar') {
    $sucesso = reativarEquipamentoHelpdeskCliente(
        $idEquipamento,
        $_SESSION['id_pessoa'],
        $_SESSION['contratos'],
        $dbh
    );
    $destino = '/equipamentos_listar_inativos';
    $texto = $sucesso
        ? 'Equipamento reativado com sucesso.'
        : 'Não foi possível reativar o equipamento. Atualize a lista e tente novamente.';
} elseif ($autorizado) {
    try {
        $solicitacao = criarSolicitacaoDesativacaoEquipamentoHelpdeskCliente(
            $idEquipamento,
            $_SESSION['id_pessoa'],
            $_SESSION['contratos'],
            $dbh
        );
        if (is_array($solicitacao) && !empty($solicitacao['pendente'])) {
            $texto = 'Já existe uma solicitação de desativação aguardando aprovação para este equipamento.';
        } elseif (is_array($solicitacao)) {
            $equipamento = $solicitacao['equipamento'];
            $hostAtual = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
            $hostPermitido = in_array($hostAtual, [
                'central.brinfor.com.br',
                'novacentral.brinfor.com.br',
            ], true) ? $hostAtual : 'central.brinfor.com.br';
            $urlAprovacao = 'https://' . $hostPermitido . '/equipamentos_desativacao_aprovar?token='
                . rawurlencode($solicitacao['token']);
            $escapar = static function ($valor) {
                return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
            };
            $assunto = 'Aprovação de desativação de equipamento #' . (int)$equipamento['id'];
            $html = '<p>Olá, gerente do contrato.</p>'
                . '<p>Foi solicitada a desativação do equipamento abaixo. Ele continuará ativo até sua aprovação.</p>'
                . '<ul><li><strong>Equipamento:</strong> ' . $escapar($equipamento['nome_equipamento']) . '</li>'
                . '<li><strong>Código:</strong> ' . $escapar($equipamento['codigo']) . '</li>'
                . '<li><strong>Número de série:</strong> ' . $escapar($equipamento['sn'] ?: 'Não informado') . '</li>'
                . '<li><strong>Contrato:</strong> ' . $escapar($equipamento['nome_contrato_outsourcing']) . '</li>'
                . '<li><strong>Solicitante:</strong> ' . $escapar($_SESSION['nome_cliente'] ?? 'Cliente') . '</li></ul>'
                . '<p><a href="' . $escapar($urlAprovacao) . '">Analisar solicitação de desativação</a></p>';
            $textoEmail = "Foi solicitada a desativação do equipamento {$equipamento['nome_equipamento']} "
                . "(código {$equipamento['codigo']}). O equipamento continuará ativo até sua aprovação.\n\n"
                . "Analisar solicitação: $urlAprovacao";
            $resultadoEmail = enviarEmailHtml(
                $solicitacao['email_gerente'],
                $assunto,
                $html,
                $textoEmail
            );

            if (!empty($resultadoEmail['enviado'])) {
                $sucesso = true;
                $texto = 'Solicitação enviada ao gerente do contrato. O equipamento continuará ativo até a aprovação.';
            } else {
                if (!cancelarSolicitacaoDesativacaoEquipamentoCliente($solicitacao['token'], $dbh)) {
                    error_log('Não foi possível cancelar a solicitação de desativação após falha no envio do e-mail.');
                }
                $texto = 'Não foi possível enviar o e-mail ao gerente. O equipamento continua ativo; tente novamente mais tarde.';
            }
        } else {
            $texto = 'Não foi possível localizar o gerente do contrato ou o equipamento. Nenhuma alteração foi feita.';
        }
    } catch (Throwable $erro) {
        error_log('Falha ao solicitar desativação de equipamento ' . (int)$idEquipamento . ': ' . $erro->getMessage());
    }
}

$_SESSION['mensagem_equipamentos'] = [
    'tipo' => $sucesso ? 'success' : 'error',
    'texto' => $texto
];

header('Location: ' . $destino);
exit;
