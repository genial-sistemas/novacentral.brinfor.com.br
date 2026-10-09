<?php include __DIR__ . '/header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div class="d-flex align-items-center justify-content-between w-100">
        <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Documentação</h2>
        </div>
        <a href="<?=htmlspecialchars($pdfDownloadUrl, ENT_QUOTES, 'UTF-8')?>" class="btn btn-outline-primary">
            <?=iconeInterface('baixar')?> Baixar PDF
        </a>
    </div>
</div>

<div class="az-content-body">
    <iframe
        src="<?=htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8')?>"
        title="Documentação do cliente em PDF"
        style="display:block; width:100%; height:calc(100vh - 230px); min-height:600px; border:1px solid #d9d9d9;"
    ></iframe>
</div>

<?php include __DIR__ . '/footer.php'; ?>
