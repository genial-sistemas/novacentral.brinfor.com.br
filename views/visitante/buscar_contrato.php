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
$id_contato    = $_POST['id_contato'] ?? ''; // Para buscar dados do contato após selecionar

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
// TIPO 2 - HOSPEDAGEM
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
                        h.id_contato
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_hosting h ON c.id = h.id_contrato
                    WHERE p.cnpj = :valor AND c.id_tipo = 2";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        h.id AS id_dominio, 
                        h.dominio,
                        h.id_contato
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_hosting h ON c.id = h.id_contrato 
                    WHERE p.cpf = :valor AND c.id_tipo = 2";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        h.id AS id_dominio,
                        h.dominio,
                        h.id_contato
                    FROM contrato_hosting h
                    WHERE h.id_contrato = :valor";
            $params = [':valor' => $valor];
            $usarSelect = false;
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode(['erro' => 'Nenhum domínio encontrado para o ' . $search_type . ' informado']);
            exit;
        }

        $primeiro = $resultados[0];

        if ($usarSelect && count($resultados) > 1) {
            $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
            $selectHtml .= '<option value="">Selecione um domínio...</option>';

            foreach ($resultados as $row) {
                $id_dominio = htmlspecialchars($row['id_dominio']);
                $dominio    = htmlspecialchars($row['dominio']);
                $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');

                $selectHtml .= "<option value='{$id_contato_val}' 
                                        data-dominio='{$dominio}'
                                        data-id-dominio='{$id_dominio}'>
                                        {$dominio}
                                </option>";
            }
            $selectHtml .= '</select>';

            echo json_encode([
                'success' => true,
                'select_html' => $selectHtml,
                'usar_select' => true
            ]);
        } else {
            $id_contato_val = $primeiro['id_contato'] ?? '';
            // Buscar dados do contato diretamente
            $sqlContato = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
            $stmtContato = $pdo->prepare($sqlContato);
            $stmtContato->execute([':id_contato' => $id_contato_val]);
            $contato = $stmtContato->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'nome' => $contato['nome'] ?? '',
                'email' => $contato['email'] ?? '',
                'telefone' => $contato['cel'] ?? '',
                'dominio' => $primeiro['dominio'] ?? '',
                'usar_select' => false
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 4 - DOMÍNIO
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
                        d.id_contato
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_dominio d ON c.id = d.id_contrato
                    WHERE p.cnpj = :valor AND c.id_tipo = 4";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        d.id AS id_dominio, 
                        d.dominio,
                        d.id_contato
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_dominio d ON c.id = d.id_contrato 
                    WHERE p.cpf = :valor AND c.id_tipo = 4";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        d.id AS id_dominio,
                        d.dominio,
                        d.id_contato
                    FROM contrato_dominio d
                    WHERE d.id_contrato = :valor";
            $params = [':valor' => $valor];
            $usarSelect = false;
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode(['erro' => 'Nenhum domínio encontrado para o ' . $search_type . ' informado']);
            exit;
        }

        $primeiro = $resultados[0];

        if ($usarSelect && count($resultados) > 1) {
            $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
            $selectHtml .= '<option value="">Selecione um domínio...</option>';

            foreach ($resultados as $row) {
                $id_dominio = htmlspecialchars($row['id_dominio']);
                $dominio    = htmlspecialchars($row['dominio']);
                $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');

                $selectHtml .= "<option value='{$id_contato_val}' 
                                        data-dominio='{$dominio}'
                                        data-id-dominio='{$id_dominio}'>
                                        {$dominio}
                                </option>";
            }
            $selectHtml .= '</select>';

            echo json_encode([
                'success' => true,
                'select_html' => $selectHtml,
                'usar_select' => true
            ]);
        } else {
            $id_contato_val = $primeiro['id_contato'] ?? '';
            $sqlContato = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
            $stmtContato = $pdo->prepare($sqlContato);
            $stmtContato->execute([':id_contato' => $id_contato_val]);
            $contato = $stmtContato->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'nome' => $contato['nome'] ?? '',
                'email' => $contato['email'] ?? '',
                'telefone' => $contato['cel'] ?? '',
                'dominio' => $primeiro['dominio'] ?? '',
                'usar_select' => false
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}

