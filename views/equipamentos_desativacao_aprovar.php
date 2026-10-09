<?php
$escapar = static function ($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
$decidida = !empty($solicitacao['data_avaliacao']);
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aprovação de desativação de equipamento</title>
    <link rel="stylesheet" href="/views/css/azia.css">
    <link rel="stylesheet" href="/views/css/custom.css">
</head>
<body class="az-body az-light">
    <main class="container" style="max-width: 760px; padding-top: 40px;">
        <section class="card card-body">
            <h1 class="az-content-title">Solicitação de desativação</h1>
            <?php if ($mensagemAprovacao !== ''): ?>
                <div class="alert alert-danger" role="alert"><?=$escapar($mensagemAprovacao)?></div>
            <?php endif; ?>

            <?php if ($solicitacao): ?>
                <?php if ($resultado === 'aprovado'): ?>
                    <div class="alert alert-success" role="status">Solicitação aprovada. O equipamento foi desativado.</div>
                <?php elseif ($resultado === 'rejeitado'): ?>
                    <div class="alert alert-info" role="status">Solicitação rejeitada. O equipamento continua ativo.</div>
                <?php elseif ($decidida): ?>
                    <div class="alert alert-info" role="status">
                        Esta solicitação já foi <?=$solicitacao['aprovado'] ? 'aprovada' : 'rejeitada'?>.
                    </div>
                <?php else: ?>
                    <p>O equipamento continuará ativo até que uma decisão seja registrada.</p>
                <?php endif; ?>

                <dl>
                    <dt>Equipamento</dt>
                    <dd><?=$escapar($solicitacao['nome_equipamento'])?></dd>
                    <dt>Código</dt>
                    <dd><?=$escapar($solicitacao['codigo_etiqueta'])?></dd>
                    <dt>Contrato</dt>
                    <dd><?=$escapar($solicitacao['nome_contrato_outsourcing'])?> (<?=$escapar($solicitacao['id_contrato'])?>)</dd>
                    <dt>Descrição</dt>
                    <dd><?=$escapar($solicitacao['nome_rede'])?></dd>
                    <dt>Modelo</dt>
                    <dd><?=$escapar($solicitacao['modelo_equipamento'])?></dd>
                    <dt>Solicitante</dt>
                    <dd><?=$escapar($solicitacao['nome_contato'])?></dd>
                </dl>

                <?php if (!$decidida): ?>
                    <form method="post" class="d-flex" style="gap: 12px;">
                        <input type="hidden" name="token" value="<?=$escapar($token)?>">
                        <input type="hidden" name="csrf" value="<?=$escapar($csrfToken)?>">
                        <button class="btn btn-az-primary" type="submit" name="acao" value="aprovar">
                            Aprovar desativação
                        </button>
                        <button class="btn btn-outline-secondary" type="submit" name="acao" value="rejeitar">
                            Rejeitar solicitação
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
