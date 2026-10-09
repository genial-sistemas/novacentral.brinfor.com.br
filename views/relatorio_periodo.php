<?php
include 'header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Relatório de Chamados Fechados Por Período</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    
    <form action="relatorio_periodo" method="post">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="data_inicial">Data Inicial</label>
                    <input type="text" class="form-control" id="data_inicial" name="data_inicial" value="<?=htmlspecialchars($data_inicial_texto, ENT_QUOTES, 'UTF-8')?>" placeholder="DD/MM/AAAA" inputmode="numeric" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="data_final">Data Final</label>
                    <input type="text" class="form-control" id="data_final" name="data_final" value="<?=htmlspecialchars($data_final_texto, ENT_QUOTES, 'UTF-8')?>" placeholder="DD/MM/AAAA" inputmode="numeric" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Contrato</label>
                    <select class="form-control" name="contrato" required>
                        <option value="" <?=($contrato_id_texto === '' ? 'selected' : '')?>>Selecione:</option>
                    <?php
                    foreach($contratos as $c): ?>
                        <option value="<?=$c['id']?>" <?=(string)$c['id'] === (string)$contrato_id_texto ? 'selected' : ''?>>
                            <?=$c['id'].' - '.$c['tipo_nome'];?>
                        </option>
                    <?php
                    endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label></label>
                    <button class="btn btn-az-primary relatorio-listar" name="acao" value="filtrar">Listar</button>
                </div>
            </div>
        </div>
    </form>
    <?php if ($erro_relatorio !== null): ?>
        <div class="alert alert-danger" role="alert"><?=htmlspecialchars($erro_relatorio, ENT_QUOTES, 'UTF-8')?></div>
    <?php endif; ?>
    <?php if ($relatorio_executado): ?>
    <div class="row row-sm">
        <div class="col-md-12">
            <div class="table-responsive">
                    <table class="table mg-b-0">
                        <thead>
                            <tr>
                                <th>Chamado</th>
                                <th class="width-170">Data e Hora</th>
                                <th class="width-120">Horas Totais</th>
                                <th class="width-170">Resp. Abertura</th>
                                <th>Descrição</th>
                                <th class="text-center">Opções</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach($chamados as $k => $c): ?>
                            <tr>
                                <td><?=htmlspecialchars((string)$c['id'], ENT_QUOTES, 'UTF-8')?></td>
                                <td><?=htmlspecialchars(data_brasil_datetime($c['data_fechamento']), ENT_QUOTES, 'UTF-8')?></td>
                                <td><?=htmlspecialchars((string)$c['horas_totais'], ENT_QUOTES, 'UTF-8')?></td>
                                <td><?=htmlspecialchars((string)($c['nome_pessoa'] ?? 'Não informado'), ENT_QUOTES, 'UTF-8')?></td>
                                <td><?=htmlspecialchars(stripslashes((string)($chamados_primeira_interacao[$k]['descricao'] ?? '')), ENT_QUOTES, 'UTF-8')?></td>
                                <td class="text-center text-nowrap">
                                    <form action="/relatorio_periodo" method="post" class="d-inline">
                                        <input type="hidden" name="chamado_id" value="<?=htmlspecialchars((string)$c['id'], ENT_QUOTES, 'UTF-8')?>">
                                        <button type="submit" class="btn btn-link p-0 chamados-fechados-acao" name="acao" value="detalhes" title="Detalhes" aria-label="Ver detalhes do chamado">
                                            <?=iconeInterface('detalhes', 'acao')?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php
                        endforeach;?>
                        <?php if (!$chamados): ?>
                            <tr><td colspan="6">Nenhum chamado fechado encontrado para o contrato e período selecionados.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div><!-- az-content-body -->

<?php
include 'footer.php'; ?>