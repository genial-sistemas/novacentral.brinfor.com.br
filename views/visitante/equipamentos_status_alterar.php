<?php 
    include __DIR__ . '/header.php';
?>

    <div class="az-header">
        <div class="container-fluid">
            <a href="https://brinfor.com.br"><img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px"></a>
            <!-- az-header-center -->
            <div class="az-header-center">
                <div class="dropdown az-profile-menu">
                    <h2 class="az-content-title mg-b-5 mg-b-lg-8">Solicitação Desativação de Equipamento</h2>
                </div>
            </div><!-- az-header-right -->
            <div class="az-header-right"></div>
        </div><!-- container -->
    </div><!-- az-header -->

    <div class="az-content-body">

    <?php if ($showForm): ?>
        <form action="#" method="post">
            <input type="hidden" name="seguranca" value="<?=$codigoSeguranca?>" />
            <input type="hidden" name="id_chamado" value="<?=$idChamado?>" />
            <input type="hidden" name="id_equipamento" value="<?=$idEqpto?>" />
            <input type="hidden" name="id_gerente_contas" value="<?=$idGerenteContas?>" />

            <div align="center">
                <div class="col-md-6 mt-4" align="center">
                    <div class="form-group">
                        <label style="font-size: 20px;">
                            <label style="margin-top: -5px; color: red; font-size: 20px">*</label>
                            Motivo por não aprovar desativação?
                        </label>
                        <div>
                            <textarea name="motivo_manter_ativao" 
                                class="form-control" 
                                rows="6" 
                                value="" 
                                required=""
                                placeholder="Por favor, descreva aqui o motivo por que voce não aprovou a solicitação para desativar o Equipamento..." 
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" align="center">
                    <button class="btn btn-az-primary pd-x-20" type="submit" name="acao" value="finalizar_chamado_desativacao">Salvar Motivo</button>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <?php if ($showPageMessage): ?>
        <div class="row mt-5">
            <div class="col-md-12">
                <p class="mb-5 mt-3" style="font-size: 2rem; text-align: center;">
                    <b><?=$pageMessage?></b>
                </p>
            </div>
        </div>
    <?php endif; ?>

    </div>
    <!-- az-content-body -->

<?php if (!$showForm): ?>
<script>
    (function (arguments) {
        setTimeout(function() { window.close(); }, 10000);
    })();
</script>
<?php endif; ?>

<?php 
    include __DIR__ . '/footer.php';
?>
