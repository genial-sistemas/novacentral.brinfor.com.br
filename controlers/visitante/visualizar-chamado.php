<?php
// visualizar-chamado.php
// Este arquivo deve ser salvo na mesma pasta do formulário

// ============================================
// INÍCIO - TRATAMENTO DE ERROS E CONEXÃO
// ============================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

$dsn  = 'mysql:host=bhcloud.com.br;dbname=bhcloud_bhinfor;charset=utf8';
$user = 'bhcloud_admin';
$pass = '$Qnv3hf@BeBL';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Exibe erro de forma amigável
?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Erro - BRInfor</title>
        <link rel="stylesheet" href="/views/css/azia.css">
        <style>
            body {
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }

            .error-box {
                max-width: 600px;
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border-left: 5px solid #dc3545;
            }

            .error-box h1 {
                color: #dc3545;
                margin-top: 0;
            }

            .error-box .detail {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                font-family: monospace;
                font-size: 13px;
                margin: 15px 0;
                border: 1px solid #dee2e6;
                word-break: break-all;
            }

            .btn {
                display: inline-block;
                padding: 10px 25px;
                background: #1a2a4a;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }

            .btn:hover {
                background: #0d1a30;
            }
        </style>
    </head>

    <body>
        <div class="error-box">
            <h1><i class="fas fa-exclamation-triangle"></i> Erro de Conexão</h1>
            <p>Não foi possível conectar ao banco de dados.</p>
            <div class="detail"><strong>Mensagem:</strong> <?= htmlspecialchars($e->getMessage()) ?></div>
            <a href="chamados_abrir" class="btn">Voltar ao Formulário</a>
        </div>
        <script src="/views/lib/fontawesome-free/js/all.min.js"></script>
    </body>

    </html>
<?php
    exit;
}

function obterPastaBaseUploadsChamados()
{
    $raiz_publica = trim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($raiz_publica === '') {
        $raiz_publica = dirname(__DIR__, 2);
    }

    return rtrim($raiz_publica, '/\\') . DIRECTORY_SEPARATOR . 'chamados' . DIRECTORY_SEPARATOR . 'eventos';
}

function tamanhoIniParaBytes($valor)
{
    $valor = trim((string)$valor);
    $unidade = strtolower(substr($valor, -1));
    $tamanho = (float)$valor;

    if ($unidade === 'g') {
        $tamanho *= 1024 * 1024 * 1024;
    } elseif ($unidade === 'm') {
        $tamanho *= 1024 * 1024;
    } elseif ($unidade === 'k') {
        $tamanho *= 1024;
    }

    return (int)$tamanho;
}

// Eu salvo os anexos dentro da raiz pública desta instalação, local ou oficial.
$pasta_upload = obterPastaBaseUploadsChamados() . DIRECTORY_SEPARATOR;
if (!is_dir($pasta_upload) && !mkdir($pasta_upload, 0775, true) && !is_dir($pasta_upload)) {
    error_log('Não foi possível criar a pasta base de uploads: ' . $pasta_upload);
}

// ============================================
// BUSCAR O PRÓXIMO ID DO CHAMADO
// ============================================
try {
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM bhcloud_bhinfor.contrato_chamado");
    $result = $stmt->fetch();
    if ($result && $result['max_id'] > 0) {
        $proximo_id_chamado = $result['max_id'] + 1;
    } else {
        $proximo_id_chamado = 1;
    }
} catch (PDOException $e) {
?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Erro - BRInfor</title>
        <link rel="stylesheet" href="/views/css/azia.css">
        <style>
            body {
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
            }

            .error-box {
                max-width: 600px;
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border-left: 5px solid #dc3545;
            }

            .error-box h1 {
                color: #dc3545;
                margin-top: 0;
            }

            .error-box .detail {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 5px;
                font-family: monospace;
                font-size: 13px;
                margin: 15px 0;
                border: 1px solid #dee2e6;
                word-break: break-all;
            }

            .btn {
                display: inline-block;
                padding: 10px 25px;
                background: #1a2a4a;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }

            .btn:hover {
                background: #0d1a30;
            }
        </style>
    </head>

    <body>
        <div class="error-box">
            <h1><i class="fas fa-exclamation-triangle"></i> Erro ao Buscar ID</h1>
            <p>Não foi possível obter o próximo ID do chamado.</p>
            <div class="detail"><strong>Erro:</strong> <?= htmlspecialchars($e->getMessage()) ?></div>
            <a href="chamados_abrir" class="btn">Voltar ao Formulário</a>
        </div>
        <script src="/views/lib/fontawesome-free/js/all.min.js"></script>
    </body>

    </html>
<?php
    exit;
}

// ============================================
// CONSTANTES DE UPLOAD
// ============================================
define('UPLOAD_FILE_RELATIVE_BASE_PATH', 'chamados/eventos');

// Verifica se o formulário foi enviado
$dados_submetidos = isset($_POST['acao']) && $_POST['acao'] === 'abrir_chamado';

$tamanho_requisicao = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
$limite_post = tamanhoIniParaBytes(ini_get('post_max_size'));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tamanho_requisicao > $limite_post && empty($_POST) && empty($_FILES)) {
    $erro = 'O envio ultrapassou o limite total do servidor (' . ini_get('post_max_size') . '). Reduza o tamanho do arquivo e tente novamente.';
    // Eu devolvo ao formulário quando o PHP descarta um POST acima do limite configurado.
    include __DIR__ . '/../../views/visitante/chamados_abrir.php';
    exit;
}

