<?php
include 'header.php';
include __DIR__ . '/partials/botoes_icone.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$data = static function ($v) {
    $t = strtotime((string)$v);
    return $v && $t ? date('d/m/Y', $t) : '-';
};
?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Ordens de serviço <?=$filtro === 'fechadas' ? 'fechadas' : 'abertas'?></h2>
    </div>
</div>
<div class="az-content-body">
    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <th scope="col">OS</th>
                    <th scope="col">Abertura</th>
                    <th scope="col"><?=$filtro === 'fechadas' ? 'Fechamento' : 'Atualização'?></th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Equipamento / serviço</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Opções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordens as $os):
                    $resumo = trim((string)($os['equipamento'] ?? '')) ?: trim((string)($os['visita'] ?? ''));
                    $defeito = trim((string)($os['defeito'] ?? ''));
                ?>
                    <tr>
                        <th scope="row"><?=(int)$os['id']?></th>
                        <td class="text-nowrap"><?=$h($data($os['data_abertura']))?></td>
                        <td class="text-nowrap"><?=$h($data($os['data_atualizacao']))?></td>
                        <td><?=$h($os['tipo'] ?? '')?></td>
                        <td class="chamados-detalhes">
                            <?=$h($resumo !== '' ? $resumo : '-')?>
                            <?php if ($defeito !== ''): ?><br><small><?=$h(mb_strimwidth($defeito, 0, 90, '…'))?></small><?php endif; ?>
                        </td>
                        <td class="text-nowrap">R$ <?=number_format((float)$os['valor'], 2, ',', '.')?></td>
                        <td><?=$h($os['status'] ?? '')?></td>
                        <td>
                            <div class="chamados-acoes">
                                <a class="btn btn-icone btn-interagir" href="/ordens_servico?id=<?=(int)$os['id']?>&amp;pagina=<?=(int)$pagina_atual?>"
                                   title="Ver detalhes" aria-label="Ver detalhes da OS <?=(int)$os['id']?>"><?=iconeInterface('detalhes', 'inline')?></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$ordens): ?>
                    <tr><td colspan="8" class="text-center text-muted">Nenhuma ordem de serviço <?=$filtro === 'fechadas' ? 'fechada' : 'aberta'?> encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_paginas > 1):
        $inicio_janela = max(1, min($pagina_atual - 4, $total_paginas - 9));
        $paginas_visiveis = range($inicio_janela, min($total_paginas, $inicio_janela + 9));
        $link_pagina = static function ($n) use ($filtro) { return '/ordens_servico?filtro=' . $filtro . '&pagina=' . (int)$n; };
    ?>
        <nav class="mt-4" aria-label="Paginação das ordens de serviço">
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
    <?php if ($total_os > 0): ?>
        <p class="text-center text-muted">Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_os?> ordens de serviço</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
