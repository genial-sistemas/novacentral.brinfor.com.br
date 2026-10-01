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
    <link href="/views/lib/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="/views/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
    <link href="/views/lib/typicons.font/typicons.css" rel="stylesheet">
    <link href="/views/lib/flag-icon-css/css/flag-icon.min.css" rel="stylesheet">

    <!-- Vendors CSS -->
    <link href="/views/css/iziToast.min.css" rel="stylesheet">
    <link href="/views/lib/morris.js/morris.css" rel="stylesheet">

    <!-- azia CSS -->
    <link rel="stylesheet" href="/views/css/azia.css">

    <!-- Customizações -->
    <link rel="stylesheet" href="/views/css/custom.css">

    <style>
        .custom-alert-icon.warning {
            color: #ffc107;
        }

        /* Esconder o botão Buscar - busca automática via Enter ou blur */
        #btnBuscarContrato {
            display: none !important;
        }

        /* Alertas personalizados */
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

        /* Box Brinfor - Estilo para helpdesk */
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

        /* Estilo para o container de resultados ao lado do botão */
        #resultadoBuscaContainer select,
        #resultadoBuscaContainer input {
            width: 100%;
        }

        /* Estilo para campos obrigatórios */
        .form-control:required,
        select:required,
        textarea:required {
            border-left: 3px solid #dc3545;
        }

        .form-control:required:valid,
        select:required:valid,
        textarea:required:valid {
            border-left: 3px solid #28a745;
        }
    </style>

</head>

