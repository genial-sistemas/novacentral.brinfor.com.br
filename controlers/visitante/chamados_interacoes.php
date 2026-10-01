<?php
// ============================================
// chamados_interacoes.php - VERSÃO FUNCIONAL
// ============================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================
// CAPTURA OS PARÂMETROS
// ============================================
$idChamado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$seguranca = isset($_GET['seguranca']) ? trim($_GET['seguranca']) : '';

// ============================================
// CONEXÃO COM O BANCO
// ============================================
try {
    $dsn = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
    $user = 'bhcloud_admin';
    $pass = '$Qnv3hf@BeBL';
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// ============================================
// BUSCA O CHAMADO
// ============================================
$viewData = [
    'idChamado' => 0,
    'seguranca' => '',
    'idTipoContrato' => 0,
    'tipoContrato' => '',
    'situacao' => '',
    'dataAbertura' => '',
    'nome' => '',
    'email' => '',
    'telefone' => '',
    'urgencia' => '',
    'solicitacao' => '',
    'interacoes' => [],
    'error' => null
];

if ($idChamado > 0 && !empty($seguranca)) {
    try {
        // Busca o chamado
        $sql = "SELECT * FROM bhcloud_bhinfor.contrato_chamado WHERE id = ? AND seguranca = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idChamado, $seguranca]);
        $chamado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($chamado) {
            $viewData['idChamado'] = $chamado['id'];
            $viewData['seguranca'] = $chamado['seguranca'];
            $viewData['idTipoContrato'] = $chamado['id_tipo_contrato'] ?? 0;
            $viewData['situacao'] = $chamado['id_situacao'] ?? '';
            $viewData['nome'] = $chamado['nome'] ?? '';
            $viewData['email'] = $chamado['email'] ?? '';
            $viewData['telefone'] = $chamado['telefone'] ?? '';
            $viewData['urgencia'] = $chamado['urgencia'] ?? '';
            
            // Formata data
            if (!empty($chamado['data_abertura'])) {
                $viewData['dataAbertura'] = date('d/m/Y H:i:s', strtotime($chamado['data_abertura']));
            }
            
            // Busca interações do chamado
            $sqlInteracoes = "SELECT * FROM bhcloud_bhinfor.contrato_chamado_eventos WHERE id_chamado = ? ORDER BY data DESC, hora DESC";
            $stmtInteracoes = $pdo->prepare($sqlInteracoes);
            $stmtInteracoes->execute([$idChamado]);
            $viewData['interacoes'] = $stmtInteracoes->fetchAll(PDO::FETCH_ASSOC);
            
        } else {
            $viewData['error'] = 'Chamado não encontrado.';
        }
    } catch (PDOException $e) {
        $viewData['error'] = 'Erro ao buscar chamado: ' . $e->getMessage();
    }
} else {
    $viewData['error'] = 'Parâmetros inválidos.';
}

// Extrai variáveis para a view
extract($viewData);

// ============================================
// DEBUG (remover depois que funcionar)
//echo "<h2>📊 Dados do Chamado</h2>";
//echo "<pre>";
//print_r($viewData);
//echo "</pre>";
// ============================================
if ($viewData['error']) {
    echo "<h2 style='color:red;'>❌ " . htmlspecialchars($viewData['error']) . "</h2>";
    die();
}
// ============================================

// ============================================
// TENTA CARREGAR A VIEW ORIGINAL
// ============================================
$viewPath = __DIR__ . '/../../views/visitante/chamados_interacoes.php';

if (file_exists($viewPath)) {
    //echo "<h2>✅ View encontrada: " . $viewPath . "</h2>";
    include $viewPath;
} else {
    // Se a view não existir, mostra os dados diretamente
    echo "<h1>Chamado #{$viewData['idChamado']}</h1>";
    echo "<h3>Dados do Chamado</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>ID</td><td>{$viewData['idChamado']}</td></tr>";
    echo "<tr><td>Nome</td><td>{$viewData['nome']}</td></tr>";
    echo "<tr><td>Email</td><td>{$viewData['email']}</td></tr>";
    echo "<tr><td>Telefone</td><td>{$viewData['telefone']}</td></tr>";
    echo "<tr><td>Data Abertura</td><td>{$viewData['dataAbertura']}</td></tr>";
    echo "<tr><td>Situação</td><td>{$viewData['situacao']}</td></tr>";
    echo "</table>";
    
    if (!empty($viewData['interacoes'])) {
        echo "<h3>Interações ({$viewData['interacoes']})</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Data</th><th>Hora</th><th>Tipo</th><th>Descrição</th></tr>";
        foreach ($viewData['interacoes'] as $interacao) {
            echo "<tr>";
            echo "<td>" . ($interacao['data'] ?? '') . "</td>";
            echo "<td>" . ($interacao['hora'] ?? '') . "</td>";
            echo "<td>" . ($interacao['tipo'] ?? '') . "</td>";
            echo "<td>" . ($interacao['descricao'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}