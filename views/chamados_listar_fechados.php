<?php
include 'header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Chamados fechados</h2>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <div class="row row-sm">
        <div class="col-md-12">
            <form action="chamados_listar_fechados" id="chamados_fechados" method="post">
                <div id="carregando">
                    <p>Carregando dados aguarde...</p>
                </div>
                <table id="datatable1" class="display responsive nowrap">
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
                        <?php
                        foreach ($chamados_fechados as $cf) : ?>
                            <tr>
                                <th scope="row"><?= $cf['id'] ?></th>
                                <td><?= $cf['tipo_contrato'] ?></td>
                                <td><?= data_brasil_datetime($chamados_fechados_ultima_interacao[$cf['id']]['data'] . ' ' . $chamados_fechados_ultima_interacao[$cf['id']]['hora']) ?></td>
                                <td><?= $chamados_fechados_ultima_interacao[$cf['id']]['nome_pessoa'] ?></td>
                                <td><?= $cf['situacao'] ?></td>
                                <td>
									<button class="btn btn-az-primary" name="chamado_id" value="<?= $cf['id'] ?>">Interacoes</button>
								</td>
                            </tr>
                        <?php
                        endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="acao" value="interacoes">
            </form>
        </div>
    </div>
</div><!-- az-content-body -->
<link href="views/lib/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="views/lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css" rel="stylesheet">
<link href="views/lib/select2/css/select2.min.css" rel="stylesheet">
<script src="views/lib/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="views/lib/datatables.net-dt/js/dataTables.dataTables.min.js"></script>
<script src="views/lib/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="views/lib/datatables.net-responsive-dt/js/responsive.dataTables.min.js"></script>
<script src="views/lib/select2/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        'use strict';

        $('#datatable1').DataTable({
            responsive: true,
            language: {
                searchPlaceholder: 'Buscar...',
                sSearch: '',
                lengthMenu: '_MENU_ items/página',
            },
            "order": [[ 1, "desc" ]]
        });

        // Select2
        $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity });

        $('#carregando').hide();
    });
</script>

<?php
include 'footer.php'; ?>