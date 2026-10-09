<?php

require_once __DIR__ . '/../models/contato.php';

$idFuncionario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idFuncionario || $idFuncionario <= 0) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}

$funcionario = obterContato($idFuncionario);
if ($funcionario === null) {
    http_response_code(404);
    include __DIR__ . '/../views/404.php';
    exit;
}

$menu = 'Funcionários';
$pagina = 'Editar';
$tipo_mensagem = null;
$mensagem = null;
$cargos = obterCargos();
$acaoLabel = 'Editar';
$acao = 'funcionario_editar';
$acaoRedirecionar = '/funcionarios_listar';
$acaoLabelSucesso = 'editado';
$redirecionar = false;
$uriRedirecionar = null;
$messages = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['acao'] ?? null) === 'funcionario_editar') {
    $funcionario = [
        'nome' => $_POST['nome'] ?? '',
        'cargo' => $_POST['cargo'] ?? '',
        'tel' => $_POST['telefone'] ?? '',
        'ramal' => $_POST['ramal'] ?? '',
        'cel' => $_POST['celular'] ?? '',
        'email' => $_POST['email'] ?? ''
    ];

    if (editarContato(
        $idFuncionario,
        $funcionario['nome'],
        $funcionario['cargo'],
        $funcionario['tel'],
        $funcionario['ramal'],
        $funcionario['cel'],
        $funcionario['email']
    )) {
        $_POST['acao'] = 'funcionario_editar';
    } else {
        $tipo_mensagem = 'Error';
        $mensagem = 'Não foi possível editar o funcionário. Confira os dados e tente novamente.';
        $_POST['acao'] = null;
    }
}

include __DIR__ . '/../views/funcionarios_cadastrar.php';