// Se não houver dados submetidos, redireciona para o formulário
if (!$dados_submetidos && empty($_FILES['arquivo'])) {
    header('Location: index.php?page=visitante-abrir-chamado');
    exit;
}

// ============================================
// GERAR CÓDIGO DE SEGURANÇA
// ============================================
function gerarCodigoSeguranca()
{
    $seguranca = rand(123456789, 987654321);
    $datacod = date('hYsHdim');
    $codigo = $seguranca . $datacod;
    return sha1($codigo);
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
// FUNÇÃO PARA GERAR CAMINHO COMPLETO DO ARQUIVO
// ============================================
function gerarCaminhoArquivo($idChamado, $nomeRenomeado)
{
    return UPLOAD_FILE_RELATIVE_BASE_PATH . '/' . $idChamado . '/' . $nomeRenomeado;
}

// ============================================
// FUNÇÃO PARA FORMATAR TAMANHO
// ============================================
function formatarTamanho($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

// ============================================
// FUNÇÃO PARA VERIFICAR SE UM CAMPO TEM VALOR
// ============================================
function temValor($valor)
{
    if ($valor === null || $valor === '') return false;
    if (is_string($valor) && trim($valor) === '') return false;
    if (is_numeric($valor) && $valor == 0) return false;
    if (is_array($valor) && empty($valor)) return false;
    return true;
}

// ============================================
// FUNÇÃO PARA RENDERIZAR APENAS CAMPOS PREENCHIDOS
// ============================================
function renderizarCampo($label, $valor, $classe_extra = '')
{
    if (!temValor($valor)) return '';
    $classe = $classe_extra ? 'value ' . $classe_extra : 'value';
    return '<div class="dados-item">
                <span class="label">' . htmlspecialchars($label) . '</span>
                <span class="' . $classe . '">' . nl2br(htmlspecialchars($valor)) . '</span>
            </div>';
}

// ============================================
// FUNÇÃO PARA RENDERIZAR SEÇÃO APENAS SE TIVER CAMPOS
// ============================================
function renderizarSecao($titulo, $icone, $campos, $badge = '')
{
    $html = '';
    foreach ($campos as $campo) {
        if (isset($campo['valor']) && temValor($campo['valor'])) {
            $html .= renderizarCampo($campo['label'], $campo['valor'], $campo['classe'] ?? '');
        }
    }
    if (empty($html)) return '';
    return '<div class="dados-section">
                <h3><i class="fas ' . $icone . '"></i> ' . htmlspecialchars($titulo) . $badge . '</h3>
                <div class="dados-grid">' . $html . '</div>
            </div>';
}

// ============================================
// MAPEAMENTOS
// ============================================
$tipos_contrato = [
    '1' => 'Helpdesk (Outsourcing)',
    '2' => 'Hospedagem',
    '4' => 'Domínio',
    '5' => 'Backup',
    '6' => 'Suporte Produtos',
    '7' => 'Locação'
];

$tipos_solicitacao = [
    '1' => 'Incidente Técnico',
    '2' => 'Requisição Usuário',
    '3' => 'Incidente de Segurança'
];

$urgencias = [
    '1' => 'Muito Alta',
    '2' => 'Alta',
    '3' => 'Média',
    '4' => 'Padrão',
    '5' => 'Baixa'
];

$situacoes = [
    '1' => 'Aberto',
    '2' => 'Em Atendimento',
    '3' => 'Aguardando Cliente',
    '4' => 'Aguardando Terceiros',
    '5' => 'Resolvido',
    '6' => 'Fechado',
    '7' => 'Cancelado'
];

$tipos_abertura = [
    '1' => 'Usuário',
    '2' => 'Sistema',
    '3' => 'Atendente'
];

$tipos_atendimento = [
    '1' => 'Presencial',
    '2' => 'Remoto',
    '3' => 'Telefone',
    '4' => 'E-mail',
    '5' => 'Chat'
];

// ============================================
// COLETA DOS DADOS DO POST
// ============================================
$dados = [
    'acao' => $_POST['acao'] ?? '',
    'id_contrato' => $_POST['id_contrato'] ?? '0',
    'id_contato' => $_POST['id_contato'] ?? '0',
    'id_equipamento' => $_POST['id_equipamento'] ?? '0',
    'id_dominio' => $_POST['id_dominio'] ?? '0',
    'id_backup' => $_POST['id_backup'] ?? '0',
    'id_suporte' => $_POST['id_suporte'] ?? '0',
    'id_pessoa' => $_POST['id_pessoa'] ?? '0',
    'id_situacao' => $_POST['id_situacao'] ?? '1',
    'id_tipo_abertura' => $_POST['id_tipo_abertura'] ?? '1',
    'id_tipo_atendimento' => $_POST['id_tipo_atendimento'] ?? '1',
    'id_resp_abertura' => $_POST['id_resp_abertura'] ?? '0',
    'codigoSeguranca' => $_POST['codigoSeguranca'] ?? '',
    'id_tipo_contrato' => $_POST['id_tipo_contrato'] ?? '',
    'etiqueta_codigo_contrato' => $_POST['etiqueta_codigo_contrato'] ?? '',
    'etiqueta_codigo_equipamento' => $_POST['etiqueta_codigo_equipamento'] ?? '',
    'search_type' => $_POST['search_type'] ?? '',
    'search_context' => $_POST['search_context'] ?? '',
    'nome' => $_POST['nome'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telefone' => $_POST['telefone'] ?? '',
    'urgencia' => $_POST['urgencia'] ?? '4',
    'id_tipo_solicitacao' => $_POST['id_tipo_solicitacao'] ?? '',
    'solicitacao' => $_POST['solicitacao'] ?? '',
    'obs' => $_POST['obs'] ?? '',
];

// Se não veio código de segurança do POST, gera um novo
if (empty($dados['codigoSeguranca'])) {
    $dados['codigoSeguranca'] = gerarCodigoSeguranca();
}

// Se o id_contrato veio como 0 e search_type é ID, tenta usar o search_context
if ($dados['id_contrato'] == 0 && $dados['search_type'] == 'ID') {
    $idDigitado = preg_replace('/[^0-9]/', '', $dados['search_context']);
    if (!empty($idDigitado) && is_numeric($idDigitado)) {
        $dados['id_contrato'] = intval($idDigitado);
    }
}

// ============================================
// PROCESSAMENTO DO ARQUIVO ENVIADO
// ============================================
$arquivo_info = null;
$arquivo_base64 = null;
$arquivo_erro = null;
$arquivo_nome_renomeado = null;
$arquivo_caminho_completo = null;

if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $arquivo = $_FILES['arquivo'];

    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $tipos_permitidos = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed',
            'application/json',
            'application/xml'
        ];

        $extensoes_permitidas = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'svg',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'txt',
            'csv',
            'zip',
            'rar',
            '7z',
            'json',
            'xml'
        ];

        $nome_original = $arquivo['name'];
        $tipo_mime = $arquivo['type'];
        $tamanho = $arquivo['size'];
        $nome_temp = $arquivo['tmp_name'];
        $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

        $tipo_permitido = in_array($tipo_mime, $tipos_permitidos) || in_array($extensao, $extensoes_permitidas);
        $limite_php_upload = tamanhoIniParaBytes(ini_get('upload_max_filesize'));
        $tamanho_maximo = min(10 * 1024 * 1024, $limite_php_upload);

        if ($tamanho > $tamanho_maximo) {
            $arquivo_erro = 'Arquivo muito grande. Limite atual por arquivo: ' . formatarTamanho($tamanho_maximo) . '.';
        } elseif (!$tipo_permitido) {
            $arquivo_erro = 'Tipo de arquivo não permitido.';
        } else {
            $idBase = $proximo_id_chamado;
            $arquivo_nome_renomeado = renomearArquivo($idBase, $extensao);
            $arquivo_caminho_completo = gerarCaminhoArquivo($idBase, $arquivo_nome_renomeado);

            $conteudo = file_get_contents($nome_temp);
            $arquivo_base64 = base64_encode($conteudo);

            $tipo_arquivo = 'outro';
            $icone_arquivo = 'fa-file';
            $cor_icone = '#6c757d';

            if (strpos($tipo_mime, 'image/') === 0) {
                $tipo_arquivo = 'imagem';
                $icone_arquivo = 'fa-file-image';
                $cor_icone = '#28a745';
            } elseif ($tipo_mime === 'application/pdf') {
                $tipo_arquivo = 'pdf';
                $icone_arquivo = 'fa-file-pdf';
                $cor_icone = '#dc3545';
            } elseif (strpos($tipo_mime, 'word') !== false || strpos($tipo_mime, 'document') !== false) {
                $tipo_arquivo = 'documento';
                $icone_arquivo = 'fa-file-word';
                $cor_icone = '#007bff';
            } elseif (strpos($tipo_mime, 'excel') !== false || strpos($tipo_mime, 'spreadsheet') !== false) {
                $tipo_arquivo = 'planilha';
                $icone_arquivo = 'fa-file-excel';
                $cor_icone = '#28a745';
            } elseif (strpos($tipo_mime, 'text/') === 0) {
                $tipo_arquivo = 'texto';
                $icone_arquivo = 'fa-file-alt';
                $cor_icone = '#17a2b8';
            } elseif (strpos($tipo_mime, 'zip') !== false || strpos($tipo_mime, 'rar') !== false || strpos($tipo_mime, '7z') !== false) {
                $tipo_arquivo = 'compactado';
                $icone_arquivo = 'fa-file-archive';
                $cor_icone = '#fd7e14';
            }

            $arquivo_info = [
                'nome' => $nome_original,
                'nome_renomeado' => $arquivo_nome_renomeado,
                'caminho_completo' => $arquivo_caminho_completo,
                'tipo_mime' => $tipo_mime,
                'tamanho' => $tamanho,
                'tamanho_formatado' => formatarTamanho($tamanho),
                'extensao' => $extensao,
                'tipo' => $tipo_arquivo,
                'icone' => $icone_arquivo,
                'cor_icone' => $cor_icone,
                'base64' => $arquivo_base64,
                'data_uri' => 'data:' . $tipo_mime . ';base64,' . $arquivo_base64
            ];
        }
    } else {
        $erros_upload = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido.',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'O arquivo foi apenas parcialmente enviado.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão do PHP interrompeu o upload.'
        ];
        $arquivo_erro = $erros_upload[$arquivo['error']] ?? 'Erro desconhecido no upload.';
    }
}

