<?php
// ============================================
// chamados_interacoes.php - VERSÃO FUNCIONAL
// ============================================
// Página pública: erros vão só para o log, nunca para a tela.
ini_set('display_errors', '0');

// ============================================
// CAPTURA OS PARÂMETROS
// ============================================
$idChamado = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$seguranca = isset($_GET['seguranca']) ? trim($_GET['seguranca']) : '';

// ============================================
// DADOS VIA API DO ERP
// ============================================
require_once __DIR__ . '/../../models/erp_api.php';

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
        // Busca o chamado com status e tipo da base de dados
        $chamado = erp_api('chamado.obter_seguro', ['id' => $idChamado, 'seguranca' => $seguranca]);

        if ($chamado) {
            $viewData['idChamado'] = $chamado['id'];
            $viewData['seguranca'] = $chamado['seguranca'];
            $viewData['idTipoContrato'] = $chamado['id_tipo_contrato'] ?? 0;
            $viewData['tipoContrato'] = $chamado['tipo_contrato_nome'] ?? 'Chamado';
            $viewData['idSituacao'] = (int)($chamado['id_situacao'] ?? 0);
            $viewData['situacao'] = $chamado['situacao_nome'] ?? 'Não informado';
            $viewData['nome'] = $chamado['nome'] ?? '';
            $viewData['email'] = $chamado['email'] ?? '';
            $viewData['telefone'] = $chamado['telefone'] ?? '';
            $viewData['urgencia'] = $chamado['urgencia'] ?? '';
            
            // Formata data
            if (!empty($chamado['data_abertura'])) {
                $viewData['dataAbertura'] = date('d/m/Y H:i:s', strtotime($chamado['data_abertura']));
            }
            
            // Busca interações do chamado
            // A API devolve em ordem crescente; aqui a lista fica da mais recente para a mais antiga.
            $eventosChamado = erp_api('chamado.eventos_visitante', ['id' => $idChamado]) ?: [];
            $viewData['interacoes'] = array_reverse($eventosChamado);

        } else {
            $viewData['error'] = 'Chamado não encontrado.';
        }
    } catch (ErpApiErro $e) {
        $viewData['error'] = 'Não foi possível consultar o chamado agora. Tente novamente em instantes.';
    }
} else {
    $viewData['error'] = 'Parâmetros inválidos.';
}

if (isset($_GET['endpoint'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    if ($viewData['error']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => $viewData['error']]);
        exit;
    }

    $endpoint = $_GET['endpoint'];

    if ($endpoint === 'info') {
        $ultimaInteracao = $viewData['interacoes'][0] ?? null;
        // Último técnico e último equipamento saem da lista de eventos (mais recente primeiro).
        $tecnico = '';
        $idEquipamento = 0;
        foreach ($viewData['interacoes'] as $evento) {
            if ($tecnico === '' && (int)($evento['id_pessoa'] ?? 0) > 0 && !empty($evento['nome_tecnico'])) {
                $tecnico = $evento['nome_tecnico'];
            }
            if ($idEquipamento === 0 && (int)($evento['id_equipamento'] ?? 0) > 0) {
                $idEquipamento = (int)$evento['id_equipamento'];
            }
        }

        require_once __DIR__ . '/../../models/chamado.php';
        $equipamentoRotulo = 'Não informado';
        if (function_exists('obterRotuloEquipamentoChamado') && $idEquipamento > 0) {
            $rotulo = obterRotuloEquipamentoChamado(
                (int)($chamado['id_tipo_contrato'] ?? 0),
                (int)($chamado['id_contrato'] ?? 0),
                $idEquipamento
            );
            if (!empty($rotulo)) {
                $equipamentoRotulo = $rotulo;
            }
        }

        echo json_encode([
            'success' => true,
            'info' => [
                'id_chamado' => $idChamado,
                'id_situacao' => (int)($chamado['id_situacao'] ?? 0),
                'tipo_chamado' => $viewData['tipoContrato'] ?: 'Chamado',
                'tecnico_responsavel' => $tecnico ?: 'Não informado',
                'data_abertura' => $chamado['data_abertura'] ?? '',
                'equipamento' => $equipamentoRotulo,
                'situacao' => $viewData['situacao'] ?: 'Não informado',
                'ultima_interacao' => $ultimaInteracao ? [
                    'data' => $ultimaInteracao['data'] ?? '',
                    'hora' => $ultimaInteracao['hora'] ?? ''
                ] : null
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($endpoint === 'eventos') {
        echo json_encode(['success' => true, 'eventos' => $eventosChamado]);
        exit;
    }

    if ($endpoint === 'enviar') {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método inválido.']);
            exit;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $payload = is_array($payload) ? $payload : $_POST;
        $descricao = trim((string) ($payload['descricao'] ?? ''));
        $idPost = (int) ($payload['id_chamado'] ?? 0);

        if ($idPost !== $idChamado || $descricao === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Informe uma mensagem válida.']);
            exit;
        }

        if ((int) ($chamado['id_situacao'] ?? 0) === 7) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Este chamado está finalizado.']);
            exit;
        }

        try {
            $idEvento = erp_api('chamado.responder_visitante', [
                'id' => $idChamado,
                'seguranca' => $seguranca,
                'descricao' => $descricao,
            ]);
        } catch (ErpApiErro $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Não foi possível enviar a mensagem agora.']);
            exit;
        }

        echo json_encode(['success' => true, 'id_evento' => (int)$idEvento]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Operação não encontrada.']);
    exit;
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
// ============================================
// CARREGA A VIEW
// ============================================
include __DIR__ . '/../../views/visitante/chamados_interacoes.php';
