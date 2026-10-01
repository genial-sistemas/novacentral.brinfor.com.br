<?php
// api/enviar-evento-chamado.php

// ============================================
// CONFIGURAÇÕES
// ============================================
// Limpa qualquer saída anterior
ob_clean();

// Configura o header para JSON
header('Content-Type: application/json; charset=utf-8');

// ============================================
// FUNÇÃO PARA RETORNAR ERRO EM JSON
// ============================================
function retornarErro($mensagem, $debug = null) {
    $resposta = ['success' => false, 'message' => $mensagem];
    if ($debug !== null) {
        $resposta['debug'] = $debug;
    }
    echo json_encode($resposta);
    exit;
}

// ============================================
// CONEXÃO COM O BANCO
// ============================================
$dsn = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
$user = 'bhcloud_admin';
$pass = '$Qnv3hf@BeBL';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    retornarErro('Erro de conexão: ' . $e->getMessage());
}

// ============================================
// FUNÇÃO PARA RENOMEAR ARQUIVO
// ============================================
function renomearArquivo($idChamado, $extensao)
{
    $extensao = strtolower(trim($extensao));
    $extensao = preg_replace('/[^a-zA-Z0-9]/', '', $extensao);
    $nomeArquivo = uniqid($idChamado . '_');
    $nomeCompleto = $nomeArquivo . '.' . $extensao;
    return $nomeCompleto;
}

// ============================================
// FUNÇÃO PARA CRIAR PASTA
// ============================================
function criarPasta($caminho) {
    if (!file_exists($caminho)) {
        $criou = mkdir($caminho, 0777, true);
        if (!$criou) {
            return false;
        }
        chmod($caminho, 0777);
    }
    if (!is_writable($caminho)) {
        chmod($caminho, 0777);
        if (!is_writable($caminho)) {
            return false;
        }
    }
    return true;
}

// ============================================
// VERIFICA SE É UPLOAD DE ARQUIVO
// ============================================
$isFileUpload = isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK;

if ($isFileUpload) {
    // === UPLOAD DE ARQUIVO ===
    $idChamado = isset($_POST['id_chamado']) ? (int)$_POST['id_chamado'] : 0;
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 7;
    $data = isset($_POST['data']) ? $_POST['data'] : date('Y-m-d');
    $hora = isset($_POST['hora']) ? $_POST['hora'] : date('H:i:s');
    
    if ($idChamado <= 0) {
        retornarErro('ID do chamado inválido');
    }
    
    // Processa o arquivo
    $arquivo = $_FILES['arquivo'];
    $nomeOriginal = $arquivo['name'];
    $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
    $tmpName = $arquivo['tmp_name'];
    
    // Verifica se o arquivo temporário existe
    if (!file_exists($tmpName)) {
        retornarErro('Arquivo temporário não encontrado', ['tmp_name' => $tmpName]);
    }
    
    $novoNome = renomearArquivo($idChamado, $extensao);
    
    // Tenta diferentes caminhos
    $caminhosParaTestar = [
        $_SERVER['DOCUMENT_ROOT'] . "/chamados/eventos/{$idChamado}/",
        dirname(__DIR__) . "/chamados/eventos/{$idChamado}/",
        __DIR__ . "/../chamados/eventos/{$idChamado}/",
        "/home/bhcloud/public_html/chamados/eventos/{$idChamado}/",
    ];
    
    $pastaDestino = null;
    foreach ($caminhosParaTestar as $caminho) {
        if (criarPasta($caminho)) {
            $pastaDestino = $caminho;
            break;
        }
    }
    
    if (!$pastaDestino) {
        retornarErro('Não foi possível criar a pasta', [
            'caminhos_testados' => $caminhosParaTestar,
            'document_root' => $_SERVER['DOCUMENT_ROOT']
        ]);
    }
    
    $caminhoCompleto = $pastaDestino . $novoNome;
    $caminhoRelativo = "chamados/eventos/{$idChamado}/{$novoNome}";
    
    // Move o arquivo
    if (!move_uploaded_file($tmpName, $caminhoCompleto)) {
        $error = error_get_last();
        retornarErro('Erro ao salvar o arquivo', [
            'pasta' => $pastaDestino,
            'caminho' => $caminhoCompleto,
            'error' => $error ? $error['message'] : 'Erro desconhecido'
        ]);
    }
    
    // Verifica se o arquivo foi salvo
    if (!file_exists($caminhoCompleto)) {
        retornarErro('Arquivo não encontrado após o upload', ['caminho' => $caminhoCompleto]);
    }
    
    // ============================================
    // MONTA A DESCRIÇÃO COM O CAMINHO DO ARQUIVO
    // ============================================
    // Se veio descrição, mantém e adiciona o caminho
    // Se não veio descrição, usa apenas o caminho
    if (!empty($descricao)) {
        $descricaoFinal = $descricao . " - " . $caminhoRelativo;
    } else {
        $descricaoFinal = $caminhoRelativo;
    }
    
    // Insere no banco
    try {
        $query = "INSERT INTO bhcloud_bhinfor.contrato_chamado_eventos 
                  (id_chamado, descricao, status, data, hora) 
                  VALUES 
                  (:id_chamado, :anexo, :status, :data, :hora )";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':id_chamado' => $idChamado,
            ':descricao' => $caminhoRelativo,
            ':status' => $status,
            ':data' => $data,
            ':hora' => $hora,
        ]);
        
        $idEvento = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Arquivo enviado com sucesso',
            'id_evento' => $idEvento,
            'arquivo' => $caminhoRelativo,
            'nome_original' => $nomeOriginal,
            'nome_gerado' => $novoNome,
            'descricao' => $descricaoFinal
        ]);
        
    } catch (PDOException $e) {
        retornarErro('Erro ao inserir no banco: ' . $e->getMessage());
    }
    
} else {
    // === MENSAGEM DE TEXTO (JSON) ===
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        retornarErro('Dados inválidos');
    }
    
    $idChamado = isset($input['id_chamado']) ? (int)$input['id_chamado'] : 0;
    $descricao = isset($input['descricao']) ? trim($input['descricao']) : '';
    $status = isset($input['status']) ? (int)$input['status'] : 7;
    $data = isset($input['data']) ? $input['data'] : date('Y-m-d');
    $hora = isset($input['hora']) ? $input['hora'] : date('H:i:s');
    
    if ($idChamado <= 0 || empty($descricao)) {
        retornarErro('Dados incompletos');
    }
    
    try {
        $query = "INSERT INTO bhcloud_bhinfor.contrato_chamado_eventos 
                  (id_chamado, descricao, status, data, hora) 
                  VALUES 
                  (:id_chamado, :descricao, :status, :data, :hora)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':id_chamado' => $idChamado,
            ':descricao' => $descricao,
            ':status' => $status,
            ':data' => $data,
            ':hora' => $hora
        ]);
        
        $idEvento = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Evento inserido com sucesso',
            'id_evento' => $idEvento
        ]);
        
    } catch (PDOException $e) {
        retornarErro('Erro ao inserir: ' . $e->getMessage());
    }
}
?>