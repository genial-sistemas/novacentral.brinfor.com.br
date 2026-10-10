<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta http-equiv="Expires" content="0"/>

        <!-- PWA favicon setup -->
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">

        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="theme-color" content="#ffffff">

        <title>Central do Cliente BRInfor</title>

        <link rel="shortcut icon" href="/views/img/brinfor-01.webp">

        <!-- Fonts css -->
        <link href="/views/js/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <link href="/views/js/vendor/ionicons/css/ionicons.min.css" rel="stylesheet">
        <link href="/views/js/vendor/typicons.font/typicons.css" rel="stylesheet">

        <!-- Vendors CSS -->
        <link href="/views/css/iziToast.min.css" rel="stylesheet">

        <!-- azia CSS -->
        <!-- <link rel="stylesheet" href="/views/css/azia.css"> -->

        <!-- Customizações -->
        <!-- <link rel="stylesheet" href="/views/css/custom.css"> -->

        <!-- Bootstrap CSS -->
        <link href="/views/css/bootstrap.min.css" rel="stylesheet">

        <!-- JS header -->

        <!-- Jquery -->
        <script src="/views/js/vendor/jquery/jquery.min.js"></script>
        <script src="/views/js/iziToast.min.js"></script>

        <script src="/views/js/jquery.mask.min.js"></script>
        <!-- <script src="/views/js/jquery.numeric.js"></script> -->

        <!-- Bootstrap -->
        <!-- <script src="/views/js/vendor/bootstrap/js/bootstrap.bundle.min.js"></script> -->

        <!-- Theme script -->
        <script src="/views/js/azia.js"></script>

        <!-- Commun scripts -->
        <script src="/views/js/geral/global-constants.js"></script>
        <script src="/views/js/geral/helper-functions.js"></script>

        <!-- Alert messages functions -->
        <script src="/views/js/geral/alert-message-functions.js"></script>

        <style>
            body {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                margin: 0; /* Remove default body margin */
            }

            .header, .footer {
                background-color: #f8f9fa;
                padding: 1rem;
                text-align: center;
                max-height: 10vh;
            }

            .header {
                display: inline-flex;
                justify-content: space-between;
                /* background-color: #fcfcfc; */
                box-shadow: 0 2px 3px rgba(33, 34, 41, 0.05);
            }

            main div.dados-chamado {
                width: 100%;
                min-width: 100%;
                margin-top: 1rem;
                margin-bottom: 1rem;
                padding: 0;
            }

            div#container-chat {
                /* Portrait */
                @media only screen
                and (min-device-width: 320px)
                and (max-device-width: 480px)
                and (-webkit-min-device-pixel-ratio: 2)
                and (orientation: portrait) {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    margin-top: 2rem;
                    max-height: 50vh;
                }
            }

            .az-chat-body {
                overflow-y: scroll;
                padding: 2rem;
            }

            .az-chat-time {
                /* text-decoration: underline; */
                border-bottom: 1px dotted #436931;
            }

            .media {
                /* border-bottom: 1px dashed #436931; */
                padding: 1rem;
            }

            .media .az-msg-wrapper {
               background-color: #487731;
               color: white;
            }

            .media.flex-row-reverse .az-msg-wrapper {
                color: black !important;
            }

            div#form-group {
                margin-top:3rem;

                /* Portrait */
                @media only screen
                    and (min-device-width: 320px)
                    and (max-device-width: 480px)
                    and (-webkit-min-device-pixel-ratio: 2)
                    and (orientation: portrait) {

                    margin: 0;
                }
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

        </style>

        <script>
            // Url FormInteracoes post data
            const urlFormInteracoes = "<?php echo(\sprintf('interagir-chamado?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
            const urlFormNovaMsg = "<?php echo(\sprintf('novamsg?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
            const urlPollingInteracoes = "<?php echo(\sprintf('interacoes?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? '')));?>";
            const urlAvaliarChamado = "<?php echo(\sprintf('avaliar-chamado?id=%s&seguranca=%s', ($idChamado ?? 0), ($seguranca ?? ''))); ?>";
            //
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

    </head>
    <body>
        <header class="header">
            <a href="https://brinfor.com.br"><img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px"></a>
            <h2 class="az-content-title mg-b-5 mg-b-lg-8">Interagir Chamado</h2>
        </header>

        <main class="container-fluid main-content">
            <!-- PHP-If -->
            <?php if (!isset($error)):  ?>

                <div class="dados-chamado">
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

                <div id="chat-container" class="chat-container" style="background-color:#dfdfdf;">
                    <div id="azChatBody" class="az-chat-body chat-chamados">
                    </div><!-- az-chat-body -->
                </div>

                <hr class="w-100" />

                <div class="form-group">
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

                        <div class="col-12">
                            <div class="row">
                                <div class="col-md-10 col-sm-12">
                                    <input type="text" class="form-control" name="nova-msg" placeholder="Nova mensagem...">
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <input type="file" id="arquivo" name="arquivo" class="d-none">

                                    <button type="button"
                                        id="btnAnexo"
                                        class="btn btn-primary btn-xhr-submit ml-sm-3"
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

            <!-- PHP-Else -->
            <?php else:  ?>
                <div class="row">
                    <div class="col-md-12">
                        <p class="mb-5 mt-3" style="font-size: 30px;"><?php echo $error ?></b></p>
                    </div>
                </div>
            <!-- PHP-EndIf -->
            <?php endif;  ?>

        </main>

        <footer class="footer">
            <h1>Footer</h1>
        </footer>

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
