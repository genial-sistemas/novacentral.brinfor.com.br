<?php
include 'header.php';
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<?php include __DIR__ . '/partials/botoes_icone.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Chamados em aberto</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <?php if ($chamado_reaberto): ?>
        <div class="alert alert-success" role="status">Chamado reaberto com sucesso.</div>
    <?php endif; ?>
    <?php if ($email_reabertura_falhou): ?>
        <div class="alert alert-warning" role="alert">O chamado foi reaberto, mas não foi possível enviar todas as notificações por e-mail.</div>
    <?php endif; ?>
    <?php if ($chamado_encerrado > 0): ?>
        <div class="alert alert-success" role="status">Chamado #<?=$chamado_encerrado?> encerrado. Ele está agora em Listar Fechados.</div>
    <?php endif; ?>
    <?php if ($erro_encerrar): ?>
        <div class="alert alert-danger" role="alert">Não foi possível encerrar o chamado. Recarregue a página e tente novamente.</div>
    <?php endif; ?>
    <div class="row row-sm">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table mg-b-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Aberto por</th>
                            <th>Detalhes</th>
                            <th>Última Interação</th>
                            <th>Status</th>
                            <th>Opções</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($chamados_abertos)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Não existem chamados em aberto.</td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($chamados_abertos as $ca):
                        $ultima = !empty($ca['ultima_data'])
                            ? data_brasil_datetime($ca['ultima_data'] . ' ' . $ca['ultima_hora'])
                            : '-';
                        $detalhes = trim((string)($ca['detalhes'] ?? ''), " -");
                    ?>
                        <tr>
                            <th scope="row"><?=$h($ca['id'])?></th>
                            <td><?=$h($ca['tipo_contrato'])?></td>
                            <td><?=$h(trim((string)$ca['solicitante']) !== '' ? $ca['solicitante'] : 'Não informado')?></td>
                            <td class="chamados-detalhes"><?=$h($detalhes !== '' ? $detalhes : '-')?></td>
                            <td><?=$h($ultima)?></td>
                            <td><?=$h($ca['situacao'])?></td>
                            <td>
                                <div class="chamados-acoes">
                                    <a class="btn btn-icone btn-interagir" href="/chamados_interagir?id=<?=(int)$ca['id']?>"
                                       title="Interagir" aria-label="Interagir com o chamado <?=(int)$ca['id']?>">
                                        <?=iconeInterface('conversa', 'inline')?>
                                    </a>
                                    <form method="post" action="/chamados_listar_abertos"
                                          onsubmit="return confirm('Encerrar o chamado #<?=(int)$ca['id']?>? Depois de encerrado, não será possível enviar novas mensagens.');">
                                        <input type="hidden" name="acao" value="encerrar">
                                        <input type="hidden" name="chamado_id" value="<?=(int)$ca['id']?>">
                                        <input type="hidden" name="token" value="<?=$h($token_chamados)?>">
                                        <button type="submit" class="btn btn-icone btn-encerrar"
                                                title="Encerrar chamado" aria-label="Encerrar o chamado <?=(int)$ca['id']?>">
                                            <?=iconeInterface('encerrar', 'inline')?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- az-content-body -->
<?php
include 'footer.php';
?>
