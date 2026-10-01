<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="0"/>

    <title>Abrir Chamado - BRInfor</title>
 
    <!-- vendor css -->
    <link href="/views/lib/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/views/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="/views/lib/typicons.font/typicons.css" rel="stylesheet">
    <link href="/views/lib/flag-icon-css/css/flag-icon.min.css" rel="stylesheet">

    <!-- azia CSS -->
    <link rel="stylesheet" href="/views/css/azia.css">

    <!-- Customizações -->
    <link rel="stylesheet" href="/views/css/custom.css">

    <link id="favicon" rel="shortcut icon" href="/favicon.ico">
    <link href="/views/css/iziToast.min.css" rel="stylesheet">

    <!-- Jquery -->
    <script src="/views/lib/jquery/jquery.min.js"></script>

    <style>
        /* Seus estilos existentes... */
        .btn-group-custom {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .btn-group-custom .btn {
            min-width: 200px;
        }
        .chamado-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0056b3;
        }
        @media (max-width: 768px) {
            .chamado-number {
                font-size: 1.8rem;
            }
            .btn-group-custom .btn {
                min-width: 100%;
            }
        }
    </style>
</head>

<body class="az-body az-body-sidebar az-light">
<div class="az-content az-content-dashboard-five">
    <div class="az-header">
        <div class="container-fluid">
            <a href="https://brinfor.com.br"><img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px"></a>
            <div class="az-header-center"></div>
            <div class="az-header-right">
                <div class="dropdown az-profile-menu">
                    <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
                </div>
            </div>
        </div>
    </div>

    <div id="successContainer" class="az-content-body">
        <div class="row">
            <div class="col-md-12 text-center">
                <div style="margin: 40px 0;">
                    <i class="fas fa-check-circle" style="font-size: 80px; color: #28a745;"></i>
                </div>
                <p style="font-size: 30px; margin-bottom: 10px;">
                    Chamado <span class="chamado-number" id="chamadoIdText"><?= htmlspecialchars($idChamado) ?></span> 
                    aberto com sucesso! 🎉
                </p>
                <p style="font-size: 18px; color: #6c757d; margin-bottom: 30px;">
                    Seu chamado foi registrado e aguarda atendimento.
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group-custom justify-content-center">
                    <!-- ============================================================
                    CORREÇÃO: BOTÃO INTERAGIR COM URL CORRETA
                    ============================================================ -->
                    <button class="btn btn-az-primary pd-x-20" 
                            onclick="window.location.href='<?= htmlspecialchars($urlInteragir) ?>'">
                        <i class="fas fa-comment-dots"></i> Interagir com Chamado
                    </button>
                    
                    <button class="btn btn-outline-primary pd-x-20" 
                            onclick="window.location.href='abrir-chamado'">
                        <i class="fas fa-plus"></i> Novo Chamado
                    </button>
                    
                    <button class="btn btn-outline-secondary pd-x-20" 
                            onclick="window.location.href='/'">
                        <i class="fas fa-home"></i> Página Inicial
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================
        INFORMAÇÕES ADICIONAIS DO CHAMADO
        ============================================================ -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle"></i> Informações do Chamado
                        </h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Número:</strong> 
                                <span class="badge badge-primary">#<?= htmlspecialchars($idChamado) ?></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong> 
                                <span class="badge badge-success">Aberto</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Data:</strong> 
                                <span><?= date('d/m/Y H:i:s') ?></span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb"></i> 
                                    <strong>Dica:</strong> Clique em "Interagir com Chamado" para 
                                    conversar com o técnico, enviar arquivos e acompanhar o 
                                    andamento do seu chamado.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="az-footer">
        <div class="container-fluid">
            <span>&copy; 2002 - <?= date('Y') ?> <a href="https://genialsistemas.com.br/" target="_blank">Genial Sistemas</a> Ltda. Todos os direitos reservados.</span>
        </div>
    </div>
</div>

<script src="/views/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/views/lib/ionicons/ionicons.js"></script>
<script src="/views/js/azia.js"></script>
<script src="/views/js/iziToast.min.js"></script>

