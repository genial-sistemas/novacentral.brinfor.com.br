<?php include 'header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Relatório Chamados por Equipamento</h2>
    </div>
</div>
<div class="az-content-body">
    <form action="/relatorio_equipamento" method="post">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="data_inicial">Data inicial do fechamento</label>
                    <input type="text" class="form-control" id="data_inicial" name="data_inicial" value="<?=htmlspecialchars($data_inicial_texto, ENT_QUOTES, 'UTF-8')?>" placeholder="DD/MM/AAAA" inputmode="numeric" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="data_final">Data final do fechamento</label>
                    <input type="text" class="form-control" id="data_final" name="data_final" value="<?=htmlspecialchars($data_final_texto, ENT_QUOTES, 'UTF-8')?>" placeholder="DD/MM/AAAA" inputmode="numeric" required>
                </div>
            </div>
            <?php if ($mostrar_filtro_tipo): ?>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="tipo_contrato">Tipo de contrato</label>
                    <select class="form-control" id="tipo_contrato" name="tipo_contrato">
                        <option value="0" <?=($tipo_filtro === 0 ? 'selected' : '')?>>Todos</option>
                        <option value="1" <?=($tipo_filtro === 1 ? 'selected' : '')?>>Helpdesk</option>
                        <option value="7" <?=($tipo_filtro === 7 ? 'selected' : '')?>>Locação</option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div class="<?=$mostrar_filtro_tipo ? 'col-md-3' : 'col-md-4'?>">
                <div class="form-group">
                    <label for="equipamento">Equipamento</label>
                    <select class="form-control" id="equipamento" name="equipamento" required>
                        <option value="todos" <?=($referencia === 'todos' ? 'selected' : '')?>>Todos os equipamentos</option>
                        <?php foreach ($equipamentos as $equipamento): ?>
                            <option value="<?=htmlspecialchars($equipamento['referencia'], ENT_QUOTES, 'UTF-8')?>" data-tipo="<?=(int)$equipamento['id_tipo']?>" <?=($referencia === $equipamento['referencia'] ? 'selected' : '')?>>
                                <?=htmlspecialchars($equipamento['rotulo'], ENT_QUOTES, 'UTF-8')?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="<?=$mostrar_filtro_tipo ? 'col-md-1' : 'col-md-2'?>">
                <div class="form-group">
                    <label></label>
                    <button type="submit" class="btn btn-az-primary relatorio-listar" name="acao" value="filtrar">Listar</button>
                </div>
            </div>
        </div>
    </form>
    <?php if ($mostrar_filtro_tipo): ?>
    <script>
    // Mostra no campo Equipamento só os equipamentos do tipo de contrato escolhido.
    (function () {
        var tipo = document.getElementById('tipo_contrato');
        var equipamento = document.getElementById('equipamento');
        function filtrar() {
            var escolhido = tipo.value;
            for (var i = 0; i < equipamento.options.length; i++) {
                var opcao = equipamento.options[i];
                if (!opcao.dataset.tipo) continue; // "Todos os equipamentos"
                var visivel = escolhido === '0' || opcao.dataset.tipo === escolhido;
                opcao.hidden = !visivel;
                opcao.disabled = !visivel;
                if (!visivel && opcao.selected) equipamento.value = 'todos';
            }
        }
        tipo.addEventListener('change', filtrar);
        filtrar();
    })();
    </script>
    <?php endif; ?>

    <?php if ($erro_relatorio !== null): ?>
        <div class="alert alert-danger" role="alert"><?=htmlspecialchars($erro_relatorio, ENT_QUOTES, 'UTF-8')?></div>
    <?php endif; ?>

    <?php if ($relatorio_executado): ?>
    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <th class="text-center">Chamado</th>
                    <th class="text-center">Data de fechamento e horas</th>
                    <th class="text-center">Contato</th>
                    <th class="relatorio-equipamento-descricao">Descrição</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chamados as $indice => $chamado): ?>
                    <?php $interacao = $chamados_primeira_interacao[$indice] ?? array(); ?>
                    <?php $contato = trim((string)($chamado['nome'] ?? '')) ?: (string)($chamado['nome_pessoa'] ?? ''); ?>
                    <tr>
                        <td class="text-center"><?=htmlspecialchars((string)$chamado['id'], ENT_QUOTES, 'UTF-8')?></td>
                        <td class="text-center">
                            <?=htmlspecialchars(data_brasil_datetime($chamado['data_fechamento']), ENT_QUOTES, 'UTF-8')?>
                            <?php if (!empty($interacao['horas_total'])): ?>
                                <br><small><?=htmlspecialchars((string)$interacao['horas_total'], ENT_QUOTES, 'UTF-8')?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?=htmlspecialchars($contato, ENT_QUOTES, 'UTF-8')?></td>
                        <td class="relatorio-equipamento-descricao"><?=htmlspecialchars(stripslashes($interacao['descricao'] ?? ''), ENT_QUOTES, 'UTF-8')?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($relatorio_executado && !$chamados): ?>
                    <tr>
                        <td colspan="4">Nenhum chamado encontrado para o equipamento e período selecionados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