if ($arquivo_erro !== null) {
    $erro = $arquivo_erro;
    // Eu interrompo a abertura para não criar chamado sem o anexo selecionado.
    include __DIR__ . '/../../views/visitante/chamados_abrir.php';
    exit;
}

// ============================================
// FUNÇÃO PARA SALVAR ARQUIVO ANEXADO
// ============================================
function salvarArquivoChamado($arquivo_temp, $id_chamado, $nome_original)
{
    global $pasta_upload;
    $pasta_base = rtrim($pasta_upload, '/\\') . DIRECTORY_SEPARATOR;
    $pasta_chamado = $pasta_base . $id_chamado . '/';

    if (!is_dir($pasta_chamado)) {
        if (!mkdir($pasta_chamado, 0775, true) && !is_dir($pasta_chamado)) {
            error_log("❌ Erro ao criar pasta: " . $pasta_chamado);
            return ['status' => 'erro', 'mensagem' => 'Não foi possível criar a pasta do chamado: ' . $pasta_chamado];
        }
        chmod($pasta_chamado, 0777);
        error_log("📁 Pasta criada: " . $pasta_chamado);
    }

    $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
    $timestamp = date('Ymd_His');
    $random = substr(md5(uniqid()), 0, 6);
    $nome_arquivo = "chamado_{$id_chamado}_{$timestamp}_{$random}." . $extensao;
    $caminho_completo = $pasta_chamado . $nome_arquivo;

    if (move_uploaded_file($arquivo_temp, $caminho_completo)) {
        chmod($caminho_completo, 0644);
        error_log("✅ Arquivo salvo: " . $caminho_completo);
        return [
            'status' => 'sucesso',
            'nome_renomeado' => $nome_arquivo,
            'caminho_completo' => 'chamados/eventos/' . $id_chamado . '/' . $nome_arquivo,
            'caminho_absoluto' => $caminho_completo,
            'pasta' => $pasta_chamado,
            'tamanho' => filesize($caminho_completo)
        ];
    } else {
        error_log("❌ Erro ao mover arquivo: " . $arquivo_temp . " para " . $caminho_completo);
        return ['status' => 'erro', 'mensagem' => 'Não foi possível salvar o arquivo no servidor'];
    }
}

