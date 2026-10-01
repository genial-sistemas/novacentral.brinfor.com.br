<?php
// api/buscar-info-chamado.php

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
    // SUA QUERY ORIGINAL
    $query = "SELECT
                c.id,
                c.data_abertura,
                c.id_situacao,
                e.id_pessoa,
                p.nome_pessoa AS tecnico
            FROM bhcloud_bhinfor.contrato_chamado c
            LEFT JOIN bhcloud_bhinfor.contrato_chamado_eventos e
                ON e.id_chamado = c.id
            LEFT JOIN bhcloud_bhinfor.pessoa p
                ON p.id = e.id_pessoa
            WHERE c.id = :id_chamado
              AND e.id_pessoa IS NOT NULL";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id_chamado' => $idChamado]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    // BUSCA A ÚLTIMA INTERAÇÃO
    $queryUltima = "SELECT 
                        data, 
                        hora 
                    FROM bhcloud_bhinfor.contrato_chamado_eventos 
                    WHERE id_chamado = :id_chamado 
                    ORDER BY id_evento DESC 
                    LIMIT 1";
    
    $stmtUltima = $pdo->prepare($queryUltima);
    $stmtUltima->execute([':id_chamado' => $idChamado]);
    $ultimaInteracao = $stmtUltima->fetch(PDO::FETCH_ASSOC);

    // MAPEIA O STATUS
    $statusMap = [
        1 => 'Aberto',
        2 => 'Pendente',
        3 => 'Em Atendimento',
        4 => 'Finalizado',
        5 => 'Cancelado',
        6 => 'Aguardando',
        7 => 'Em Andamento'
    ];

    $situacao = 'Não informado';
    if ($info && $info['id_situacao']) {
        $situacao = isset($statusMap[$info['id_situacao']]) ? $statusMap[$info['id_situacao']] : 'Status ' . $info['id_situacao'];
    }

    // MONTA A RESPOSTA
    $response = [
        'success' => true,
        'info' => [
            'id_chamado' => $info ? $info['id'] : $idChamado,
            'data_abertura' => $info ? $info['data_abertura'] : null,
            'id_situacao' => $info ? $info['id_situacao'] : null,
            'situacao' => $situacao,
            'tecnico_responsavel' => $info && $info['tecnico'] ? $info['tecnico'] : 'Não informado',
            'id_tecnico' => $info ? $info['id_pessoa'] : null,
            'ultima_interacao' => $ultimaInteracao ? [
                'data' => $ultimaInteracao['data'],
                'hora' => $ultimaInteracao['hora']
            ] : null
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>