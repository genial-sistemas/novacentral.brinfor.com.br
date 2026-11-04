<?php
/*
 * Upload de anexos de chamados (pasta pública chamados/eventos/<id>/).
 * Regras: extensão E tipo real (conteúdo) na lista; nome gerado pelo servidor; nunca grava scripts.
 */

const ANEXO_TAMANHO_MAXIMO = 10485760; // 10 MB

function anexoExtensoesPermitidas()
{
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'zip', 'rar', '7z'];
}

function anexoTiposPermitidos()
{
    return [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain', 'text/csv', 'application/csv',
        'application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/vnd.rar',
        'application/x-rar', 'application/x-7z-compressed', 'application/octet-stream',
        'application/CDFV2', 'application/x-ole-storage',
    ];
}

/** Endereço público do site atual (só hosts conhecidos; evita usar Host forjado). */
function anexoUrlBase()
{
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $permitidos = ['central.brinfor.com.br', 'novacentral.brinfor.com.br'];
    return 'https://' . (in_array($host, $permitidos, true) ? $host : 'central.brinfor.com.br');
}

/**
 * Valida e salva o arquivo de $_FILES[$campo] para o chamado.
 * Retorna ['ok' => true, 'url' => ..., 'nome' => ...] ou ['ok' => false, 'erro' => ...].
 * Retorna null se nenhum arquivo foi enviado.
 */
function anexoSalvar($campo, $idChamado)
{
    if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $arquivo = $_FILES[$campo];
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'erro' => 'Falha no envio do arquivo. Tente novamente.'];
    }
    if ($arquivo['size'] <= 0 || $arquivo['size'] > ANEXO_TAMANHO_MAXIMO) {
        return ['ok' => false, 'erro' => 'O arquivo deve ter até 10 MB.'];
    }

    $extensao = strtolower(pathinfo((string)$arquivo['name'], PATHINFO_EXTENSION));
    $tipo = 'application/octet-stream';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipo = (string)finfo_file($finfo, $arquivo['tmp_name']);
    }
    if (!in_array($extensao, anexoExtensoesPermitidas(), true)
        || !in_array($tipo, anexoTiposPermitidos(), true)
        || stripos($tipo, 'php') !== false) {
        return ['ok' => false, 'erro' => 'Tipo de arquivo não permitido.'];
    }

    $raiz = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\') ?: dirname(__DIR__);
    $pasta = $raiz . '/chamados/eventos/' . (int)$idChamado;
    if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
        error_log('Anexo: não foi possível criar a pasta ' . $pasta);
        return ['ok' => false, 'erro' => 'Não foi possível salvar o arquivo.'];
    }

    $nome = 'chamado_' . (int)$idChamado . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extensao;
    if (!move_uploaded_file($arquivo['tmp_name'], $pasta . '/' . $nome)) {
        error_log('Anexo: falha ao mover o arquivo para ' . $pasta);
        return ['ok' => false, 'erro' => 'Não foi possível salvar o arquivo.'];
    }
    @chmod($pasta . '/' . $nome, 0644);

    return [
        'ok' => true,
        'url' => anexoUrlBase() . '/chamados/eventos/' . (int)$idChamado . '/' . $nome,
        'caminho' => $pasta . '/' . $nome,
        'nome' => (string)$arquivo['name'],
    ];
}
