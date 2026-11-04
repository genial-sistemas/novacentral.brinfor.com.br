<?php
/*
 * Endpoint JSON da conversa do chamado (rota /conversa).
 *   GET  ?id=&seguranca=&desde=   -> estado do chamado + eventos novos
 *   POST id, seguranca, descricao, arquivo, token -> resposta do cliente
 * Acesso: cliente logado (contratos da sessão) ou visitante com o código de segurança do e-mail.
 */
require_once __DIR__ . '/../models/erp_api.php';
require_once __DIR__ . '/../models/chamado.php';
require_once __DIR__ . '/../models/anexo.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function conversaResponder($http, array $dados)
{
    http_response_code($http);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Token anti-CSRF da conversa, guardado na sessão. */
function conversaToken()
{
    if (empty($_SESSION['csrf_conversa'])) {
        $_SESSION['csrf_conversa'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_conversa'];
}

$entrada = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$idChamado = (int)($entrada['id'] ?? 0);
$seguranca = substr(trim((string)($entrada['seguranca'] ?? '')), 0, 128);

$acesso = ['id' => $idChamado];
if ($seguranca !== '') {
    $acesso['seguranca'] = $seguranca;
} elseif (!empty($_SESSION['id_pessoa'])) {
    $acesso['contratos'] = array_values(array_map('intval', $_SESSION['contratos'] ?? []));
} else {
    conversaResponder(401, ['ok' => false, 'erro' => 'Sessão expirada. Entre novamente.']);
}
if ($idChamado <= 0) {
    conversaResponder(400, ['ok' => false, 'erro' => 'Chamado inválido.']);
}

/** Converte um evento da API no formato usado pela tela (sem dados internos). */
function conversaEvento(array $e)
{
    // O ERP grava parte das mensagens com entidades HTML (&#039; &lt; &gt;): converte para o caractere real.
    // A tela mostra o texto com textContent, então isso não abre brecha para HTML/script.
    $texto = html_entity_decode(stripslashes((string)($e['descricao'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $anexo = null;
    if ((int)($e['link'] ?? 0) === 1 && preg_match('#^https?://#i', $texto)) {
        $anexo = $texto;
    } elseif (preg_match('#^/?(chamados/eventos/\d+/[A-Za-z0-9_.-]+)$#', $texto, $m)) {
        // Anexos antigos gravados com caminho relativo.
        $anexo = anexoUrlBase() . '/' . $m[1];
    }
    $tecnico = (int)($e['id_pessoa'] ?? 0) > 0;
    $nome = trim((string)($e['nome_pessoa'] ?? ''));
    return [
        'id' => (int)$e['id'],
        'autor' => $tecnico ? 'tecnico' : 'cliente',
        'nome' => $tecnico ? ($nome !== '' ? explode(' ', $nome)[0] : 'Técnico') : 'Você',
        'data' => (string)$e['data'],
        'hora' => substr((string)$e['hora'], 0, 5),
        'texto' => $anexo === null ? $texto : '',
        'anexo' => $anexo,
        'anexo_nome' => $anexo === null ? null : basename(parse_url($anexo, PHP_URL_PATH)),
    ];
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Cliente logado precisa do token da sessão; visitante já prova acesso com o código de segurança.
        if ($seguranca === '' && !hash_equals(conversaToken(), (string)($_POST['token'] ?? ''))) {
            conversaResponder(403, ['ok' => false, 'erro' => 'Página expirada. Recarregue e tente novamente.']);
        }

        $descricao = trim((string)($_POST['descricao'] ?? ''));
        // Confere acesso e situação antes de gravar qualquer arquivo.
        $estado = erp_api('conversa.estado', $acesso + ['desde' => PHP_INT_MAX]);
        if ((int)$estado['chamado']['id_situacao'] === 7) {
            conversaResponder(409, ['ok' => false, 'erro' => 'Este chamado está encerrado e não aceita novas mensagens.', 'encerrado' => true]);
        }

        $anexo = anexoSalvar('arquivo', $idChamado);
        if ($anexo !== null && !$anexo['ok']) {
            conversaResponder(400, ['ok' => false, 'erro' => $anexo['erro']]);
        }
        if ($descricao === '' && $anexo === null) {
            conversaResponder(400, ['ok' => false, 'erro' => 'Escreva uma mensagem ou anexe um arquivo.']);
        }

        try {
            $resultado = erp_api('conversa.responder', $acesso + [
                'descricao' => $descricao,
                'anexo_url' => $anexo ? $anexo['url'] : '',
            ]);
        } catch (ErpApiErro $e) {
            if ($anexo) {
                @unlink($anexo['caminho']); // não deixa arquivo órfão se a gravação falhar
            }
            throw $e;
        }
        conversaResponder(200, ['ok' => true, 'ids' => $resultado['ids'] ?? []]);
    }

    // GET: estado + eventos novos
    $desde = max(0, (int)($_GET['desde'] ?? 0));
    $estado = erp_api('conversa.estado', $acesso + ['desde' => $desde]);
    $chamado = $estado['chamado'];
    $ultima = $estado['ultima_interacao'];

    $resposta = [
        'ok' => true,
        'chamado' => [
            'id' => (int)$chamado['id'],
            'tipo_contrato' => (string)($chamado['tipo_contrato'] ?? ''),
            'situacao' => (string)($chamado['situacao'] ?? ''),
            'id_situacao' => (int)$chamado['id_situacao'],
            'encerrado' => (int)$chamado['id_situacao'] === 7,
            'data_abertura' => $chamado['data_abertura'] ? date('d/m/Y', strtotime($chamado['data_abertura'])) : '',
        ],
        'tecnico' => $estado['tecnico'] ?: 'Não informado',
        'ultima_interacao' => $ultima ? date('d/m/Y', strtotime($ultima['data'])) . ' às ' . substr($ultima['hora'], 0, 5) : '',
        'eventos' => array_map('conversaEvento', $estado['eventos'] ?: []),
    ];
    // Detalhes (domínio, equipamento com nº de série etc.) com a mesma regra da lista de chamados.
    $detalhes = trim((string)($estado['detalhes'] ?? ''), " -");
    $resposta['equipamento'] = $detalhes !== '' ? $detalhes : '-';
    conversaResponder(200, $resposta);

} catch (ErpApiErro $e) {
    $mensagem = $e->getMessage();
    if (strpos($mensagem, 'não encontrado') !== false) {
        conversaResponder(404, ['ok' => false, 'erro' => 'Chamado não encontrado.']);
    }
    if (strpos($mensagem, 'encerrado') !== false) {
        conversaResponder(409, ['ok' => false, 'erro' => 'Este chamado está encerrado e não aceita novas mensagens.', 'encerrado' => true]);
    }
    error_log('Conversa do chamado ' . $idChamado . ': ' . $mensagem);
    conversaResponder(503, ['ok' => false, 'erro' => 'Não foi possível falar com o sistema agora. Tentando novamente…']);
}
