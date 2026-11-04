<?php
/*
 * Busca pública de contrato (AJAX do formulário de abertura de chamado de visitante).
 * Atende a rota "buscar-contrato" e o endereço antigo views/visitante/buscar_contrato.php.
 * Os dados vêm da API do ERP; aqui só se monta a resposta JSON esperada pelo formulário.
 */
require_once __DIR__ . '/../models/erp_api.php';

header('Content-Type: application/json');

function busca_responder(array $dados): void {
    echo json_encode($dados);
    exit;
}

function busca_html(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function busca_contato($id_contato): array {
    $contato = (int)$id_contato > 0 ? erp_api('contato.obter', ['id' => (int)$id_contato]) : null;
    return [
        'nome' => $contato['nome'] ?? '',
        'email' => $contato['email'] ?? '',
        'telefone' => $contato['cel'] ?? '',
    ];
}

$id_tipo_contrato = (string)($_POST['id_tipo_contrato'] ?? '');
$contrato = (string)($_POST['contrato'] ?? '');
$equipamento = (string)($_POST['equipamento'] ?? '');
$search_type = (string)($_POST['search_type'] ?? '');
$valor = (string)($_POST['valor'] ?? '');
$id_contato = $_POST['id_contato'] ?? ''; // dados do contato depois de escolher no select

try {
    // TIPO 1 - HELPDESK
    if ($id_tipo_contrato === '1') {
        if ($contrato === '' && $equipamento === '') {
            busca_responder(['erro' => 'Informe contrato ou equipamento']);
        }
        $resultados = erp_api('busca.helpdesk', ['etiqueta' => $contrato, 'codigo' => $equipamento]) ?: [];
        if (!$resultados) {
            busca_responder(['erro' => 'Nenhum contrato encontrado']);
        }
        if (count($resultados) > 1) {
            busca_responder(['erro' => 'Mais de um equipamento corresponde a esses códigos. Confirme o contrato e o equipamento.']);
        }
        $dados = $resultados[0];
        busca_responder([
            'success' => true,
            'nome' => $dados['nome'] ?? '',
            'email' => $dados['email'] ?? '',
            'telefone' => $dados['cel'] ?? '',
            'id_contato' => $dados['id_contato'] ?? 0,
            'id_equipamento' => $dados['id_equipamento'] ?? 0,
            'id_contrato' => $dados['id_contrato'] ?? '',
            'contrato' => $dados['contrato'] ?? '',
            'codigo' => $dados['codigo'] ?? '',
        ]);
    }

    // DADOS DO CONTATO POR ID
    if (!empty($id_contato)) {
        $contato = (int)$id_contato > 0 ? erp_api('contato.obter', ['id' => (int)$id_contato]) : null;
        if (!$contato) {
            busca_responder(['erro' => 'Contato não encontrado']);
        }
        busca_responder(['success' => true] + busca_contato($id_contato));
    }

    if (!in_array($id_tipo_contrato, ['2', '4', '5', '6', '7'], true)) {
        busca_responder(['erro' => 'Tipo de contrato não implementado']);
    }
    if ($valor === '') {
        busca_responder(['erro' => 'Informe CNPJ, CPF ou ID']);
    }
    if (!in_array($search_type, ['CNPJ', 'CPF', 'ID'], true)) {
        busca_responder(['erro' => 'Tipo de busca inválido (use CNPJ, CPF ou ID)']);
    }

    $resultados = erp_api('busca.contrato', [
        'tipo' => (int)$id_tipo_contrato,
        'search_type' => $search_type,
        'valor' => $valor,
    ]) ?: [];

    // TIPO 2 - HOSPEDAGEM / TIPO 4 - DOMÍNIO
    if ($id_tipo_contrato === '2' || $id_tipo_contrato === '4') {
        if (!$resultados) {
            busca_responder(['erro' => 'Nenhum domínio encontrado para o ' . $search_type . ' informado']);
        }
        $primeiro = $resultados[0];
        if ($search_type !== 'ID' && count($resultados) > 1) {
            $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
            $selectHtml .= '<option value="">Selecione um domínio...</option>';
            foreach ($resultados as $row) {
                $id_dominio = busca_html((string)$row['id_dominio']);
                $dominio = busca_html((string)$row['dominio']);
                $id_contato_val = busca_html((string)($row['id_contato'] ?? ''));
                $selectHtml .= "<option value='{$id_contato_val}'
                                        data-dominio='{$dominio}'
                                        data-id-dominio='{$id_dominio}'>
                                        {$dominio}
                                </option>";
            }
            $selectHtml .= '</select>';
            busca_responder(['success' => true, 'select_html' => $selectHtml, 'usar_select' => true]);
        }
        busca_responder(['success' => true] + busca_contato($primeiro['id_contato'] ?? 0) + [
            'dominio' => $primeiro['dominio'] ?? '',
            'usar_select' => false,
        ]);
    }

    // TIPO 5 - BACKUP (busca por ID sempre mostra o select)
    if ($id_tipo_contrato === '5') {
        if (!$resultados) {
            busca_responder(['erro' => 'Nenhum backup encontrado para o ' . $search_type . ' informado']);
        }
        $primeiro = $resultados[0];
        if (count($resultados) > 1 || $search_type === 'ID') {
            $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
            $selectHtml .= '<option value="">Selecione um plano de backup...</option>';
            foreach ($resultados as $row) {
                $id_backup = busca_html((string)$row['id_backup']);
                $backup = busca_html((string)($row['backup'] ?? ''));
                $id_contato_val = busca_html((string)($row['id_contato'] ?? ''));
                $selectHtml .= "<option value='{$id_contato_val}'
                                        data-backup='{$backup}'
                                        data-id-backup='{$id_backup}'>
                                        {$backup}
                                </option>";
            }
            $selectHtml .= '</select>';
            busca_responder(['success' => true, 'select_html' => $selectHtml, 'usar_select' => true]);
        }
        busca_responder(['success' => true] + busca_contato($primeiro['id_contato'] ?? 0) + [
            'backup' => $primeiro['backup'] ?? '',
            'id_backup' => $primeiro['id_backup'] ?? '',
            'usar_select' => false,
        ]);
    }

    // TIPO 6 - SUPORTE PRODUTO (sempre select)
    if ($id_tipo_contrato === '6') {
        if (!$resultados) {
            busca_responder(['erro' => 'Nenhum suporte produto encontrado para o ' . $search_type . ' informado']);
        }
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="0">Selecione o contrato/produto...</option>';
        foreach ($resultados as $row) {
            $id_contrato = busca_html((string)$row['id_contrato']);
            $id_suporte = busca_html((string)$row['id_suporte']);
            $suporte = busca_html((string)($row['suporte'] ?? ''));
            $id_contato_val = busca_html((string)($row['id_contato'] ?? ''));
            $nome = busca_html((string)($row['nome'] ?? ''));
            $email = busca_html((string)($row['email'] ?? ''));
            $telefone = busca_html((string)($row['cel'] ?? ''));
            $rotulo = 'Contrato #' . $id_contrato . ' - ' . $suporte;
            $selectHtml .= "<option value='{$id_contrato}'
                                    data-id-contrato='{$id_contrato}'
                                    data-id-suporte='{$id_suporte}'
                                    data-id-contato='{$id_contato_val}'
                                    data-nome='{$nome}'
                                    data-email='{$email}'
                                    data-telefone='{$telefone}'>
                                    {$rotulo}
                            </option>";
        }
        $selectHtml .= '</select>';
        busca_responder(['success' => true, 'select_html' => $selectHtml, 'usar_select' => true]);
    }

    // TIPO 7 - LOCAÇÃO DE EQUIPAMENTOS
    if (!$resultados) {
        busca_responder(['erro' => 'Nenhum equipamento de locação encontrado para o ' . $search_type . ' informado']);
    }
    if (count($resultados) > 1) {
        $selectHtml = '<select class="form-control" id="resultadoBusca" style="display:block;">';
        $selectHtml .= '<option value="">Selecione um equipamento...</option>';
        foreach ($resultados as $row) {
            $id_contrato = busca_html((string)$row['id_contrato']);
            $id_equipamento = busca_html((string)$row['id_equipamento']);
            $id_contato_val = busca_html((string)($row['id_contato'] ?? ''));
            $locacao = busca_html((string)($row['locacao'] ?? ''));
            $nome = busca_html((string)($row['nome'] ?? ''));
            $email = busca_html((string)($row['email'] ?? ''));
            $telefone = busca_html((string)($row['telefone'] ?? ''));
            $selectHtml .= "<option value='{$id_contato_val}'
                data-id-contrato='{$id_contrato}'
                data-id-contato='{$id_contato_val}'
                data-id-equipamento='{$id_equipamento}'
                data-nome='{$nome}'
                data-email='{$email}'
                data-telefone='{$telefone}'>{$locacao}</option>";
        }
        $selectHtml .= '</select>';
        busca_responder(['success' => true, 'select_html' => $selectHtml, 'usar_select' => true]);
    }
    $resultado = $resultados[0];
    busca_responder([
        'success' => true,
        'id_contrato' => $resultado['id_contrato'],
        'id_contato' => $resultado['id_contato'] ?? '',
        'id_equipamento' => $resultado['id_equipamento'],
        'nome' => $resultado['nome'] ?? '',
        'email' => $resultado['email'] ?? '',
        'telefone' => $resultado['telefone'] ?? '',
        'locacao' => $resultado['locacao'] ?? '',
        'usar_select' => false,
    ]);

} catch (ErpApiErro $e) {
    // Detalhes ficam no log do servidor; o formulário recebe uma mensagem simples.
    busca_responder(['erro' => 'Não foi possível consultar agora. Tente novamente em instantes.']);
}
