<?php
// ============================================================
// CONFIGURAÇÃO DE ERROS (REMOVER EM PRODUÇÃO)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// CONEXÃO COM O BANCO DE DADOS
// ============================================================
try {
    $dsn  = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
    $user = 'bhcloud_admin';
    $pass = '$Qnv3hf@BeBL';
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

// ============================================================
// CAPTURA OS PARÂMETROS DA URL
// ============================================================
$idChamado = isset($_GET['id']) ? (int)$_GET['id'] : null;
$strSeguranca = isset($_GET['seguranca']) ? $_GET['seguranca'] : null;

// Fallback: tenta capturar de outras formas
if (!$idChamado || !$strSeguranca) {
    $urlAtual = $_SERVER['REQUEST_URI'];
    parse_str(parse_url($urlAtual, PHP_URL_QUERY), $params);
    $idChamado = $idChamado ?? (isset($params['id']) ? (int)$params['id'] : null);
    $strSeguranca = $strSeguranca ?? ($params['seguranca'] ?? null);
}

// ============================================================
// VALIDA E BUSCA OS DADOS
// ============================================================
$erro = null;
$dadosChamado = null;
$eventosChamado = [];

if ($idChamado && $strSeguranca) {
    try {
        // ============================================================
        // 1. BUSCA DADOS DO CHAMADO
        // ============================================================
        $stmt = $pdo->prepare("
            SELECT 
                id,
                id_contrato,
                id_tipo_contrato,
                resp_abertura,
                resp_fechamento,
                tipo_abertura,
                tipo_solicitacao,
                tipo_atendimento,
                urgencia,
                data_abertura,
                data_entrada,
                data_saida,
                seguranca,
                obs,
                id_causa,
                id_situacao,
                nome,
                telefone,
                email
            FROM contrato_chamado 
            WHERE id = :id 
            AND seguranca = :seguranca
        ");
        $stmt->execute([
            'id' => $idChamado,
            'seguranca' => $strSeguranca
        ]);
        $dadosChamado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dadosChamado) {
            $erro = "Chamado #{$idChamado} não encontrado ou token de segurança inválido.";
        } else {
            // ============================================================
            // 2. Mapeamento de status (mapeamento direto)
            // ============================================================
            $statusMap = [
                1 => 'Aberto',
                2 => 'Em Atendimento',
                3 => 'Atendido',
                4 => 'Finalizado',
                5 => 'Fechado',
                6 => 'Cancelado',
                7 => 'Pendente',
                8 => 'Aguardando',
                9 => 'Em Análise'
            ];
            $dadosChamado['situacao_texto'] = $statusMap[$dadosChamado['id_situacao']] ?? 'Desconhecido';

            // ============================================================
            // 3. Mapeamento de urgência (mapeamento direto)
            // ============================================================
            $urgenciaMap = [
                1 => 'Baixa',
                2 => 'Média',
                3 => 'Alta',
                4 => 'Urgente',
                5 => 'Crítica'
            ];
            $dadosChamado['urgencia_texto'] = $urgenciaMap[$dadosChamado['urgencia']] ?? 'Não definida';

            // ============================================================
            // 4. BUSCA NOME DO RESPONSÁVEL (se existir a tabela pessoas)
            // ============================================================
            if (!empty($dadosChamado['resp_abertura'])) {
                try {
                    $stmtPessoa = $pdo->prepare("
                        SELECT nome, email 
                        FROM pessoas 
                        WHERE id = :id
                    ");
                    $stmtPessoa->execute(['id' => $dadosChamado['resp_abertura']]);
                    $pessoa = $stmtPessoa->fetch(PDO::FETCH_ASSOC);
                    if ($pessoa) {
                        $dadosChamado['responsavel_nome'] = $pessoa['nome'];
                        $dadosChamado['responsavel_email'] = $pessoa['email'];
                    }
                } catch (PDOException $e) {
                    // Tabela pessoas pode não existir, ignora
                }
            }

            // ============================================================
            // 5. BUSCA EVENTOS DO CHAMADO
            // ============================================================
            try {
                $stmtEventos = $pdo->prepare("
                    SELECT 
                        id,
                        id_chamado,
                        descricao,
                        status,
                        data,
                        hora,
                        anexo,
                        id_pessoa,
                        id_tecnico
                    FROM contrato_chamado_eventos 
                    WHERE id_chamado = :id_chamado
                    ORDER BY data DESC, hora DESC
                    LIMIT 20
                ");
                $stmtEventos->execute(['id_chamado' => $idChamado]);
                $eventosChamado = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Tabela de eventos pode não existir, ignora
                $eventosChamado = [];
            }
        }
    } catch (PDOException $e) {
        $erro = "Erro ao buscar dados: " . $e->getMessage();
        error_log("Erro PDO: " . $e->getMessage());
    } catch (Exception $e) {
        $erro = "Erro inesperado: " . $e->getMessage();
        error_log("Erro geral: " . $e->getMessage());
    }
} else {
    $erro = "ID do chamado ou token de segurança não informado.";
}

// ============================================================
// MAPEAMENTO DE CORES PARA URGÊNCIA
// ============================================================
function getUrgenciaCor($texto)
{
    $texto = strtolower($texto);
    if (strpos($texto, 'crítica') !== false || strpos($texto, 'urgente') !== false) {
        return 'danger';
    } elseif (strpos($texto, 'alta') !== false) {
        return 'danger';
    } elseif (strpos($texto, 'média') !== false) {
        return 'warning';
    } elseif (strpos($texto, 'baixa') !== false) {
        return 'success';
    }
    return 'secondary';
}

function getSituacaoCor($id)
{
    $cores = [
        1 => 'primary',   // Aberto
        2 => 'warning',   // Em Atendimento
        3 => 'info',      // Atendido
        4 => 'success',   // Finalizado
        5 => 'secondary', // Fechado
        6 => 'danger',    // Cancelado
        7 => 'warning',   // Pendente
        8 => 'info',      // Aguardando
        9 => 'primary'    // Em Análise
    ];
    return $cores[$id] ?? 'secondary';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <title>Chamado #<?= htmlspecialchars($idChamado ?? '') ?> - BRInfor</title>

    <link href="/views/lib/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/views/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="/views/css/azia.css" rel="stylesheet">

    <style>
        body {
            background-color: #f0f2f5;
        }

        .az-content {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .az-content-body {
            flex: 1;
            padding: 30px;
        }

        .chamado-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #d15d04;;
        }

        .error-container {
            padding: 40px;
            text-align: center;
        }

        .btn-group-custom {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
            justify-content: center;
        }

        .btn-group-custom .btn {
            min-width: 200px;
        }

        .info-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .info-card .label {
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .info-card .value {
            color: #212529;
            font-size: 1.1rem;
        }

        .evento-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .evento-item:last-child {
            border-bottom: none;
        }

        .evento-item .evento-desc {
            color: #333;
        }

        .evento-item .evento-data {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .az-footer {
            background: #fff;
            padding: 15px 0;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        .badge-urgencia {
            font-size: 0.9rem;
            padding: 5px 12px;
        }

        .debug-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 15px auto;
            max-width: 500px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .chamado-number {
                font-size: 1.8rem;
            }

            .btn-group-custom .btn {
                min-width: 100%;
            }
        }
    </style>
</head>

<body class="az-body az-body-sidebar az-light">
    <div class="az-content az-content-dashboard-five">
        <div class="az-header">
            <div class="container-fluid">
                <a href="https://brinfor.com.br">
                    <img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px">
                </a>
                <div class="az-header-center"></div>
                <div class="az-header-right">
                    <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
                </div>
            </div>
        </div>

        <div class="az-content-body">
            <?php if ($idChamado && $strSeguranca && $dadosChamado && !$erro): ?>
                <!-- ===== SUCESSO ===== -->
                <div class="row">
                    <div class="col-md-12 text-center">
                        <div style="margin: 40px 0;">
                            <i class="fas fa-check-circle" style="font-size: 80px; color: #28a745;"></i>
                        </div>
                        <p style="font-size: 30px;">
                            Chamado <span class="chamado-number">#<?= htmlspecialchars($idChamado) ?></span>
                            aberto com sucesso! 🎉
                        </p>
                        <p style="font-size: 18px; color: #6c757d;">
                            Seu chamado foi registrado e aguarda atendimento.
                        </p>
                    </div>
                </div>

                <!-- ===== DETALHES DO CHAMADO ===== -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5><i class="fas fa-info-circle"></i> Detalhes do Chamado</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-3 col-sm-6">
                                        <div class="info-card">
                                            <div class="label">Número</div>
                                            <div class="value"><strong>#<?= htmlspecialchars($idChamado) ?></strong></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="info-card">
                                            <div class="label">Situação</div>
                                            <div class="value">
                                                <span class="badge badge-<?= getSituacaoCor($dadosChamado['id_situacao']) ?>">
                                                    <?= htmlspecialchars($dadosChamado['situacao_texto']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="info-card">
                                            <div class="label">Urgência</div>
                                            <div class="value">
                                                Padrão
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <div class="info-card">
                                            <div class="label">Data de Abertura</div>
                                            <div class="value">
                                                <?= date('d/m/Y H:i', strtotime($dadosChamado['data_abertura'] ?? date('Y-m-d H:i:s'))) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="label">Solicitante</div>
                                            <div class="value">
                                                <i class="fas fa-user"></i>
                                                <?= htmlspecialchars($dadosChamado['nome'] ?? $dadosChamado['responsavel_nome'] ?? 'Não informado') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-card">
                                            <div class="label">Contato</div>
                                            <div class="value">
                                                <?php if (!empty($dadosChamado['telefone'])): ?>
                                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($dadosChamado['telefone']) ?>
                                                <?php endif; ?>
                                                <?php if (!empty($dadosChamado['email'])): ?>
                                                    <br><i class="fas fa-envelope"></i> <?= htmlspecialchars($dadosChamado['email']) ?>
                                                <?php endif; ?>
                                                <?php if (empty($dadosChamado['telefone']) && empty($dadosChamado['email'])): ?>
                                                    Não informado
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($dadosChamado['obs'])): ?>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div class="info-card">
                                                <div class="label">Observações</div>
                                                <div class="value">
                                                    <?= nl2br(htmlspecialchars($dadosChamado['obs'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($eventosChamado)): ?>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <div class="info-card">
                                                <div class="label">Últimas Atualizações</div>
                                                <div class="value">
                                                    <?php foreach (array_slice($eventosChamado, 0, 10) as $evento): ?>
                                                        <div class="evento-item">
                                                            <span class="evento-data">
                                                                <i class="fas fa-clock text-muted"></i>
                                                                <?= date('d/m/Y H:i', strtotime($evento['data'] . ' ' . ($evento['hora'] ?? '00:00:00'))) ?>
                                                            </span>
                                                            <span class="evento-desc">
                                                                <?= htmlspecialchars($evento['descricao'] ?? '') ?>
                                                            </span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== BOTÕES CORRIGIDOS ===== -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="btn-group-custom">
                            <!-- Botão: Interagir com Chamado (dinâmico) -->
                            <button style="background-color: #d15d04; border-color: #d15d04;" class="btn btn-az-primary pd-x-20" 
                                    onclick="window.location.href='https://novacentral.brinfor.com.br/index.php?page=visitante-interagir-chamado&id=<?= urlencode($idChamado) ?>&seguranca=<?= urlencode($strSeguranca) ?>'">
                                <i class="fas fa-comment-dots"></i> Interagir com Chamado
                            </button>
                            
                            <!-- Botão: Novo Chamado (fixo) -->
                            <button class="btn btn-outline-primary pd-x-20" 
                                    onclick="window.location.href='https://novacentral.brinfor.com.br/visitante-abrir-chamado?tipo_contrato=1&etiqueta=&equipamento=0&preencher'">
                                <i class="fas fa-plus"></i> Novo Chamado
                            </button>
                            
                            <!-- Botão: Página Inicial (fixo) -->
                            <button class="btn btn-outline-secondary pd-x-20" 
                                    onclick="window.location.href='https://brinfor.com.br/'">
                                <i class="fas fa-home"></i> Página Inicial
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DICA -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb"></i> 
                            <strong>Dica:</strong> Clique em "Interagir com Chamado" para 
                            conversar com o técnico e acompanhar o andamento.
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- ===== ERRO ===== -->
                <div class="error-container">
                    <div style="margin: 40px 0;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 80px; color: #e74c3c;"></i>
                    </div>
                    <h2 style="color: #e74c3c;">Ops! Algo deu errado</h2>
                    <p style="font-size: 18px; color: #6c757d;">
                        <?= htmlspecialchars($erro ?? 'Não foi possível carregar as informações do chamado.') ?>
                    </p>
                    
                    <!-- ===== DEBUG ===== -->
                    <div class="debug-box">
                        <p><strong>🔍 Debug:</strong></p>
                        <ul class="list-unstyled" style="font-size: 14px;">
                            <li><strong>ID recebido:</strong> <code><?= htmlspecialchars(var_export($_GET['id'] ?? 'não definido', true)) ?></code></li>
                            <li><strong>Segurança recebida:</strong> <code><?= htmlspecialchars(var_export($_GET['seguranca'] ?? 'não definido', true)) ?></code></li>
                            <li><strong>URL atual:</strong> <code><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?></code></li>
                            <li><strong>Método:</strong> <code><?= $_SERVER['REQUEST_METHOD'] ?? 'GET' ?></code></li>
                            <li><strong>Query String:</strong> <code><?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?></code></li>
                        </ul>
                    </div>
                    
                    <div class="btn-group-custom">
                        <button class="btn btn-outline-primary pd-x-20" 
                                onclick="window.location.href='https://novacentral.brinfor.com.br/visitante-abrir-chamado?tipo_contrato=1&etiqueta=&equipamento=0&preencher'">
                            <i class="fas fa-plus"></i> Novo Chamado
                        </button>
                        <button class="btn btn-outline-secondary pd-x-20" 
                                onclick="window.location.href='https://brinfor.com.br/'">
                            <i class="fas fa-home"></i> Página Inicial
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="az-footer">
            <div class="container-fluid">
                <span>&copy; 2002 - <?= date('Y') ?>
                    <a href="https://genialsistemas.com.br/" target="_blank">Genial Sistemas</a>
                    Ltda. Todos os direitos reservados.
                </span>
            </div>
        </div>
    </div>

    <script src="/views/lib/jquery/jquery.min.js"></script>
    <script src="/views/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/views/js/azia.js"></script>

    <script>
        $(document).ready(function() {
            console.log('🔍 Parâmetros da URL:');
            console.log('ID:', '<?= addslashes($_GET['id'] ?? 'não definido') ?>');
            console.log('Segurança:', '<?= addslashes($_GET['seguranca'] ?? 'não definido') ?>');
            console.log('URL completa:', '<?= addslashes($_SERVER['REQUEST_URI'] ?? '') ?>');
            console.log('Query String:', '<?= addslashes($_SERVER['QUERY_STRING'] ?? '') ?>');

            <?php if ($erro): ?>
                console.error('❌ Erro: <?= addslashes($erro) ?>');
            <?php endif; ?>

            <?php if ($dadosChamado): ?>
                console.log('✅ Dados do chamado carregados:', <?= json_encode($dadosChamado) ?>);
            <?php endif; ?>
        });
    </script>
</body>

</html>