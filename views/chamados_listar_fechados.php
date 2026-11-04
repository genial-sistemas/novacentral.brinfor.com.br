<?php
include 'header.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
include __DIR__ . '/partials/botoes_icone.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Chamados fechados</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <?php if (!empty($erro_reabrir)): ?>
        <div class="alert alert-danger" role="alert"><?=htmlspecialchars($erro_reabrir, ENT_QUOTES, 'UTF-8')?></div>
    <?php endif; ?>
    <div class="table-responsive chamados-fechados-responsive">
        <table class="table mg-b-0 chamados-fechados-table">
            <thead>
                <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Aberto por</th>
                        <th scope="col">Detalhes</th>
                        <th scope="col">Última Interação</th>
                        <th scope="col">Urgência</th>
                        <th scope="col">Status</th>
                        <th scope="col">Opções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chamados_fechados as $cf):
                        $ultima = !empty($cf['ultima_data'])
                            ? data_brasil_datetime($cf['ultima_data'] . ' ' . $cf['ultima_hora'])
                            : '-';
                        $detalhes = trim((string)($cf['detalhes'] ?? ''), " -");
                        ?>
                        <tr>
                            <th scope="row"><?=$h($cf['id'])?></th>
                            <td><?=$h($cf['tipo_contrato'])?></td>
                            <td><?=$h(trim((string)$cf['solicitante']) !== '' ? $cf['solicitante'] : 'Não informado')?></td>
                            <td class="chamados-detalhes"><?=$h($detalhes !== '' ? $detalhes : '-')?></td>
                            <td><?=$h($ultima)?></td>
                            <td><span class="urgencia urgencia-<?=(int)($cf['id_urgencia'] ?? 0)?>"><?=$h($cf['urgencia'] ?: 'Padrão')?></span></td>
                            <td><?=$h($cf['situacao'])?></td>
                            <td>
                                <div class="chamados-acoes">
                                    <a class="btn btn-icone btn-interagir" href="/chamados_interagir?id=<?=(int)$cf['id']?>"
                                       title="Ver conversa" aria-label="Ver a conversa do chamado <?=(int)$cf['id']?>">
                                        <?=iconeInterface('conversa', 'inline')?>
                                    </a>
                                    <form action="/chamados_listar_fechados" method="post"
                                          onsubmit="return confirm('Reabrir o chamado #<?=(int)$cf['id']?>?');">
                                        <input type="hidden" name="chamado_id" value="<?=(int)$cf['id']?>">
                                        <button type="submit" class="btn btn-icone btn-reabrir" name="acao" value="reabrir_chamado"
                                                title="Reabrir chamado" aria-label="Reabrir o chamado <?=(int)$cf['id']?>">
                                            <?=iconeInterface('reabrir', 'inline')?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                <?php endforeach; ?>
                <?php if (!$chamados_fechados): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhum chamado fechado encontrado.</td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_paginas > 1):
        // Até 10 números de página, numa janela que acompanha a página atual.
        $inicio_janela = max(1, min($pagina_atual - 4, $total_paginas - 9));
        $paginas_visiveis = range($inicio_janela, min($total_paginas, $inicio_janela + 9));
        $link_pagina = static function ($n) { return '/chamados_listar_fechados?pagina=' . (int)$n; };
    ?>
        <nav class="mt-4" aria-label="Paginação dos chamados fechados">
            <ul class="pagination justify-content-center chamados-fechados-pagination">
                <li class="page-item <?=$pagina_atual <= 1 ? 'disabled' : ''?>">
                    <a class="page-link" href="<?=$link_pagina(max(1, $pagina_atual - 1))?>" aria-label="Página anterior">‹ Anterior</a>
                </li>
                <?php $anterior = 0; foreach ($paginas_visiveis as $n): ?>
                    <?php if ($anterior && $n > $anterior + 1): ?>
                        <li class="page-item disabled reticencias"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item <?=$n === $pagina_atual ? 'active' : ''?>">
                        <a class="page-link" href="<?=$link_pagina($n)?>" <?=$n === $pagina_atual ? 'aria-current="page"' : ''?>><?=$n?></a>
                    </li>
                <?php $anterior = $n; endforeach; ?>
                <li class="page-item <?=$pagina_atual >= $total_paginas ? 'disabled' : ''?>">
                    <a class="page-link" href="<?=$link_pagina(min($total_paginas, $pagina_atual + 1))?>" aria-label="Próxima página">Próxima ›</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php if ($total_chamados > 0): ?>
        <p class="text-center text-muted chamados-fechados-paginacao-info">
            Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_chamados?> chamados
        </p>
    <?php endif; ?>
</div><!-- az-content-body -->

<?php
include 'footer.php'; ?>