<?php
require_once __DIR__ . '/conexao.php';
require_once  __DIR__ . '/contrato.php';

require_once __DIR__ . "/../helpers/typeHelpers.php";
require_once __DIR__ . "/../helpers/arrayHelpers.php";
require_once __DIR__ . "/../helpers/dateTimeHelpers.php";
require_once __DIR__ . "/../helpers/cryptoHelpers.php";

include_once __DIR__ . '/equipamentoConstantes.php';

/**
 * obterEquipamentos function
 *
 * @param string $eqpStatus
 * @param int $categoriaTipo
 * @param \PDO|null $dbh
 * @return array
 */
function obterEquipamentos($eqpStatus=null, $categoriaTipo=EQP_CATEGORIA_TODOS, $dbh=null) {
    $opt = '';

    if (isset($eqpStatus)) {
        $opt .= sprintf("
            AND `e`.`status` = '%s'
        ", $eqpStatus);
    }

    if ($categoriaTipo == 1) {

        //Desktop e Workstations
        $opt .= "
            AND `e`.`tipo` = 1
        ";

    } elseif($categoriaTipo == 2) {

        // Notebooks
        $opt .= "
            AND (`e`.`tipo` = 2 OR `e`.`tipo` = 3)
        ";

    } elseif($categoriaTipo == 3) {

        // Servidor Físico
        $opt .= "
            AND `e`.`tipo` = 4
        ";

    } elseif($categoriaTipo == 4) {

        // Servidor Virtual
        $opt .= "
            AND (`e`.`tipo` = 19 OR `e`.`tipo` = 20)
        ";

    } elseif($categoriaTipo == 5) {

        // Storage
        $opt .= "
            AND `e`.`tipo` = 11
        ";

    } elseif($categoriaTipo == 6) {

        // Nobreak
        $opt .= "
            AND `e`.`tipo` = 13
        ";

    } elseif($categoriaTipo == 7) {

        // Outros
        $opt .= "
            AND `e`.`tipo` != 1
            AND `e`.`tipo` != 2
            AND `e`.`tipo` != 3
            AND `e`.`tipo` != 4
            AND `e`.`tipo` != 19
            AND `e`.`tipo` != 20
            AND `e`.`tipo` != 11
            AND `e`.`tipo` != 13
            AND `e`.`tipo` != 25
        ";


    } elseif($categoriaTipo == 9) {

        // Access Point
        $opt .= "
            AND `e`.`tipo` = 9
        ";

    } elseif($categoriaTipo == 25) {

        // Servidor Nuvem
        $opt .= "
            AND (`e`.`tipo` = 25)
        ";

    } else {
        // Todos
        $opt .= "";
    }

    $consulta = sprintf(\trim("
        SELECT `e`.*,
            `c`.`id` AS 'id_contrato',
            `p`.`nome`,
            `t`.`tipo` AS 'equipamento',
            `tt`.`descricao` AS 'tipo_contrato'

        FROM `contrato_outsourcing_equipamentos` AS `e`

        JOIN `contrato` AS `c`
            ON `e`.`contrato` = `c`.`id`

        LEFT JOIN `contrato_outsourcing_equipamentos_tipo` AS `t`
            ON `e`.tipo = t.id

        LEFT JOIN `contrato_outsourcing_equipamentos_contrato_tipo` AS `tt`
            ON `e`.`id_tipo_contrato` = `tt`.`id`

        LEFT JOIN `pessoa_juridica_contatos` AS `p`
            ON `e`.`contato` = `p`.`id`

        WHERE `c`.`status` = :ctStatus
            AND `c`.`id_pessoa` = :idPessoa
            %s

        ORDER BY `e`.`codigo`;
    "), $opt);

    $params = [
        ':ctStatus' => CONTRATO_STATUS_ATIVO,
        ':idPessoa' => $_SESSION['id_pessoa']
    ];

    //error_log(sprintf('consulta: %s, params: %s', $consulta, var_export($params, true)));

    $res = dbSqlQuery($consulta, $params, DB_QUERY_FETCH_ALL, $dbh);
    return $res;
}

/**
 * obterEquipamento function
 *
 * @param integer $id
 * @param ?\PDO $dbh
 * @return void
 */
function obterEquipamento($id=0, $dbh=null) {
    if (!isValidInteger($id)) { return null; }

    $consulta = \trim("
        SELECT coe.id,
            coe.contrato,
            cto.id AS id_contrato,

            cto.id_tipo AS id_tipo_contrato,
            ctotp.descricao AS nome_tipo_contrato,

            coe.id_tipo_contrato AS id_tipo_contrato_equipamento,
            tt.descricao AS tipo_contrato_outsourcing,
            tt.descricao AS nome_tipo_contrato_outsourcing,

            cos.nome AS nome_empresa,
            cos.nome AS nome_contrato_outsourcing,
            cos.prefixo AS prefixo_empresa,

            cos.etiqueta,
            cos.etiqueta AS codigo_contrato,
            cos.etiqueta AS codigo_etiqueta_contrato,

            coe.codigo,
            coe.codigo AS codigo_eqpto,
            coe.codigo AS codigo_etiqueta_eqpto,

            coe.contato,
            coe.contato AS id_contato,
            coe.contato AS id_contato_equipamento,

            coe.usuario AS nome_usuario_eqpto,

            peq.nome AS nome_pessoa_eqpto,
            peq.nome AS nome_contato,

            peq.email AS email_pessoa_eqpto,
            peq.email AS email_contato,

            peq.tel AS telefone_contato,

            cto.id_pessoa,
            cto.id_pessoa AS id_pessoa_empresa,

            pje.razao_social AS nome_pessoa_empresa,
            pje.email AS email_pessoa_empresa,

            cto.vendedor,
            cto.vendedor AS id_vendedor,
            cto.vendedor AS id_gerente_contas,

            pss.nome_pessoa AS nome_pessoa_vendedor,
            pss.nome_pessoa AS nome_gerente_contas,

            pfs.email AS email_pessoa_vendedor,
            pfs.email AS email_gerente_contas,

            cto.email_gerente AS email_gerente_empresa,

            coe.descricao,
            coe.descricao AS nome_rede,

            coetp.tipo AS equipamento,
            coetp.tipo AS nome_eqpto,

            coe.modelo,
            coe.modelo AS modelo_eqpto,

            coe.data_ativado,
            coe.data_ativado AS data_ativacao_equipamento,

            coe.valor_equipamento,
            coe.valor_equipamento AS valor_eqpto,

            coe.data_ativado

        FROM contrato_outsourcing_equipamentos AS coe

        JOIN contrato_outsourcing AS cos
            ON coe.contrato = cos.id_contrato

        JOIN contrato AS cto
            ON coe.contrato = cto.id

        JOIN contrato_tipo AS ctotp
            ON ctotp.id = cto.id_tipo

        JOIN contrato_outsourcing_equipamentos_tipo AS coetp
            ON coe.tipo = coetp.id

        JOIN contrato_outsourcing_equipamentos_contrato_tipo AS tt
            ON coe.id_tipo_contrato = tt.id

        LEFT JOIN pessoa_juridica_contatos AS peq
            ON coe.contato = peq.id

        LEFT JOIN pessoa_juridica AS pje
            ON cto.id_pessoa = pje.id_pessoa

        LEFT JOIN pessoa AS pss
            ON cto.vendedor = pss.id

        LEFT JOIN pessoa_fisica AS pfs
            ON cto.vendedor = pfs.id_pessoa

        WHERE coe.id = :idEqpto

        LIMIT 1;
    ");
    $params = [':idEqpto' => $id];

    $res = dbSqlQuery($consulta, $params, DB_QUERY_FETCH_ONE, $dbh);
    return $res;
}

/**
 * obterEqptoContatoDescricao function
 *
 * @param integer $idEqpto
 * @param \PDO|null $dbh
 * @return mixed
 */
function obterEqptoContatoDescricao($idEqpto=0, $dbh=null) {
    if (!isValidInteger($idEqpto)) { return null; }

    $consulta = \trim("
        SELECT coe.id,
            coe.id_tipo_contrato AS id_tipo_contrato_equipamento,
            tt.descricao AS tipo_contrato_outsourcing,
            tt.descricao AS nome_tipo_contrato_outsourcing,

            cos.etiqueta,
            cos.etiqueta AS codigo_contrato,
            cos.etiqueta AS codigo_etiqueta_contrato,

            coe.codigo,
            coe.codigo AS codigo_eqpto,
            coe.codigo AS codigo_etiqueta_eqpto,

            coe.contato,
            coe.contato AS id_contato,
            coe.contato AS id_contato_equipamento,

            coe.usuario AS nome_usuario_eqpto,

            coe.descricao,
            coe.descricao AS nome_rede,

            coetp.tipo AS equipamento,
            coetp.tipo AS nome_eqpto,

            coe.modelo,
            coe.modelo AS modelo_eqpto,

            coe.data_ativado,
            coe.data_ativado AS data_ativacao_equipamento,

            coe.data_ativado

        FROM contrato_outsourcing_equipamentos AS coe

        JOIN contrato_outsourcing AS cos
            ON coe.contrato = cos.id_contrato

        JOIN `contrato_outsourcing_equipamentos_tipo` AS `coetp`
            ON `coe`.`tipo` = `coetp`.`id`

        JOIN contrato_outsourcing_equipamentos_contrato_tipo AS tt
            ON coe.id_tipo_contrato = tt.id

        WHERE coe.id = :idEqpto

        LIMIT 1;
    ");
    $params = [':idEqpto' => $idEqpto];

    $res = dbSqlQuery($consulta, $params, DB_QUERY_FETCH_ONE, $dbh);
    return $res;
}


function obterEqptoContatoByEtiqueta($etiquetaNumContrato, $etiquetaCodigoEqp) {
    $consulta = \trim("
        SELECT `ct`.`id_tipo`,
            `ct`.`status`,
            `co`.`etiqueta`,
            `co`.`nome` AS 'cliente',
            `coe`.`contrato` AS 'id_contrato',
            `coe`.`codigo`,
            `coe`.`contato`,
            `coe`.`descricao`,
            `coe`.`modelo`,
            `pjc`.`nome` AS 'nome_usuario',
            `pjc`.`email`,
            `pjc`.`tel`,
            `pjc`.`cel`

        FROM `contrato` AS `ct`

        JOIN `contrato_outsourcing` AS `co`
            ON `ct`.`id` = `co`.`id_contrato`

        JOIN `contrato_outsourcing_equipamentos` AS `coe`
            ON `coe`.`contrato` = `co`.`id_contrato`

        LEFT JOIN `pessoa_juridica_contatos` AS `pjc`
            ON `coe`.`contato` = `pjc`.`id`

        WHERE `ct`.`id_tipo` = 1
            AND `ct`.`status` = 2
            AND `co`.`etiqueta` = :etiquetaNumContrato
            AND `coe`.`codigo` = :etiquetaCodigoEqp
        ;
    ");
    $dbh = getConexao();
    $sth = $dbh->prepare($consulta);
    $params = [
        ':etiquetaNumContrato' => $etiquetaNumContrato,
        ':etiquetaCodigoEqp' => $etiquetaCodigoEqp
    ];
    return $sth->execute($params) ? $sth->fetch() : null;
}

function obterEquipamentosDeContratos($contratos, $status=EQP_STATUS_ATIVO, $dbh=null) {
    $contratosStrList = implode(",", $contratos);
    $consulta = sprintf(\trim("
        SELECT `coe`.`id`,
            `coe`.`contrato`,
            `coe`.`id_tipo_contrato`,
            `coe`.`status`,
            `cos`.`etiqueta` AS 'codigo_contrato',
            `coe`.`codigo` AS 'codigo_eqpto',
            `coe`.`descricao`,
            `coetp`.`tipo`,
            `coe`.`modelo`,
            `coe`.`contato`,
            `pjc`.`nome` AS 'usuario',
            `coe`.`patrimonio`

        FROM `contrato_outsourcing_equipamentos` AS `coe`

        INNER JOIN `contrato_outsourcing_equipamentos_tipo` AS `coetp`
            ON `coetp`.`id` = `coe`.`tipo`

        INNER JOIN `contrato_outsourcing` AS `cos`
            ON `cos`.`id_contrato` = `coe`.`contrato`

        LEFT JOIN `pessoa_juridica_contatos` AS `pjc`
            ON `pjc`.`id` = `coe`.`contato`

        WHERE `coe`.`status` = '%s'
            AND `coe`.`contrato` IN (%s)

        ORDER BY codigo_eqpto
        ;
    "), $status, $contratosStrList);

    $eqptos = dbSqlQuery($consulta, null, DB_QUERY_FETCH_ALL, $dbh);
    return $eqptos ?? [];
}


function obterMaquinasAtivas($contratos, $dbh=null) {
    $eqptos = obterEquipamentosDeContratos($contratos, EQP_STATUS_ATIVO, $dbh);
    return $eqptos ?? [];
}

function obterMaquinasInativas($contratos, $dbh=null) {
    $eqptos = obterEquipamentosDeContratos($contratos, EQP_STATUS_INATIVO, $dbh);
    return $eqptos ?? [];
}


/**
 * obterQtdMaquinasAtivas function
 *
 * @param array $contratos
 * @param \PDO|null $dbh
 * @return void
 */
function obterQtdMaquinasAtivas($contratos, $dbh=null) {
    $contratosStrList = implode(",", $contratos);
    $consulta = sprintf(\trim("
        SELECT `id`

        FROM `contrato_outsourcing_equipamentos`

        WHERE `contrato` IN (%s)
            AND `status` = '%s';
    "), $contratosStrList, EQP_STATUS_ATIVO);

    $eqptos = dbSqlQuery($consulta, null, DB_QUERY_FETCH_ALL, $dbh);
    return is_array($eqptos) ? \count($eqptos) : 0;
}


/**
 * obterQtdMaquinasInativas function
 *
 * @param array $contratos
 * @param \PDO|null $dbh
 * @return int
 */
function obterQtdMaquinasInativas($contratos, $dbh=null) {
    $contratosStrList = implode(",", $contratos);
    $consulta = sprintf(\trim("
        SELECT `id`

        FROM `contrato_outsourcing_equipamentos`

        WHERE `contrato` IN (%s)
            AND `status` = '%s';
    "), $contratosStrList, EQP_STATUS_INATIVO);

    $eqptos = dbSqlQuery($consulta, null, DB_QUERY_FETCH_ALL, $dbh);
    return is_array($eqptos) ? \count($eqptos) : 0;
}



/**
 * atualizarContatoEqpto function
 *
 * @param integer $id
 * @param integer $idContato
 * @param ?\PDO $dbh
 * @return bool
 */
function atualizarContatoEqpto($id=0, $idContato=0, $dbh=null) {

    $sqlExec = \trim("
        UPDATE `contrato_outsourcing_equipamentos`
        SET `contato` = :idContato
        WHERE `id` = :idEqpto;
    ");

    $params = [
        ':idContato' => $idContato,
        ':idEqpto' => $id
    ];

    return dbSqlExec($sqlExec, $params, $dbh);
}

/**
 * atualizarStatusEqpto function
 *
 * @param integer $id
 * @param string $status
 * @param ?\PDO $dbh
 * @return void
 */
function atualizarStatusEqpto($id=0, $status='', $dbh=null) {
    if (
        !isValidInteger($id)
        || !isValidString($status)
        || !in_array($status, CONTRATO_OUTSOURCING_EQUIPAMENTO_STATUS_LIST)
    ) {
        return false;
    }

    $sqlExec = \trim("
        UPDATE `contrato_outsourcing_equipamentos`
        SET `status` = :status
        WHERE `id` = :id;
    ");

    $params = [
        ':status' => $status,
        ':id' => $id
    ];

    $resp = dbSqlExec($sqlExec, $params, $dbh);
    return $resp;
}

/**
 * inserirDadosEqptoDesativacao function
 *
 * @param array $eqptoData
 * @param ?\PDO $dbh
 * @return bool
 */
function inserirDadosEqptoDesativacao($eqptoData=[], $dbh=null) {
    if (!isValidArray($eqptoData)
        || !arrayHasKeys($eqptoData, [
            'idEquipamento',
            'idContrato',
            'idContatoEqpto',
            'idPessoaEmpresa',
            'idGerenteContas',
            'idChamado',
            'dataAtivacaoEqpto',
            'codigoEtiqueta',
            'nomeContratoOutsourcing',
            'nomeTipoContratoOutsourcing',
            'dataSolicitacao',
            'nomeContato',
            'emailContato',
            'nomeGerenteContas',
            'emailGerenteContas',
            'tokenAvaliacao',
            'motivacao'
        ])
    ) {
        return false;
    }

    $sqlExec = \trim("
        INSERT INTO `contrato_outsourcing_equipamento_desativacao` (
            `id_equipamento`,
            `id_contrato`,
            `id_chamado`,
            `id_contato_equipamento`,
            `id_pessoa_empresa`,
            `id_gerente_contas`,
            `data_ativacao_equipamento`,
            `data_solicitacao`,
            `nome_contrato_outsourcing`,
            `nome_tipo_contrato_outsourcing`,
            `codigo_etiqueta`,
            `nome_rede`,
            `nome_equipamento`,
            `modelo_equipamento`,
            `valor_equipamento`,
            `nome_contato`,
            `email_contato`,
            `nome_gerente_contas`,
            `email_gerente_contas`,
            `motivacao`,
            `token_avaliacao`,
            `data_avaliacao`,
            `aprovado`
        ) VALUES (
            :idEquipamento,
            :idContrato,
            :idChamado,
            :idContatoEqpto,
            :idPessoaEmpresa,
            :idGerenteContas,
            :dataAtivacaoEqpto,
            :dataSolicitacao,
            :nomeContratoOutsourcing,
            :nomeTipoContratoOutsourcing,
            :codigoEtiqueta,
            :nomeRede,
            :nomeEqpto,
            :modeloEqpto,
            :valorEqpto,
            :nomeContato,
            :emailContato,
            :nomeGerenteContas,
            :emailGerenteContas,
            :motivacao,
            :tokenAvaliacao,
            :dataAvaliacao,
            :aprovado
        );
    ");

    $params = [
        ':idEquipamento' => $eqptoData['idEquipamento'],
        ':idContrato' => $eqptoData['idContrato'],
        ':idChamado' => $eqptoData['idChamado'],

        ':idContatoEqpto' => $eqptoData['idContatoEqpto'],
        ':idPessoaEmpresa' => $eqptoData['idPessoaEmpresa'],
        ':idGerenteContas' => $eqptoData['idGerenteContas'],

        ':dataAtivacaoEqpto' => $eqptoData['dataAtivacaoEqpto'],
        ':dataSolicitacao' => $eqptoData['dataSolicitacao'],

        ':nomeContratoOutsourcing' => $eqptoData['nomeContratoOutsourcing'],
        ':nomeTipoContratoOutsourcing' => $eqptoData['nomeTipoContratoOutsourcing'],

        ':nomeRede' => $eqptoData['nomeRede'],
        ':codigoEtiqueta' => $eqptoData['codigoEtiqueta'],

        ':nomeEqpto' => $eqptoData['nomeEqpto'],
        ':modeloEqpto' => $eqptoData['modeloEqpto'],
        ':valorEqpto' => $eqptoData['valorEqpto'],

        ':nomeContato' => $eqptoData['nomeContato'],
        ':emailContato' => $eqptoData['emailContato'],

        ':nomeGerenteContas' => $eqptoData['nomeGerenteContas'],
        ':emailGerenteContas' => $eqptoData['emailGerenteContas'],

        ':motivacao' => $eqptoData['motivacao'],

        ':tokenAvaliacao' => $eqptoData['tokenAvaliacao'],

        ':dataAvaliacao' => null,
        ':aprovado' => 0
    ];

    $dbResp = dbSqlExec($sqlExec, $params, $dbh);
    return $dbResp;
}

/**
 * atualizaEqptoDesativarIdChamado function
 *
 * @param integer $idEqptoDesativacao
 * @param integer $idChamado
 * @param ?\PDO $dbh
 * @return bool
 */
function atualizaEqptoDesativarIdChamado($idEqptoDesativacao=0, $idChamado=0, $dbh=null) {
    if (
        !isValidInteger($idEqptoDesativacao)
        || !isValidInteger($idChamado)
    ) {
        return false;
    }

    $sqlExec = \trim("
        UPDATE `contrato_outsourcing_equipamento_desativacao`
        SET `id_chamado` = :idChamado
        WHERE `id` = :idEqptoDesativacao
            AND `id_chamado` = 0;
    ");

    $params = [
        ':idChamado' => $idChamado,
        ':idEqptoDesativacao' => $idEqptoDesativacao
    ];

    $dbResp = dbSqlExec($sqlExec, $params, $dbh);
    return $dbResp;
}

/**
 * atualizarAvaliacaoEqptoDesativacao function
 *
 * @param string $tokenAvaliacao
 * @param bool $aprovado
 * @param ?\PDO $dbh
 * @return bool
 */
function atualizarAvaliacaoEqptoDesativacao($tokenAvaliacao='', $aprovado=false, $dbh=null) {
    if (
        !isValidString($tokenAvaliacao)
        || !is_bool($aprovado)
    ) {
        return false;
    }

    $sqlExec = \trim("
        UPDATE `contrato_outsourcing_equipamento_desativacao`

        SET `aprovado` = :aprovado,
            `data_avaliacao` = :dataAvaliacao

        WHERE `token_avaliacao` = :tokenAvaliacao;
    ");

    $dataAvaliacao = getTodayDate(DB_DATETIME_FORMAT);

    $params = [
        ':dataAvaliacao' => $dataAvaliacao,
        ':aprovado' => $aprovado ? 1 : 0,
        ':tokenAvaliacao' => $tokenAvaliacao
    ];

    $dbResp = dbSqlExec($sqlExec, $params, $dbh);
    return $dbResp;
}

/**
 * obterEqptoDesativacaoByToken function
 *
 * @param string $tokenAvaliacao
 * @param array $fields
 * @param ?\PDO $dbh
 * @return mixed
 */
function obterEqptoDesativacaoByToken($tokenAvaliacao='', $fields=['*'], $dbh=null) {
    if (!isValidString($tokenAvaliacao) || !isValidArray($fields)) {
        return false;
    }

    $strFieldsList = '';
    if (\count($fields) > 1) {
        foreach ($fields as $fn) {
            $strFieldsList .= "`" . $fn . "`,";
        }
        $strFieldsList = rtrim($strFieldsList, ',');
    } else {
        $strFieldsList = $fields[0];
    }

    $sqlExec = \trim("
        SELECT {$strFieldsList}
        FROM `contrato_outsourcing_equipamento_desativacao`
        WHERE `token_avaliacao` = :tokenAvaliacao
        LIMIT 1;
    ");

    $params = [
        ':tokenAvaliacao' => $tokenAvaliacao
    ];

    $dbResp = dbSqlQuery($sqlExec, $params, DB_QUERY_FETCH_ONE, $dbh);
    return $dbResp;
}

/**
 * removerTokenAvaliacaoEqptoDesativacao function
 *
 * @param string $tokenAvaliacao
 * @param ?\PDO $dbh
 * @return bool
 */
function removerTokenAvaliacaoEqptoDesativacao($tokenAvaliacao='', $dbh=null) {
    if (!isValidString($tokenAvaliacao)) {
        return false;
    }

    $sqlExec = \trim("
        UPDATE `contrato_outsourcing_equipamento_desativacao`
        SET `token_avaliacao` = ''
        WHERE `token_avaliacao` = :tokenAvaliacao
            AND `data_avaliacao` IS NOT NULL;
    ");

    $params = [
        ':tokenAvaliacao' => $tokenAvaliacao
    ];

    $dbResp = dbSqlExec($sqlExec, $params, $dbh);
    return $dbResp;
}

/**
 * ativarEquipamento function
 *
 * @param integer $idEqpto
 * @param ?\PDO $dbh
 * @return bool
 */
function ativarEquipamento($idEqpto=0, $dbh=null) {
    $sqlExec = \trim("
        UPDATE `contrato_outsourcing_equipamentos`
        SET `status` = :status
        WHERE `id` = :idEqpto;
    ");

    $params = [
        ':status' => EQP_STATUS_ATIVO,
        ':idEqpto' => $idEqpto
    ];

    $dbResp = dbSqlExec($sqlExec, $params, $dbh);
    return $dbResp;
}

/**
 * removerTokenAvaliacaoEqptoDesativacao function
 *
 * @param string $tokenAvaliacao
 * @param ?\PDO $dbh
 * @return bool
 */
function removerEqptoDesativacaoPorToken($tokenAvaliacao='', $dbh=null) {
    if (!isValidString($tokenAvaliacao)) {
        return false;
    }

    $sqlExec = \trim("
        DELETE FROM `contrato_outsourcing_equipamento_desativacao`
        WHERE `token_avaliacao` = :tokenAvaliacao;
    ");

    $params = [
        ':tokenAvaliacao' => $tokenAvaliacao
    ];

    $dbResp = dbSqlExec($sqlExec, $params, $dbh);
    return $dbResp;
}

/**
 * removerTodosEqptoDesativacaoSemChamado function
 *
 * @param \PDO|null $dbh
 * @return bool
 */
function removerTodosEqptoDesativacaoSemChamado($dbh=null) {
    $sqlExec = \trim("
        DELETE FROM `contrato_outsourcing_equipamento_desativacao`
        WHERE `id_chamado` = 0;
    ");

    $dbResp = dbSqlExec($sqlExec, null, $dbh);
    return $dbResp;
}
