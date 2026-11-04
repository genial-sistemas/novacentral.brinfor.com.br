<?php include 'header.php'; include __DIR__ . '/partials/botoes_icone.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Relatório Chamados por Equipamento</h2>
    </div>
</div>
<div class="az-content-body">
    <form action="/relatorio_equipamento" method="get">
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
                        <option value="" disabled <?=($referencia === '' ? 'selected' : '')?>>Selecione o equipamento</option>
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
                if (!opcao.dataset.tipo) continue; // "Selecione o equipamento"
                var visivel = escolhido === '0' || opcao.dataset.tipo === escolhido;
                opcao.hidden = !visivel;
                opcao.disabled = !visivel;
                if (!visivel && opcao.selected) equipamento.value = '';
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

    <?php if ($relatorio_executado):
        $h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
    ?>
    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <th class="text-center">Chamado</th>
                    <th class="text-center">Data de fechamento</th>
                    <th class="text-center">Horas</th>
                    <th class="text-center">Aberto por</th>
                    <th class="text-center">Finalizado por</th>
                    <th class="relatorio-equipamento-descricao">Motivo da abertura</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Opções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chamados as $chamado):
                    $horas = (string)($chamado['horas_total'] ?? '');
                ?>
                    <tr>
                        <td class="text-center">
                            <?=(int)$chamado['id']?>
                        </td>
                        <td class="text-center"><?=$h(data_brasil_datetime($chamado['data_fechamento']))?></td>
                        <td class="text-center"><?=$h($horas !== '' ? $horas : '-')?></td>
                        <td class="text-center"><?=$h(trim((string)($chamado['aberto_por'] ?? '')) ?: 'Não informado')?></td>
                        <td class="text-center"><?=$h(trim((string)($chamado['tecnico'] ?? '')) ?: 'Não informado')?></td>
                        <td class="relatorio-equipamento-descricao"><?=nl2br($h(stripslashes((string)($chamado['motivo'] ?? ''))))?></td>
                        <td class="text-center"><?=$h(($chamado['situacao'] ?? '') ?: '-')?></td>
                        <td>
                            <?php $id_chamado_botoes = (int)$chamado['id']; include __DIR__ . '/partials/botoes_chamado_relatorio.php'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$chamados): ?>
                    <tr>
                        <td colspan="7">Nenhum chamado encontrado para o equipamento e período selecionados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include __DIR__ . '/partials/modal_conversa.php'; ?>
    <?php if ($total_paginas > 1):
        $inicio_janela = max(1, min($pagina_atual - 4, $total_paginas - 9));
        $paginas_visiveis = range($inicio_janela, min($total_paginas, $inicio_janela + 9));
    ?>
        <nav class="mt-4" aria-label="Paginação do relatório">
            <ul class="pagination justify-content-center chamados-fechados-pagination">
                <li class="page-item <?=$pagina_atual <= 1 ? 'disabled' : ''?>">
                    <a class="page-link" href="<?=$h($link_pagina(max(1, $pagina_atual - 1)))?>" aria-label="Página anterior">&lt;</a>
                </li>
                <?php foreach ($paginas_visiveis as $n): ?>
                    <li class="page-item <?=$n === $pagina_atual ? 'active' : ''?>">
                        <a class="page-link" href="<?=$h($link_pagina($n))?>"><?=$n?></a>
                    </li>
                <?php endforeach; ?>
                <li class="page-item <?=$pagina_atual >= $total_paginas ? 'disabled' : ''?>">
                    <a class="page-link" href="<?=$h($link_pagina(min($total_paginas, $pagina_atual + 1)))?>" aria-label="Próxima página">&gt;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php if ($total_chamados > 0): ?>
        <p class="text-center text-muted">Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_chamados?> chamados</p>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