// ============================================
// FUNÇÃO PARA ENVIAR E-MAIL COM PHPMailer
// ============================================
function enviarEmailChamado($html_email, $dados, $proximo_id_chamado)
{
    global $pdo;

    require_once __DIR__ . '/../../models/email.php';

    $destinatarios = [];
    $logs_busca = [];

    $email_solicitante = trim((string)($dados['email'] ?? ''));
    if (filter_var($email_solicitante, FILTER_VALIDATE_EMAIL)) {
        $destinatarios[] = $email_solicitante;
        $logs_busca[] = "📧 E-mail do formulário: " . $email_solicitante;
    } else {
        $logs_busca[] = "❌ E-mail do formulário ausente ou inválido";
    }

    $tipo_contrato = $dados['id_tipo_contrato'] ?? '0';
    $logs_busca[] = "📋 Tipo de Contrato: " . $tipo_contrato;
    $logs_busca[] = "📋 ID do Contrato: " . ($dados['id_contrato'] ?? 'NULL');

    try {
        if (!empty($dados['id_contrato']) && $dados['id_contrato'] > 0) {
            if ($tipo_contrato == '1') {
                $sql_contrato = "SELECT id_pessoa FROM bhcloud_bhinfor.contrato WHERE id = ?";
                $stmt = $pdo->prepare($sql_contrato);
                $stmt->execute([$dados['id_contrato']]);
                $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($contrato && !empty($contrato['id_pessoa']) && $contrato['id_pessoa'] > 0) {
                    $id_pessoa = $contrato['id_pessoa'];
                    $logs_busca[] = "✅ id_pessoa encontrado: " . $id_pessoa;

                    $sql_pessoa = "SELECT email FROM bhcloud_bhinfor.pessoa_juridica WHERE id_pessoa = ?";
                    $stmt = $pdo->prepare($sql_pessoa);
                    $stmt->execute([$id_pessoa]);
                    $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($pessoa && !empty($pessoa['email'])) {
                        $destinatarios[] = $pessoa['email'];
                        $logs_busca[] = "✅ E-mail da pessoa_juridica encontrado: " . $pessoa['email'];
                    }
                }
            } else {
                $sql = "SELECT email_gerente FROM bhcloud_bhinfor.contrato WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$dados['id_contrato']]);
                $contrato = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($contrato && !empty($contrato['email_gerente'])) {
                    $destinatarios[] = $contrato['email_gerente'];
                    $logs_busca[] = "✅ E-mail do gerente encontrado: " . $contrato['email_gerente'];
                }
            }
        }
    } catch (PDOException $e) {
        $logs_busca[] = "❌ Erro ao buscar e-mail: " . $e->getMessage();
        error_log("Erro ao buscar e-mail adicional: " . $e->getMessage());
    }

    $destinatarios = array_unique($destinatarios);
    $destinatarios = array_filter($destinatarios, function ($email) {
        return is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    });

    if (empty($destinatarios)) {
        $erro_destinatario = 'Nenhum destinatário válido para o e-mail de abertura do chamado.';
        error_log($erro_destinatario);
        return [
            'resultados' => [['destinatario' => $email_solicitante, 'enviado' => false, 'erro' => $erro_destinatario]],
            'logs' => array_merge($logs_busca, [$erro_destinatario]),
            'total_destinatarios' => 0,
            'destinatarios_lista' => []
        ];
    }

    $logs_busca[] = "📨 DESTINATÁRIOS FINAIS (" . count($destinatarios) . "): " . implode(", ", $destinatarios);

    $assunto = '🔒 Chamado #' . $proximo_id_chamado . ' - ' . $dados['nome'] . ' (Seg: ' . $dados['codigoSeguranca'] . ')';
    $url_interacao = 'https://central.brinfor.com.br/interagir-chamado?id=' . (int)$proximo_id_chamado
        . '&seguranca=' . rawurlencode((string)$dados['codigoSeguranca']);

    $resultados = [];

    foreach ($destinatarios as $destinatario) {
        $alt_body = "Chamado #" . $proximo_id_chamado . "\n" .
                "🔑 Código de Segurança: " . $dados['codigoSeguranca'] . "\n" .
                "Data: " . date('d/m/Y H:i:s') . "\n" .
                "Acesse: " . $url_interacao;
        $resultado = enviarEmailHtml($destinatario, $assunto, $html_email, $alt_body);
        $resultado['destinatario'] = $destinatario;
        $resultados[] = $resultado;
    }

    return [
        'resultados' => $resultados,
        'logs' => $logs_busca,
        'total_destinatarios' => count($destinatarios),
        'destinatarios_lista' => $destinatarios
    ];
}

