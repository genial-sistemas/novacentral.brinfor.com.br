<?php
$menu   = 'Chamados';
$pagina = 'Listar Abertos';
$chat_fixo = true;
// Dados do componente de conversa. Os controllers que incluem esta view já conferiram
// que o chamado pertence ao cliente logado.
$conversa_id = (int)$chamado['id'];
$conversa_seguranca = '';
if (empty($_SESSION['csrf_conversa'])) {
    $_SESSION['csrf_conversa'] = bin2hex(random_bytes(32));
}
$conversa_token = $_SESSION['csrf_conversa'];
$data_abertura = $data_abertura ?? date('d/m/Y', strtotime($chamado['data_abertura']));
include 'header.php'
?>
<div class="az-content-body chat-interacoes-page">
    <div class="row row-sm chat-interacoes-info">
        <div class="col-md-12">
            <div class="card card-body card-dashboard-fifteen">
                <div class="row mg-b-10">
                    <div class="col-md-4">
                        <b>Chamado</b>: <?=htmlspecialchars((string)$chamado['id'], ENT_QUOTES, 'UTF-8')?> (<span data-conversa="tipo"><?=htmlspecialchars((string)$chamado['tipo_contrato'], ENT_QUOTES, 'UTF-8')?></span>)<br />
                        <b>Técnico responsável</b>: <span data-conversa="tecnico">…</span>
                    </div>
                    <div class="col-md-4">
                        <b>Última interação</b>: <span data-conversa="ultima">…</span><br />
                        <b>Status</b>: <span data-conversa="situacao"><?=htmlspecialchars((string)$chamado['situacao'], ENT_QUOTES, 'UTF-8')?></span>
                    </div>
                    <div class="col-md-4">
                        <b>Data abertura</b>: <?=htmlspecialchars($data_abertura, ENT_QUOTES, 'UTF-8')?><br />
                        <b>Equipamento</b>: <span data-conversa="equipamento">…</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-sm chat-interacoes-conversa">
        <div class="col-md-12 chat-interacoes-coluna" style="min-height: 60vh;">
            <?php include __DIR__ . '/partials/conversa.php'; ?>
        </div>
    </div>
</div><!-- az-content-body -->
<?php
include 'footer.php'; ?>
