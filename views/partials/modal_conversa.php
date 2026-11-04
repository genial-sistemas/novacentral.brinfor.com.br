<?php // Modal com a conversa do chamado (só leitura, chamados encerrados). Botões: data-abrir-conversa="<id>". ?>
    <div class="modal fade" id="modalConversa" tabindex="-1" role="dialog" aria-labelledby="modalConversaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConversaTitulo">Chamado</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="modalConversaFrame" title="Conversa do chamado" style="width:100%;height:70vh;border:0;display:block"></iframe>
                </div>
            </div>
        </div>
    </div>
    <script>
    // Conversa do chamado num modal, para continuar no relatório.
    document.querySelectorAll('[data-abrir-conversa]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var id = botao.dataset.abrirConversa;
            document.getElementById('modalConversaTitulo').textContent = 'Chamado #' + id;
            document.getElementById('modalConversaFrame').src = '/chamados_conversa_modal?id=' + encodeURIComponent(id);
            $('#modalConversa').modal('show');
        });
    });
    $('#modalConversa').on('hidden.bs.modal', function () {
        document.getElementById('modalConversaFrame').src = 'about:blank';
    });
    </script>
