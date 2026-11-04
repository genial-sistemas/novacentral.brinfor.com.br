<?php
require_once __DIR__ . '/erp_api.php';

function obterContratosTiposAtivos() {
    return erp_api('contrato.tipos_ativos') ?: [];
}

function obterContratosAtivos($ids_contatos) {
    return erp_api('contrato.ativos', ['contratos' => array_values((array)$ids_contatos)]) ?: [];
}

function obterContratoOutsourcingEquipamentosaAtivos($id_contato) {
    return erp_api('contrato.outsourcing_equipamentos_ativos', ['id_contrato' => (int)$id_contato]) ?: [];
}

function obterEquipamentosLocacaoContratos($contratos) {
    return erp_api('contrato.locacao_equipamentos', ['contratos' => array_values((array)$contratos)]) ?: [];
}

function obterEquipamentosParaRelatorio($contratos) {
    $equipamentos = array();

    foreach ($contratos as $contrato) {
        $id_contrato = (int)$contrato['id'];
        $id_tipo = (int)$contrato['id_tipo'];

        if ($id_tipo === 1) {
            foreach (obterContratoOutsourcingEquipamentosaAtivos($id_contrato) as $equipamento) {
                $equipamento['id_contrato'] = $id_contrato;
                $equipamento['referencia'] = $id_contrato . ':' . (int)$equipamento['id'];
                $responsavel = trim((string)($equipamento['usuario'] ?? ''));
                if ($responsavel === '') {
                    $responsavel = trim((string)($equipamento['nome_contato'] ?? ''));
                }
                $equipamento['rotulo'] = $id_contrato . ' - ' . str_pad($equipamento['codigo'], 4, '0', STR_PAD_LEFT) . ' - ' . $equipamento['descricao'];
                if ($responsavel !== '') {
                    $equipamento['rotulo'] .= ' - ' . $responsavel;
                }
                $equipamentos[] = $equipamento;
            }
        } elseif ($id_tipo === 7) {
            foreach (erp_api('contrato.locacao_equipamentos_relatorio', ['id_contrato' => $id_contrato]) ?: [] as $equipamento) {
                $equipamento['id_contrato'] = $id_contrato;
                $equipamento['referencia'] = $id_contrato . ':' . (int)$equipamento['id'];
                $patrimonio = trim((string)($equipamento['patrimonio'] ?? ''));
                $equipamento['rotulo'] = $id_contrato . ' - ' . ($patrimonio !== '' ? $patrimonio . ' - ' : '') . $equipamento['descricao'];
                $equipamentos[] = $equipamento;
            }
        }
    }

    return $equipamentos;
}

function obterNomeContato($id_contato) {
    // Equipamento sem contato: devolve vazio (a API recusa id 0 e a página parava de carregar).
    if ((int)$id_contato <= 0) {
        return false;
    }
    return erp_api('contato.obter', ['id' => (int)$id_contato]) ?: false;
}

function obterEtiqueta($id_contato) {
    return erp_api('contrato.etiqueta', ['id_contrato' => (int)$id_contato]);
}

function obterContratos($contratos_ids) {
    return erp_api('contrato.lista', ['contratos' => array_values((array)$contratos_ids)]) ?: [];
}

function obterContratoOutsourcingPorId($id) {
    return erp_api('contrato.outsourcing_equipamento', ['id' => (int)$id]);
}

function obterMaquinasAtivas($contratos) {
    return (int)erp_api('contrato.qtd_maquinas', ['contratos' => array_values((array)$contratos), 'status' => 'a']);
}

function obterMaquinasInativas($contratos) {
    return (int)erp_api('contrato.qtd_maquinas', ['contratos' => array_values((array)$contratos), 'status' => 'i']);
}

function obterContratoHospedagem($contrato_id) {
    return erp_api('contrato.hospedagem', ['id_contrato' => (int)$contrato_id]);
}

function obterContratoHospedagemPorId($id) {
    return erp_api('contrato.hospedagem_por_id', ['id' => (int)$id]);
}

function obterContratoBackup($contrato_id) {
    return erp_api('contrato.backup', ['id_contrato' => (int)$contrato_id]);
}

function obterContratoBackupPorId($id) {
    return erp_api('contrato.backup_por_id', ['id' => (int)$id]);
}
