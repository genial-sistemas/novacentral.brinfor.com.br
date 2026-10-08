<?php include __DIR__ . '/header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Editar Equipamento</h2>
    </div>
</div>

<div class="az-content-body">
    <?php if ($erroEquipamento !== null): ?>
        <div class="alert alert-danger" role="alert"><?=htmlspecialchars($erroEquipamento, ENT_QUOTES, 'UTF-8')?></div>
    <?php endif; ?>

    <form action="/equipamentos_editar?id=<?=urlencode((string)$equipamento['id'])?>" method="post">
        <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')?>">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="codigo">Código</label>
                    <input id="codigo" class="form-control" value="<?=htmlspecialchars((string)$equipamento['codigo'], ENT_QUOTES, 'UTF-8')?>" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="tipo_equipamento">Equipamento</label>
                    <input id="tipo_equipamento" class="form-control" value="<?=htmlspecialchars((string)($equipamento['nome_tipo'] ?? ''), ENT_QUOTES, 'UTF-8')?>" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="nome_rede">Nome Rede</label>
                    <input id="nome_rede" class="form-control" value="<?=htmlspecialchars((string)($equipamento['nome_rede'] ?? ''), ENT_QUOTES, 'UTF-8')?>" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="modelo">Modelo</label>
                    <input id="modelo" class="form-control" value="<?=htmlspecialchars((string)($equipamento['modelo'] ?? ''), ENT_QUOTES, 'UTF-8')?>" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="funcionario">Usuário</label>
                    <select id="funcionario" name="contato" class="form-control">
                        <option value="">Sem funcionário vinculado</option>
                        <?php foreach ($contatos as $contato): ?>
                            <option
                                value="<?=htmlspecialchars((string)$contato['id'], ENT_QUOTES, 'UTF-8')?>"
                                <?=(string)$contato['id'] === (string)($equipamento['contato'] ?? '') ? 'selected' : ''?>
                            ><?=htmlspecialchars($contato['nome'], ENT_QUOTES, 'UTF-8')?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="tipo_contrato">Tipo Contrato</label>
                    <input id="tipo_contrato" class="form-control" value="<?=htmlspecialchars((string)($equipamento['tipo_contrato'] ?? ''), ENT_QUOTES, 'UTF-8')?>" disabled>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="valor">Valor Equipamento</label>
                    <input id="valor" class="form-control" value="R$ <?=htmlspecialchars(number_format((float)($equipamento['valor_equipamento'] ?? 0), 2, ',', '.'), ENT_QUOTES, 'UTF-8')?>" disabled>
                </div>
            </div>
        </div>
        <div class="form-group">
            <a href="/equipamentos_listar" class="btn btn-az-primary">Voltar</a>
            <button type="submit" class="btn btn-az-primary">Salvar</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
