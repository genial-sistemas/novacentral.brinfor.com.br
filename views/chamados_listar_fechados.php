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
                            <td colspan="7" class="text-center text-muted">Nenhum chamado fechado encontrado.</td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_chamados > 0): ?>
        <nav class="mt-3" aria-label="Paginação dos chamados fechados">
            <ul class="pagination justify-content-center chamados-fechados-pagination">
                <li class="page-item <?=$pagina_atual <= 1 ? 'disabled' : ''?>">
                    <a class="page-link" href="/chamados_listar_fechados?pagina=<?=max(1, $pagina_atual - 1)?>" aria-label="Página anterior">Anterior</a>
                </li>
                <?php for ($pagina = 1; $pagina <= $total_paginas; $pagina++): ?>
                    <li class="page-item <?=$pagina === $pagina_atual ? 'active' : ''?>">
                        <a class="page-link" href="/chamados_listar_fechados?pagina=<?=$pagina?>" <?=$pagina === $pagina_atual ? 'aria-current="page"' : ''?>><?=$pagina?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?=$pagina_atual >= $total_paginas ? 'disabled' : ''?>">
                    <a class="page-link" href="/chamados_listar_fechados?pagina=<?=min($total_paginas, $pagina_atual + 1)?>" aria-label="Próxima página">Próxima</a>
                </li>
            </ul>
            <p class="text-center text-muted">Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_chamados?> chamados</p>
        </nav>
    <?php endif; ?>
</div><!-- az-content-body -->

<?php
include 'footer.php'; ?>