<body class="az-body az-body-sidebar az-light">

    <!-- az-content -->
    <div class="az-content az-content-dashboard-five">
        <div class="az-header">
            <div class="container-fluid">
                <a href="https://brinfor.com.br">
                    <img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px">
                </a>
                <div class="az-header-center"></div><!-- az-header-center -->
                <div class="az-header-right">
                    <div class="dropdown az-profile-menu">
                        <h2 class="az-content-title mg-b-5 mg-b-lg-8">Abrir Chamado</h2>
                    </div>
                </div><!-- az-header-right -->
            </div><!-- container -->
        </div><!-- az-header -->
        <div class="az-content-body">
            <form method="post" id="formChamado" class="validated" enctype="multipart/form-data" action="chamados_abrir">
                <!-- Primeira linha: Tipo de Contrato + Box Brinfor -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="row align-items-end">
                            <!-- SELECT CONTRATO (obrigatório) -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                                    <select class="form-control" name="id_tipo_contrato" id="id_tipo_contrato" required>
                                        <option value="">Selecione:</option>
                                        <option value="1">1 - Helpdesk (Outsourcing)</option>
                                        <option value="2">2 - Hospedagem</option>
                                        <option value="4">4 - Domínio</option>
                                        <option value="5">5 - Backup</option>
                                        <option value="6">6 - Suporte Produtos</option>
                                        <option value="7">7 - Locação</option>
                                    </select>
                                </div>
                            </div>

                            <!-- BOX BRINFOR (apenas para Helpdesk) -->
                            <div class="col-md-4" id="boxContratoOutsourcing" style="display: none;">
                                <div class="box-brinfor">
                                    <div class="box-brinfor-esquerda">
                                        <div class="box-brinfor-esquerda-input">
                                            <input type="text"
                                                class="input-brinfor-esquerda"
                                                id="inputCodigoContrato"
                                                name="etiqueta_codigo_contrato"
                                                value="<?= $frm_etiqueta_codigo_contrato ?>"
                                                maxlength="2"
                                                max="2"
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
                                                value="<?= $frm_etiqueta_codigo_equipamento ?? null ?>"
                                                maxlength="4"
                                                max="4"
                                                autocomplete="off"
                                                placeholder=" ">
                                        </div>
                                        <div class="box-brinfor-direita-label">
                                            EQUIPAMENTO <span class="text-danger">*</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BUSCA CNPJ/CPF/ID (para outros tipos) -->
                            <div class="col-md-8" id="boxBuscaDados" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">Buscar dados <span class="text-danger">*</span></label>
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
                                            <button type="button" id="btnBuscarContrato" class="btn btn-primary" style="width: 100%;">
                                                Buscar
                                            </button>
                                        </div>
                                    </div>
                                    <!-- CONTAINER DO RESULTADO (aparece após a busca) -->
                                    <div class="row mt-2" id="resultadoBuscaContainer" style="display:none;">
                                        <div class="col-md-12">
                                            <!-- O select/input será inserido aqui -->
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
                            <label>Tipo de Solicitação <span class="text-danger">*</span></label>
                            <select class="form-control" id="selectTipoSolicitacao" name="id_tipo_solicitacao" required>
                                <option value="">Selecione:</option>
                                <option value="1">Incidente Técnico</option>
                                <option value="2">Requisição Usuário</option>
                                <option value="3">Incidente de Segurança</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Urgência <span class="text-danger">*</span></label>
                            <select class="form-control" name="urgencia" required>
                                <option value="4">Padrão</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Descrição da sua solicitação: <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="6" name="solicitacao" placeholder="Descreva sua solicitação ..." required></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Anexo:</label>
                            <input type="file" name="arquivo">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-az-primary pd-x-20" name="acao" value="abrir_chamado" type="submit">Abrir Chamado</button>
                    </div>
                </div>
            </form>

            <?php if ($_POST['acao'] == "abrir_chamado"): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <p>Chamado Aberto com sucesso!!</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-az-primary pd-x-20" onclick="window.location.href = 'chamados_abrir'">Ok</button>
                    </div>
                </div>
            <?php endif; ?>
        </div><!-- az-content-body -->
    </div><!-- az-content -->

    <!-- JS no final do body -->
    <script src="/views/lib/jquery/jquery.min.js"></script>
    <script src="/views/js/iziToast.min.js"></script>
    <script src="/views/js/jquery.mask.min.js"></script>
    <script src="/views/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/views/lib/ionicons/ionicons.js"></script>
    <script src="/views/js/azia.js"></script>
    <script src="/views/js/geral/global-constants.js"></script>
    <script src="/views/js/geral/helper-functions.js"></script>
    <script src="/views/js/geral/alert-message-functions.js"></script>

    <script>
        // ============================================
        // ALERTAS PERSONALIZADOS
        // ============================================

        function showSuccessAlert(message, detail = '') {
            showAlert('success', '✅ Sucesso!', message, detail);
        }

        function showErrorAlert(message, detail = '') {
            showAlert('error', '❌ Atenção!', message, detail);
        }

        function showWarningAlert(message, detail = '') {
            showAlert('warning', '⚠️ Atenção!', message, detail);
        }

        function showAlert(type, title, message, detail = '') {
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

            var content = `
        <div class="custom-alert-content">
            <div class="custom-alert-icon ${iconClass}">
                <span>${iconSymbol}</span>
            </div>
            <div class="custom-alert-message">
                <strong>${title}</strong>
                <span>${message}</span>
                ${detail ? `<small style="display:block; margin-top:4px; font-size:11px; color:#888;">${detail}</small>` : ''}
            </div>
            <div class="custom-alert-close">×</div>
        </div>
        <div class="custom-alert-bar ${barClass}">
            <div class="progress"></div>
        </div>
    `;

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

        (function($) {
            $(document).ready(function() {

                // ============================================
                // 1. ELEMENTOS DA PÁGINA
                // ============================================
                const $tipoContrato = $('#id_tipo_contrato');
                const $inputBusca = $('#inputSearchContext');
                const $selectSearchType = $('#selectSearchType');
                const $boxBuscaDados = $('#boxBuscaDados');
                const $boxContratoOutsourcing = $('#boxContratoOutsourcing');
                const $resultadoBuscaContainer = $('#resultadoBuscaContainer');

                // Esconder o botão Buscar - busca automática via Enter ou blur
                $('#btnBuscarContrato').hide();

                // ============================================
                // 2. CONTROLE DE BLOCOS
                // ============================================
                function showBlocksByType(tipo) {
                    const tipoNumero = parseInt(tipo);

                    // Esconde ambos por padrão
                    $boxContratoOutsourcing.hide();
                    $boxBuscaDados.hide();
                    $resultadoBuscaContainer.hide().find('.col-md-12').empty();

                    if (tipoNumero === 1) {
                        $boxContratoOutsourcing.show();
                    } else if (tipoNumero >= 2 && tipoNumero <= 7) {
                        $boxBuscaDados.show();
                    }
                }

                // ============================================
                // 3. FUNÇÃO PARA GERENCIAR CAMPOS REQUIRED DINAMICAMENTE
                // ============================================
                function gerenciarCamposRequired() {
                    const tipoContrato = $('#id_tipo_contrato').val();

                    // Campos do Helpdesk (Tipo 1)
                    const $contrato = $('#inputCodigoContrato');
                    const $equipamento = $('#codigo_equipamento');
                    const $searchContext = $('#inputSearchContext');
                    const $searchTypeSelect = $('#selectSearchType');

                    // Remove required de todos os campos dinâmicos
                    $contrato.prop('required', false);
                    $equipamento.prop('required', false);
                    $searchContext.prop('required', false);
                    $searchTypeSelect.prop('required', false);

                    if (tipoContrato === '1') {
                        // Helpdesk: ativa required para contrato e equipamento
                        $contrato.prop('required', true);
                        $equipamento.prop('required', true);
                        console.log('Required ativado para CONTRATO e EQUIPAMENTO');
                    } else if (tipoContrato >= 2 && tipoContrato <= 7) {
                        // Outros tipos: ativa required para o campo de busca e tipo de busca
                        $searchContext.prop('required', true);
                        $searchTypeSelect.prop('required', true);
                        console.log('Required ativado para campo de busca e tipo de busca');
                    }
                }

                // ============================================
                // 4. EVENTO DE MUDANÇA DO TIPO DE CONTRATO
                // ============================================
                $tipoContrato.on('change', function() {
                    showBlocksByType(this.value);
                    gerenciarCamposRequired();
                    limparCamposFormulario();
                    $resultadoBuscaContainer.hide().find('.col-md-12').empty();
                    $('#inputSearchContext').val('');
                    $('#inputCodigoContrato, #codigo_equipamento').val('');
                });

                // Inicialização
                showBlocksByType($tipoContrato.val());
                gerenciarCamposRequired();

                // ============================================
                // 5. MÁSCARA DO TELEFONE
                // ============================================
                if ($.fn.mask) {
                    $('#telefone').mask('(00) 00000-0000');
                }

                // ============================================
                // FUNÇÃO PARA LIMPAR RESULTADOS
                // ============================================
                function limparResultados() {
                    $resultadoBuscaContainer.hide().find('.col-md-12').empty();
                }

                // ============================================
                // FUNÇÃO PARA LIMPAR CAMPOS DO FORMULÁRIO
                // ============================================
                function limparCamposFormulario() {
                    $('#nome').val('');
                    $('#email').val('');
                    $('#telefone').val('');
                }

                // ============================================
                // FUNÇÃO PARA REMOVER FORMATAÇÃO
                // ============================================
                function apenasNumeros(valor) {
                    return valor.replace(/\D/g, '');
                }

                // ============================================
                // FUNÇÃO PARA VERIFICAR SE DADOS ESTÃO VAZIOS
                // ============================================
                function verificarDadosVazios(nome, email, telefone) {
                    if ((!nome || nome === '') && (!email || email === '') && (!telefone || telefone === '')) {
                        showWarningAlert('Dados de cadastro vazios!', 'Este contrato não possui informações de contato cadastradas.');
                        return true;
                    }
                    return false;
                }

                // ============================================
                // BUSCAR DADOS DO CONTATO POR ID
                // ============================================
                function buscarDadosContato(id_contato, callback) {
                    $.ajax({
                        url: 'buscar-contrato',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_contato: id_contato
                        },
                        success: function(resposta) {
                            if (resposta.success) {
                                var nome = resposta.nome || '';
                                var email = resposta.email || '';
                                var telefone = resposta.telefone || '';

                                if (verificarDadosVazios(nome, email, telefone)) {
                                    limparCamposFormulario();
                                } else {
                                    $('#nome').val(nome);
                                    $('#email').val(email);
                                    $('#telefone').val(telefone);
                                    showSuccessAlert('Contato carregado!', 'Dados preenchidos automaticamente');
                                }
                                if (callback) callback(true);
                            } else {
                                showErrorAlert('Erro', resposta.erro || 'Contato não encontrado');
                                limparCamposFormulario();
                                if (callback) callback(false);
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            showErrorAlert('Erro', 'Não foi possível carregar os dados do contato');
                            limparCamposFormulario();
                            if (callback) callback(false);
                        }
                    });
                }

                // ============================================
                // BUSCA HELPDESK (TIPO 1)
                // ============================================
                function buscarContratoHelpdesk() {
                    const tipoContrato = $('#id_tipo_contrato').val();
                    if (tipoContrato !== '1') return;

                    const contrato = $('#inputCodigoContrato').val().trim();
                    const equipamento = $('#codigo_equipamento').val().trim();

                    if (!contrato && !equipamento) return;

                    $.ajax({
                        url: 'buscar-contrato',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_tipo_contrato: tipoContrato,
                            contrato: contrato,
                            equipamento: equipamento
                        },
                        beforeSend: function() {
                            $('#inputCodigoContrato, #codigo_equipamento').css('opacity', '0.6');
                            limparCamposFormulario();
                        },
                        success: function(resposta) {
                            if (resposta.erro) {
                                showErrorAlert('Nenhum contrato encontrado', resposta.erro);
                                limparCamposFormulario();
                            } else if (resposta.success) {
                                var nome = resposta.nome || '';
                                var email = resposta.email || '';
                                var telefone = resposta.telefone || '';

                                if (verificarDadosVazios(nome, email, telefone)) {
                                    limparCamposFormulario();
                                } else {
                                    $('#nome').val(nome);
                                    $('#email').val(email);
                                    $('#telefone').val(telefone);
                                    showSuccessAlert('Dados encontrados!', 'Contrato carregado com sucesso');
                                }
                            }
                            limparResultados();
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            showErrorAlert('Erro na busca', 'Verifique o console');
                            limparResultados();
                        },
                        complete: function() {
                            $('#inputCodigoContrato, #codigo_equipamento').css('opacity', '1');
                        }
                    });
                }

                // ============================================
                // FUNÇÃO PARA ENVIAR BUSCA (TIPOS 2-7)
                // ============================================
                function enviarBusca() {
                    const tipoContrato = $('#id_tipo_contrato').val();
                    const searchType = $('#selectSearchType').val();
                    let valorBusca = $('#inputSearchContext').val().trim();

                    if (searchType === 'CNPJ' || searchType === 'CPF') {
                        valorBusca = apenasNumeros(valorBusca);
                    }

                    if (!valorBusca) {
                        showErrorAlert('Campo vazio', 'Informe um valor para busca');
                        return;
                    }

                    limparCamposFormulario();
                    limparResultados();

                    $.ajax({
                        url: 'buscar-contrato',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_tipo_contrato: tipoContrato,
                            search_type: searchType,
                            valor: valorBusca
                        },
                        beforeSend: function() {
                            $('#inputSearchContext').css('opacity', '0.6');
                        },
                        success: function(resposta) {
                            console.log('RESPOSTA COMPLETA:', resposta);

                            if (resposta.erro) {
                                showErrorAlert('Nenhum resultado encontrado', resposta.erro);
                                limparCamposFormulario();
                                limparResultados();
                            } else if (resposta.success) {
                                if (resposta.select_html) {
                                    $resultadoBuscaContainer.show().find('.col-md-12').html(resposta.select_html);
                                    showSuccessAlert('Dados encontrados!', 'Múltiplos resultados. Selecione um abaixo.');
                                    limparCamposFormulario();
                                } else if (resposta.input_html) {
                                    $resultadoBuscaContainer.show().find('.col-md-12').html(resposta.input_html);
                                    setTimeout(function() {
                                        var $input = $('#resultadoBusca');
                                        var nome = $input.data('nome') || resposta.nome || '';
                                        var email = $input.data('email') || resposta.email || '';
                                        var telefone = $input.data('telefone') || resposta.telefone || '';
                                        $('#nome').val(nome);
                                        $('#email').val(email);
                                        $('#telefone').val(telefone);
                                        if (nome || email || telefone) {
                                            showSuccessAlert('Dados encontrados!', 'Contato carregado com sucesso');
                                        } else {
                                            showWarningAlert('Dados de cadastro vazios!', 'Este contrato não possui informações de contato cadastradas.');
                                        }
                                    }, 100);
                                } else if (resposta.nome || resposta.email || resposta.telefone) {
                                    var nome = resposta.nome || '';
                                    var email = resposta.email || '';
                                    var telefone = resposta.telefone || '';
                                    if (verificarDadosVazios(nome, email, telefone)) {
                                        limparCamposFormulario();
                                    } else {
                                        $('#nome').val(nome);
                                        $('#email').val(email);
                                        $('#telefone').val(telefone);
                                        showSuccessAlert('Dados encontrados!', 'Contrato carregado com sucesso');
                                    }
                                    limparResultados();
                                }
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            showErrorAlert('Erro na busca', 'Verifique o console');
                            limparResultados();
                        },
                        complete: function() {
                            $('#inputSearchContext').css('opacity', '1');
                        }
                    });
                }

                // ============================================
                // BUSCA AUTOMÁTICA (sem botão)
                // ============================================
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
                });

                $('#inputSearchContext').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        enviarBusca();
                    }
                });

                $('#inputSearchContext').on('blur', function() {
                    const valor = $(this).val().trim();
                    if (valor !== '') {
                        enviarBusca();
                    }
                });

                // ============================================
                // QUANDO MUDAR O TIPO DE BUSCA
                // ============================================
                $('#selectSearchType').on('change', function() {
                    limparCamposFormulario();
                    $('#inputSearchContext').val('');
                    limparResultados();
                    console.log('Tipo de busca alterado para:', $(this).val());
                });

                // ============================================
                // PREENCHER FORMULÁRIO AO SELECIONAR RESULTADO
                // ============================================
                $(document).on('change', '#resultadoBusca', function() {
                    if (this.tagName === 'SELECT') {
                        var optionSelected = $(this).find('option:selected');
                        var id_contato = $(this).val();
                        if (!id_contato) {
                            limparCamposFormulario();
                            return;
                        }
                        $('#nome').val('Carregando...');
                        $('#email').val('Carregando...');
                        $('#telefone').val('Carregando...');
                        console.log('Selecionado:', optionSelected.text(), 'ID Contato:', id_contato);
                        buscarDadosContato(id_contato);
                    }
                });

                // ============================================
                // VALIDAÇÃO DO FORMULÁRIO ANTES DE ENVIAR
                // ============================================
                $('#formChamado').on('submit', function(e) {
                    const tipoContrato = $('#id_tipo_contrato').val();

                    if (!tipoContrato || tipoContrato === '0' || tipoContrato === '') {
                        e.preventDefault();
                        showErrorAlert('Selecione um tipo de contrato', 'Por favor, selecione um tipo de contrato válido.');
                        return false;
                    }

                    if (tipoContrato === '1') {
                        const contrato = $('#inputCodigoContrato').val().trim();
                        const equipamento = $('#codigo_equipamento').val().trim();
                        if (!contrato || !equipamento) {
                            e.preventDefault();
                            showErrorAlert('Campos obrigatórios', 'Por favor, preencha o Contrato e o Equipamento.');
                            return false;
                        }
                    } else if (tipoContrato >= 2 && tipoContrato <= 7) {
                        const searchContext = $('#inputSearchContext').val().trim();
                        if (!searchContext) {
                            e.preventDefault();
                            showErrorAlert('Campo obrigatório', 'Por favor, informe um valor para busca.');
                            return false;
                        }
                    }

                    return true;
                });
            });
        })(jQuery);
    </script>
    <?php include 'footer.php'; ?>
</body>

</html>
