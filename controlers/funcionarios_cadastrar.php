<?php

// O modelo concentra as consultas e operações de persistência dos funcionários.
require_once __DIR__ . "/../models/contato.php";

// Identifica esta tela para o título e o estado ativo do menu lateral.
$menu   = 'Funcionários';
$pagina = 'Cadastrar';

// Estado apresentado pela tela após uma ação; os campos também preservam os dados do formulário.
$tipo_mensagem = null;
$mensagem = null;
$funcionario = null;

// Os cargos ativos são necessários para preencher a lista de seleção do formulário.
$cargos = obterCargos();

// Valores padrão usados no modo de cadastro.
$acaoLabel = 'Cadastrar';
$acao = 'funcionario_cadastrar';
$acaoRedirecionar = 'funcionarios_cadastrar';
$acaoLabelSucesso = null;
$acaoRedirecionar = null;

// Compatibilidade com os componentes compartilhados de mensagem e redirecionamento.
$redirecionar = false;
$uriRedirecionar = null;
$messages = [];

// As ações de edição e exclusão chegam por GET com o identificador do funcionário.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && isset($_GET['acao'])) {
    if ($_GET['acao'] == 'editar' && isset($_GET['id'])) {
        // Carrega os dados existentes e configura o formulário para atualização.
        $acaoLabel = 'Editar';
        $acaoLabelSucesso = 'editado';
        $funcionario = obterContato($_GET['id']);
        $acao = 'funcionario_editar';
        $acaoRedirecionar = 'funcionarios_listar';

    } else if ($_GET['acao'] == 'apagar' && isset($_GET['id'])) {
        // A exclusão é lógica: o registro é marcado inativo pelo modelo.
        apagarContato($_GET['id']);
        $acaoRedirecionar = 'funcionarios_listar';
        $_POST['acao'] = 'funcionario_apagar';
        $acaoLabelSucesso = 'apagado';
        $acaoLabel = 'Apagar';
    }
}

// O valor do botão "acao" diferencia o cadastro da edição enviada pelo formulário.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'funcionario_cadastrar') {
        // Mantém os valores digitados caso a validação no modelo não aceite o cadastro.
        $funcionario = [
            'nome' => $_POST['nome'],
            'cargo' => $_POST['cargo'],
            'telefone' => $_POST['telefone'],
            'ramal' => $_POST['ramal'],
            'celular' => $_POST['celular'],
            'email' => $_POST['email']
        ];

        $ok = cadastrarContato(
            $_POST['nome'],
            $_POST['cargo'],
            \trim($_POST['telefone']),
            $_POST['ramal'],
            \trim($_POST['celular']),
            \trim($_POST['email'])
        );

        if (!$ok) {
            // A view volta a exibir o formulário e apresenta o erro de validação.
            $tipo_mensagem = 'Error';
            $mensagem = "Dados invalidos!";
            $_POST['acao'] = null;
        } else {
            // Usado pela view para formar a mensagem "Funcionário cadastrado com sucesso".
            $acaoLabelSucesso = 'cadastrado';
        }

    } else if ($_POST['acao'] == 'funcionario_editar') {
        // O ID da edição vem da URL gerada pelo botão "Editar" da listagem.
        editarContato(
            $_GET['id'],
            $_POST['nome'],
            $_POST['cargo'],
            $_POST['telefone'],
            $_POST['ramal'],
            $_POST['celular'],
            $_POST['email']
        );
    }
}

// Renderiza o formulário em modo de cadastro, edição ou confirmação de ação.
include __DIR__ . '/../views/funcionarios_cadastrar.php';