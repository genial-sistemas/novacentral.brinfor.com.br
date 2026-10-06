<?php
include 'header.php'; ?>

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
    <div class="row row-sm">
        <div class="col-md-12">
            <form action="/chamados_interagir" method="post">
                <div class="table-responsive">
                    <table class="table mg-b-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Última Interação</th>
                                <th>Técnico</th>
                                <th>Status</th>
                                <th>Opções</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($chamados_abertos)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Não existem chamados em aberto.</td>
                            </tr>
                        <?php else: ?>
                        <?php foreach($chamados_abertos as $ca): ?>
                            <tr>
                                <th scope="row"><?=$ca['id']?></th>
                                <td><?=$ca['tipo_contrato']?></td>
                                <td><?=data_brasil_datetime($chamados_abertos_ultima_interacao[$ca['id']]['data'].' '.$chamados_abertos_ultima_interacao[$ca['id']]['hora'])?></td>
                                <td><?=$chamados_abertos_ultima_interacao[$ca['id']]['nome_pessoa']?></td>
                                <td><?=$ca['situacao']?></td>
                                <td><button class="btn btn-az-primary" name="chamado_id" value="<?=$ca['id']?>">Interagir</button></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div><!-- az-content-body -->
<?php
include 'footer.php'; 
?>