<?php
include 'header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Chamados fechados</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <form action="chamados_listar_fechados" id="chamados_fechados" method="post">
        <div class="table-responsive">
            <table class="table mg-b-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Última Interação</th>
                        <th scope="col">Técnico</th>
                        <th scope="col">Status</th>
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
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$chamados_fechados): ?>
                        <tr>
                            <td colspan="5">Nenhum chamado fechado encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="acao" value="interacoes">
    </form>
</div><!-- az-content-body -->

<?php
include 'footer.php'; ?>