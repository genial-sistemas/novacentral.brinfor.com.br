<?php
include 'header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Chamados fechados</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <?php if (!empty($erro_reabrir)): ?>
        <div class="alert alert-danger" role="alert"><?=htmlspecialchars($erro_reabrir, ENT_QUOTES, 'UTF-8')?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Última Interação</th>
                        <th scope="col">Técnico</th>
                        <th scope="col">Status</th>
                        <th scope="col">Opções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chamados_fechados as $cf): ?>
                        <?php
                        $ultima_interacao = $chamados_fechados_ultima_interacao[$cf['id']] ?? array();
                        $data_hora_interacao = trim(($ultima_interacao['data'] ?? '') . ' ' . ($ultima_interacao['hora'] ?? ''));
                        ?>
                        <tr>
                            <th scope="row"><?=htmlspecialchars((string)$cf['id'], ENT_QUOTES, 'UTF-8')?></th>
                            <td><?=htmlspecialchars((string)$cf['tipo_contrato'], ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars($data_hora_interacao !== '' ? data_brasil_datetime($data_hora_interacao) : '-', ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars((string)($ultima_interacao['nome_pessoa'] ?? '-'), ENT_QUOTES, 'UTF-8')?></td>
                            <td><?=htmlspecialchars((string)$cf['situacao'], ENT_QUOTES, 'UTF-8')?></td>
                            <td class="text-nowrap">
                                <form action="/chamados_listar_fechados" method="post" class="d-inline mr-2">
                                    <input type="hidden" name="chamado_id" value="<?=htmlspecialchars((string)$cf['id'], ENT_QUOTES, 'UTF-8')?>">
                                    <button type="submit" class="btn btn-link p-0" style="color:#e89928;font-size:20px;line-height:1" name="acao" value="detalhes" title="Detalhes" aria-label="Ver detalhes do chamado">
                                        <i class="fas fa-list-ul" aria-hidden="true"></i>
                                    </button>
                                </form>
                                <form action="/chamados_listar_fechados" method="post" class="d-inline">
                                    <input type="hidden" name="chamado_id" value="<?=htmlspecialchars((string)$cf['id'], ENT_QUOTES, 'UTF-8')?>">
                                    <button type="submit" class="btn btn-link p-0" style="color:#e89928;font-size:20px;line-height:1" name="acao" value="reabrir_chamado" title="Reabrir chamado" aria-label="Reabrir chamado">
                                        <i class="fas fa-envelope" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                <?php endforeach; ?>
                <?php if (!$chamados_fechados): ?>
                        <tr>
                            <td colspan="6">Nenhum chamado fechado encontrado.</td>
                        </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_chamados > 0): ?>
        <nav class="mt-3" aria-label="Paginação dos chamados fechados">
            <ul class="pagination justify-content-center">
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