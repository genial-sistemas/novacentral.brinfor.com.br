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
        .box-brinfor {
            width: 210px;
            height: 75px;
            background-color: white;
            margin-bottom: 1rem;
            margin-left: 15px;
            border-radius: 10px;
            border: 1px solid black;
        }
        .box-brinfor-esquerda {
            position: absolute;
            width: 85px;
            height: 73px;
            background-color: transparent;
            border-right: 1px solid black;
        }
        .box-brinfor-esquerda-input {
            position: absolute;
            margin-top: -5px;
            margin-left: 15px;
        }
        .box-brinfor-esquerda-label {
            position: absolute;
            font-size: 10px;
            margin-top: 56px;
            margin-left: 15px;
            font-weight: bold;
        }
        .box-brinfor-direita {
            width: 123px;
            height: 73px;
            margin-left: 85px;
            background-color: transparent;
        }
        .box-brinfor-direita-imagem {
            position: absolute;
            margin-top: 3px;
            margin-left: 25px;
        }
        .box-brinfor-direita-input {
            position: absolute;
            font-size: 30px;
            margin-top: 15px;
            margin-left: 21px;
        }
        .box-brinfor-direita-label {
            position: absolute;
            font-size: 10px;
            margin-top: 56px;
            margin-left: 26px;
            font-weight: bold;
        }

        .input-brinfor-esquerda {
            text-align: center;
            width: 62px;
            margin-left: -5px;
            height: 46px;
            font-size: 2vw;
            margin-top: 13px;
        }

        .input-brinfor-direita {
            text-align: center;
            width: 87px;
            margin-left: -5px;
            height: 27px;
            font-size: 1.2rem;
            margin-top: 13px;
        }

        .imagem-brinfor-direita {
            width: 67px;
        }

        .form-select {
            display: block;
            width: 100%;
            min-height: 2.4rem;
            padding: .375rem 2.25rem .375rem .75rem;
            -moz-padding-start: calc(0.75rem - 3px);
            padding-top: .5rem;
            font-size: .9rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right .75rem center;
            background-size: 16px 12px;
            border: 1px solid #ced4da;
            /* border-radius: .25rem; */
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-label {
            padding-left: .7rem;
            font-weight: 500;
        }

        .input-group-label {
            padding-left: .7rem;
            width: 100%;
        }

        .input-group-addon {
            max-width: 30%;
            height: 100% !important;
        }

        /* .input-group-prepend {
            padding: .5rem;
            border: 1px solid #ced4da;
        } */

        @media only screen and (max-width: 600px) {
            .input-brinfor-esquerda {
                font-size: 30px;
                padding: 0;
            }
            .input-brinfor-direita {
                font-size: 20px;
                padding: 0;
            }
        }
    </style>
</head>

<body class="az-body az-body-sidebar az-light">
<div class="az-content az-content-dashboard-five">
    <div class="az-header">
        <div class="container-fluid">
            <a href="https://brinfor.com.br"><img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px"></a>
            <div class="az-header-center"></div><!-- az-header-center -->
            <div class="az-header-right">
                <div class="dropdown az-profile-menu">
                    <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
                </div>
            </div><!-- az-header-right -->
        </div><!-- container -->
    </div><!-- az-header -->

    <div id="successContainer" class="az-content-body">
        <div class="row">
            <div class="col-md-12">
                <p class="mb-5 mt-3" style="font-size: 30px;">Chamado <b id="chamadoIdText"><?=$idChamado?></b> aberto com sucesso!!</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='<?=$urlInteragir?>'">Interagir com Chamado</button>
                <button class="btn btn-az-primary pd-x-20" onclick="window.location.href='abrir-chamado'">Novo Chamado</button>
            </div>
        </div>
    </div>

    <div class="az-footer">
        <div class="container-fluid">
                <span>&copy; 2002 - <?=date('Y')?> <a href="https://genialsistemas.com.br/" target="_blank">Genial Sistemas</a> Ltda. Todos os direitos reservados.</span>
        </div><!-- container -->
    </div><!-- az-footer -->
</div><!-- az-content -->


<script src="/views/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/views/lib/ionicons/ionicons.js"></script>
<script src="/views/js/azia.js"></script>
<script src="/views/js/iziToast.min.js"></script>

<script>
    // Global Constants
    const CONTRATO_HELPDESK_ID = 1;
    const CONTRATO_HOSPEDAGEM_ID = 2;
    const CONTRATO_DOMINIO_ID = 4;
    const CONTRATO_BACKUP_ID = 5;
    const CONTRATO_SUPORTE_PRODUTO_ID = 6;

    const CONTRATOS_OUTSOURCING_ID_LIST = [
        CONTRATO_HELPDESK_ID,
    ];

    // Message constantes
    const alertMessage = <?=$mensagem ?? '""' ?>;
    const alertTipoMensagem = <?=$tipo_mensagem ?? '""' ?>;

    // Chamado has created
    const createdChamadoId = <?=$createdChamadoId ?? '""' ?>;

    // Global Vars
    let tipoContrato = null;
    let idContrato = null;
    //
    let loading = false;

// IIFE Jquery
(function($) {

    const alertError = function(msg, timeout=3000) {
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
    }

    const alertSuccess = function(msg, timeout=5000) {
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
    }

    const alertWarning = function(msg, timeout=3000) {
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
    }

    const isValidObject = function(objValue) {
        return typeof objValue === 'object'
            && Object.keys(objValue).length > 0;
    }

    const isValidArray = function(arrValue) {
        return typeof arrValue !== 'undefined'
            && Array.isArray(arrValue)
            && arrValue.length > 0;
    }

    const isValidString = function(strValue) {
        return typeof strValue === 'string'
            && String(strValue).length > 0;
    }

    const objectHasProp = function(obj, propName) {
        return isValidObject(obj)
            && isValidString(propName)
            && Object.keys(obj).indexOf(propName) >= 0;
    }

    const getSafeObjPropValue = function(obj, propName='', defValue=null) {
        return objectHasProp(obj, propName) ? obj[propName] : defValue;
    }

    const isOutsourcingContract = function(tipo) {
       return CONTRATOS_OUTSOURCING_ID_LIST.indexOf(tipo) >= 0;
    }

    const gotElmFocus = function(idElm) {
        $(String('#').concat(idElm)).focus();
    }

    const procAlertMessage = function(msg=null, tipo=null) {
        if (msg && msg.length > 0 && tipo && tipo.length > 0) {
            switch (tipo) {
                case 'Success':
                    changeStatusSuccessContainer(true);
                    alertSuccess(msg);

                    break;
                case 'Error':
                    changeStatusSuccessContainer(false);
                    alertError(msg);
                    break;

                default:
                    break;
            }
        }
    };

    $(document).ready(function() {
        procAlertMessage(alertMessage, alertTipoMensagem);
    });

})(jQuery);
</script>

</body>
</html>
