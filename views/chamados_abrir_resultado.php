<?php
require "models/chamado.php";
$menu   = 'Chamados';
$pagina = 'Abrir';
$id_chamado = $_SESSION['id_chamado'];
$id_seguranca = $_SESSION['id_seguranca'];
$urlInteragir = sprintf('/chamados_interagir?id=%s&seguranca=%s', $id_chamado, $id_seguranca);
include 'header.php';
?>
<body class="az-body az-body-sidebar az-light">
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
    </div>
</div><!-- az-content-header -->
    <div id="successContainer" class="az-content-body">
        <div class="row">
            <div class="col-md-12">
                <p class="mb-5 mt-3" style="font-size: 30px;">Chamado <b id="chamadoIdText"><?=$id_chamado?></b> aberto com sucesso!!</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='<?=$urlInteragir?>'">Interagir com Chamado</button>
                <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='chamados_abrir'">Novo Chamado</button>
            </div>
        </div>
    </div>
<?php
    include 'footer.php';
?>