<script>
    // ============================================================
    // CONFIGURAÇÕES GLOBAIS
    // ============================================================
    const CONTRATO_HELPDESK_ID = 1;
    const CONTRATO_HOSPEDAGEM_ID = 2;
    const CONTRATO_DOMINIO_ID = 4;
    const CONTRATO_BACKUP_ID = 5;
    const CONTRATO_SUPORTE_PRODUTO_ID = 6;
    const CONTRATOS_OUTSOURCING_ID_LIST = [CONTRATO_HELPDESK_ID];

    // ============================================================
    // MENSAGENS DO SISTEMA
    // ============================================================
    const alertMessage = <?= json_encode($mensagem ?? '') ?>;
    const alertTipoMensagem = <?= json_encode($tipo_mensagem ?? '') ?>;
    const createdChamadoId = <?= json_encode($idChamado ?? '') ?>;

    // ============================================================
    // VARIÁVEIS GLOBAIS
    // ============================================================
    let tipoContrato = null;
    let idContrato = null;
    let loading = false;

    // ============================================================
    // IIFE Jquery
    // ============================================================
    (function($) {
        'use strict';

        // ============================================================
        // FUNÇÕES DE ALERTA
        // ============================================================
        const alertError = function(msg, timeout = 3000) {
            iziToast.error({
                id: 'error',
                title: 'Erro!',
                message: msg,
                position: 'topRight',
                transitionIn: 'bounceInLeft',
                timeout,
                backgroundColor: 'rgba(255, 175, 180, 1)',
                icon: 'fa fa-times',
                closeOnClick: true
            });
        };

        const alertSuccess = function(msg, timeout = 5000) {
            iziToast.success({
                id: 'success',
                title: 'Sucesso!',
                message: msg,
                position: 'topRight',
                transitionIn: 'bounceInLeft',
                timeout,
                backgroundColor: 'rgba(166, 239, 184, 1)',
                icon: 'fa fa-check',
                closeOnClick: true
            });
        };

        const alertWarning = function(msg, timeout = 3000) {
            iziToast.warning({
                id: 'warning',
                title: 'Atenção!',
                message: msg,
                position: 'topRight',
                transitionIn: 'bounceInLeft',
                timeout,
                backgroundColor: 'rgba(166, 239, 184, 1)',
                icon: 'fa fa-check',
                closeOnClick: true
            });
        };

        // ============================================================
        // FUNÇÕES DE VALIDAÇÃO
        // ============================================================
        const isValidObject = function(objValue) {
            return typeof objValue === 'object' && Object.keys(objValue).length > 0;
        };

        const isValidArray = function(arrValue) {
            return typeof arrValue !== 'undefined' && Array.isArray(arrValue) && arrValue.length > 0;
        };

        const isValidString = function(strValue) {
            return typeof strValue === 'string' && String(strValue).length > 0;
        };

        const objectHasProp = function(obj, propName) {
            return isValidObject(obj) && isValidString(propName) && Object.keys(obj).indexOf(propName) >= 0;
        };

        const getSafeObjPropValue = function(obj, propName = '', defValue = null) {
            return objectHasProp(obj, propName) ? obj[propName] : defValue;
        };

        const isOutsourcingContract = function(tipo) {
            return CONTRATOS_OUTSOURCING_ID_LIST.indexOf(tipo) >= 0;
        };

        const gotElmFocus = function(idElm) {
            $(String('#').concat(idElm)).focus();
        };

        // ============================================================
        // PROCESSAR MENSAGENS DE ALERTA
        // ============================================================
        const procAlertMessage = function(msg = null, tipo = null) {
            if (msg && msg.length > 0 && tipo && tipo.length > 0) {
                switch (tipo) {
                    case 'Success':
                        alertSuccess(msg);
                        break;
                    case 'Error':
                        alertError(msg);
                        break;
                    default:
                        break;
                }
            }
        };

        // ============================================================
        // DOCUMENT READY
        // ============================================================
        $(document).ready(function() {
            // Mostra mensagens de alerta
            procAlertMessage(alertMessage, alertTipoMensagem);

            // Log do chamado criado
            if (createdChamadoId) {
                console.log('✅ Chamado #' + createdChamadoId + ' criado com sucesso!');
                console.log('🔗 URL para interagir: ' + window.location.href);
            }

            // Atualiza o número do chamado no título da página
            if (createdChamadoId) {
                document.title = 'Chamado #' + createdChamadoId + ' - BRInfor';
            }

            // ============================================================
            // CORREÇÃO: VALIDA A URL DE INTERAÇÃO
            // ============================================================
            const btnInteragir = document.querySelector('[onclick*="interagir-chamado"]');
            if (btnInteragir) {
                const url = btnInteragir.getAttribute('onclick').match(/'(.*?)'/);
                if (url && url[1]) {
                    console.log('🔗 Botão Interagir redireciona para: ' + url[1]);
                    
                    // Verifica se a URL tem os parâmetros necessários
                    if (!url[1].includes('id=') || !url[1].includes('seguranca=')) {
                        console.warn('⚠️ URL de interação pode estar incompleta!');
                    }
                }
            }
        });

    })(jQuery);
</script>

</body>
</html>