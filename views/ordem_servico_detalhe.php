<?php
include 'header.php';
$h = static function ($v) { return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8'); };
$texto = static function ($v) use ($h) { $v = $h(stripslashes((string)$v)); return $v !== '' ? nl2br($v) : '-'; };
$data = static function ($v, $comHora = false) {
    $t = strtotime((string)$v);
    return $v && $t ? date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $t) : '-';
};
$moeda = static function ($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); };
$os = $detalhe['os'];
$equipamento = $detalhe['equipamento'];
$visita = $detalhe['visita'];
$eventos = $detalhe['eventos'];
$produtos = $detalhe['produtos'];
$voltar = '/ordens_servico?filtro=' . $filtro . '&pagina=' . (int)$pagina_retorno;
?>
<style>
.os-dados { display: grid; grid-template-columns: 200px 1fr; gap: 6px 16px; margin: 0; }
.os-dados dt { color: #596882; font-weight: 600; text-align: right; }
.os-dados dd { margin: 0; color: #1c273c; word-break: break-word; }
@media (max-width: 576px) { .os-dados { grid-template-columns: 1fr; } .os-dados dt { text-align: left; } }
.os-card { margin-bottom: 20px; }
.os-card .card-header { font-weight: 700; background: #f5f7fb; }
</style>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Ordem de Serviço <?=(int)$os['id']?></h2>
        <p class="mg-b-0"><?=$h($os['status'])?> · aberta em <?=$h($data($os['data_abertura']))?></p>
    </div>
    <div class="mg-t-10 mg-md-t-0">
        <a href="<?=$h($voltar)?>" class="btn btn-outline-secondary"><?=iconeInterface('voltar', 'inline')?> Voltar</a>
    </div>
</div>
<div class="az-content-body">
    <div class="card os-card">
        <div class="card-header">Dados da ordem de serviço</div>
        <div class="card-body">
            <dl class="os-dados">
                <dt>Cliente:</dt><dd><?=$texto($os['cliente'])?></dd>
                <dt>Endereço:</dt><dd><?=$texto($os['endereco'])?></dd>
                <dt>Telefone:</dt><dd><?=$h(function_exists('formata_telefone') ? formata_telefone($os['telefone']) : $os['telefone']) ?: '-'?></dd>
                <dt>Responsável abertura:</dt><dd><?=$texto($os['responsavel_abertura'])?></dd>
                <dt>Vendedor:</dt><dd><?=$texto($os['vendedor'])?></dd>
                <dt>Tipo:</dt><dd><?=$texto($os['tipo'])?></dd>
                <dt>Valor:</dt><dd><?=$h($moeda($os['valor']))?></dd>
                <dt>Obs:</dt><dd><?=$texto($os['obs'])?></dd>
            </dl>
        </div>
    </div>

    <?php if ($equipamento): ?>
    <div class="card os-card">
        <div class="card-header">Equipamento</div>
        <div class="card-body">
            <dl class="os-dados">
                <dt>Fabricante:</dt><dd><?=$texto($equipamento['fabricante'])?></dd>
                <dt>Equipamento:</dt><dd><?=$texto($equipamento['equipamento'])?></dd>
                <dt>Acessórios:</dt><dd><?=$texto($equipamento['acessorios'])?></dd>
                <dt>Avarias:</dt><dd><?=$texto($equipamento['avarias'])?></dd>
                <dt>Número de série:</dt><dd><?=$texto($equipamento['numero_serie'])?></dd>
                <dt>Defeito:</dt><dd><?=$texto($equipamento['defeito'])?></dd>
            </dl>
        </div>
    </div>
    <?php elseif ($visita): ?>
    <div class="card os-card">
        <div class="card-header">Detalhes da visita</div>
        <div class="card-body">
            <dl class="os-dados">
                <dt>Data do atendimento:</dt><dd><?=$h($data($visita['data_evento']))?></dd>
                <dt>Período:</dt><dd><?=$texto($visita['periodo'])?></dd>
                <dt>Descrição:</dt><dd><?=$texto($visita['descricao'])?></dd>
            </dl>
        </div>
    </div>
    <?php endif; ?>

    <div class="card os-card">
        <div class="card-header">Eventos da ordem de serviço</div>
        <div class="table-responsive">
            <table class="table mg-b-0">
                <thead>
                    <tr><th>Código</th><th>Data</th><th>Hora</th><th>Técnico</th><th>Descrição</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><?=(int)$evento['id']?></td>
                            <td class="text-nowrap"><?=$h($data($evento['data']))?></td>
                            <td class="text-nowrap"><?=$h(substr((string)$evento['hora'], 0, 8))?></td>
                            <td><?=$texto($evento['tecnico'])?></td>
                            <td><?=$texto($evento['descricao'])?></td>
                            <td><?=$texto($evento['status'])?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$eventos): ?>
                        <tr><td colspan="6" class="text-center text-muted">Nenhum evento registrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($produtos):
        $totalProdutos = 0;
    ?>
    <div class="card os-card">
        <div class="card-header">Produtos da ordem de serviço</div>
        <div class="table-responsive">
            <table class="table mg-b-0">
                <thead>
                    <tr><th>Item</th><th>Quant.</th><th>Produto</th><th class="text-right">Valor</th><th class="text-right">Valor total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $i => $produto):
                        $quantidade = (float)$produto['quantidade'];
                        $subtotal = $quantidade * (float)$produto['valor'];
                        $totalProdutos += $subtotal;
                    ?>
                        <tr>
                            <td><?=$i + 1?></td>
                            <td><?=$h(rtrim(rtrim(number_format($quantidade, 2, ',', ''), '0'), ','))?></td>
                            <td><?=$texto($produto['produto'])?></td>
                            <td class="text-right text-nowrap"><?=$h($moeda($produto['valor']))?></td>
                            <td class="text-right text-nowrap"><?=$h($moeda($subtotal))?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-right">Total dos produtos</th><th class="text-right text-nowrap"><?=$h($moeda($totalProdutos))?></th></tr>
                </tfoot>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