// ============================================
// FUNÇÕES PARA GERAR QUERIES
// ============================================

function gerarQueryChamado($dados, $proximo_id_chamado)
{
    $campos = [];
    $valores = [];

    $campos[] = 'id';
    $valores[] = intval($proximo_id_chamado);

    if (temValor($dados['id_contrato']) && $dados['id_contrato'] > 0) {
        $campos[] = 'id_contrato';
        $valores[] = intval($dados['id_contrato']);
    }

    if (temValor($dados['id_tipo_contrato'])) {
        $campos[] = 'id_tipo_contrato';
        $valores[] = intval($dados['id_tipo_contrato']);
    }

    if (temValor($dados['id_resp_abertura']) && $dados['id_resp_abertura'] > 0) {
        $campos[] = 'resp_abertura';
        $valores[] = intval($dados['id_resp_abertura']);
    }

    $campos[] = 'resp_fechamento';
    $valores[] = 'NULL';

    if (temValor($dados['id_tipo_abertura'])) {
        $campos[] = 'tipo_abertura';
        $valores[] = intval($dados['id_tipo_abertura']);
    }

    if (temValor($dados['id_tipo_solicitacao'])) {
        $campos[] = 'tipo_solicitacao';
        $valores[] = intval($dados['id_tipo_solicitacao']);
    }

    if (temValor($dados['id_tipo_atendimento'])) {
        $campos[] = 'tipo_atendimento';
        $valores[] = intval($dados['id_tipo_atendimento']);
    }

    if (temValor($dados['urgencia'])) {
        $campos[] = 'urgencia';
        $valores[] = intval($dados['urgencia']);
    }

    $campos[] = 'data_abertura';
    $valores[] = 'NOW()';

    $campos[] = 'data_entrada';
    $valores[] = 'NULL';

    $campos[] = 'data_saida';
    // Eu registro a data e hora da abertura em data_saida, não em data_entrada.
    $valores[] = 'NOW()';

    if (temValor($dados['codigoSeguranca'])) {
        $campos[] = 'seguranca';
        $valores[] = "'" . addslashes($dados['codigoSeguranca']) . "'";
    }

    if (temValor($dados['obs'])) {
        $campos[] = 'obs';
        $valores[] = "'" . addslashes($dados['obs']) . "'";
    }

    $campos[] = 'id_causa';
    $valores[] = 'NULL';

    if (temValor($dados['id_situacao'])) {
        $campos[] = 'id_situacao';
        $valores[] = intval($dados['id_situacao']);
    }

    if (temValor($dados['nome'])) {
        $campos[] = 'nome';
        $valores[] = "'" . addslashes($dados['nome']) . "'";
    }

    if (temValor($dados['telefone'])) {
        $campos[] = 'telefone';
        $valores[] = "'" . addslashes($dados['telefone']) . "'";
    }

    if (temValor($dados['email'])) {
        $campos[] = 'email';
        $valores[] = "'" . addslashes($dados['email']) . "'";
    }

    if (empty($campos)) {
        return '-- Nenhum dado para inserir na tabela contrato_chamado';
    }

    $sql = "INSERT INTO bhcloud_bhinfor.contrato_chamado (\n    " . implode(",\n    ", $campos) . "\n) VALUES (\n    " . implode(",\n    ", $valores) . "\n);";
    return $sql;
}

