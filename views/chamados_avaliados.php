<?php
include 'header.php';
include __DIR__ . '/partials/botoes_icone.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$notas = [3 => ['Muito satisfeito', 'avaliacao-3'], 2 => ['Satisfeito', 'avaliacao-2'], 1 => ['Não satisfeito', 'avaliacao-1']];
?>
<style>
.avaliacao { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; white-space: nowrap; background: #eef0f4; color: #596882; }
.avaliacao-3 { background: #e6f6e0; color: #2e7d0f; }
.avaliacao-2 { background: #e8f0fe; color: #0b57d0; }
.avaliacao-1 { background: #fdecea; color: #c62828; }
</style>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Chamados avaliados</h2>
    </div>
</div>
<div class="az-content-body">
    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Data da avaliação</th>
                    <th scope="col">Aberto por</th>
                    <th scope="col">Finalizado por</th>
                    <th scope="col">Avaliação</th>
                    <th scope="col">Atendido</th>
                    <th scope="col">Comentário</th>
                    <th scope="col">Status</th>
                    <th scope="col">Opções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chamados_avaliados as $c):
                    $nota = $notas[(int)$c['nota']] ?? ['Não informada', ''];
                    $atendido = strtolower((string)$c['atendido']);
                    $data = (string)($c['data_avaliacao'] ?? '');
                    $comentario = trim(stripslashes((string)($c['comentario'] ?? '')));
                ?>
                    <tr>
                        <th scope="row"><?=(int)$c['id']?></th>
                        <td><?=$h($c['tipo_contrato'] ?? '')?></td>
                        <td class="text-nowrap"><?=$h($data !== '' ? date('d/m/Y', strtotime($data)) : '-')?></td>
                        <td><?=$h(trim((string)($c['aberto_por'] ?? '')) ?: 'Não informado')?></td>
                        <td><?=$h(trim((string)($c['tecnico'] ?? '')) ?: 'Não informado')?></td>
                        <td><span class="avaliacao <?=$nota[1]?>"><?=$h($nota[0])?></span></td>
                        <td><?=$atendido === 's' ? 'Sim' : ($atendido === 'n' ? 'Não' : '-')?></td>
                        <td class="chamados-detalhes"><?=nl2br($h($comentario !== '' ? $comentario : '-'))?></td>
                        <td><?=$h($c['situacao'] ?? '')?></td>
                        <td>
                            <?php if ((int)$c['id_situacao'] === 7): ?>
                                <?php $id_chamado_botoes = (int)$c['id']; include __DIR__ . '/partials/botoes_chamado_relatorio.php'; ?>
                            <?php else: // reaberto: interage pela tela normal ?>
                                <div class="chamados-acoes">
                                    <a class="btn btn-icone btn-interagir" href="/chamados_interagir?id=<?=(int)$c['id']?>"
                                       title="Interagir" aria-label="Interagir no chamado <?=(int)$c['id']?>"><?=iconeInterface('conversa', 'inline')?></a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$chamados_avaliados): ?>
                    <tr><td colspan="10" class="text-center text-muted">Nenhum chamado avaliado encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_paginas > 1):
        $inicio_janela = max(1, min($pagina_atual - 4, $total_paginas - 9));
        $paginas_visiveis = range($inicio_janela, min($total_paginas, $inicio_janela + 9));
        $link_pagina = static function ($n) { return '/chamados_avaliados?pagina=' . (int)$n; };
    ?>
        <nav class="mt-4" aria-label="Paginação dos chamados avaliados">
            <ul class="pagination justify-content-center chamados-fechados-pagination">
                <li class="page-item <?=$pagina_atual <= 1 ? 'disabled' : ''?>">
                    <a class="page-link" href="<?=$link_pagina(max(1, $pagina_atual - 1))?>" aria-label="Página anterior">&lt;</a>
                </li>
                <?php foreach ($paginas_visiveis as $n): ?>
                    <li class="page-item <?=$n === $pagina_atual ? 'active' : ''?>">
                        <a class="page-link" href="<?=$link_pagina($n)?>"><?=$n?></a>
                    </li>
                <?php endforeach; ?>
                <li class="page-item <?=$pagina_atual >= $total_paginas ? 'disabled' : ''?>">
                    <a class="page-link" href="<?=$link_pagina(min($total_paginas, $pagina_atual + 1))?>" aria-label="Próxima página">&gt;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php if ($total_chamados > 0): ?>
        <p class="text-center text-muted">Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_chamados?> chamados</p>
    <?php endif; ?>
    <?php include __DIR__ . '/partials/modal_conversa.php'; ?>
</div>

<?php include 'footer.php'; ?>
