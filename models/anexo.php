<?php
/*
 * Upload de anexos de chamados (pasta pública chamados/eventos/<id>/).
 * Só imagem (JPG, PNG, GIF, WebP) e PDF; imagens grandes são reduzidas.
 * Regras: extensão E tipo real (conteúdo) na lista; nome gerado pelo servidor; nunca grava scripts.
 */

const ANEXO_TAMANHO_MAXIMO = 10485760; // 10 MB

// Só imagem e PDF.
function anexoExtensoesPermitidas()
{
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
}

function anexoTiposPermitidos()
{
    return ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
}

/** Extensão e tipo real (conteúdo) precisam estar na lista e combinar entre si. */
function anexoTipoValido($extensao, $tipo)
{
    $mapa = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'gif' => 'image/gif', 'webp' => 'image/webp', 'pdf' => 'application/pdf',
    ];
    return isset($mapa[$extensao]) && $mapa[$extensao] === $tipo;
}

const ANEXO_IMAGEM_LADO_MAXIMO = 1920;      // px no maior lado
const ANEXO_IMAGEM_COMPACTAR_ACIMA = 512000; // só mexe em imagens acima de 500 KB ou maiores que o lado máximo

/**
 * Reduz imagens grandes (JPEG, PNG, WebP) no próprio arquivo: redimensiona até 1920 px e recomprime.
 * Só substitui se o resultado ficar menor. GIF fica como está (pode ser animado). Sem GD, não faz nada.
 */
function anexoCompactarImagem($caminho, $tipo)
{
    if (!function_exists('imagecreatetruecolor') || !in_array($tipo, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return;
    }
    $info = @getimagesize($caminho);
    if (!$info) {
        return;
    }
    list($largura, $altura) = $info;
    $tamanho = (int)@filesize($caminho);
    $maiorLado = max($largura, $altura);
    if ($tamanho <= ANEXO_IMAGEM_COMPACTAR_ACIMA && $maiorLado <= ANEXO_IMAGEM_LADO_MAXIMO) {
        return;
    }
    // Evita estourar a memória com imagens gigantes (≈ 5 bytes por pixel).
    if ($largura * $altura > 40000000) {
        return;
    }

    if ($tipo === 'image/jpeg') {
        $origem = @imagecreatefromjpeg($caminho);
    } elseif ($tipo === 'image/png') {
        $origem = @imagecreatefrompng($caminho);
    } else {
        $origem = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($caminho) : false;
    }
    if (!$origem) {
        return;
    }

    // Foto de celular: aplica a rotação do EXIF, senão fica deitada depois de recomprimir.
    if ($tipo === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($caminho);
        $angulos = [3 => 180, 6 => -90, 8 => 90];
        $orientacao = (int)($exif['Orientation'] ?? 1);
        if (isset($angulos[$orientacao]) && ($girada = imagerotate($origem, $angulos[$orientacao], 0))) {
            if (PHP_VERSION_ID < 80000) { imagedestroy($origem); }
            $origem = $girada;
            $largura = imagesx($origem);
            $altura = imagesy($origem);
        }
    }

    $escala = min(1, ANEXO_IMAGEM_LADO_MAXIMO / max($largura, $altura));
    $novaLargura = max(1, (int)round($largura * $escala));
    $novaAltura = max(1, (int)round($altura * $escala));
    $destino = imagecreatetruecolor($novaLargura, $novaAltura);
    if ($tipo !== 'image/jpeg') {
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
    }
    imagecopyresampled($destino, $origem, 0, 0, 0, 0, $novaLargura, $novaAltura, $largura, $altura);
    if (PHP_VERSION_ID < 80000) { imagedestroy($origem); }

    $temporario = $caminho . '.tmp';
    if ($tipo === 'image/jpeg') {
        $ok = imagejpeg($destino, $temporario, 80);
    } elseif ($tipo === 'image/png') {
        $ok = imagepng($destino, $temporario, 9);
    } else {
        $ok = imagewebp($destino, $temporario, 80);
    }
    if (PHP_VERSION_ID < 80000) { imagedestroy($destino); }

    clearstatcache();
    if ($ok && is_file($temporario) && filesize($temporario) > 0 && filesize($temporario) < $tamanho) {
        @rename($temporario, $caminho);
        @chmod($caminho, 0644);
    } else {
        @unlink($temporario);
    }
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
    if (!anexoTipoValido($extensao, $tipo)) {
        return ['ok' => false, 'erro' => 'Envie apenas imagem (JPG, PNG, GIF, WebP) ou PDF.'];
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
    anexoCompactarImagem($pasta . '/' . $nome, $tipo);

    return [
        'ok' => true,
        'url' => anexoUrlBase() . '/chamados/eventos/' . (int)$idChamado . '/' . $nome,
        'caminho' => $pasta . '/' . $nome,
        'nome' => (string)$arquivo['name'],
    ];
}
