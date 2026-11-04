<?php
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <meta name="robots" content="noindex, nofollow">
    <title>Interagir Chamado</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; color: #1a202c; min-height: 100vh; display: flex; flex-direction: column; }
        .topo { background: #fff; border-bottom: 2px solid #e2e8f0; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .topo-conteudo { max-width: 1100px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .topo img { width: 127px; height: auto; }
        .topo h1 { font-size: 20px; font-weight: 600; margin: 0; }
        main { flex: 1; width: 100%; max-width: 1100px; margin: 0 auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; }
        .cartao { background: #fff; border: 1px solid #e9edf2; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.04); padding: 16px 20px; }
        .info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px 24px; }
        .info .rotulo { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #718096; }
        .info .valor { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; word-break: break-word; }
        .situacao { display: inline-block; background: #fff4e5; color: #8a4b00; border-radius: 10px; padding: 2px 10px; font-size: 12px; }
        .area-conversa { flex: 1; display: flex; min-height: 60vh; }
        .area-conversa > .conversa { flex: 1; }
        .erro-visitante { text-align: center; padding: 40px 20px; }
        .erro-visitante h2 { color: #b71c1c; font-size: 18px; margin: 0 0 8px; }
        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); }
        footer { text-align: center; font-size: 12px; color: #718096; padding: 16px; }
        @media (max-width: 768px) { .info { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 480px) { .info { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="topo">
        <div class="topo-conteudo">
            <a href="https://brinfor.com.br"><img src="/views/img/brInfor_logo_mini.png" alt="BRInfor"></a>
            <h1>Interagir Chamado</h1>
        </div>
    </header>

    <main>
    <?php if ($erroVisitante !== null): ?>
        <div class="cartao erro-visitante">
            <h2><?=$h($erroVisitante)?></h2>
            <p>Se precisar, abra um novo chamado ou fale com o suporte BRInfor.</p>
        </div>
    <?php else: ?>
        <section class="cartao info" aria-label="Dados do chamado">
            <div>
                <span class="rotulo">Chamado</span>
                <span class="valor">#<span data-conversa="numero"><?=$h($chamado['id'])?></span> (<span data-conversa="tipo"><?=$h($chamado['tipo_contrato_nome'] ?? '')?></span>)</span>
                <span class="rotulo">Técnico responsável</span>
                <span class="valor" data-conversa="tecnico">…</span>
            </div>
            <div>
                <span class="rotulo">Última interação</span>
                <span class="valor" data-conversa="ultima">…</span>
                <span class="rotulo">Status</span>
                <span class="valor"><span class="situacao" data-conversa="situacao"><?=$h($chamado['situacao_nome'] ?? '')?></span></span>
            </div>
            <div>
                <span class="rotulo">Data abertura</span>
                <span class="valor" data-conversa="abertura"><?=$h(!empty($chamado['data_abertura']) ? date('d/m/Y', strtotime($chamado['data_abertura'])) : '')?></span>
                <span class="rotulo">Equipamento</span>
                <span class="valor" data-conversa="equipamento">…</span>
            </div>
        </section>

        <section class="area-conversa" aria-label="Conversa do chamado">
            <?php include __DIR__ . '/../partials/conversa.php'; ?>
        </section>
    <?php endif; ?>
    </main>

    <footer>Copyright &copy; 2002-<?=date('Y')?> <strong>Genial Sistemas</strong> Ltda. Todos os direitos reservados.</footer>
</body>
</html>
