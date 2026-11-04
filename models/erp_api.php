<?php
/*
 * Cliente da API do ERP. Substitui a conexão direta ao banco do ERP.
 * A URL e a chave ficam em models/erp_config.php (fora do Git; ver erp_config.exemplo.php).
 */
date_default_timezone_set('America/Sao_Paulo');

final class ErpApiErro extends RuntimeException {}

function erp_api_config(): array {
    static $config = null;
    if ($config === null) {
        $arquivo = __DIR__ . '/erp_config.php';
        if (!is_file($arquivo)) {
            throw new ErpApiErro('Arquivo models/erp_config.php não encontrado.');
        }
        $config = require $arquivo;
    }
    return $config;
}

/**
 * Chama uma ação da API do ERP e devolve o campo "data" da resposta.
 * Lança ErpApiErro em falha de rede, HTTP ou resposta com ok=false.
 */
function erp_api($acao, array $params = []) {
    $config = erp_api_config();
    if (!function_exists('curl_init')) {
        throw new ErpApiErro('A extensão cURL do PHP não está ativa neste servidor.');
    }
    if (empty($config['url']) || empty($config['chave'])) {
        throw new ErpApiErro('models/erp_config.php sem "url" ou "chave".');
    }
    $corpo = json_encode(['acao' => $acao, 'params' => $params], JSON_UNESCAPED_UNICODE);

    $ch = curl_init($config['url']);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $corpo,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Api-Key: ' . $config['chave'],
        ],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => (int)($config['timeout'] ?? 20),
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $resposta = curl_exec($ch);
    $erroCurl = curl_error($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resposta === false) {
        error_log("API ERP [$acao] falha de conexão: $erroCurl");
        throw new ErpApiErro('Não foi possível conectar à API do ERP (' . $erroCurl . ').');
    }

    $json = json_decode($resposta, true);
    if (!is_array($json) || empty($json['ok'])) {
        $mensagem = is_array($json) && isset($json['erro']) ? $json['erro'] : 'resposta inválida';
        error_log("API ERP [$acao] HTTP $http: $mensagem");
        throw new ErpApiErro("API do ERP: $mensagem");
    }

    return $json['data'] ?? null;
}
