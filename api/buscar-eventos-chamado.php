<?php
// api/buscar-eventos-chamado.php

$dsn = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
$user = 'bhcloud_admin';
$pass = '$Qnv3hf@BeBL';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()]);
    exit;
}

$idChamado = isset($_GET['id_chamado']) ? (int)$_GET['id_chamado'] : 0;

if ($idChamado <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID do chamado inválido']);
    exit;
}

try {
    $query = "SELECT * FROM bhcloud_bhinfor.contrato_chamado_eventos WHERE id_chamado = :id_chamado ORDER BY data, hora";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id_chamado' => $idChamado]);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'eventos' => $eventos
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>