function gerarQueryEvento($dados, $tipo, $descricao, $proximo_id_chamado, $arquivo_info = null)
{
    $campos = [];
    $valores = [];
    $id_recurso = (int)($dados['id_equipamento'] ?? 0);
    if ($id_recurso <= 0) {
        switch ((int)($dados['id_tipo_contrato'] ?? 0)) {
            case 2:
                $id_recurso = (int)($dados['id_dominio'] ?? 0);
                break;
            case 4:
                $id_recurso = (int)($dados['id_dominio'] ?? 0);
                break;
            case 5:
                $id_recurso = (int)($dados['id_backup'] ?? 0);
                break;
            case 6:
                $id_recurso = (int)($dados['id_suporte'] ?? 0);
                break;
        }
    }

    $campos[] = 'id_chamado';
    $valores[] = intval($proximo_id_chamado);

    if (temValor($dados['id_pessoa']) && $dados['id_pessoa'] > 0) {
        $campos[] = 'id_pessoa';
        $valores[] = intval($dados['id_pessoa']);
    }

    if (temValor($dados['id_contato']) && $dados['id_contato'] > 0) {
        $campos[] = 'id_contato';
        $valores[] = intval($dados['id_contato']);
    }

    if ($id_recurso > 0) {
        $campos[] = 'id_equipamento';
        $valores[] = $id_recurso;
    }

    $campos[] = 'id_evento';
    $valores[] = 'NULL';

    $campos[] = 'data';
    $valores[] = 'CURDATE()';

    $campos[] = 'hora';
    $valores[] = 'CURTIME()';

    $campos[] = 'horas_total';
    $valores[] = 'NULL';

    $campos[] = 'tipo';
    $valores[] = "'" . $tipo . "'";

    $desc = $descricao;
    if ($arquivo_info && $tipo === 'F') {
        $desc = $arquivo_info['caminho_completo'];
    }

    if (temValor($desc)) {
        $campos[] = 'descricao';
        $valores[] = "'" . addslashes($desc) . "'";
    }

    $campos[] = 'status';
    $valores[] = '1';

    $campos[] = 'tempo_atendimento';
    $valores[] = 'NULL';

    $campos[] = 'sla_fail';
    $valores[] = '0';

    $campos[] = 'link';
    $valores[] = '0';

    if (empty($campos)) {
        return '-- Nenhum dado para inserir na tabela contrato_chamado_eventos';
    }

    $sql = "INSERT INTO bhcloud_bhinfor.contrato_chamado_eventos (\n    " . implode(",\n    ", $campos) . "\n) VALUES (\n    " . implode(",\n    ", $valores) . "\n);";
    return $sql;
}

// ============================================
// GERAR TODAS AS QUERIES
// ============================================
$sql_chamado = gerarQueryChamado($dados, $proximo_id_chamado);

$queries_evento = [];
$eventos_para_visualizar = [];

// Evento de Abertura
$sql_evento_abertura = gerarQueryEvento($dados, 'A', $dados['solicitacao'] ?? 'Abertura do chamado', $proximo_id_chamado);
$queries_evento[] = $sql_evento_abertura;
$eventos_para_visualizar[] = [
    'tipo' => 'A',
    'label' => 'Abertura',
    'descricao' => $dados['solicitacao'] ?? 'Abertura do chamado',
    'cor' => '#28a745'
];

// Evento de Observação
if (temValor($dados['obs'])) {
    $sql_evento_obs = gerarQueryEvento($dados, 'O', $dados['obs'], $proximo_id_chamado);
    $queries_evento[] = $sql_evento_obs;
    $eventos_para_visualizar[] = [
        'tipo' => 'O',
        'label' => 'Observação',
        'descricao' => $dados['obs'],
        'cor' => '#ffc107'
    ];
}

// Evento de Arquivo
if ($arquivo_info) {
    $sql_evento_arquivo = gerarQueryEvento($dados, 'F', '', $proximo_id_chamado, $arquivo_info);
    $queries_evento[] = $sql_evento_arquivo;
    $eventos_para_visualizar[] = [
        'tipo' => 'F',
        'label' => 'Arquivo',
        'descricao' => $arquivo_info['caminho_completo'],
        'cor' => '#17a2b8',
        'arquivo' => $arquivo_info
    ];
}

