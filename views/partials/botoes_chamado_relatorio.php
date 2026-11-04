<?php // Botões Conversa (modal) e Reabrir dos relatórios. Espera $id_chamado_botoes. ?>
<div class="chamados-acoes">
    <button type="button" class="btn btn-icone btn-interagir" data-abrir-conversa="<?=(int)$id_chamado_botoes?>"
            title="Ver conversa" aria-label="Ver a conversa do chamado <?=(int)$id_chamado_botoes?>">
        <?=iconeInterface('conversa', 'inline')?>
    </button>
    <form action="/chamados_listar_fechados" method="post"
          onsubmit="return confirm('Reabrir o chamado #<?=(int)$id_chamado_botoes?>?');">
        <input type="hidden" name="chamado_id" value="<?=(int)$id_chamado_botoes?>">
        <button type="submit" class="btn btn-icone btn-reabrir" name="acao" value="reabrir_chamado"
                title="Reabrir chamado" aria-label="Reabrir o chamado <?=(int)$id_chamado_botoes?>">
            <?=iconeInterface('reabrir', 'inline')?>
        </button>
    </form>
</div>