// ==========================================
// TIPO 5 - BACKUP
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
                        bp.plano AS backup
                    FROM pessoa_juridica p
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa
                    JOIN contrato_backup b ON c.id = b.id_contrato
                    JOIN contrato_backup_planos bp ON b.id_plano = bp.id
                    WHERE p.cnpj = :valor AND c.id_tipo = 5";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        b.id_contato,
                        b.id AS id_backup,
                        bp.plano AS backup
                    FROM pessoa_fisica p 
                    JOIN contrato c ON c.id_pessoa = p.id_pessoa 
                    JOIN contrato_backup b ON c.id = b.id_contrato
                    JOIN contrato_backup_planos bp ON b.id_plano = bp.id
                    WHERE p.cpf = :valor AND c.id_tipo = 5";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } elseif ($search_type == 'ID') {
            // Busca por ID do contrato - retorna select com os planos
            $sql = "SELECT 
                        b.id_contato,
                        b.id AS id_backup,
                        bp.plano AS backup
                    FROM contrato_backup b
                    JOIN contrato_backup_planos bp ON b.id_plano = bp.id
                    WHERE b.id_contrato = :valor";
            $params = [':valor' => $valor];
            $usarSelect = true; // Agora usa select para múltiplos planos
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode(['erro' => 'Nenhum backup encontrado para o ' . $search_type . ' informado']);
            exit;
        }

        $primeiro = $resultados[0];

        // Se tiver mais de um resultado ou for busca por ID (sempre usar select)
        if (count($resultados) > 1 || $search_type == 'ID') {
            $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
            $selectHtml .= '<option value="">Selecione um plano de backup...</option>';

            foreach ($resultados as $row) {
                $id_backup = htmlspecialchars($row['id_backup']);
                $backup    = htmlspecialchars($row['backup'] ?? '');
                $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');

                $selectHtml .= "<option value='{$id_contato_val}' 
                                        data-backup='{$backup}'
                                        data-id-backup='{$id_backup}'>
                                        {$backup}
                                </option>";
            }
            $selectHtml .= '</select>';

            echo json_encode([
                'success' => true,
                'select_html' => $selectHtml,
                'usar_select' => true
            ]);
        } else {
            // Resultado único - busca contato se existir
            $id_contato_val = $primeiro['id_contato'] ?? '';

            if (!empty($id_contato_val)) {
                $sqlContato = "SELECT nome, cel, email FROM pessoa_juridica_contatos WHERE id = :id_contato";
                $stmtContato = $pdo->prepare($sqlContato);
                $stmtContato->execute([':id_contato' => $id_contato_val]);
                $contato = $stmtContato->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'nome' => $contato['nome'] ?? '',
                    'email' => $contato['email'] ?? '',
                    'telefone' => $contato['cel'] ?? '',
                    'backup' => $primeiro['backup'] ?? '',
                    'id_backup' => $primeiro['id_backup'] ?? '',
                    'usar_select' => false
                ]);
            } else {
                // Sem contato associado
                echo json_encode([
                    'success' => true,
                    'nome' => '',
                    'email' => '',
                    'telefone' => '',
                    'backup' => $primeiro['backup'] ?? '',
                    'id_backup' => $primeiro['id_backup'] ?? '',
                    'usar_select' => false
                ]);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}


// ==========================================
// TIPO 6 - SUPORTE PRODUTO
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
            $usarSelect = true;
        } elseif ($search_type == 'CPF') {
            $sql = "SELECT 
                        cs.id_contato,
                        cs.id_produto,
                        sp.id AS id_suporte,
                        sp.descricao AS suporte,
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
            $usarSelect = true;
        } elseif ($search_type == 'ID') {
            $sql = "SELECT 
                        cs.id_contato,
                        cs.id_produto,
                        sp.id AS id_suporte,
                        sp.descricao AS suporte,
                        cj.nome,
                        cj.cel,
                        cj.email
                    FROM contrato_suporte cs
                    JOIN contrato_suporte_produto sp ON cs.id_produto = sp.id
                    LEFT JOIN pessoa_juridica_contatos cj ON cs.id_contato = cj.id
                    WHERE cs.id_contrato = :valor";
            $params = [':valor' => $valor];
            $usarSelect = true;
        } else {
            echo json_encode(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
            exit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($resultados)) {
            echo json_encode(['erro' => 'Nenhum suporte produto encontrado para o ' . $search_type . ' informado']);
            exit;
        }

        $primeiro = $resultados[0];

        // Se tiver mais de um resultado, mostra select
        if (count($resultados) > 1) {
            $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
            $selectHtml .= '<option value="">Selecione um produto/serviço...</option>';

            foreach ($resultados as $row) {
                $id_suporte = htmlspecialchars($row['id_suporte']);
                $suporte    = htmlspecialchars($row['suporte'] ?? '');
                $id_contato_val = htmlspecialchars($row['id_contato'] ?? '');
                $nome = htmlspecialchars($row['nome'] ?? '');
                $email = htmlspecialchars($row['email'] ?? '');
                $telefone = htmlspecialchars($row['cel'] ?? '');

                $selectHtml .= "<option value='{$id_contato_val}' 
                                        data-suporte='{$suporte}'
                                        data-id-suporte='{$id_suporte}'
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
                'usar_select' => true
            ]);
        } else {
            // Resultado único - já tem os dados do contato
            $id_contato_val = $primeiro['id_contato'] ?? '';
            $suporte_descricao = $primeiro['suporte'] ?? '';
            $nome = $primeiro['nome'] ?? '';
            $email = $primeiro['email'] ?? '';
            $telefone = $primeiro['cel'] ?? '';

            // Cria o input readonly com a descrição do produto
            $inputHtml = '<input type="text" class="form-control" id="resultadoBusca" 
                                style="display:block;" 
                                value="' . htmlspecialchars($suporte_descricao) . '" 
                                readonly 
                                data-id-suporte="' . htmlspecialchars($primeiro['id_suporte'] ?? '') . '"
                                data-nome="' . htmlspecialchars($nome) . '"
                                data-email="' . htmlspecialchars($email) . '"
                                data-telefone="' . htmlspecialchars($telefone) . '">';

            echo json_encode([
                'success' => true,
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'suporte' => $suporte_descricao,
                'id_suporte' => $primeiro['id_suporte'] ?? '',
                'input_html' => $inputHtml,
                'usar_select' => false
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
    }
    exit;
}
// ==========================================
// OUTROS TIPOS (,7)
// ==========================================
else {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Tipo de contrato não implementado']);
}
