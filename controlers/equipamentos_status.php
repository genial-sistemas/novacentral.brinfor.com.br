<?php
require_once __DIR__ . '/../models/conexao.php';
require_once __DIR__ . '/../models/documentacao_cliente.php';
require_once __DIR__ . '/../models/equipamentos_cliente.php';

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
$dbh = getConexao();
$autorizado = obterContratoHelpdesk($_SESSION['contratos'], $dbh) !== null;
$desativado = $autorizado && $idEquipamento && $idEquipamento > 0
    && desativarEquipamentoHelpdeskCliente(
        $idEquipamento,
        $_SESSION['id_pessoa'],
        $_SESSION['contratos'],
        $dbh
    );

$_SESSION['mensagem_equipamentos'] = $desativado
    ? ['tipo' => 'success', 'texto' => 'Equipamento desativado com sucesso.']
    : ['tipo' => 'error', 'texto' => 'Não foi possível desativar o equipamento. Atualize a lista e tente novamente.'];

header('Location: ' . ($desativado ? '/equipamentos_listar_inativos' : '/equipamentos_listar'));
exit;
