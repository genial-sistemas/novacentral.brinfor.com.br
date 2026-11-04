<?php include __DIR__ . '/header.php'; ?>

<div class="az-content-header d-block d-md-flex">
    <div>
        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Equipamentos ativos</h2>
        <p class="mg-b-0">Equipamentos vinculados aos seus contratos Helpdesk ativos.</p>
    </div>
</div>

<div class="az-content-body">
    <?php include __DIR__ . '/equipamentos_tabela.php'; ?>

    <!-- Edição do equipamento (usuário vinculado) em modal; sem JavaScript o link abre a página de edição. -->
    <div class="modal fade" id="modalEditarEquipamento" tabindex="-1" role="dialog" aria-labelledby="modalEditarEquipamentoTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" id="formEditarEquipamento" method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarEquipamentoTitulo">Editar equipamento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?=htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8')?>">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="eqp_codigo">Código</label>
                            <input id="eqp_codigo" class="form-control" disabled>
                        </div>
                        <div class="col-md-8 form-group">
                            <label for="eqp_tipo">Equipamento</label>
                            <input id="eqp_tipo" class="form-control" disabled>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="eqp_modelo">Modelo</label>
                            <input id="eqp_modelo" class="form-control" disabled>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="eqp_contato">Usuário</label>
                            <select id="eqp_contato" name="contato" class="form-control">
                                <option value="">Sem funcionário vinculado</option>
                                <?php foreach ($contatos as $contato): ?>
                                    <option value="<?=htmlspecialchars((string)$contato['id'], ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($contato['nome'], ENT_QUOTES, 'UTF-8')?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-az-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    document.querySelectorAll('[data-editar-equipamento]').forEach(function (link) {
        link.addEventListener('click', function (evento) {
            if (typeof $ === 'undefined' || !$.fn.modal) return; // sem Bootstrap, segue o link
            evento.preventDefault();
            var d = link.dataset;
            document.getElementById('formEditarEquipamento').action = '/equipamentos_editar?id=' + encodeURIComponent(d.id);
            document.getElementById('eqp_codigo').value = d.codigo || '';
            document.getElementById('eqp_tipo').value = d.tipo || '';
            document.getElementById('eqp_modelo').value = d.modelo || '';
            var select = document.getElementById('eqp_contato');
            select.value = d.contato || '';
            if (select.value !== (d.contato || '')) select.value = ''; // vinculado a funcionário inativo
            $('#modalEditarEquipamento').modal('show');
        });
    });
    </script>
</div>

<?php include __DIR__ . '/footer.php'; ?>
