<?php
require_once __DIR__ . '/erp_api.php';

// Mantém o formato antigo (lista de linhas) para não alterar o controller.
function obterUsuario($usuario, $senha) {
    $linha = erp_api('usuario.login', ['usuario' => (string)$usuario, 'senha' => (string)$senha]);
    return $linha ? [$linha] : [];
}

function obterContratosUsuario($id_pessoa) {
    return erp_api('usuario.contratos', ['id_pessoa' => (int)$id_pessoa]) ?: [];
}

function obterNome2($id) {
    return erp_api('pessoa.nome', ['id' => (int)$id]);
}
