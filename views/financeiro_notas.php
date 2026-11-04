<?php
include 'header.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
include __DIR__ . '/partials/botoes_icone.php';
?>
<style>
.btn-pdf { color: #dc3545; }
.btn-pdf:hover { background: #dc3545; border-color: #dc3545; color: #fff; }
.btn-xml { color: #0168fa; font-size: 10px; font-weight: 700; }
.btn-xml:hover { background: #0168fa; border-color: #0168fa; color: #fff; }
.nota-status { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.nota-status.ok { background: #e6f4ea; color: #1e7e34; }
.nota-status.cancelada { background: #eef0f4; color: #596882; }
.nota-status.erro { background: #fdecea; color: #c62828; }
.nota-status.pendente { background: #fff4ec; color: #d9661f; }
.nota-descricao { color: #596882; font-size: 12px; max-width: 420px; }
</style>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Notas Fiscais</h2>
    </div>
</div>
<div class="az-content-body">
    <?php if ($erro_notas): ?>
        <div class="alert alert-danger" role="alert"><?=$h($erro_notas)?></div>
    <?php endif; ?>
    <?php if ($erro_arquivo): ?>
        <div class="alert alert-warning" role="alert">Não foi possível abrir o arquivo da nota agora. Tente novamente em instantes.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Série</th>
                    <th>Emissão</th>
                    <th>Descrição</th>
                    <th class="text-right">Valor</th>
                    <th>Status</th>
                    <th>Arquivos</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$notas): ?>
                <tr><td colspan="7" class="text-center text-muted">Nenhuma nota fiscal encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($notas as $nota):
                list($statusTexto, $statusClasse) = statusNotaFiscal($nota['status'] ?? '');
                $numero = trim((string)($nota['numero_nfs'] ?? ''));
                $emissao = !empty($nota['data_emissao']) ? date('d/m/Y', strtotime($nota['data_emissao'])) : '-';
                $descricao = trim((string)($nota['descricao'] ?? ''));
            ?>
                <tr>
                    <th scope="row"><?=$h($numero !== '' ? $numero : 'Aguardando')?></th>
                    <td><?=$h($nota['serie_nfs'] ?: '-')?></td>
                    <td><?=$h($emissao)?></td>
                    <td class="nota-descricao" title="<?=$h($descricao)?>"><?=$h(mb_strimwidth($descricao, 0, 90, '…'))?></td>
                    <td class="text-right text-nowrap">R$ <?=$h(number_format((float)$nota['valor'], 2, ',', '.'))?></td>
                    <td><span class="nota-status <?=$h($statusClasse)?>"><?=$h($statusTexto)?></span></td>
                    <td>
                        <?php if (!empty($nota['tem_arquivo']) && $numero !== ''): ?>
                            <div class="chamados-acoes">
                                <a class="btn btn-icone btn-pdf" href="/financeiro_nota_arquivo?id=<?=(int)$nota['id']?>&amp;formato=pdf"
                                   target="_blank" rel="noopener" title="Abrir PDF" aria-label="Abrir PDF da nota <?=$h($numero)?>">
                                    <?=iconeInterface('documentacao', 'inline')?>
                                </a>
                                <a class="btn btn-icone btn-xml" href="/financeiro_nota_arquivo?id=<?=(int)$nota['id']?>&amp;formato=xml"
                                   title="Baixar XML" aria-label="Baixar XML da nota <?=$h($numero)?>">XML</a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_paginas > 1):
        $inicio_janela = max(1, min($pagina_atual - 4, $total_paginas - 9));
        $paginas_visiveis = range($inicio_janela, min($total_paginas, $inicio_janela + 9));
    ?>
        <nav class="mt-4" aria-label="Paginação das notas fiscais">
            <ul class="pagination justify-content-center chamados-fechados-pagination">
                <li class="page-item <?=$pagina_atual <= 1 ? 'disabled' : ''?>">
                    <a class="page-link" href="/financeiro_notas?pagina=<?=max(1, $pagina_atual - 1)?>" aria-label="Página anterior" title="Página anterior">&lt;</a>
                </li>
                <?php foreach ($paginas_visiveis as $n): ?>
                    <li class="page-item <?=$n === $pagina_atual ? 'active' : ''?>">
                        <a class="page-link" href="/financeiro_notas?pagina=<?=$n?>" <?=$n === $pagina_atual ? 'aria-current="page"' : ''?>><?=$n?></a>
                    </li>
                <?php endforeach; ?>
                <li class="page-item <?=$pagina_atual >= $total_paginas ? 'disabled' : ''?>">
                    <a class="page-link" href="/financeiro_notas?pagina=<?=min($total_paginas, $pagina_atual + 1)?>" aria-label="Próxima página" title="Próxima página">&gt;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php if ($total_notas > 0): ?>
        <p class="text-center text-muted chamados-fechados-paginacao-info">Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_notas?> notas</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
