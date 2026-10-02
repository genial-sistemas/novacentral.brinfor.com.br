<?php include_once __DIR__ . '/header.php' ?>

        <script>
            // Url formNovaMsg post data
            const urlFormInteracoes = "<?php echo(\sprintf('interagir-chamado?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
            const urlFormNovaMsg = "<?php echo(\sprintf('novamsg?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
            const urlPollingInteracoes = "<?php echo(\sprintf('interacoes?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
            const urlAvaliarChamado = "<?php echo(\sprintf('avaliar-chamado?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? ''))); ?>";

            const urlBrInforHome = "https://www.brinfor.com.br/suporte";
            const situacao = "<?php echo($situacao ?? 'N.D.') ?>";
            //
            const UPLOAD_FILE_MAX_SIZE = "<?php echo($maxUploadFileSize) ?>"; 
            const UPLOAD_FILE_ACCEPT_EXTENSIONS = JSON.parse('<?php echo($acceptsUploadFileExts) ?>');

            const reqChamadoData = {
                seguranca: "<?php echo($seguranca) ?>",
                idChamado: "<?php echo($idChamado) ?>",
                idEquipamento: "<?php echo($idEqpto) ?>",
                idContato: "<?php echo($idContatoCliente) ?>",
                csrf: "<?php echo($formHash);?>"
            };

            window.eventSourceObj = null;
                
            if (typeof reloadIntervalId !== 'undefined') {
                clearInterval(reloadIntervalId);
            }
        </script>

        <style>

            div.card.card-body.card-dashboard-fifteen {
                overflow: hidden;
                padding-top: 1rem;
                padding-bottom: .5rem;
                padding-left: 2rem;
            }

            div.az-content-body div#container-chat {
                width: 100%;
                min-width: 100%;
                border-color: #dee2e6;
                box-shadow: 0 0 10px rgba(33, 34, 41, 0.1);
                padding: 3rem;
                background-color: #FCFCFC;

                /* Portrait */
                @media only screen 
                and (min-device-width: 320px) 
                and (max-device-width: 480px)
                and (-webkit-min-device-pixel-ratio: 2)
                and (orientation: portrait) {
                    height: 50vh;
                    overflow-x: scroll;
                    padding: 0;
                }
            }

            div#container-chat div#azChatBody {
                width: 100%;
                overflow: unset;
            }

            .media {
                margin-right: 2rem !important;
                margin-left: 2rem;
            }

            div.media > div.media-body > div.az-msg-wrapper {
                background-color: #a4e583;
                color: black !important;
            }

            div.media.flex-row-reverse > div.media-body > div.az-msg-wrapper {
                background-color: #ffc876;
                color: black !important;
            }

            div.az-chat-body label.az-chat-time {
                font-weight: bold;
                text-decoration: underline;
                padding-top: .5rem;
                border-top: 1px dotted #9DA9B0 !important;
            }
            
            button#btnAnexo, button#btnEnviarMsg {
                /* Portrait */
                @media only screen 
                    and (min-device-width: 320px) 
                    and (max-device-width: 480px)
                    and (-webkit-min-device-pixel-ratio: 2)
                    and (orientation: portrait) {
                    
                    margin-top: 1rem;
                    margin-left: 3rem;
                }
            }

            div.az-footer {
                position: static;
                bottom: 0;
                height: 2rem;
            }
        </style>

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
                <!-- If no has error -->
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
                    <!-- <div class="container-fluid mt-4"> -->
                        <!-- <div class="col-md-12"> -->
                            <div id="container-chat" class="w-100 mt-3">
                                <div id="azChatBody" class="az-chat-body chat-chamados">
                                </div><!-- az-chat-body -->
                            </div>


                            <div class="form-group w-100" style="margin-top:1.5rem">
                                <!-- <form id="formNovaMsg"
                                    name="<?php echo $formName;?>"
                                > -->
                                <!-- 
                                    <input type="hidden" name="csrf" value="<?php echo($formHash);?>"/>
                                    <input type="hidden" name="operacao" value="novo_evento">
                                    <input type="hidden" name="seguranca" value="<?php echo $seguranca ?>">
                                    <input type="hidden" name="id_chamado" value="<?php echo $idChamado ?>">
                                    <input type="hidden" name="id_equipamento" value="<?php echo $idEqpto ?>">
                                    <input type="hidden" name="id_contato" value="<?php echo $idContatoCliente ?>"> 
                                -->
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-md-10 col-sm-12">
                                                <input type="text" id="iptNovaMsg" class="form-control" name="nova-msg" placeholder="Nova mensagem...">
                                            </div>
                                            <div class="col-md-2 col-sm-12">
                                                <input type="file" id="arquivo" name="arquivo" class="d-none">
                                                
                                                <button type="button"
                                                    id="btnAnexo" 
                                                    class="btn btn-primary btn-xhr-submit ml-sm-3" 
                                                    name="arquivoEscolher" 
                                                    onclick="$('#arquivo').click()"
                                                >Anexo</button>

                                                <button type="button"
                                                    id="btnEnviarMsg" 
                                                    class="btn btn-az-primary btn-xhr-submit ml-2" 
                                                >Enviar</button>
                                            </div>
                                        </div>
                                    </div>
                                <!-- </form> -->
                            </div>
                        <!-- </div> -->
                    <!-- </div> -->
                
                <!-- Else has error -->
                <?php else:  ?>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="mb-5 mt-3" style="font-size: 30px;"><?php echo $error ?></b></p>
                        </div>
                    </div>
                <!-- PHP-EndIf -->
                <?php endif;  ?>
            </div><!-- az-content-body -->

            <?php include_once __DIR__ . '/footer_section.php'; ?>

        </div><!-- az-content -->

        <?php include_once __DIR__ . '/footer_section.php'; ?>
        <?php include_once __DIR__ . '/footer_scripts.php'; ?>  

        <?php if (!isset($error)): ?>
            <script>
                var objDiv = document.getElementById("container-chat");
                if (objDiv && objDiv.scrollTop) { objDiv.scrollTop = objDiv.scrollHeight; }
            </script>
            <script src="/views/js/paginas/visitante-chamados-interagir.js"></script>
        <?php else: ?>
            <script>
                if (situacao === 'finalizado') {
                    setTimeout(function() { window.location.href = urlAvaliarChamado; }, 5000);
                } else {
                    setTimeout(function() { window.location.href = '/401'; }, 10000);
                }
            </script>
        <?php endif ?>
    </body>
</html>