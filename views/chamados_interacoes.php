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
    <?php $h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }; ?>
    <style>
    /* Cartão próprio: o tema (card-dashboard-fifteen) transformava cada <span> em bloco. */
    .chamado-info { background: #fff; border: 1px solid #e3e7ed; border-radius: 8px; padding: 14px 20px; margin-bottom: 14px;
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px 24px; }
    /* Rótulo e valor na mesma linha ("Chamado: #123 · Helpdesk"). */
    .chamado-info dl { display: grid; grid-template-columns: max-content minmax(0, 1fr); gap: 6px 6px; margin: 0; align-items: baseline; }
    .chamado-info dt { font-size: 13px; font-weight: 700; color: #1c273c; margin: 0; white-space: nowrap; }
    .chamado-info dt::after { content: ':'; }
    .chamado-info dd { font-size: 13px; color: #596882; margin: 0; overflow-wrap: anywhere; }
    .chamado-info .situacao-etiqueta { display: inline-block; background: #fff4ec; color: #d9661f; border-radius: 10px; padding: 1px 10px; font-size: 12px; font-weight: 600; }
    /* A página tem a altura da janela: a conversa ocupa o espaço que sobra e só a lista de mensagens rola,
       mantendo o campo de digitar sempre visível acima do rodapé. */
    .chat-interacoes-conversa { display: flex; }
    .chat-interacoes-coluna { flex: 1 1 auto; height: 100%; }
    .chat-interacoes-coluna > .conversa { flex: 1 1 auto; width: 100%; min-width: 0; height: 100%; min-height: 0; }
    @media (max-width: 767.98px) { .chamado-info { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 479.98px) { .chamado-info { grid-template-columns: 1fr; } }
    </style>
    <div class="chat-interacoes-info">
        <section class="chamado-info" aria-label="Dados do chamado">
            <dl>
                <dt>Chamado</dt>
                <dd>#<?=$h($chamado['id'])?></dd>
                <dt>Tipo Chamado</dt>
                <dd data-conversa="tipo"><?=$h($chamado['tipo_contrato'])?></dd>
                <dt>Tipo Atendimento</dt>
                <dd data-conversa="atendimento">…</dd>
                <dt>Aberto por</dt>
                <dd><?=$h(trim((string)($chamado['nome'] ?? '')) !== '' ? $chamado['nome'] : 'Não informado')?></dd>
                <dt>Técnico responsável</dt>
                <dd data-conversa="tecnico">…</dd>
            </dl>
            <dl>
                <dt>Data abertura</dt>
                <dd><?=$h($data_abertura)?></dd>
                <dt>Detalhes</dt>
                <dd data-conversa="equipamento">…</dd>
            </dl>
            <dl>
                <dt>Última interação</dt>
                <dd data-conversa="ultima">…</dd>
                <dt>Status</dt>
                <dd><span class="situacao-etiqueta" data-conversa="situacao"><?=$h($chamado['situacao'])?></span></dd>
            </dl>
        </section>
    </div>
    <div class="row row-sm chat-interacoes-conversa">
        <div class="col-md-12 chat-interacoes-coluna">
            <?php include __DIR__ . '/partials/conversa.php'; ?>
        </div>
    </div>
</div><!-- az-content-body -->
<?php
include 'footer.php'; ?>
