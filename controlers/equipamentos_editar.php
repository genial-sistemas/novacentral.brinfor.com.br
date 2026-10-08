<?php
require_once __DIR__ . '/../models/conexao.php';
require_once __DIR__ . '/../models/documentacao_cliente.php';
require_once __DIR__ . '/../models/equipamentos_cliente.php';

if (empty($_SESSION['id_pessoa']) || !is_array($_SESSION['contratos'] ?? null)) {
    header('Location: /login');
    exit;
}

$dbh = getConexao();
if (obterContratoHelpdesk($_SESSION['contratos'], $dbh) === null) {
    http_response_code(403);
    include __DIR__ . '/../views/404.php';
    exit;
}

$idEquipamento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idEquipamento || $idEquipamento <= 0) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}

$equipamento = obterEquipamentoHelpdeskCliente(
    $idEquipamento,
    $_SESSION['id_pessoa'],
    $_SESSION['contratos'],
    $dbh
);
if (!$equipamento) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}

$erroEquipamento = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    $contatoPostado = (string)($_POST['contato'] ?? '');
    if (!hash_equals(tokenCsrfEquipamentos(), $csrf)) {
        http_response_code(403);
        $erroEquipamento = 'A sessão expirou. Atualize a página e tente novamente.';
    } else {
        $contato = $contatoPostado === '' ? null : $contatoPostado;
        if (atualizarContatoEquipamentoCliente(
            $idEquipamento,
            $_SESSION['id_pessoa'],
            $_SESSION['contratos'],
            $contato,
            $dbh
        )) {
            $_SESSION['mensagem_equipamentos'] = [
                'tipo' => 'success',
                'texto' => 'Funcionário vinculado ao equipamento atualizado.'
            ];
            header('Location: /equipamentos_listar');
            exit;
        }

        $erroEquipamento = 'Não foi possível atualizar o funcionário. Selecione um funcionário ativo da sua empresa.';
    }
}

$contatos = obterContatosAtivosEquipamentos($_SESSION['id_pessoa'], $dbh);
$csrfToken = tokenCsrfEquipamentos();
$menu = 'Equipamentos';
$pagina = 'Ativos';
include __DIR__ . '/../views/equipamentos_editar.php';
