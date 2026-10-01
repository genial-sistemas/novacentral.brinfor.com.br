<?php include_once __DIR__ . '/header.php' ?>
    <!-- az-content -->
    <div class="az-content az-content-dashboard-five">

        <div class="az-header">
            <div class="container-fluid">
                <a href="https://brinfor.com.br"><img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px"></a>
                <div class="az-header-center"></div><!-- az-header-center -->
                <div class="az-header-right">
                    <div class="dropdown az-profile-menu">
                        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Avaliar Chamado</h2>
                    </div>
                </div><!-- az-header-right -->
            </div><!-- container -->
        </div><!-- az-header -->

        <div class="az-content-body">
        <?php if (!isset($mensagem)): ?>
            <form action="#" method="post" name="<?php echo $formName;?>">
                <input type="hidden" name="csrf" value="<?php echo($formHash);?>"/>
                <input type="hidden" name="chamado" value="<?php echo($chamado['id'] ?? 0);?>"/>
                <div align="center">
                    <div class="col-md-12 mt-4" align="center">
                        <div class="form-group">
                            <label style="font-size: 20px;">
                                <label style="margin-top: -5px; color: red; font-size: 20px">*</label>
                                Sua solicitação foi atendida?
                            </label>
                            <div>
                                <input type="radio" class="mr-1" name="atendido" id="atendido" value="s" required>
                                <label for="atendido" class="mr-3" style="font-size: 20px;">Sim</label>
                                <input type="radio" class="mr-1" name="atendido" id="nao_atendido" value="n">
                                <label for="nao_atendido" style="font-size: 20px;">Não</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-4" align="center">
                        <div class="form-group">
                            <label style="font-size: 20px;">
                                <label style="margin-top: -5px; color: red; font-size: 20px">*</label>
                                Como você avalia esse atendimento?
                            </label>
                            <div>
                                <input type="radio" class="mr-1" name="nota" id="nota_1" value="1" >
                                <label for="nota_1" style="font-size: 20px;">Ruim</label>
                                <input type="radio" class="mr-1" name="nota" id="nota_2" value="2">
                                <label for="nota_2" class="mr-3" style="font-size: 20px;">Satisfatório</label>
                                <input type="radio" class="mr-1" name="nota" id="nota_3" value="3" required>
                                <label for="nota_3" class="mr-3" style="font-size: 20px;">Excelente</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-4" align="center">
                        <div class="form-group">
                            <label style="font-size: 20px;">
                                Caso queira, deixe um comentário:
                            </label>
                            <textarea class="form-control"  name="descricao" rows="5" style="width: 500px;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" align="center">
                        <button class="btn btn-az-primary pd-x-20" type="submit" name="acao" value="avaliar_chamado">Avaliar Chamado</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="row">
                <div class="col-md-12">
                    <p class="mb-5 mt-3" style="font-size: 30px;"><?=$mensagem?></b></p>
                </div>
            </div>
        <?php endif; ?>
        </div><!-- az-content-body -->

        <?php include_once __DIR__ . '/footer_section.php'; ?>

    </div><!-- az-content -->

        <?php include_once __DIR__ . '/footer_scripts.php'; ?>
    </body>
</html>