// ============================================
// FUNÇÃO PARA GERAR HTML DO E-MAIL
// ============================================
function renderHtmlEmailChamadoVisualizacao($dados, $proximo_id_chamado, $arquivo_info, $eventos_para_visualizar)
{
    $tipo_contrato = $GLOBALS['tipos_contrato'][$dados['id_tipo_contrato']] ?? 'Não definido';
    $tipo_abertura = $GLOBALS['tipos_abertura'][$dados['id_tipo_abertura']] ?? 'Não definido';
    $tipo_atendimento = $GLOBALS['tipos_atendimento'][$dados['id_tipo_atendimento']] ?? 'Não definido';
    $urgencia = $GLOBALS['urgencias'][$dados['urgencia']] ?? $dados['urgencia'];
    $situacao = $GLOBALS['situacoes'][$dados['id_situacao']] ?? $dados['id_situacao'];
    $tipo_solicitacao = $GLOBALS['tipos_solicitacao'][$dados['id_tipo_solicitacao']] ?? 'Não definido';
    $url_interacao = htmlspecialchars(
        'https://central.brinfor.com.br/interagir-chamado?id=' . (int)$proximo_id_chamado
            . '&seguranca=' . rawurlencode((string)$dados['codigoSeguranca']),
        ENT_QUOTES,
        'UTF-8'
    );

    $html = '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Chamado BRInfor</title>
    <style>
        table, td, div, h1, p {font-family: Arial, sans-serif;}
        .email-container { max-width: 800px; margin: 0 auto; background: #ffffff; border: 1px solid #fa9758; border-radius: 8px; overflow: hidden; }
        .email-header { background: #fa9758; padding: 20px; text-align: center; }
        .email-body { padding: 30px; }
        .email-footer { background: #fa9758; padding: 20px; text-align: center; color: #ffffff; font-size: 12px; }
        .info-row { margin: 8px 0; }
        .info-label { font-weight: bold; color: #153643; }
        .info-value { color: #333; }
        .event-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 15px; }
        .event-table th { background: #f8f9fa; padding: 10px; text-align: left; border-bottom: 2px solid #dee2e6; font-size: 11px; text-transform: uppercase; color: #70737c; }
        .event-table td { padding: 10px; border-bottom: 1px solid #dee2e6; }
        .btn-interagir { display: inline-block; padding: 10px 25px; background: #dd9933; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-container { text-align: right; margin: 20px 0; }
        .badge-status { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-status.aberto { background: #dc3545; color: white; }
        .badge-status.fechado { background: #28a745; color: white; }
        .badge-status.em-atendimento { background: #ffc107; color: #212529; }
        .badge-status.aguardando { background: #fd7e14; color: white; }
        .badge-status.resolvido { background: #17a2b8; color: white; }
        .badge-status.cancelado { background: #6c757d; color: white; }
        .arquivo-info { background: #f8f9fa; padding: 10px 15px; border-radius: 5px; margin: 10px 0; border-left: 3px solid #17a2b8; }
        @media (max-width: 600px) {
            .email-container { width: 100%; }
            .event-table { font-size: 12px; }
            .event-table th, .event-table td { padding: 6px; }
        }
    </style>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;">
    <div class="email-container">
        <div class="email-header">
            <img src="https://central.brinfor.com.br/views/img/brInfor_logo_mini.png" alt="BRInfor" style="height:auto;max-width:200px;" />
        </div>
        <div class="email-body">
            <h1 style="font-size:24px;margin:0 0 20px 0;text-align:center;">Chamado #' . $proximo_id_chamado . '</h1>
            
            <div class="info-row">
                <span class="info-label">Contrato:</span>
                <span class="info-value">' . $tipo_contrato . ' (ID: ' . ($dados['id_contrato'] > 0 ? $dados['id_contrato'] : 'N/A') . ')</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Tipo Solicitação:</span>
                <span class="info-value">' . $tipo_solicitacao . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Responsável Abertura:</span>
                <span class="info-value">' . ($dados['nome'] ?: 'Usuário') . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">E-mail Abertura:</span>
                <span class="info-value">' . ($dados['email'] ?: 'Não informado') . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Telefone Abertura:</span>
                <span class="info-value">' . ($dados['telefone'] ?: 'Não informado') . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Tipo Abertura:</span>
                <span class="info-value">' . $tipo_abertura . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Tipo Atendimento:</span>
                <span class="info-value">' . $tipo_atendimento . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Urgência:</span>
                <span class="info-value">' . $urgencia . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Situação:</span>
                <span class="info-value"><span class="badge-status ' . (($dados['id_situacao'] == 1) ? 'aberto' : '') . '">' . $situacao . '</span></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Data/Hora Abertura:</span>
                <span class="info-value">' . date('d/m/Y H:i:s') . '</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">🔑 Código de Segurança:</span>
                <span class="info-value" style="font-weight:bold;color:#dc3545;font-size:16px;letter-spacing:2px;">' . $dados['codigoSeguranca'] . '</span>
            </div>
            
            ' . (temValor($dados['solicitacao']) ? '
            <div class="info-row" style="margin-top:15px;padding-top:15px;border-top:1px solid #dee2e6;">
                <span class="info-label">Descrição:</span>
                <div class="info-value" style="white-space:pre-wrap;margin-top:5px;background:#f8f9fa;padding:10px;border-radius:5px;">' . nl2br(htmlspecialchars($dados['solicitacao'])) . '</div>
            </div>' : '') . '
            
            ' . (temValor($dados['obs']) ? '
            <div class="info-row" style="margin-top:10px;">
                <span class="info-label">Observação:</span>
                <div class="info-value" style="white-space:pre-wrap;margin-top:5px;background:#f8f9fa;padding:10px;border-radius:5px;">' . nl2br(htmlspecialchars($dados['obs'])) . '</div>
            </div>' : '') . '
            
            ' . ($arquivo_info ? '
            <div class="arquivo-info">
                <strong>📎 Arquivo Anexado:</strong>
                <span>' . htmlspecialchars($arquivo_info['caminho_completo']) . '</span>
            </div>' : '') . '
            
            <div class="btn-container">
<a style="text-decoration:none;border-top:#dd9933 10px solid;border-right:#dd9933 20px solid;background:#dd9933;border-bottom:#dd9933 10px solid;font-weight:bold;color:white;border-left:#dd9933 20px solid;display:inline-block;" href="' . $url_interacao . '">Nova Interação</a>            </div>
            
            <h2 style="font-size:18px;margin:20px 0 15px 0;">Eventos</h2>
            <table class="event-table">
                <thead>
                    <tr>
                        <th>Data-Hora</th>
                        <th>Responsável</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($eventos_para_visualizar as $evento) {
        $html .= '
                    <tr>
                        <td>' . date('d/m/Y H:i:s') . '</td>
                        <td>' . ($dados['nome'] ?: 'Usuário') . '</td>
                        <td>' . htmlspecialchars($evento['descricao']) . '</td>
                        <td><span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:bold;background:' . $evento['cor'] . ';color:#fff;">' . $evento['label'] . '</span></td>
                    </tr>';
    }

    $html .= '
                </tbody>
            </table>
        </div>
        
        <div class="email-footer">
            <p style="margin:0;">&copy; 2002 - ' . date('Y') . ' BRInfor Soluções em TI<br/>Sistema de Chamados</p>
        </div>
    </div>
</body>
</html>';

    return $html;
}

// ============================================
// PROCESSAR ENVIO PARA O BANCO - ATIVADO
// ============================================
$erro_envio = null;
$chamado_criado = false;
$id_chamado_criado = null;
$email_resultados = [];
$email_enviado = false;

if ($dados_submetidos) {
    try {
        // ============================================
        // 1. SALVA O ARQUIVO FISICAMENTE
        // ============================================
        $arquivo_salvo = null;
        if ($arquivo_info && isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $resultado_salvar = salvarArquivoChamado(
                $_FILES['arquivo']['tmp_name'],
                $proximo_id_chamado,
                $_FILES['arquivo']['name']
            );

            if ($resultado_salvar['status'] === 'sucesso') {
                $arquivo_salvo = $resultado_salvar;
                $arquivo_info['caminho_completo'] = $resultado_salvar['caminho_completo'];
                $arquivo_info['nome_renomeado'] = $resultado_salvar['nome_renomeado'];
                error_log("✅ Arquivo salvo com sucesso: " . $resultado_salvar['caminho_completo']);
            } else {
                throw new Exception("Erro ao salvar o arquivo: " . $resultado_salvar['mensagem']);
            }
        }

        // ============================================
        // 2. INSERE NO BANCO DE DADOS
        // ============================================
        $pdo->beginTransaction();

        // 2.1 INSERT contrato_chamado
        $stmt = $pdo->prepare($sql_chamado);
        $stmt->execute();

        // 2.2 Atualiza query do evento de arquivo com caminho real
        if ($arquivo_salvo) {
            $sql_evento_arquivo_atualizado = gerarQueryEvento($dados, 'F', '', $proximo_id_chamado, [
                'caminho_completo' => $arquivo_salvo['caminho_completo']
            ]);

            foreach ($queries_evento as $index => $query) {
                if (strpos($query, "tipo = 'F'") !== false) {
                    $queries_evento[$index] = $sql_evento_arquivo_atualizado;
                    break;
                }
            }
        }

        // 2.3 INSERT eventos
        foreach ($queries_evento as $sql_evento) {
            if (!empty($sql_evento)) {
                $stmt = $pdo->prepare($sql_evento);
                $stmt->execute();
            }
        }

        // Confirma a transação
        $pdo->commit();
        $chamado_criado = true;
        $id_chamado_criado = $proximo_id_chamado;

        // ============================================
        // 3. ENVIA E-MAIL APÓS CRIAR O CHAMADO
        // ============================================
        $html_email = renderHtmlEmailChamadoVisualizacao($dados, $proximo_id_chamado, $arquivo_info, $eventos_para_visualizar);
        $email_resultados = enviarEmailChamado($html_email, $dados, $proximo_id_chamado);
        $email_enviado = !empty($email_resultados['resultados'])
            && count(array_filter($email_resultados['resultados'], static function ($resultado) {
                return !empty($resultado['enviado']);
            })) === count($email_resultados['resultados']);

        // ============================================
        // 4. REDIRECIONA PARA chamados_abrir_resultado COM O ID
        // ============================================
        $parametro_email = $email_enviado ? '' : '&email=failed';
        header("Location: index.php?page=visitante-abrir-chamado-resultado&id=" . $proximo_id_chamado . "&seguranca=" . $dados['codigoSeguranca'] . $parametro_email);
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if (isset($arquivo_salvo) && file_exists($arquivo_salvo['caminho_absoluto'])) {
            unlink($arquivo_salvo['caminho_absoluto']);
        }
        $erro_envio = 'Erro ao salvar o chamado: ' . $e->getMessage();
        error_log('❌ Erro ao criar chamado: ' . $e->getMessage());
        error_log('SQL Chamado: ' . $sql_chamado);

        // Redireciona com erro
        header("Location: index.php?page=visitante-abrir-chamado-resultado&erro=" . urlencode($e->getMessage()));
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if (isset($arquivo_salvo) && file_exists($arquivo_salvo['caminho_absoluto'])) {
            unlink($arquivo_salvo['caminho_absoluto']);
        }
        $erro_envio = 'Erro: ' . $e->getMessage();
        error_log('❌ Erro geral: ' . $e->getMessage());

        // Redireciona com erro
        header("Location: index.php?page=visitante-abrir-chamado-resultado&erro=" . urlencode($e->getMessage()));
        exit;
    }
}

// Se chegou aqui sem dados submetidos, redireciona
if (!$dados_submetidos) {
    header('Location: index.php?page=visitante-abrir-chamado');
    exit;
}
?>