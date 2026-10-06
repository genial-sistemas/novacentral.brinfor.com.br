<?php
require_once __DIR__ . '/conexao.php';

// Os contatos inativos permanecem no banco; a aplicação apenas oculta esses registros das consultas ativas.
const CONTATO_STATUS_ATIVO = 'a';
const CONTATO_STATUS_INATIVO = 'i';

/**
 * Lista os funcionários ativos da empresa da sessão, trazendo também a descrição do cargo.
 *
 * @return array Lista de funcionários ordenada pelo nome.
 */
function obterContatos() {
    $consulta = "
        SELECT c.*, cc.descricao
        FROM pessoa_juridica_contatos as c
        JOIN pessoa_juridica_contatos_cargos as cc ON c.cargo = cc.id
        WHERE c.status = 'a'
        AND c.id_cliente = :id_cliente
        ORDER BY c.nome";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(':id_cliente' => (int)($_SESSION['id_pessoa'] ?? 0)));
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca um contato pelo identificador. A conexão opcional permite reutilizar uma transação existente.
 *
 * @return array|null Dados do contato ou null se o identificador for inválido ou não houver resultado.
 */
function obterContato($idContato, $dbh=null) {
    $idContato = filter_var($idContato, FILTER_VALIDATE_INT);
    if ($idContato === false || $idContato <= 0) { return null; }
    $consulta = "
        SELECT *
        FROM pessoa_juridica_contatos
        WHERE id = :idContato
        LIMIT 1";
    $dbh = $dbh ?: getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(':idContato' => $idContato));
    return $sth->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Retorna os campos básicos dos contatos ativos de uma pessoa jurídica.
 *
 * @return array Contatos ativos ordenados pelo nome.
 */
function obterTodosContatosPessoaJuridica($idCliente, $dbh=null) {
    $consulta = "
        SELECT id,
            nome,
            tel,
            ramal,
            cel,
            email
        FROM pessoa_juridica_contatos
        WHERE status = :status
            AND id_cliente = :idCliente
        ORDER BY nome";
    $dbh = $dbh ?: getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(
        ':status' => CONTATO_STATUS_ATIVO,
        ':idCliente' => $idCliente
    ));
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca um contato e fornece aliases compatíveis com as telas de contato de pessoa jurídica.
 *
 * @return array|null Dados do contato ou null se o identificador for inválido ou não houver resultado.
 */
function obterContatoPessoaJuridica($idContato, $dbh=null) {
    $idContato = filter_var($idContato, FILTER_VALIDATE_INT);
    if ($idContato === false || $idContato <= 0) { return null; }
    $consulta = "
        SELECT `id`,
            `nome`,
            `nome` AS `nome_contato`,
            `tel`,
            `tel` AS `telefone_contato`,
            `ramal`,
            `cel`,
            `cel` AS `celular_contato`,
            `email`,
            `email` AS `email_contato`

        FROM `pessoa_juridica_contatos`
        WHERE `id` = :idContato
        LIMIT 1";
    $dbh = $dbh ?: getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(':idContato' => $idContato));
    return $sth->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Localiza o contato associado ao equipamento identificado pelas etiquetas do contrato e do equipamento.
 *
 * @return array|null Dados do contato associado ou null se não houver resultado.
 */
function obterContatoPessoaJuridicaByEtiqueta($etiquetaNumContrato, $etiquetaNumEqpto, $dbh=null) {
    $consulta = "
        SELECT pjc.*

        FROM `contrato_outsourcing_equipamentos` AS `coe`

        JOIN `contrato_outsourcing` AS `co`
            ON `co`.`id_contrato` = `coe`.`contrato`

        LEFT JOIN `pessoa_juridica_contatos` AS `pjc`
            ON `pjc`.`id` = `coe`.`contato`

        WHERE `co`.etiqueta = :etiquetaNumContrato
            AND `coe`.`codigo` = :etiquetaNumEqpto
        ";
    $dbh = $dbh ?: getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute(array(
        ':etiquetaNumContrato' => $etiquetaNumContrato,
        ':etiquetaNumEqpto' => $etiquetaNumEqpto
    ));
    return $sth->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Busca os cargos ativos disponíveis no formulário de funcionários.
 *
 * @return array Cargos ordenados pela descrição.
 */
function obterCargos() {
    $consulta = "
        SELECT *
        FROM `pessoa_juridica_contatos_cargos`
        WHERE `status` = 'a'
        ORDER BY `descricao`";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $sth->execute();
    return $sth->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Valida e cria um funcionário para a empresa autenticada.
 * Telefone e celular são normalizados para conter somente dígitos; os valores SQL são parametrizados.
 *
 * @return bool true se o INSERT for executado, false se os dados obrigatórios forem inválidos.
 */
function cadastrarContato(
    $nome,
    $cargo,
    $tel,
    $ramal,
    $cel,
    $email
) {
    $nome = trim((string)$nome);
    $cel = preg_replace('/\D+/', '', (string)$cel);
    $tel = preg_replace('/\D+/', '', (string)$tel);
    $email = trim((string)$email);
    $idPessoa = (int)($_SESSION['id_pessoa'] ?? 0);

    if ($idPessoa <= 0 || $nome === '' || $cel === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $consulta = "INSERT INTO pessoa_juridica_contatos
        (id_cliente, nome, cargo, tel, ramal, cel, email, status)
        VALUES (:id_cliente, :nome, :cargo, :tel, :ramal, :cel, :email, 'a')";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    return $sth->execute(array(
        ':id_cliente' => $idPessoa,
        ':nome' => $nome,
        ':cargo' => $cargo,
        ':tel' => $tel,
        ':ramal' => $ramal,
        ':cel' => $cel,
        ':email' => $email
    ));
}

/**
 * Valida e atualiza os dados de um funcionário existente.
 *
 * @return bool true se o UPDATE for executado, false se o identificador ou os dados forem inválidos.
 */
function editarContato(
    $id,
    $nome,
    $cargo,
    $tel,
    $ramal,
    $cel,
    $email
) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $nome = trim((string)$nome);
    $cel = preg_replace('/\D+/', '', (string)$cel);
    $tel = preg_replace('/\D+/', '', (string)$tel);
    $email = trim((string)$email);

    if ($id === false || $id <= 0 || $nome === '' || $cel === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $consulta = "UPDATE pessoa_juridica_contatos
        SET nome = :nome,
            cargo = :cargo,
            tel = :tel,
            ramal = :ramal,
            cel = :cel,
            email = :email
        WHERE id = :id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    return $sth->execute(array(
        ':nome' => $nome,
        ':cargo' => $cargo,
        ':tel' => $tel,
        ':ramal' => $ramal,
        ':cel' => $cel,
        ':email' => $email,
        ':id' => $id
    ));
}

/**
 * Desativa um funcionário sem remover fisicamente o registro do banco de dados.
 *
 * @return bool true se o UPDATE for executado, false se o identificador for inválido.
 */
function apagarContato($id) {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) { return false; }
    $consulta = "
        UPDATE `pessoa_juridica_contatos`
        SET `status` = 'i'
        WHERE `id` = :id";
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    return $sth->execute(array(':id' => $id));
}
