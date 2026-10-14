<?php

function caminhoArquivoLogSistema()
{
    $raizPublica = trim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($raizPublica === '') {
        $raizPublica = dirname(__DIR__);
    }

    return rtrim($raizPublica, '/\\') . DIRECTORY_SEPARATOR . 'logs'
        . DIRECTORY_SEPARATOR . 'sistema-erros.txt';
}

function limparDadosErroSistema($valor)
{
    $valor = (string)$valor;
    $valor = preg_replace('/[\r\n\t]+/', ' ', $valor);
    $valor = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $valor);
    $valor = preg_replace("/([\"']?(?:telefone|cel|phone|cpf|cnpj)[\"']?\\s*:\\s*[\"']?)[^\"'},\\s]+/i", '$1[redigido]', $valor);
    $valor = preg_replace('/\b\d{3}\.?\d{3}\.?\d{3}-?\d{2}\b|\b\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}\b/', '[documento]', $valor);
    $valor = preg_replace('/(?i)\b(password|passwd|senha|token|secret|authorization)\b\s*[:=]\s*[^\s,;]+/', '$1=[redigido]', $valor);

    return substr($valor, 0, 4000);
}

function registrarErroSistema($origem, $mensagem, array $contexto = array())
{
    $arquivo = caminhoArquivoLogSistema();
    $diretorio = dirname($arquivo);

    if (!is_dir($diretorio) && !@mkdir($diretorio, 0750, true) && !is_dir($diretorio)) {
        error_log('Não foi possível criar o diretório do log do sistema.');
        return false;
    }

    $requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $registro = array(
        'data' => date('c'),
        'origem' => limparDadosErroSistema($origem),
        'mensagem' => limparDadosErroSistema($mensagem),
        'metodo' => limparDadosErroSistema($_SERVER['REQUEST_METHOD'] ?? ''),
        'rota' => limparDadosErroSistema($requestPath ?: '')
    );

    foreach ($contexto as $chave => $valor) {
        if ($valor instanceof Throwable) {
            $registro['excecao'] = array(
                'tipo' => get_class($valor),
                'arquivo' => basename($valor->getFile()),
                'linha' => $valor->getLine(),
                'rastro' => limparDadosErroSistema($valor->getTraceAsString())
            );
        } elseif (is_scalar($valor) || $valor === null) {
            $registro[limparDadosErroSistema($chave)] = limparDadosErroSistema($valor);
        }
    }

    $linha = json_encode($registro, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($linha === false) {
        $linha = '{"origem":"logger","mensagem":"Falha ao codificar registro de erro"}';
    }

    if (file_exists($arquivo) && filesize($arquivo) >= 2097152) {
        @rename($arquivo . '.1', $arquivo . '.2');
        @rename($arquivo, $arquivo . '.1');
    }

    if (@file_put_contents($arquivo, $linha . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        error_log('Não foi possível gravar o arquivo logs/sistema-erros.txt.');
        return false;
    }

    return true;
}

function iniciarCapturaErrosSistema()
{
    set_error_handler(function ($nivel, $mensagem, $arquivo, $linha) {
        if (!(error_reporting() & $nivel)) {
            return false;
        }

        registrarErroSistema('php', $mensagem, array(
            'tipo' => $nivel,
            'arquivo' => basename($arquivo),
            'linha' => $linha
        ));

        return false;
    });

    set_exception_handler(function ($excecao) {
        registrarErroSistema('php.uncaught', $excecao->getMessage(), array(
            'excecao' => $excecao
        ));

        error_log('Exceção PHP não tratada: ' . limparDadosErroSistema($excecao->getMessage()));
    });

    register_shutdown_function(function () {
        $erro = error_get_last();
        $tiposFatais = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

        if ($erro && in_array($erro['type'], $tiposFatais, true)) {
            registrarErroSistema('php.fatal', $erro['message'], array(
                'tipo' => $erro['type'],
                'arquivo' => basename($erro['file']),
                'linha' => $erro['line']
            ));
        }
    });
}
