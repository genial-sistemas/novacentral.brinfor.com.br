<?php
require_once __DIR__ . '/erp_api.php';

/** Notas fiscais de serviço (NFS-e) do cliente logado, paginadas. Retorna ['total' => n, 'itens' => [...]]. */
function obterNotasFiscais($idPessoa, $limite, $offset)
{
    $resultado = erp_api('financeiro.notas', [
        'id_pessoa' => (int)$idPessoa,
        'limite' => (int)$limite,
        'offset' => (int)$offset,
    ]) ?: [];
    return ['total' => (int)($resultado['total'] ?? 0), 'itens' => $resultado['itens'] ?? []];
}

/** PDF/XML da nota (a API confere se a nota é do cliente). Retorna ['tipo' => ..., 'conteudo' => bytes]. */
function obterArquivoNotaFiscal($idNota, $idPessoa, $formato)
{
    $arquivo = erp_api('financeiro.nota_arquivo', [
        'id' => (int)$idNota,
        'id_pessoa' => (int)$idPessoa,
        'formato' => $formato === 'xml' ? 'xml' : 'pdf',
    ]);
    return ['tipo' => $arquivo['tipo'], 'conteudo' => base64_decode((string)$arquivo['base64'])];
}

/** Texto amigável para o status vindo da Spedy (valores desconhecidos aparecem como vieram). */
function statusNotaFiscal($status)
{
    $mapa = [
        'authorized' => ['Autorizada', 'ok'],
        'issued' => ['Autorizada', 'ok'],
        'canceled' => ['Cancelada', 'cancelada'],
        'cancelled' => ['Cancelada', 'cancelada'],
        'rejected' => ['Rejeitada', 'erro'],
        'error' => ['Com erro', 'erro'],
        'processing' => ['Em processamento', 'pendente'],
        'enqueued' => ['Em processamento', 'pendente'],
        'pending' => ['Em processamento', 'pendente'],
    ];
    $chave = strtolower(trim((string)$status));
    if (isset($mapa[$chave])) {
        return $mapa[$chave];
    }
    return [$status !== '' && $status !== null ? ucfirst((string)$status) : 'Não informado', 'pendente'];
}
