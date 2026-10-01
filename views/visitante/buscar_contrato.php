<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dsn  = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
$user = 'bhcloud_admin';
$pass = '$Qnv3hf@BeBL';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Conexão falhou: ' . $e->getMessage()]);
    exit;
}

$id_tipo_contrato = $_POST['id_tipo_contrato'] ?? '';
$contrato      = $_POST['contrato'] ?? '';
$equipamento   = $_POST['equipamento'] ?? '';
$search_type   = $_POST['search_type'] ?? '';
$valor         = $_POST['valor'] ?? '';
$id_contato    = $_POST['id_contato'] ?? '';

// ==========================================
// TIPO 1 - HELPDESK
// ==========================================
if ($id_tipo_contrato == '1') {
    header('Content-Type: application/json');

    if (empty($contrato) && empty($equipamento)) {
        echo json_encode(['erro' => 'Informe contrato ou equipamento']);
        exit;
    }

    $sql = "
    SELECT 
        j.nome,
        j.cel,
        j.email,
        c.etiqueta,
        c.id_contrato,
        e.contrato,
        e.codigo
    FROM contrato_outsourcing c
    INNER JOIN contrato_outsourcing_equipamentos e ON c.id_contrato = e.contrato
    INNER JOIN pessoa_juridica_contatos j ON e.contato = j.id
    WHERE c.etiqueta = :etiqueta
    ";

    $params = [':etiqueta' => $id_tipo_contrato];

    if (!empty($equipamento)) {
        $sql .= " AND e.codigo = :equipamento";
        $params[':equipamento'] = $equipamento;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode(['erro' => 'Nenhum contrato encontrado']);
            exit;
        }

        $dados = $resultados[0];
        echo json_encode([
            'success' => true,
            'nome' => $dados['nome'] ?? '',
            'email' => $dados['email'] ?? '',
            'telefone' => $dados['cel'] ?? '',
            'id_contrato' => $dados['id_contrato'] ?? '',
            'contrato' => $dados['contrato'] ?? '',
            'codigo' => $dados['codigo'] ?? ''
        ]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// BUSCAR DADOS DO CONTATO POR ID
// ==========================================
if (!empty($id_contato)) {
    header('Content-Type: application/json');
    try {
        $sql = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_contato' => $id_contato]);
        $contato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($contato) {
            echo json_encode([
                'success' => true,
                'nome' => $contato['nome'] ?? '',
                'email' => $contato['email'] ?? '',
                'telefone' => $contato['cel'] ?? ''
            ]);
        } else {
            echo json_encode(['erro' => 'Contato não encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao buscar contato: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 2 - HOSPEDAGEM (CORRIGIDO - VALUE = ID_CONTRATO)
// ==========================================
elseif ($id_tipo_contrato == '2') {
    header('Content-Type: application/json');

    if (empty($valor)) {
        echo json_encode(['erro' => 'Informe CNPJ, CPF ou ID']);
        exit;
    }

    try {
        if ($search_type == 'CNPJ') {
            $sql = "SELECT
                        h.id AS id_dominio,
                        h.dominio,
                        h.id_contato,
                        c.id AS id_contrato
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_hosting h ON c.id = h.id_contrato
                    WHERE p.cnpj = :valor AND c.id_tipo = 2";
            $params = [':valor' => $valor];
            $search_label = 'CNPJ';
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        h.id AS id_dominio, 
                        h.dominio,
                        h.id_contato,
                        c.id AS id_contrato
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_hosting h ON c.id = h.id_contrato 
                    WHERE p.cpf = :valor AND c.id_tipo = 2";
            $params = [':valor' => $valor];
            $search_label = 'CPF';
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        h.id AS id_dominio,
                        h.dominio,
                        h.id_contato,
                        c.id AS id_contrato
                    FROM contrato_hosting h
                    JOIN contrato c ON c.id = h.id_contrato
                    WHERE h.id_contrato = :valor";
            $params = [':valor' => $valor];
            $search_label = 'ID';
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $sql_debug = $sql;
        foreach ($params as $key => $value) {
            $sql_debug = str_replace($key, "'" . addslashes($value) . "'", $sql_debug);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode([
                'erro' => 'Nenhum domínio encontrado para o ' . $search_label . ' informado',
                'query_executada' => $sql_debug,
                'parametros' => $params,
                'search_type' => $search_type,
                'valor_buscado' => $valor
            ]);
            exit;
        }

        // SEMPRE gerar select - VALUE = ID_CONTRATO
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="">Selecione um domínio...</option>';

        foreach ($resultados as $row) {
            $id_contrato_val = htmlspecialchars($row['id_contrato'] ?? '');
            $id_dominio = htmlspecialchars($row['id_dominio']);
            $dominio    = htmlspecialchars($row['dominio']);
            $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');

            $sqlContato = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
            $stmtContato = $pdo->prepare($sqlContato);
            $stmtContato->execute([':id_contato' => $id_contato_val]);
            $contato = $stmtContato->fetch(PDO::FETCH_ASSOC);

            $selectHtml .= "<option value='{$id_contrato_val}' 
                                    data-id-contrato='{$id_contrato_val}'
                                    data-id-dominio='{$id_dominio}'
                                    data-id-contato='{$id_contato_val}'
                                    data-dominio='{$dominio}'
                                    data-nome='" . htmlspecialchars($contato['nome'] ?? '') . "'
                                    data-email='" . htmlspecialchars($contato['email'] ?? '') . "'
                                    data-telefone='" . htmlspecialchars($contato['cel'] ?? '') . "'>
                                    {$dominio}
                            </option>";
        }
        $selectHtml .= '</select>';

        echo json_encode([
            'success' => true,
            'select_html' => $selectHtml,
            'usar_select' => true,
            'total_encontrados' => count($resultados),
            'query_executada' => $sql_debug
        ]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 4 - DOMÍNIO (CORRIGIDO - VALUE = ID_CONTRATO)
// ==========================================
elseif ($id_tipo_contrato == '4') {
    header('Content-Type: application/json');

    if (empty($valor)) {
        echo json_encode(['erro' => 'Informe CNPJ, CPF ou ID']);
        exit;
    }

    try {
        if ($search_type == 'CNPJ') {
            $sql = "SELECT
                        d.id AS id_dominio,
                        d.dominio,
                        d.id_contato,
                        c.id AS id_contrato
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_dominio d ON c.id = d.id_contrato
                    WHERE p.cnpj = :valor AND c.id_tipo = 4";
            $params = [':valor' => $valor];
            $search_label = 'CNPJ';
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        d.id AS id_dominio, 
                        d.dominio,
                        d.id_contato,
                        c.id AS id_contrato
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_dominio d ON c.id = d.id_contrato 
                    WHERE p.cpf = :valor AND c.id_tipo = 4";
            $params = [':valor' => $valor];
            $search_label = 'CPF';
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        d.id AS id_dominio,
                        d.dominio,
                        d.id_contato,
                        c.id AS id_contrato
                    FROM contrato_dominio d
                    JOIN contrato c ON c.id = d.id_contrato
                    WHERE d.id_contrato = :valor";
            $params = [':valor' => $valor];
            $search_label = 'ID';
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $sql_debug = $sql;
        foreach ($params as $key => $value) {
            $sql_debug = str_replace($key, "'" . addslashes($value) . "'", $sql_debug);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode([
                'erro' => 'Nenhum domínio encontrado para o ' . $search_label . ' informado',
                'query_executada' => $sql_debug,
                'parametros' => $params,
                'search_type' => $search_type,
                'valor_buscado' => $valor
            ]);
            exit;
        }

        // SEMPRE gerar select - VALUE = ID_CONTRATO
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="">Selecione um domínio...</option>';

        foreach ($resultados as $row) {
            $id_contrato_val = htmlspecialchars($row['id_contrato'] ?? '');
            $id_dominio = htmlspecialchars($row['id_dominio']);
            $dominio    = htmlspecialchars($row['dominio']);
            $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');

            $sqlContato = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
            $stmtContato = $pdo->prepare($sqlContato);
            $stmtContato->execute([':id_contato' => $id_contato_val]);
            $contato = $stmtContato->fetch(PDO::FETCH_ASSOC);

            $selectHtml .= "<option value='{$id_contrato_val}' 
                                    data-id-contrato='{$id_contrato_val}'
                                    data-id-dominio='{$id_dominio}'
                                    data-id-contato='{$id_contato_val}'
                                    data-dominio='{$dominio}'
                                    data-nome='" . htmlspecialchars($contato['nome'] ?? '') . "'
                                    data-email='" . htmlspecialchars($contato['email'] ?? '') . "'
                                    data-telefone='" . htmlspecialchars($contato['cel'] ?? '') . "'>
                                    {$dominio}
                            </option>";
        }
        $selectHtml .= '</select>';

        echo json_encode([
            'success' => true,
            'select_html' => $selectHtml,
            'usar_select' => true,
            'total_encontrados' => count($resultados),
            'query_executada' => $sql_debug
        ]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 5 - BACKUP (CORRIGIDO - VALUE = ID_CONTRATO)
// ==========================================
elseif ($id_tipo_contrato == '5') {
    header('Content-Type: application/json');

    if (empty($valor)) {
        echo json_encode(['erro' => 'Informe CNPJ, CPF ou ID']);
        exit;
    }

    try {
        if ($search_type == 'CNPJ') {
            $sql = "SELECT
                        b.id_contato,
                        b.id AS id_backup,
                        bp.plano AS backup,
                        c.id AS id_contrato
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_backup b ON c.id = b.id_contrato
                    JOIN contrato_backup_planos bp ON b.id_plano = bp.id
                    WHERE p.cnpj = :valor AND c.id_tipo = 5";
            $params = [':valor' => $valor];
            $search_label = 'CNPJ';
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        b.id_contato,
                        b.id AS id_backup,
                        bp.plano AS backup,
                        c.id AS id_contrato
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_backup b ON c.id = b.id_contrato
                    JOIN contrato_backup_planos bp ON b.id_plano = bp.id
                    WHERE p.cpf = :valor AND c.id_tipo = 5";
            $params = [':valor' => $valor];
            $search_label = 'CPF';
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        b.id_contato,
                        b.id AS id_backup,
                        bp.plano AS backup,
                        c.id AS id_contrato
                    FROM contrato_backup b
                    JOIN contrato c ON c.id = b.id_contrato
                    JOIN contrato_backup_planos bp ON b.id_plano = bp.id
                    WHERE b.id_contrato = :valor";
            $params = [':valor' => $valor];
            $search_label = 'ID';
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $sql_debug = $sql;
        foreach ($params as $key => $value) {
            $sql_debug = str_replace($key, "'" . addslashes($value) . "'", $sql_debug);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode([
                'erro' => 'Nenhum backup encontrado para o ' . $search_label . ' informado',
                'query_executada' => $sql_debug,
                'parametros' => $params,
                'search_type' => $search_type,
                'valor_buscado' => $valor
            ]);
            exit;
        }

        // SEMPRE gerar select - VALUE = ID_CONTRATO
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="">Selecione um plano de backup...</option>';

        foreach ($resultados as $row) {
            $id_contrato_val = htmlspecialchars($row['id_contrato'] ?? '');
            $id_backup = htmlspecialchars($row['id_backup']);
            $backup    = htmlspecialchars($row['backup'] ?? '');
            $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');

            $sqlContato = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
            $stmtContato = $pdo->prepare($sqlContato);
            $stmtContato->execute([':id_contato' => $id_contato_val]);
            $contato = $stmtContato->fetch(PDO::FETCH_ASSOC);

            $selectHtml .= "<option value='{$id_contrato_val}' 
                                    data-id-contrato='{$id_contrato_val}'
                                    data-id-backup='{$id_backup}'
                                    data-id-contato='{$id_contato_val}'
                                    data-backup='{$backup}'
                                    data-nome='" . htmlspecialchars($contato['nome'] ?? '') . "'
                                    data-email='" . htmlspecialchars($contato['email'] ?? '') . "'
                                    data-telefone='" . htmlspecialchars($contato['cel'] ?? '') . "'>
                                    {$backup}
                            </option>";
        }
        $selectHtml .= '</select>';

        echo json_encode([
            'success' => true,
            'select_html' => $selectHtml,
            'usar_select' => true,
            'total_encontrados' => count($resultados),
            'query_executada' => $sql_debug
        ]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 6 - SUPORTE PRODUTO (CORRIGIDO - VALUE = ID_CONTRATO)
// ==========================================
elseif ($id_tipo_contrato == '6') {
    header('Content-Type: application/json');

    if (empty($valor)) {
        echo json_encode(['erro' => 'Informe CNPJ, CPF ou ID']);
        exit;
    }

    try {
        if ($search_type == 'CNPJ') {
            $sql = "SELECT 
                        cs.id_contato,
                        cs.id_produto,
                        sp.id AS id_suporte,
                        sp.descricao AS suporte,
                        c.id AS id_contrato,
                        cj.nome,
                        cj.cel,
                        cj.email
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_suporte cs ON c.id = cs.id_contrato
                    JOIN contrato_suporte_produto sp ON cs.id_produto = sp.id
                    LEFT JOIN pessoa_juridica_contatos cj ON cs.id_contato = cj.id
                    WHERE p.cnpj = :valor AND c.id_tipo = 6";
            $params = [':valor' => $valor];
            $search_label = 'CNPJ';
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        cs.id_contato,
                        cs.id_produto,
                        sp.id AS id_suporte,
                        sp.descricao AS suporte,
                        c.id AS id_contrato,
                        cj.nome,
                        cj.cel,
                        cj.email
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_suporte cs ON c.id = cs.id_contrato
                    JOIN contrato_suporte_produto sp ON cs.id_produto = sp.id
                    LEFT JOIN pessoa_juridica_contatos cj ON cs.id_contato = cj.id
                    WHERE p.cpf = :valor AND c.id_tipo = 6";
            $params = [':valor' => $valor];
            $search_label = 'CPF';
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        cs.id_contato,
                        cs.id_produto,
                        sp.id AS id_suporte,
                        sp.descricao AS suporte,
                        c.id AS id_contrato,
                        cj.nome,
                        cj.cel,
                        cj.email
                    FROM contrato_suporte cs
                    JOIN contrato c ON c.id = cs.id_contrato
                    JOIN contrato_suporte_produto sp ON cs.id_produto = sp.id
                    LEFT JOIN pessoa_juridica_contatos cj ON cs.id_contato = cj.id
                    WHERE cs.id_contrato = :valor";
            $params = [':valor' => $valor];
            $search_label = 'ID';
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $sql_debug = $sql;
        foreach ($params as $key => $value) {
            $sql_debug = str_replace($key, "'" . addslashes($value) . "'", $sql_debug);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode([
                'erro' => 'Nenhum suporte produto encontrado para o ' . $search_label . ' informado',
                'query_executada' => $sql_debug,
                'parametros' => $params,
                'search_type' => $search_type,
                'valor_buscado' => $valor
            ]);
            exit;
        }

        // SEMPRE gerar select - VALUE = ID_CONTRATO
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="">Selecione um produto/serviço...</option>';

        foreach ($resultados as $row) {
            $id_contrato_val = htmlspecialchars($row['id_contrato'] ?? '');
            $id_suporte = htmlspecialchars($row['id_suporte']);
            $suporte    = htmlspecialchars($row['suporte'] ?? '');
            $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');
            $nome = htmlspecialchars($row['nome'] ?? '');
            $email = htmlspecialchars($row['email'] ?? '');
            $telefone = htmlspecialchars($row['cel'] ?? '');

            $selectHtml .= "<option value='{$id_contrato_val}' 
                                    data-id-contrato='{$id_contrato_val}'
                                    data-id-suporte='{$id_suporte}'
                                    data-id-contato='{$id_contato_val}'
                                    data-suporte='{$suporte}'
                                    data-nome='{$nome}'
                                    data-email='{$email}'
                                    data-telefone='{$telefone}'>
                                    {$suporte}
                            </option>";
        }
        $selectHtml .= '</select>';

        echo json_encode([
            'success' => true,
            'select_html' => $selectHtml,
            'usar_select' => true,
            'total_encontrados' => count($resultados),
            'query_executada' => $sql_debug
        ]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 7 - LOCAÇÃO (CORRIGIDO - VALUE = ID_CONTRATO)
// ==========================================
elseif ($id_tipo_contrato == '7') {
    header('Content-Type: application/json');

    if (empty($valor)) {
        echo json_encode(['erro' => 'Informe CNPJ, CPF ou ID']);
        exit;
    }

    try {
        if ($search_type == 'CNPJ') {
            $sql = "
                SELECT 
                    e.id_contrato,
                    e.id_contato,
                    n.id AS id_equipamento,
                    n.descricao AS equipamento,
                    cj.nome,
                    cj.cel,
                    cj.email
                FROM pessoa_juridica p
                JOIN contrato c ON c.id_pessoa = p.id_pessoa
                JOIN contrato_locacao_equipamentos_locados e ON c.id = e.id_contrato
                LEFT JOIN contrato_locacao_equipamentos n ON e.id_equipamento = n.id
                LEFT JOIN pessoa_juridica_contatos cj ON e.id_contato = cj.id
                WHERE p.cnpj = :valor AND c.id_tipo = 7
                ORDER BY n.descricao ASC
            ";
            $params = [':valor' => $valor];
            $search_label = 'CNPJ';
        } elseif ($search_type == 'CPF') {
            $sql = "
                SELECT 
                    e.id_contrato,
                    e.id_contato,
                    n.id AS id_equipamento,
                    n.descricao AS equipamento,
                    cj.nome,
                    cj.cel,
                    cj.email
                FROM pessoa_fisica p 
                JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                JOIN contrato_locacao_equipamentos_locados e ON c.id = e.id_contrato
                LEFT JOIN contrato_locacao_equipamentos n ON e.id_equipamento = n.id
                LEFT JOIN pessoa_juridica_contatos cj ON e.id_contato = cj.id
                WHERE p.cpf = :valor AND c.id_tipo = 7
                ORDER BY n.descricao ASC
            ";
            $params = [':valor' => $valor];
            $search_label = 'CPF';
        } elseif ($search_type == 'ID') {
            $sql = "
                SELECT 
                    e.id_contrato, 
                    e.id_contato, 
                    n.id AS id_equipamento, 
                    n.descricao AS equipamento,
                    cj.nome,
                    cj.cel,
                    cj.email
                FROM contrato_locacao_equipamentos_locados e
                LEFT JOIN contrato_locacao_equipamentos n ON e.id_equipamento = n.id
                LEFT JOIN pessoa_juridica_contatos cj ON e.id_contato = cj.id
                WHERE e.id_contrato = :valor
                ORDER BY n.descricao ASC
            ";
            $params = [':valor' => $valor];
            $search_label = 'ID';
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        } 

        $sql_debug = $sql;
        foreach ($params as $key => $value) {
            $sql_debug = str_replace($key, "'" . addslashes($value) . "'", $sql_debug);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode([
                'erro' => 'Nenhum equipamento locado encontrado para o ' . $search_label . ' informado',
                'query_executada' => $sql_debug,
                'parametros' => $params,
                'search_type' => $search_type,
                'valor_buscado' => $valor
            ]);
            exit;
        }

        // SEMPRE gerar select - VALUE = ID_CONTRATO
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="">Selecione um equipamento...</option>';

        foreach ($resultados as $row) {
            $id_contrato_val = htmlspecialchars($row['id_contrato'] ?? '');
            $id_equipamento = htmlspecialchars($row['id_equipamento'] ?? '');
            $equipamento    = htmlspecialchars($row['equipamento'] ?? 'Equipamento sem descrição');
            $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');
            $nome = htmlspecialchars($row['nome'] ?? '');
            $email = htmlspecialchars($row['email'] ?? '');
            $telefone = htmlspecialchars($row['cel'] ?? '');

            $selectHtml .= "<option value='{$id_contrato_val}' 
                                    data-id-contrato='{$id_contrato_val}'
                                    data-id-equipamento='{$id_equipamento}'
                                    data-id-contato='{$id_contato_val}'
                                    data-equipamento='{$equipamento}'
                                    data-nome='{$nome}'
                                    data-email='{$email}'
                                    data-telefone='{$telefone}'>
                                    {$equipamento}
                            </option>";
        }
        $selectHtml .= '</select>';

        echo json_encode([
            'success' => true,
            'select_html' => $selectHtml,
            'usar_select' => true,
            'total_encontrados' => count($resultados),
            'query_executada' => $sql_debug
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'erro' => 'Erro na consulta: ' . $e->getMessage(),
            'query_executada' => $sql_debug ?? $sql,
            'parametros' => $params ?? []
        ]);
    }
    exit;
}

// ==========================================
// OUTROS TIPOS
// ==========================================
else {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Tipo de contrato não implementado']);
}
