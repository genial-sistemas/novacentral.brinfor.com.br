<?php
include 'header.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
include __DIR__ . '/partials/botoes_icone.php';
?>
<style>
.btn-pdf { color: #dc3545; }
.btn-pdf:hover { background: #dc3545; border-color: #dc3545; color: #fff; }
.boleto-status { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.boleto-status.ok { background: #e6f4ea; color: #1e7e34; }
.boleto-status.cancelada { background: #eef0f4; color: #596882; }
.boleto-status.erro { background: #fdecea; color: #c62828; }
.boleto-status.pendente { background: #fff4ec; color: #d9661f; }
.boleto-cancelado td, .boleto-cancelado th { color: #97a3b9; }
</style>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Boletos</h2>
    </div>
</div>
<div class="az-content-body">
    <?php if ($erro_boletos): ?>
        <div class="alert alert-danger" role="alert"><?=$h($erro_boletos)?></div>
    <?php endif; ?>
    <?php if ($erro_arquivo): ?>
        <div class="alert alert-warning" role="alert">Não foi possível abrir o boleto agora. Tente novamente em instantes.</div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table mg-b-0">
            <thead>
                <tr>
                    <th>Nosso número</th>
                    <th>Vencimento</th>
                    <th>Descrição</th>
                    <th>Emissão</th>
                    <th class="text-right">Valor</th>
                    <th>Opções</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$boletos): ?>
                <tr><td colspan="6" class="text-center text-muted">Nenhum boleto encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($boletos as $boleto):
                list($statusTexto, $statusClasse) = statusBoleto($boleto['status'] ?? '');
                $vencimento = !empty($boleto['data_vencimento']) ? date('d/m/Y', strtotime($boleto['data_vencimento'])) : '-';
                $emissao = !empty($boleto['data_emissao']) ? date('d/m/Y', strtotime($boleto['data_emissao'])) : '-';
                $cancelado = $statusClasse === 'cancelada';
            ?>
                <tr class="<?=$cancelado ? 'boleto-cancelado' : ''?>">
                    <th scope="row"><?=$h($boleto['nosso_numero'] ?: '-')?></th>
                    <td class="text-nowrap">
                        <?=$h($vencimento)?>
                        <br><span class="boleto-status <?=$h($statusClasse)?>"><?=$h($statusTexto)?></span>
                    </td>
                    <td><?=$h(trim((string)($boleto['descricao'] ?? '')) ?: 'Boleto avulso')?></td>
                    <td class="text-nowrap"><?=$h($emissao)?></td>
                    <td class="text-right text-nowrap">R$ <?=$h(number_format((float)$boleto['valor'], 2, ',', '.'))?></td>
                    <td>
                        <?php if (!empty($boleto['tem_arquivo']) && !$cancelado): ?>
                            <a class="btn btn-icone btn-pdf" href="/financeiro_boleto_arquivo?id=<?=(int)$boleto['id']?>"
                               target="_blank" rel="noopener" title="Abrir boleto (PDF)" aria-label="Abrir boleto com vencimento em <?=$h($vencimento)?>">
                                <?=iconeInterface('documentacao', 'inline')?>
                            </a>
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
        <nav class="mt-4" aria-label="Paginação dos boletos">
            <ul class="pagination justify-content-center chamados-fechados-pagination">
                <li class="page-item <?=$pagina_atual <= 1 ? 'disabled' : ''?>">
                    <a class="page-link" href="/financeiro_boletos?pagina=<?=max(1, $pagina_atual - 1)?>" aria-label="Página anterior" title="Página anterior">&lt;</a>
                </li>
                <?php foreach ($paginas_visiveis as $n): ?>
                    <li class="page-item <?=$n === $pagina_atual ? 'active' : ''?>">
                        <a class="page-link" href="/financeiro_boletos?pagina=<?=$n?>" <?=$n === $pagina_atual ? 'aria-current="page"' : ''?>><?=$n?></a>
                    </li>
                <?php endforeach; ?>
                <li class="page-item <?=$pagina_atual >= $total_paginas ? 'disabled' : ''?>">
                    <a class="page-link" href="/financeiro_boletos?pagina=<?=min($total_paginas, $pagina_atual + 1)?>" aria-label="Próxima página" title="Próxima página">&gt;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php if ($total_boletos > 0): ?>
        <p class="text-center text-muted chamados-fechados-paginacao-info">Página <?=$pagina_atual?> de <?=$total_paginas?> · <?=$total_boletos?> boletos</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
