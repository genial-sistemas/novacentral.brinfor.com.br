<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- PWA favicon setup -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">

    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <title>Abrir Chamado - BRInfor</title>

    <link rel="shortcut icon" href="/views/img/brinfor-01.webp">

    <!-- Fonts css -->
    <link href="/views/js/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/views/js/vendor/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="/views/js/vendor/typicons.font/typicons.css" rel="stylesheet">

    <!-- Vendors CSS -->

    <!-- azia CSS -->
    <link rel="stylesheet" href="/views/css/azia.css">

    <!-- Customizações -->
    <link rel="stylesheet" href="/views/css/custom.css">

    <style>
        .custom-alert-icon.warning {
            color: #ffc107;
        }

        #btnBuscarContrato {
            display: none !important;
        }

        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            max-width: 450px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            overflow: hidden;
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .custom-alert-content {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .custom-alert-icon {
            font-size: 24px;
            width: 32px;
            text-align: center;
        }

        .custom-alert-icon.success {
            color: #28a745;
        }

        .custom-alert-icon.error {
            color: #dc3545;
        }

        .custom-alert-message {
            flex: 1;
            font-size: 14px;
            color: #333;
        }

        .custom-alert-message strong {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .custom-alert-message span {
            font-size: 13px;
            color: #666;
        }

        .custom-alert-close {
            cursor: pointer;
            font-size: 18px;
            color: #999;
            transition: color 0.2s;
        }

        .custom-alert-close:hover {
            color: #333;
        }

        .custom-alert-bar {
            height: 3px;
            width: 100%;
            background: #e0e0e0;
            position: relative;
        }

        .custom-alert-bar .progress {
            height: 100%;
            width: 100%;
            background: #28a745;
            animation: shrink 3s linear forwards;
        }

        .custom-alert-bar.error .progress {
            background: #dc3545;
        }

        @keyframes shrink {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        .custom-alert.fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100%);
                display: none;
            }
        }

        /* Box Brinfor */
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

        #resultadoBuscaContainer {
            width: 100%;
        }

        #resultadoBuscaContainer select,
        #resultadoBuscaContainer input {
            width: 100%;
            margin-top: 0;
        }

        /* ESTILOS DE VALIDAÇÃO - CORRIGIDOS */
        .campo-obrigatorio {
            border-left: 3px solid #dc3545 !important;
            transition: border-left 0.3s ease;
        }

        .campo-valido {
            border-left: 3px solid #28a745 !important;
            transition: border-left 0.3s ease;
        }

        select.campo-obrigatorio,
        textarea.campo-obrigatorio {
            border-left: 3px solid #dc3545 !important;
        }

        select.campo-valido,
        textarea.campo-valido {
            border-left: 3px solid #28a745 !important;
        }

        .form-control,
        select.form-control,
        textarea.form-control {
            border-left: 1px solid #ced4da;
            transition: border-left 0.3s ease;
        }

        /* Estilos para os resultados da busca */
        .contrato-info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin-top: 10px;
        }

        .contrato-info-card .info-row {
            display: flex;
            padding: 4px 0;
            font-size: 13px;
        }

        .contrato-info-card .info-row .label {
            font-weight: 600;
            color: #495057;
            min-width: 100px;
        }

        .contrato-info-card .info-row .value {
            color: #212529;
        }

        .select-resultado {
            border-color: #28a745 !important;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

</head>

<body class="az-body az-body-sidebar az-light">

    <div class="az-content az-content-dashboard-five">
        <div class="az-header">
            <div class="container-fluid">
                <a href="https://brinfor.com.br">
                    <img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px">
                </a>
                <div class="az-header-center"></div>
                <div class="az-header-right">
                    <div class="dropdown az-profile-menu">
                        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="az-content-body">
            <form method="post" id="formChamado" class="validated" enctype="multipart/form-data" action="index.php?page=visualizar-chamado">
                <!-- CAMPOS OCULTOS -->
                <input type="hidden" id="id_contrato" name="id_contrato" value="0">
                <input type="hidden" id="id_contato" name="id_contato" value="0">
                <input type="hidden" id="id_equipamento" name="id_equipamento" value="0">
                <input type="hidden" id="id_dominio" name="id_dominio" value="0">
                <input type="hidden" id="id_backup" name="id_backup" value="0">
                <input type="hidden" id="id_suporte" name="id_suporte" value="0">
                <input type="hidden" id="id_pessoa" name="id_pessoa" value="0">
                <input type="hidden" id="id_situacao" name="id_situacao" value="1">
                <input type="hidden" id="id_tipo_abertura" name="id_tipo_abertura" value="1">
                <input type="hidden" id="id_tipo_atendimento" name="id_tipo_atendimento" value="1">
                <input type="hidden" id="id_resp_abertura" name="id_resp_abertura" value="0">
                <input type="hidden" name="codigoSeguranca" value="">

                <!-- Primeira linha: Tipo de Contrato + Busca -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="row align-items-end">
                            <!-- SELECT CONTRATO -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                                    <select class="form-control" name="id_tipo_contrato" id="id_tipo_contrato">
                                        <option value="">Selecione:</option>
                                        <option value="1" <?=($frm_tipo_contrato === '1' ? 'selected' : '')?>>1 - Helpdesk (Outsourcing)</option>
                                        <option value="2" <?=($frm_tipo_contrato === '2' ? 'selected' : '')?>>2 - Hospedagem</option>
                                        <option value="4" <?=($frm_tipo_contrato === '4' ? 'selected' : '')?>>4 - Domínio</option>
                                        <option value="5" <?=($frm_tipo_contrato === '5' ? 'selected' : '')?>>5 - Backup</option>
                                        <option value="6" <?=($frm_tipo_contrato === '6' ? 'selected' : '')?>>6 - Suporte Produtos</option>
                                        <option value="7" <?=($frm_tipo_contrato === '7' ? 'selected' : '')?>>7 - Locação</option>
                                    </select>
                                </div>
                            </div>

                            <!-- BOX BRINFOR (Helpdesk) -->
                            <div class="col-md-4" id="boxContratoOutsourcing" style="display: none;">
                                <div class="box-brinfor">
                                    <div class="box-brinfor-esquerda">
                                        <div class="box-brinfor-esquerda-input">
                                            <input type="text"
                                                class="input-brinfor-esquerda"
                                                id="inputCodigoContrato"
                                                name="etiqueta_codigo_contrato"
                                                value="<?=htmlspecialchars($frm_etiqueta, ENT_QUOTES, 'UTF-8')?>"
                                                maxlength="2"
                                                autocomplete="off"
                                                placeholder=" ">
                                        </div>
                                        <div class="box-brinfor-esquerda-label">
                                            CONTRATO <span class="text-danger">*</span>
                                        </div>
                                    </div>
                                    <div class="box-brinfor-direita">
                                        <div class="box-brinfor-direita-imagem">
                                            <img src="/views/img/brInfor_logo_mini.png" class="img-fluid imagem-brinfor-direita" alt="Br Info logo">
                                        </div>
                                        <div class="box-brinfor-direita-input">
                                            <input type="text"
                                                class="input-brinfor-direita"
                                                id="codigo_equipamento"
                                                name="etiqueta_codigo_equipamento"
                                                value="<?=htmlspecialchars($frm_equipamento, ENT_QUOTES, 'UTF-8')?>"
                                                maxlength="4"
                                                autocomplete="off"
                                                placeholder=" ">
                                        </div>
                                        <div class="box-brinfor-direita-label">
                                            EQUIPAMENTO <span class="text-danger">*</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BUSCA para outros tipos -->
                            <div class="col-md-8" id="boxBuscaDados" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">Buscar dados</label>
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <select class="form-control" id="selectSearchType" name="search_type">
                                                <option value="CNPJ">CNPJ</option>
                                                <option value="CPF">CPF</option>
                                                <option value="ID">ID</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text"
                                                id="inputSearchContext"
                                                name="search_context"
                                                class="form-control"
                                                placeholder="Digite o valor"
                                                maxlength="18"
                                                autocomplete="off">
                                        </div>
                                        <div class="col-md-4">
                                            <div id="resultadoBuscaContainer" style="display:none;"></div>
                                            <button type="button" id="btnBuscarContrato" class="btn btn-primary" style="width: 100%; display: none;">Buscar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campos do formulário -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nome">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite o seu nome" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Digite o e-mail" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" placeholder="Digite o seu telefone">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Urgência <span class="text-danger">*</span></label>
                            <select class="form-control" name="urgencia">
                                <option value="4">Padrão</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Solicitação <span class="text-danger">*</span></label>
                            <select class="form-control" id="selectTipoSolicitacao" name="id_tipo_solicitacao" required>
                                <option value="">Selecione:</option>
                                <option value="1">Incidente Técnico</option>
                                <option value="2">Requisição Usuário</option>
                                <option value="3">Incidente de Segurança</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                Descrição da sua solicitação:
                                <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control"
                                rows="6"
                                name="solicitacao"
                                placeholder="Descreva sua solicitação..."
                                required></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Anexo:</label>
                            <input type="file" name="arquivo" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z,.json,.xml">
                            <small class="form-text text-muted">Limite por arquivo neste servidor: <?=htmlspecialchars(ini_get('upload_max_filesize'), ENT_QUOTES, 'UTF-8')?>.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-az-primary pd-x-20" name="acao" value="abrir_chamado" type="submit">Abrir Chamado</button>
                    </div>
                </div>
            </form>

            <?php if (isset($sucesso) && $sucesso && isset($_POST['id_chamado'])): ?>
                <div style="display:none;">
                    <input type="hidden" id="chamado_id" value="<?= $_POST['id_chamado'] ?>">
                </div>
            <?php elseif (!empty($erro)): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-circle"></i>
                            <?=htmlspecialchars((string)$erro, ENT_QUOTES, 'UTF-8')?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <!-- JS -->
    <script src="/views/js/vendor/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="/views/js/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/views/js/azia.js"></script>
    <script>
        (function($) {
            $(document).ready(function() {
                const $tipoContrato = $('#id_tipo_contrato');
                const $inputBusca = $('#inputSearchContext');
                const $selectSearchType = $('#selectSearchType');
                const $boxBuscaDados = $('#boxBuscaDados');
                const $boxContratoOutsourcing = $('#boxContratoOutsourcing');
                const $resultadoBuscaContainer = $('#resultadoBuscaContainer');

                // ============================================
                // FUNÇÕES DE UTILIDADE
                // ============================================
                function apenasNumeros(valor) {
                    return String(valor).replace(/\D/g, '');
                }

                function limparCamposFormulario() {
                    $('#id_contrato').val('0');
                    $('#id_contato').val('0');
                    $('#id_equipamento').val('0');
                    $('#id_dominio').val('0');
                    $('#id_backup').val('0');
                    $('#id_suporte').val('0');
                    $('#id_pessoa').val('0');
                    $('#nome, #email, #telefone').val('');
                }

                function limparResultados() {
                    $resultadoBuscaContainer.hide().empty();
                }

                // ============================================
                // FUNÇÃO PARA EXTRAIR ID DE QUALQUER FONTE
                // ============================================
                function extrairIdContrato(option) {
                    var $opt = $(option);
                    var id = parseInt($opt.data('id-contrato'), 10) || 0;
                    if (id > 0) return id;

                    var val = $opt.val();
                    if (val && val !== '' && val !== '0') {
                        id = parseInt(val, 10);
                    }

                    if (!id || id === 0) {
                        var dataId = parseInt($opt.data('id-contrato'), 10);
                        if (dataId && dataId > 0) {
                            id = dataId;
                        }
                    }

                    if (!id || id === 0) {
                        var dataDominio = parseInt($opt.data('id-dominio'), 10);
                        if (dataDominio && dataDominio > 0) {
                            id = dataDominio;
                        }
                    }

                    if (!id || id === 0) {
                        var dataBackup = parseInt($opt.data('id-backup'), 10);
                        if (dataBackup && dataBackup > 0) {
                            id = dataBackup;
                        }
                    }

                    if (!id || id === 0) {
                        var dataSuporte = parseInt($opt.data('id-suporte'), 10);
                        if (dataSuporte && dataSuporte > 0) {
                            id = dataSuporte;
                        }
                    }

                    if (!id || id === 0) {
                        var dataLocacao = parseInt($opt.data('id-locacao'), 10);
                        if (dataLocacao && dataLocacao > 0) {
                            id = dataLocacao;
                        }
                    }

                    return id;
                }

                // ============================================
                // FUNÇÃO PRINCIPAL: PREENCHER DADOS DO CONTRATO
                // ============================================
                function preencherDadosContrato(dados) {
                    console.log('=== PREENCHENDO DADOS DO CONTRATO ===');
                    console.log('Dados recebidos:', dados);

                    var idContrato = parseInt(dados.id_contrato, 10) || 0;
                    var idContato = parseInt(dados.id_contato, 10) || 0;
                    var idPessoa = parseInt(dados.id_pessoa, 10) || 0;
                    var idDominio = parseInt(dados.id_dominio, 10) || 0;
                    var idBackup = parseInt(dados.id_backup, 10) || 0;
                    var idSuporte = parseInt(dados.id_suporte, 10) || 0;

                    console.log('ID do contrato FINAL:', idContrato);

                    $('#id_contrato').val(idContrato);
                    $('#id_contato').val(idContato);
                    $('#id_pessoa').val(idPessoa);

                    if (idDominio > 0) $('#id_dominio').val(idDominio);
                    if (idBackup > 0) $('#id_backup').val(idBackup);
                    if (idSuporte > 0) $('#id_suporte').val(idSuporte);
                    if (dados.id_equipamento) $('#id_equipamento').val(parseInt(dados.id_equipamento, 10) || 0);

                    if (dados.nome) $('#nome').val(dados.nome);
                    if (dados.email) $('#email').val(dados.email);
                    if (dados.telefone) $('#telefone').val(dados.telefone);

                    if (idContrato > 0) {
                        console.log('✅ Contrato ID #' + idContrato + ' selecionado');
                        showSuccessAlert('Contrato selecionado!', 'ID #' + idContrato);
                    } else {
                        console.warn('⚠️ ID do contrato não identificado');
                        showWarningAlert('Atenção', 'ID do contrato não identificado.');
                    }

                    // Aplica validação após preencher os dados
                    aplicarValidacaoCampos();
                    return idContrato;
                }

                function showSuccessAlert(message, detail) {
                    showAlert('success', '✅ Sucesso!', message, detail);
                }

                function showErrorAlert(message, detail) {
                    showAlert('error', '❌ Atenção!', message, detail);
                }

                function showWarningAlert(message, detail) {
                    showAlert('warning', '⚠️ Atenção!', message, detail);
                }

                function showAlert(type, title, message, detail) {
                    $('.custom-alert').remove();
                    var $alert = $('<div>', {
                        class: 'custom-alert',
                        'data-type': type
                    });
                    var iconClass = '';
                    var iconSymbol = '';
                    var barClass = (type === 'success') ? '' : 'error';
                    if (type === 'success') {
                        iconClass = 'success';
                        iconSymbol = '✓';
                        barClass = '';
                    } else if (type === 'error') {
                        iconClass = 'error';
                        iconSymbol = '⚠';
                        barClass = 'error';
                    } else if (type === 'warning') {
                        iconClass = 'warning';
                        iconSymbol = '⚠';
                        barClass = 'error';
                    }
                    var content =
                        `<div class="custom-alert-content"><div class="custom-alert-icon ${iconClass}"><span>${iconSymbol}</span></div><div class="custom-alert-message"><strong>${title}</strong><span>${message}</span>${detail ? `<small style="display:block; margin-top:4px; font-size:11px; color:#888;">${detail}</small>` : ''}</div><div class="custom-alert-close">×</div></div><div class="custom-alert-bar ${barClass}"><div class="progress"></div></div>`;
                    $alert.html(content);
                    $('body').append($alert);
                    $alert.find('.custom-alert-close').on('click', function() {
                        closeAlert($alert);
                    });
                    setTimeout(function() {
                        closeAlert($alert);
                    }, 3000);
                }

                function closeAlert($alert) {
                    $alert.addClass('fade-out');
                    setTimeout(function() {
                        $alert.remove();
                    }, 500);
                }

                // ============================================
                // GERENCIAMENTO DE VALIDAÇÃO - CORRIGIDO
                // ============================================
                function aplicarValidacaoCampos() {
                    const tipoContrato = $('#id_tipo_contrato').val();

                    function validarCampo($el, tipo) {
                        if (!$el || $el.length === 0) return;
                        if (!$el.is(':visible')) {
                            $el.removeClass('campo-obrigatorio campo-valido');
                            return;
                        }

                        let valido = false;

                        if (tipo === 'select') {
                            const valor = $el.val();
                            valido = (valor !== '' && valor !== null && valor !== '0');

                            if ($el.is('#selectTipoSolicitacao')) {
                                valido = (valor !== '' && valor !== null && parseInt(valor) > 0);
                            }
                        } else if (tipo === 'textarea') {
                            const valor = $el.val().trim();
                            valido = (valor !== '');
                        } else if (tipo === 'input') {
                            const valor = $el.val().trim();
                            valido = (valor !== '');

                            if (valido && $el.is('#email')) {
                                valido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
                            }

                            if (valido && $el.is('#telefone')) {
                                const numeros = valor.replace(/\D/g, '');
                                valido = (numeros.length >= 10);
                            }
                        }

                        $el.removeClass('campo-obrigatorio campo-valido');

                        if (valido) {
                            $el.addClass('campo-valido');
                        } else if ($el.is(':visible') && ($el.prop('required') || $el.is('[required]'))) {
                            $el.addClass('campo-obrigatorio');
                        }
                    }

                    validarCampo($('#nome'), 'input');
                    validarCampo($('#email'), 'input');
                    validarCampo($('#telefone'), 'input');
                    validarCampo($('#selectTipoSolicitacao'), 'select');
                    validarCampo($('select[name="urgencia"]'), 'select');
                    validarCampo($('textarea[name="solicitacao"]'), 'textarea');

                    if (tipoContrato === '1') {
                        validarCampo($('#inputCodigoContrato'), 'input');
                        validarCampo($('#codigo_equipamento'), 'input');
                    }
                }

                // ============================================
                // GERENCIAMENTO DE REQUIRED
                // ============================================
                function gerenciarCamposRequired() {
                    const tipoContrato = $('#id_tipo_contrato').val();

                    const $contrato = $('#inputCodigoContrato');
                    const $equipamento = $('#codigo_equipamento');
                    const $searchContext = $('#inputSearchContext');
                    const $searchTypeSelect = $('#selectSearchType');

                    $contrato.prop('required', false);
                    $equipamento.prop('required', false);
                    $searchContext.prop('required', false);
                    $searchTypeSelect.prop('required', false);

                    if (tipoContrato === '1') {
                        if ($('#boxContratoOutsourcing').is(':visible')) {
                            $contrato.prop('required', true);
                            $equipamento.prop('required', true);
                        }
                    }

                    if (['2', '4', '5', '6', '7'].includes(tipoContrato)) {
                        if ($('#boxBuscaDados').is(':visible')) {
                            $searchContext.prop('required', true);
                            $searchTypeSelect.prop('required', true);
                        }
                    }
                }

                // ============================================
                // SHOW BLOCKS BY TYPE
                // ============================================
                function showBlocksByType(tipo) {
                    const tipoNumero = parseInt(tipo);
                    $boxContratoOutsourcing.hide();
                    $boxBuscaDados.hide();
                    $resultadoBuscaContainer.hide().empty();
                    if (tipoNumero === 1) $boxContratoOutsourcing.show();
                    else if (tipoNumero >= 2 && tipoNumero <= 7) $boxBuscaDados.show();
                }

                // ============================================
                // BUSCA DE CONTRATO (AJAX)
                // ============================================
                var buscaTimeoutId = null;
                var buscaHelpdeskEmAndamento = false;
                var ultimaBuscaHelpdesk = '';

                function buscarContratoHelpdesk() {
                    const tipoContrato = $('#id_tipo_contrato').val();
                    if (tipoContrato !== '1') return;
                    const contrato = $('#inputCodigoContrato').val().trim();
                    const equipamento = $('#codigo_equipamento').val().trim();
                    if (!contrato || !equipamento) return;

                    const buscaAtual = contrato + '|' + equipamento;
                    if (buscaAtual === ultimaBuscaHelpdesk && (buscaHelpdeskEmAndamento || ($('#id_contrato').val() !== '0' && $('#id_equipamento').val() !== '0'))) return;
                    ultimaBuscaHelpdesk = buscaAtual;
                    buscaHelpdeskEmAndamento = true;

                    limparCamposFormulario();
                    limparResultados();

                    if (buscaTimeoutId) {
                        clearTimeout(buscaTimeoutId);
                        buscaTimeoutId = null;
                    }

                    $.ajax({
                        url: 'index.php?page=buscar-contrato',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_tipo_contrato: tipoContrato,
                            contrato: contrato,
                            equipamento: equipamento
                        },
                        beforeSend: function() {
                            $('#inputCodigoContrato, #codigo_equipamento').css('opacity', '0.6');
                            buscaTimeoutId = setTimeout(function() {
                                if ($.active > 0) {
                                    showErrorAlert('Tempo esgotado', 'A busca está demorando muito. Tente novamente.');
                                    $('#inputCodigoContrato, #codigo_equipamento').css('opacity', '1');
                                    limparResultados();
                                    aplicarValidacaoCampos();
                                }
                            }, 30000);
                        },
                        success: function(resposta) {
                            if (buscaTimeoutId) {
                                clearTimeout(buscaTimeoutId);
                                buscaTimeoutId = null;
                            }
                            if (resposta.erro) {
                                showErrorAlert('Nenhum contrato encontrado', resposta.erro);
                                limparCamposFormulario();
                            } else if (resposta.success) {
                                var dados = {
                                    id_contrato: resposta.id_contrato || 0,
                                    id_contato: resposta.id_contato || 0,
                                    id_pessoa: resposta.id_pessoa || 0,
                                    id_equipamento: resposta.id_equipamento || 0,
                                    nome: resposta.nome || '',
                                    email: resposta.email || '',
                                    telefone: resposta.telefone || '',
                                    codigo_contrato: resposta.codigo_contrato || '',
                                    descricao_contrato: resposta.descricao_contrato || ''
                                };
                                preencherDadosContrato(dados);
                                showSuccessAlert('Contrato encontrado!', 'Dados carregados com sucesso');
                            }
                            aplicarValidacaoCampos();
                        },
                        error: function(xhr) {
                            if (buscaTimeoutId) {
                                clearTimeout(buscaTimeoutId);
                                buscaTimeoutId = null;
                            }
                            console.error(xhr.responseText);
                            showErrorAlert('Erro na busca', 'Verifique o console');
                            limparResultados();
                            aplicarValidacaoCampos();
                        },
                        complete: function() {
                            $('#inputCodigoContrato, #codigo_equipamento').css('opacity', '1');
                            buscaHelpdeskEmAndamento = false;
                            if (buscaTimeoutId) {
                                clearTimeout(buscaTimeoutId);
                                buscaTimeoutId = null;
                            }
                        }
                    });
                }

                function enviarBusca() {
                    const tipoContrato = $('#id_tipo_contrato').val();
                    const searchType = $('#selectSearchType').val();
                    let valorBusca = $('#inputSearchContext').val().trim();

                    if (!tipoContrato || tipoContrato === '0' || tipoContrato === '') {
                        showErrorAlert('Selecione o tipo de contrato', 'Escolha um tipo de contrato antes de buscar.');
                        return;
                    }

                    if (searchType === 'CNPJ' || searchType === 'CPF') {
                        valorBusca = apenasNumeros(valorBusca);
                    }

                    if (!valorBusca) {
                        showErrorAlert('Campo vazio', 'Informe um valor para busca');
                        aplicarValidacaoCampos();
                        return;
                    }

                    if (searchType === 'CPF' && valorBusca.length !== 11) {
                        showErrorAlert('CPF inválido', 'O CPF deve ter 11 dígitos.');
                        return;
                    }

                    if (searchType === 'CNPJ' && valorBusca.length !== 14) {
                        showErrorAlert('CNPJ inválido', 'O CNPJ deve ter 14 dígitos.');
                        return;
                    }

                    if (searchType === 'ID' && !/^\d+$/.test(valorBusca)) {
                        showErrorAlert('ID inválido', 'Por favor, informe um número válido para busca.');
                        return;
                    }

                    limparCamposFormulario();
                    limparResultados();

                    if (buscaTimeoutId) {
                        clearTimeout(buscaTimeoutId);
                        buscaTimeoutId = null;
                    }

                    $.ajax({
                        url: 'index.php?page=buscar-contrato',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_tipo_contrato: tipoContrato,
                            search_type: searchType,
                            valor: valorBusca
                        },
                        beforeSend: function() {
                            $('#inputSearchContext').css('opacity', '0.6');
                            $resultadoBuscaContainer.html(
                                '<div class="text-center p-2"><span class="loading-spinner"></span> Buscando...</div>'
                            ).show();
                            buscaTimeoutId = setTimeout(function() {
                                if ($.active > 0) {
                                    showErrorAlert('Tempo esgotado', 'A busca está demorando muito. Tente novamente.');
                                    $('#inputSearchContext').css('opacity', '1');
                                    limparResultados();
                                    aplicarValidacaoCampos();
                                }
                            }, 30000);
                        },
                        success: function(resposta) {
                            if (buscaTimeoutId) {
                                clearTimeout(buscaTimeoutId);
                                buscaTimeoutId = null;
                            }
                            console.log('RESPOSTA DA BUSCA:', resposta);

                            if (resposta.erro) {
                                showErrorAlert('Nenhum resultado', resposta.erro);
                                limparCamposFormulario();
                                limparResultados();
                                aplicarValidacaoCampos();
                                return;
                            }

                            if (resposta.success) {
                                if (resposta.select_html) {
                                    $resultadoBuscaContainer.html(resposta.select_html).show();

                                    var $select = $('#contrato_selecionado');
                                    var totalOptions = $select.find('option').length;

                                    if (totalOptions <= 2) {
                                        showSuccessAlert('Contrato encontrado', 'Selecione o contrato na lista abaixo para continuar.');
                                    } else {
                                        showSuccessAlert('Encontrados ' + (totalOptions - 1) + ' contratos', 'Selecione um na lista abaixo');
                                    }
                                    limparCamposFormulario();
                                } else {
                                    const dados = {
                                        id_contrato: parseInt(resposta.id_contrato, 10) || (searchType === 'ID' ? parseInt(valorBusca, 10) : 0),
                                        id_contato: parseInt(resposta.id_contato, 10) || 0,
                                        id_equipamento: parseInt(resposta.id_equipamento, 10) || 0,
                                        id_dominio: parseInt(resposta.id_dominio, 10) || 0,
                                        id_backup: parseInt(resposta.id_backup, 10) || 0,
                                        id_suporte: parseInt(resposta.id_suporte, 10) || 0,
                                        nome: resposta.nome || '',
                                        email: resposta.email || '',
                                        telefone: resposta.telefone || ''
                                    };
                                    const itemEncontrado = resposta.dominio || resposta.backup || resposta.suporte || resposta.locacao || '';
                                    limparResultados();
                                    preencherDadosContrato(dados);
                                    if (itemEncontrado) {
                                        $resultadoBuscaContainer.text(itemEncontrado).show();
                                    }
                                }
                            }
                            aplicarValidacaoCampos();
                        },
                        error: function(xhr) {
                            if (buscaTimeoutId) {
                                clearTimeout(buscaTimeoutId);
                                buscaTimeoutId = null;
                            }
                            console.error('ERRO AJAX:', xhr.responseText);
                            showErrorAlert('Erro na busca', 'Não foi possível conectar ao servidor');
                            limparResultados();
                            aplicarValidacaoCampos();
                        },
                        complete: function() {
                            $('#inputSearchContext').css('opacity', '1');
                            if (buscaTimeoutId) {
                                clearTimeout(buscaTimeoutId);
                                buscaTimeoutId = null;
                            }
                        }
                    });
                }

                // ============================================
                // PROCESSAR SELEÇÃO DO SELECT
                // ============================================
                $(document).on('change', '#contrato_selecionado', function() {
                    var optionSelected = $(this).find('option:selected');

                    if (!optionSelected.val() || optionSelected.val() === '' || optionSelected.val() === '0') {
                        var id = extrairIdContrato(optionSelected);
                        if (id > 0) {
                            var dados = {
                                id_contrato: id,
                                id_dominio: parseInt(optionSelected.data('id-dominio'), 10) || 0,
                                id_backup: parseInt(optionSelected.data('id-backup'), 10) || 0,
                                id_suporte: parseInt(optionSelected.data('id-suporte'), 10) || 0,
                                id_contato: parseInt(optionSelected.data('id-contato'), 10) || 0,
                                id_equipamento: parseInt(optionSelected.data('id-equipamento'), 10) || 0,
                                id_pessoa: parseInt(optionSelected.data('id-pessoa'), 10) || 0,
                                nome: optionSelected.data('nome') || '',
                                email: optionSelected.data('email') || '',
                                telefone: optionSelected.data('telefone') || '',
                                codigo_contrato: optionSelected.data('codigo') || '',
                                descricao_contrato: optionSelected.data('descricao') || '',
                                dominio: optionSelected.data('dominio') || ''
                            };
                            preencherDadosContrato(dados);
                            $resultadoBuscaContainer.hide().empty();
                            aplicarValidacaoCampos();
                            return;
                        }

                        limparCamposFormulario();
                        aplicarValidacaoCampos();
                        return;
                    }

                    var idContrato = parseInt(optionSelected.val(), 10);

                    var dados = {
                        id_contrato: idContrato,
                        id_contato: parseInt(optionSelected.data('id-contato'), 10) || 0,
                        id_pessoa: parseInt(optionSelected.data('id-pessoa'), 10) || 0,
                        id_dominio: parseInt(optionSelected.data('id-dominio'), 10) || 0,
                        id_backup: parseInt(optionSelected.data('id-backup'), 10) || 0,
                        id_suporte: parseInt(optionSelected.data('id-suporte'), 10) || 0,
                        nome: optionSelected.data('nome') || '',
                        email: optionSelected.data('email') || '',
                        telefone: optionSelected.data('telefone') || '',
                        codigo_contrato: optionSelected.data('codigo') || '',
                        descricao_contrato: optionSelected.data('descricao') || '',
                        dominio: optionSelected.data('dominio') || '',
                        cpf: optionSelected.data('cpf') || '',
                        cnpj: optionSelected.data('cnpj') || ''
                    };

                    preencherDadosContrato(dados);
                    $resultadoBuscaContainer.hide().empty();
                    aplicarValidacaoCampos();
                });

                $(document).on('change', '#resultadoBusca', function() {
                    var optionSelected = $(this).find('option:selected');
                    var id = extrairIdContrato(optionSelected);

                    if (id > 0) {
                        var $select = $('#contrato_selecionado');
                        if ($select.length) {
                            $select.val(id);
                            $select.trigger('change');
                        } else {
                            var dados = {
                                id_contrato: id,
                                id_dominio: parseInt(optionSelected.data('id-dominio'), 10) || 0,
                                id_backup: parseInt(optionSelected.data('id-backup'), 10) || 0,
                                id_suporte: parseInt(optionSelected.data('id-suporte'), 10) || 0,
                                id_contato: parseInt(optionSelected.data('id-contato'), 10) || 0,
                                id_equipamento: parseInt(optionSelected.data('id-equipamento'), 10) || 0,
                                id_pessoa: parseInt(optionSelected.data('id-pessoa'), 10) || 0,
                                nome: optionSelected.data('nome') || '',
                                email: optionSelected.data('email') || '',
                                telefone: optionSelected.data('telefone') || '',
                                codigo_contrato: optionSelected.data('codigo') || '',
                                descricao_contrato: optionSelected.data('descricao') || '',
                                dominio: optionSelected.data('dominio') || ''
                            };
                            preencherDadosContrato(dados);
                            $resultadoBuscaContainer.hide().empty();
                            aplicarValidacaoCampos();
                        }
                    } else {
                        limparCamposFormulario();
                    }
                });

                // ============================================
                // EVENTOS DO FORMULÁRIO
                // ============================================
                $tipoContrato.on('change', function() {
                    showBlocksByType(this.value);
                    gerenciarCamposRequired();
                    limparCamposFormulario();
                    limparResultados();
                    $('#inputSearchContext').val('');
                    $('#inputCodigoContrato, #codigo_equipamento').val('');
                    aplicarValidacaoCampos();
                });

                $('#inputCodigoContrato, #codigo_equipamento').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        buscarContratoHelpdesk();
                    }
                });

                $('#inputCodigoContrato, #codigo_equipamento').on('blur', function() {
                    const contrato = $('#inputCodigoContrato').val().trim();
                    const equipamento = $('#codigo_equipamento').val().trim();
                    if (contrato !== '' || equipamento !== '') {
                        buscarContratoHelpdesk();
                    }
                    aplicarValidacaoCampos();
                });

                $('#inputSearchContext').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        const valor = $(this).val().trim();
                        if (valor !== '') {
                            enviarBusca();
                        }
                    }
                });

                $('#inputSearchContext').on('blur', function() {
                    const valor = $(this).val().trim();
                    if (valor !== '') {
                        const searchType = $('#selectSearchType').val();
                        let valorLimpo = valor;
                        if (searchType === 'CNPJ' || searchType === 'CPF') {
                            valorLimpo = apenasNumeros(valor);
                        }
                        if (valorLimpo.length >= 1) {
                            enviarBusca();
                        }
                    }
                    aplicarValidacaoCampos();
                });

                $('#selectSearchType').on('change', function() {
                    limparCamposFormulario();
                    $('#inputSearchContext').val('');
                    limparResultados();
                    aplicarValidacaoCampos();
                });

                // ============================================
                // MÁSCARA DOS CAMPOS
                // ============================================
                $('#selectSearchType').on('change', function() {
                    const tipo = $(this).val();
                    const $campoBusca = $('#inputSearchContext');
                    $campoBusca.val('');
                    if (tipo === 'CNPJ') {
                        $campoBusca.mask('00.000.000/0000-00', {
                            reverse: true
                        });
                        $campoBusca.attr('placeholder', 'Digite o CNPJ');
                    } else if (tipo === 'CPF') {
                        $campoBusca.mask('000.000.000-00', {
                            reverse: true
                        });
                        $campoBusca.attr('placeholder', 'Digite o CPF');
                    } else {
                        $campoBusca.unmask();
                        $campoBusca.attr('placeholder', 'Digite o ID do contrato');
                    }
                    aplicarValidacaoCampos();
                });
                $('#selectSearchType').trigger('change');

                if ($.fn.mask) {
                    $('#telefone').mask('(00) 00000-0000');
                }

                // ============================================
                // VALIDAÇÃO DOS CAMPOS EM TEMPO REAL - COM ONCHANGE E ONINPUT
                // ============================================

                // --- CAMPOS PRINCIPAIS ---
                // Evento 'input' - dispara enquanto digita
                $('#nome, #email, #telefone, #selectTipoSolicitacao, select[name="urgencia"], textarea[name="solicitacao"]')
                    .on('input change', function() {
                        aplicarValidacaoCampos();
                    });

                // Evento 'blur' - dispara quando perde o foco
                $('#nome, #email, #telefone, #selectTipoSolicitacao, select[name="urgencia"], textarea[name="solicitacao"]')
                    .on('blur', function() {
                        aplicarValidacaoCampos();
                    });

                // --- CAMPOS DO HELPDESK ---
                $('#inputCodigoContrato, #codigo_equipamento')
                    .on('input change', function() {
                        aplicarValidacaoCampos();
                    });

                $('#inputCodigoContrato, #codigo_equipamento')
                    .on('blur', function() {
                        aplicarValidacaoCampos();
                    });

                // --- CAMPO DE BUSCA ---
                $('#inputSearchContext')
                    .on('input change', function() {
                        aplicarValidacaoCampos();
                    })
                    .on('blur', function() {
                        aplicarValidacaoCampos();
                    });

                // --- SELECT DE TIPO DE CONTRATO ---
                $('#id_tipo_contrato').on('change', function() {
                    aplicarValidacaoCampos();
                });

                // --- SELECT DE TIPO DE BUSCA ---
                $('#selectSearchType').on('change', function() {
                    aplicarValidacaoCampos();
                });

                // ============================================
                // VALIDAÇÃO FINAL NO ENVIO
                // ============================================
                $('#formChamado').on('submit', function(e) {
                    var tipoContrato = $('#id_tipo_contrato').val();
                    var searchType = $('#selectSearchType').val();

                    if (searchType === 'ID') {
                        var idDigitado = $('#inputSearchContext').val().trim();
                        if (idDigitado) {
                            var idNum = parseInt(idDigitado.replace(/[^0-9]/g, ''), 10);
                            if (idNum > 0) {
                                $('#id_contrato').val(idNum);
                            } else {
                                var numeros = idDigitado.match(/\d+/g);
                                if (numeros && numeros.length > 0) {
                                    idNum = parseInt(numeros[0], 10);
                                    if (idNum > 0) {
                                        $('#id_contrato').val(idNum);
                                    }
                                }
                            }
                        }
                    }

                    if (searchType === 'CPF' || searchType === 'CNPJ') {
                        var $select = $('#contrato_selecionado');
                        if ($select.length && $select.val() && $select.val() !== '' && $select.val() !== '0') {
                            $('#id_contrato').val($select.val());
                        } else {
                            var $selectOld = $('#resultadoBusca');
                            if ($selectOld.length) {
                                var optionSelected = $selectOld.find('option:selected');
                                var id = extrairIdContrato(optionSelected);
                                if (id > 0) {
                                    $('#id_contrato').val(id);
                                }
                            }
                        }
                    }

                    var camposInvalidos = [];

                    if (!$('#nome').val().trim()) {
                        camposInvalidos.push('Nome');
                    }

                    if (!$('#email').val().trim()) {
                        camposInvalidos.push('E-mail');
                    }

                    if (!$('#selectTipoSolicitacao').val()) {
                        camposInvalidos.push('Tipo de Solicitação');
                    }

                    if (!$('textarea[name="solicitacao"]').val().trim()) {
                        camposInvalidos.push('Descrição da solicitação');
                    }

                    if (!tipoContrato || tipoContrato === '0' || tipoContrato === '') {
                        e.preventDefault();
                        showErrorAlert('Selecione um tipo de contrato',
                            'Por favor, selecione um tipo de contrato válido.');
                        return false;
                    }

                    if (tipoContrato !== '1') {
                        var idContratoFinalCheck = $('#id_contrato').val();
                        if (!idContratoFinalCheck || idContratoFinalCheck === '0') {
                            e.preventDefault();
                            showErrorAlert('Contrato não selecionado',
                                'Por favor, busque e selecione um contrato antes de abrir o chamado.');
                            return false;
                        }
                    }

                    if (tipoContrato === '1') {
                        const contrato = $('#inputCodigoContrato').val().trim();
                        const equipamento = $('#codigo_equipamento').val().trim();
                        if (!contrato || !equipamento) {
                            e.preventDefault();
                            showErrorAlert('Campos obrigatórios',
                                'Por favor, preencha o Contrato e o Equipamento.');
                            return false;
                        }

                        const idContratoHelpdesk = $('#id_contrato').val();
                        if (!idContratoHelpdesk || idContratoHelpdesk === '0') {
                            e.preventDefault();
                            showErrorAlert('Contrato não encontrado',
                                'Por favor, aguarde a validação automática do contrato.');
                            return false;
                        }
                    }

                    if (camposInvalidos.length > 0) {
                        e.preventDefault();
                        showErrorAlert('Campos obrigatórios',
                            'Por favor, preencha os seguintes campos:\n• ' + camposInvalidos.join('\n• '));
                        return false;
                    }

                    return true;
                });

                // ============================================
                // INICIALIZAÇÃO
                // ============================================
                showBlocksByType($tipoContrato.val());
                gerenciarCamposRequired();
                aplicarValidacaoCampos();
                if (
                    $tipoContrato.val() === '1'
                    && $('#inputCodigoContrato').val().trim() !== ''
                    && $('#codigo_equipamento').val().trim() !== ''
                ) {
                    buscarContratoHelpdesk();
                }

            });
        })(jQuery);
    </script>
    <?php include 'footer.php'; ?>
</body>

</html>