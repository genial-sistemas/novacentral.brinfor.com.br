<?php
    include 'header.php';
?>
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Dashboard</h2>
        <p class="mg-b-0 tx-danger">Bem Vindo <?= $_SESSION['nome_cliente'] ?></p>
    </div>
    <div style="margin:0;">
        <form id="formPeriodo" method="post" action="/dashboard">
            <select id="periodo" name="periodo" class="form-control" onchange="enviarFormulario()">
                <?php foreach ($peridoDados as $opt): ?>
                    <option value="<?php echo($opt['valor']) ?>"<?php echo(($opt['valor'] == $selectPeriodo) ? 'selected' : '') ?>><?php echo($opt['descricao']) ?></option>
                <?php endforeach ?>
            </select>
        </form>
        <!-- Overlay escondido inicialmente -->
        <div id="overlay" style="display:none;">
            <img src="views/img/loading.gif" alt="Carregando..." />
        </div>
    </div>
</div><!-- az-content-header -->
<div class="az-content-body">
    <div class="row row-sm mg-b-10">
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $chamados_total_aberto ?></h1>
                <label class="tx-primary tx-danger">Total de chamados em aberto.</label>
                <span></span>
            </div>
            <!-- card -->
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $resultado_pesquisa['nao_avaliados'] ?></h1>
                <label class="tx-primary tx-danger">Chamados não avaliados.</label>
                <span></span>
            </div>
            <!-- card -->
        </div>
        <!-- col -->
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $maquinas_inativas ?></h1>
                <label class="tx-primary tx-danger">Máquinas inativas </label>
                <span></span>

                <!-- chart-wrapper -->
            </div>
            <!-- card -->
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= sizeof($_SESSION['contratos']); ?></h1>
                <label class="tx-primary">Contratos Ativos</label>
                <span></span>

                <!-- chart-wrapper -->
            </div>
            <!-- card -->
        </div>
    </div>
    <div class="row row-sm">
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $chamados_total ?></h1>
                <label class="tx-primary">Chamados de mês.</label>
                <span></span>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $resultado_pesquisa['total_avaliados'] ?></h1>
                <label class="tx-primary tx-success">Chamados avaliados.</label>
                <span></span>
            </div>
            <!-- card -->
        </div>
        <!-- col -->
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $maquinas_ativas ?></h1>
                <label class="tx-primary tx-success">Máquinas ativas</label>
                <span></span>

                <!-- chart-wrapper -->
            </div>
            <!-- card -->
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3 mg-t-20 mg-sm-t-0">
            <div class="card card-body card-dashboard-fifteen">
                <h1><?= $horas_trabalhadas ?></h1>
                <label class="tx-primary">Horas trabalhadas </label>
                <span></span>

                <!-- chart-wrapper -->
            </div>
            <!-- card -->
        </div>
    </div>
    <div class="row row-sm">
        <!-- col-3 -->
        <div class="col-xl-6 mg-t-15 mg-t-20">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title tx-14 mg-b-5">Avaliação de chamados</h6>
                    <!--O chart acontece dentro da div piechart_3d--->
                    <div id="piechart_3d" style="width: 500px; height: 300px; margin-left: 9%;"></div>
                </div>
            </div>
            <!-- card -->
        </div>
        <!-- col -->
        <div class="col-gl-5 col-xl-6 mg-t-20">
            <div class="card" style="height: 100%;">
                <div class="card-header">
                    <h6 class="card-title tx-14 mg-b-5">Últimos chamados</h6>
                </div>
                <!-- card-header -->
                <div class="table-responsive mg-t-15">
                    <table class="table table-striped table-talk-time">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data Abertura</th>
                                <th>Contrato</th>
                                <th>Situação</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach($obterUltimosChamados as $uc): ?>
                            <tr>
                                <td><?=$uc['id']?></td>
                                <td><?=data_brasil_datetime($uc['data_abertura'])?></td>
                                <td><?=$uc['tipo_contrato']?></td>
                                <td><?=$uc['situacao']?></td>
                            </tr>
                        <?php
                        endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <!-- table-responsive -->
            </div>
            <!-- card -->
        </div>
        <!-- col -->
        <div class="col-md-5 col-lg-5 col-xl-4 mg-t-20">
            <div class="card card-dashboard-sixteen">
                <div class="card-header">
                    <h6 class="card-title tx-14 mg-b-0">Interação por técnico nos Chamados</h6>
                </div>
                <!-- card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mg-b-0">
                            <tbody>
                            <?php foreach($interacao_tecnico as $t): ?>
                                <tr>
                                    <td>
                                        <div class="az-img-user"><i class="fa fa-2x fa-user-circle-o" aria-hidden="true" style="color: #FF8C00;"></i></div>
                                    </td>
                                    <td>
                                        <h6 class="mg-b-0 tx-inverse"><?=$t['tecnico'];?></h6>
                                        <small class="tx-11 tx-gray-500">Agent ID: </small>
                                    </td>
                                    <td>
                                        <h6 class="mg-b-0 tx-inverse"><?=$t['total']?></h6>
                                        <small class="tx-11 tx-gray-500">Total</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- table-responsive -->
                </div>
                <!-- card-body -->
            </div>
            <!-- card -->
        </div>
        <!-- col -->
        <div class="col-md-7 col-lg-7 col-xl-8 mg-t-20">
            <div class="card" style="height: 100%;">
                <div class="card-header">
                    <h6 class="card-title tx-14 mg-b-5">Contratos Ativos</h6>
                </div>
                <table class="table table-striped table-talk-time">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Contrato</th>
                            <th>Gerente de Contas</th>
                            <th>Dia Venc. Mensal</th>
                            <th>Valor Contrato</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($contratosAtivos as $ca): ?>
                        <tr>
                            <td><?=$ca['id']?></td>    
                            <td><?=$ca['tipo_contrato']?></td>
                            <td><?=$ca['gerente']?></td>
                            <td><?=$ca['dia_vencimento']?></td>
                            <td>R$ <?=number_format($ca['valor'], 2, ',', '.')?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
            <!-- card -->
        </div>
        <!-- col -->
    </div>
</div><!-- az-content-body -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load("current", {
        packages: ["corechart"]
    });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['opcoes', 'satisfação'],
            ['Satisfeito', <?=$resultado_pesquisa['satisfeito']?>],
            ['Não satisfeito', <?=$resultado_pesquisa['nao_satisfeito']?>],
            ['Não avaliados', <?=$resultado_pesquisa['nao_avaliados']?>],
            ['Muito satisfeito', <?=$resultado_pesquisa['muito_satisfeito']?>]
        ]);

        var options = {
            title: '',
            is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
    }
</script>
<script>
function enviarFormulario() {
    // Mostra o overlay com a GIF centralizada
    document.getElementById('overlay').style.display = 'flex';
    // Delay pequeno para garantir que apareça antes do reload
    setTimeout(function() {
        document.getElementById('formPeriodo').submit();
    }, 200);
}
</script>
<?php
    include 'footer.php';
?>