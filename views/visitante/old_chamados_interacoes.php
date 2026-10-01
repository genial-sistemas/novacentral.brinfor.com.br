<?php include_once __DIR__ . '/header.php' ?>
    <script>
        // Url FormInteracoes post data
        const urlFormInteracoes = "<?php echo(sprintf('interagir-chamado?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
        //
        const UPLOAD_FILE_MAX_SIZE = "<?php echo($maxUploadFileSize) ?>"; 
        const UPLOAD_FILE_ACCEPT_EXTENSIONS = JSON.parse('<?php echo($acceptsUploadFileExts) ?>');
            
        if (typeof reloadIntervalId !== 'undefined') {
            clearInterval(reloadIntervalId);
        }
    </script>

        <div class="az-content az-content-dashboard-five">

            <div class="az-header">
                <div class="container-fluid">
                    <a href="https://brinfor.com.br">"<img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px"></a>
                    <div class="az-header-center"></div><!-- az-header-center -->
                    <div class="az-header-right">
                        <div class="dropdown az-profile-menu">
                            <h2 class="az-content-title mg-b-5 mg-b-lg-8">Interagir Chamado</h2>
                        </div>
                    </div><!-- az-header-right -->
                </div><!-- container -->
            </div><!-- az-header -->

            <div class="az-content-body">
                <?php if (!isset($error)):  ?>
                <div class="row row-sm">
                    <div class="col-md-12">
                        <div class="card card-body card-dashboard-fifteen">
                            <div class="row mg-b-10">
                                <div class="col-md-4">
                                    <b>Chamado</b>: <?php echo $idChamado ?> (<?php echo $tipoContrato ?>)<br />
                                    <b>Técnico responsavel</b>: <?php echo $ultEventoNomeTecnico ?>
                                </div>
                                <div class="col-md-4">
                                    <b>Última interação</b>: <?php echo $ultEventoDataEhHora ?><br />
                                    <b>Status</b>: <?php echo $situacao ?>
                                </div>
                                <div class="col-md-4">
                                    <b>Data abertura</b>: <?php echo $dataAbertura ?><br />
                                    <?php if ($idTipoContrato == 1) {  ?>
                                        <b>Equipamento</b>: <?php echo $ultEventoDescrEqpto ?>
                                    <?php }  ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid mt-4">
                    <div class="col-md-12">
                        <div id="container-chat">
                            <div id="azChatBody" class="az-chat-body chat-chamados">
                                <div class="content-inner">
                                    <?php
                                        $data_atual = "";
                                        foreach($interacoes as $i):
                                    ?>
                                    <?php if($data_atual!=$i['data']): ?>
                                        <label class="az-chat-time"><span><?php echo data_brasil($i['data']) ?></span></label>
                                    <?php
                                        $data_atual = $i['data'];
                                        endif; 
                                    ?>
                                        <div class="media<?php echo $i['tipo_pessoa'] == CHAMADO_EVENTO_TIPO_PESSOA_CLIENTE ? ' flex-row-reverse' : '' ?>">
                                            <div class="az-img-user online" title="<?php echo $i['nome_pessoa_titulo'] ?>">
                                                <img src="/views/img/generic-user-512.png" alt="">
                                                <?php echo $i['nome_pessoa_curto'] ?>
                                            </div>

                                            <div class="media-body">
                                                <div class="az-msg-wrapper">
                                                    <?php
                                                    if ($i['link'] == 1) {
                                                        ?>
                                                        Anexo: <a href="<?php echo $i['descricao'] ?>" target="_blank">Clique aqui</a> para abrir.
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <?php echo stripslashes($i['descricao']) ?>
                                                        <?php
                                                    }
                                                    ?>
                                                </div><!-- az-msg-wrapper -->
                                                <div><span><?php echo $i['hora'] ?></span> <a href=""><i class="icon ion-android-more-horizontal"></i></a></div>
                                            </div><!-- media-body -->
                                        </div><!-- media -->
                                    <?php
                                    endforeach;  ?>
                                </div><!-- content-inner -->
                            </div><!-- az-chat-body -->
                        </div>
                        <div class="form-group" style="margin-top:3rem;">
                            <form id="formInteracoes"
                                method="post"  
                                enctype="multipart/form-data"
                                name="<?php echo $formName;?>"
                            >
                                <input type="hidden" name="csrf" value="<?php echo($formHash);?>"/>

                                <input type="hidden" name="operacao" value="novo_evento">
                                <input type="hidden" name="seguranca" value="<?php echo $seguranca ?>">
                                <input type="hidden" name="id_chamado" value="<?php echo $idChamado ?>">
                                <input type="hidden" name="id_equipamento" value="<?php echo $idEqpto ?>">
                                <input type="hidden" name="id_contato" value="<?php echo $idContatoCliente ?>">

                                <div class="col-sm-12">
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" class="form-control" name="nova-msg" placeholder="Nova mensagem...">
                                        </div>
                                        <div class="col-2">
                                            <input type="file" id="arquivo" name="arquivo" class="d-none">
                                            <button type="button"
                                                id="btnAnexo" 
                                                class="btn btn-primary btn-xhr-submit" 
                                                name="arquivoEscolher" 
                                                onclick="$('#arquivo').click()"
                                            >Anexo</button>

                                            <button type="submit"
                                                id="btnEnviarMsg" 
                                                class="btn btn-az-primary btn-xhr-submit ml-2" 
                                            >Enviar</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php else:  ?>
                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-5 mt-3" style="font-size: 30px;"><?php echo $error ?></b></p>
                    </div>
                </div>
                <?php endif;  ?>
            </div><!-- az-content-body -->

            <?php include_once __DIR__ . '/footer_section.php'; ?>

        </div><!-- az-content -->

        <?php include_once __DIR__ . '/footer_scripts.php'; ?>  

        <script>
            var objDiv = document.getElementById("container-chat");
            if (objDiv && objDiv.scrollTop) { objDiv.scrollTop = objDiv.scrollHeight; }
        </script>
        <script src="/views/js/paginas/visitante-chamados-interagir.js"></script>
    </body>
</html>
