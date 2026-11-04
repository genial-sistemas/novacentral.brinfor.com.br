<?php
require_once __DIR__ . '/erp_api.php';

// Os contatos inativos permanecem no banco; a aplicação apenas oculta esses registros das consultas ativas.
const CONTATO_STATUS_ATIVO = 'a';
const CONTATO_STATUS_INATIVO = 'i';

/**
 * Lista os funcionários ativos da empresa da sessão, trazendo também a descrição do cargo.
 */
function obterContatos() {
    $idCliente = (int)($_SESSION['id_pessoa'] ?? 0);
    return $idCliente > 0 ? (erp_api('contato.listar_ativos', ['id_cliente' => $idCliente]) ?: []) : [];
}

/**
 * Busca um contato pelo identificador. O parâmetro $dbh é mantido só por compatibilidade.
 */
function obterContato($idContato, $dbh = null) {
    $idContato = filter_var($idContato, FILTER_VALIDATE_INT);
    if ($idContato === false || $idContato <= 0) { return null; }
    return erp_api('contato.obter', ['id' => $idContato]) ?: null;
}

/**
 * Cargos ativos disponíveis no formulário de funcionários.
 */
function obterCargos() {
    return erp_api('contato.cargos') ?: [];
}

/**
 * Valida e cria um funcionário para a empresa autenticada.
 * Telefone e celular são normalizados para conter somente dígitos.
 */
function cadastrarContato($nome, $cargo, $tel, $ramal, $cel, $email) {
    $nome = trim((string)$nome);
    $cel = preg_replace('/\D+/', '', (string)$cel);
    $tel = preg_replace('/\D+/', '', (string)$tel);
    $email = trim((string)$email);
    $idPessoa = (int)($_SESSION['id_pessoa'] ?? 0);

    if ($idPessoa <= 0 || $nome === '' || $cel === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return (bool)erp_api('contato.cadastrar', [
        'id_cliente' => $idPessoa,
        'nome' => $nome,
        'cargo' => (string)$cargo,
        'tel' => $tel,
        'ramal' => (string)$ramal,
        'cel' => $cel,
        'email' => $email,
    ]);
}

/**
 * Valida e atualiza os dados de um funcionário da empresa autenticada.
 */
function editarContato($id, $nome, $cargo, $tel, $ramal, $cel, $email) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $nome = trim((string)$nome);
    $cel = preg_replace('/\D+/', '', (string)$cel);
    $tel = preg_replace('/\D+/', '', (string)$tel);
    $email = trim((string)$email);
    $idPessoa = (int)($_SESSION['id_pessoa'] ?? 0);

    if ($id === false || $id <= 0 || $idPessoa <= 0 || $nome === '' || $cel === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // A API só altera contatos da própria empresa (id_cliente).
    return (bool)erp_api('contato.editar', [
        'id' => $id,
        'id_cliente' => $idPessoa,
        'nome' => $nome,
        'cargo' => (string)$cargo,
        'tel' => $tel,
        'ramal' => (string)$ramal,
        'cel' => $cel,
        'email' => $email,
    ]);
}

/**
 * Desativa um funcionário sem remover o registro do banco de dados.
 */
function apagarContato($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $idPessoa = (int)($_SESSION['id_pessoa'] ?? 0);
    if ($id === false || $id <= 0 || $idPessoa <= 0) { return false; }
    return (bool)erp_api('contato.inativar', ['id' => $id, 'id_cliente' => $idPessoa]);
}
