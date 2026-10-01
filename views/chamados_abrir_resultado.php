<?php
require "models/chamado.php";
$menu = 'Chamados';
$pagina = 'Abrir';

// Validar sessão
if (!isset($_SESSION['id_chamado']) || !isset($_SESSION['id_seguranca'])) {
    header('Location: chamados_abrir');
    exit;
}

$id_chamado = htmlspecialchars($_SESSION['id_chamado'], ENT_QUOTES, 'UTF-8');
$id_seguranca = htmlspecialchars($_SESSION['id_seguranca'], ENT_QUOTES, 'UTF-8');
$urlInteragir = sprintf('/chamados_interagir?id=%s&seguranca=%s', $id_chamado, $id_seguranca);

include 'header.php';
?>
<body class="az-body az-body-sidebar az-light">
<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
    </div>
</div>
<div id="successContainer" class="az-content-body">
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">Chamado #<?= $id_chamado ?> aberto com sucesso!</h4>
                <p>Seu chamado foi registrado e está aguardando atendimento.</p>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='<?= $urlInteragir ?>'">
                Interagir com Chamado
            </button>
            <button class="btn btn-az-secondary pd-x-20" onclick="window.location.href='chamados_abrir'">
                Novo Chamado
            </button>
        </div>
    </div>
</div>
<?php
include 'footer.php';
?>