<?php

// O modelo fornece a consulta de funcionários ativos da empresa autenticada.
require "models/contato.php";

// Identifica a seção e o item atual para o menu lateral.
$menu   = 'Funcionários';
$pagina = 'Listar';

// Variáveis compartilhadas com o layout e seus componentes de mensagem/redirecionamento.
$redirecionar = false;
$uriRedirecionar = null;
$messages = [];

// A consulta restringe o resultado aos funcionários ativos do cliente da sessão.
$contatos = obterContatos();

// A view monta a tabela de funcionários e as ações de editar/apagar.
include __DIR__ . '/../views/funcionarios_listar.php';