<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interagir Chamado</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .message {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 12px;
            max-width: 85%;
        }

        .message.received {
            flex-direction: row;
        }

        .message.sent {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #4a5568;
            font-size: 15px;
        }

        .message.received .message-avatar {
            background: #dbeafe;
            color: #2563eb;
        }

        .message.sent .message-avatar {
            background: #e5e7eb;
            color: #6b7280;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            color: #1a202c;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== HEADER ===== */
        .az-header {
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            flex-shrink: 0;
        }

        .container-fluid {
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .az-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .az-header-center {
            flex: 1;
        }

        .az-header-right {
            display: flex;
            align-items: center;
        }

        .az-content-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .header-icon {
            color: #4a5568;
            font-size: 18px;
            margin-left: 20px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .header-icon:hover {
            color: #2b6cb0;
        }

        /* ===== CONTEÚDO ===== */
        .az-content {
            margin: 0 auto;
            padding: 0 30px 30px;
            flex: 1;
            width: 100%;
        }

        .az-content-body {
            padding-top: 20px;
        }

        /* ===== CARD INFORMAÇÕES ===== */
        .card-dashboard {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e9edf2;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            padding: 16px 24px 12px 24px;
            margin-bottom: 20px;
            min-height: 100px;
        }

        .card-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a0aec0;
            font-size: 14px;
            padding: 20px 0;
        }

        .card-loading i {
            margin-right: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .info-item .label {
            font-size: 11px;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-item .value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }

        .info-item .value strong {
            font-weight: 700;
            color: #1a202c;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: lowercase;
        }

        .status-badge.atendimento {
            background: #bee3f8;
            color: #2b6cb0;
        }

        .status-badge.finalizado {
            background: #c6f6d5;
            color: #276749;
        }

        .status-badge.pendente {
            background: #fefcbf;
            color: #975a16;
        }

        .status-badge.aberto {
            background: #fefcbf;
            color: #975a16;
        }

        .status-badge.aguardando {
            background: #e9d8fd;
            color: #6b46c1;
        }

        /* ===== CONTAINER CHAT ===== */
        #container-chat {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-top: 0;
            max-height: 480px;
            overflow-y: auto;
            padding: 0;
        }

        #azChatBody {
            width: 100%;
            padding: 20px 24px;
        }

        /* ===== MENSAGENS ===== */
        .system-message {
            text-align: center;
            margin: 16px 0;
            clear: both;
        }

        .system-message .bubble {
            background: #edf2f7;
            padding: 6px 18px;
            border-radius: 16px;
            display: inline-block;
            font-size: 12px;
            color: #4a5568;
            font-weight: 500;
        }

        .system-message.invalida .bubble {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .system-message .bubble i {
            margin-right: 6px;
        }

        /* Separador de data */
        .date-separator {
            text-align: center;
            margin: 20px 0 16px 0;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            position: relative;
        }

        .date-separator span {
            background: #ffffff;
            padding: 0 16px;
            font-size: 12px;
            font-weight: 600;
            color: #718096;
            position: relative;
            top: -10px;
        }

        /* Mensagem individual */
        .message {
            display: flex;
            flex-direction: column;
            margin-bottom: 12px;
            max-width: 85%;
            clear: both;
        }

        .message.received {
            float: left;
            align-items: flex-start;
        }

        .message.sent {
            float: right;
            align-items: flex-end;
        }

        .message .bubble {
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .message.received .bubble {
            background: #a4e583;
            color: #2d3748;
            border: 1px solid #e2e8f0;
        }

        .message.sent .bubble {
            background: #ffc876;
            color: #2d3748;
            border: 1px solid #bee3f8;
        }

        .message .bubble .msg-author {
            font-weight: 600;
            color: #2b6cb0;
            margin-right: 4px;
        }

        /* Estilo para anexos na mensagem */
        .message .bubble .file-attachment {
            display: block;
            margin-top: 6px;
            padding: 6px 12px;
            background: rgba(43, 108, 176, 0.08);
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid rgba(43, 108, 176, 0.15);
            transition: all 0.2s;
        }

        .message .bubble .file-attachment:hover {
            background: rgba(43, 108, 176, 0.15);
            border-color: rgba(43, 108, 176, 0.3);
        }

        .message .bubble .file-attachment i {
            margin-right: 8px;
            color: #2b6cb0;
            font-size: 14px;
        }

        .message .bubble .file-attachment a {
            color: #2b6cb0;
            text-decoration: none;
            font-weight: 500;
        }

        .message .bubble .file-attachment a:hover {
            text-decoration: underline;
        }

        .message .bubble .file-attachment .file-size {
            font-size: 11px;
            color: #a0aec0;
            margin-left: 8px;
        }

        .message .meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            font-size: 11px;
            color: #a0aec0;
        }

        .message.received .meta {
            justify-content: flex-start;
        }

        .message.sent .meta {
            justify-content: flex-end;
        }

        .message .meta .time {
            font-size: 11px;
        }

        .message .meta .author {
            font-weight: 500;
            color: #4a5568;
        }

        /* ===== FORMULÁRIO ===== */
        .form-group {
            width: 100%;
            margin-top: 16px;
        }

        .form-group .form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .form-group .form-row .input-wrap {
            flex: 1;
            min-width: 200px;
        }

        .form-group .form-row .actions-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        #iptNovaMsg {
            width: 100%;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: #ffffff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            font-family: inherit;
        }

        #iptNovaMsg:focus {
            border-color: #2b6cb0;
            box-shadow: 0 0 0 3px rgba(43, 108, 176, 0.1);
        }

        #iptNovaMsg::placeholder {
            color: #a0aec0;
        }

        .btn {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: inherit;
        }

        .btn-outline {
            background: transparent;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        .btn-outline:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }

        .btn-primary {
            background: #2b6cb0;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #2c5282;
        }

        .btn-primary i {
            font-size: 13px;
        }

        .d-none {
            display: none !important;
        }

        /* ===== FOOTER ===== */
        .az-footer {
            margin-top: 24px;
            padding: 16px 0;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #a0aec0;
            text-align: center;
            flex-shrink: 0;
        }

        .az-footer strong {
            color: #4a5568;
        }

        /* ===== SCROLL ===== */
        #container-chat::-webkit-scrollbar {
            width: 6px;
        }

        #container-chat::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 3px;
        }

        #container-chat::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        #container-chat::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* ===== SPINNER ===== */
        .loading-spinner {
            text-align: center;
            padding: 30px 20px;
            color: #a0aec0;
            font-size: 14px;
        }

        .loading-spinner i {
            margin-right: 8px;
        }

        /* ===== RESPONSIVIDADE ===== */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0 16px;
            }

            .az-content {
                padding: 0 16px 16px;
            }

            .info-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .az-content-title {
                font-size: 16px;
            }

            #azChatBody {
                padding: 16px;
            }

            .message {
                max-width: 95%;
            }
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .az-header .container-fluid {
                flex-wrap: wrap;
                gap: 6px;
            }

            .az-content-title {
                font-size: 14px;
            }

            .form-group .form-row {
                flex-direction: column;
                align-items: stretch;
            }

            .form-group .form-row .actions-wrap {
                justify-content: stretch;
            }

            .form-group .form-row .actions-wrap .btn {
                flex: 1;
                justify-content: center;
            }

            .message {
                max-width: 100%;
            }

            #container-chat {
                max-height: 400px;
            }

            .header-icon {
                font-size: 15px;
                margin-left: 12px;
            }
        }
    </style>
</head>

<body>

    <!-- ===== HEADER ===== -->
    <header class="az-header">
        <div class="container-fluid">
            <div class="az-header-left">
                <a href="https://brinfor.com.br">
                    <img src="/views/img/brInfor_logo_mini.png" class="img-fluid" alt="Br Info logo" style="width: 127px;">
                </a>
            </div>
            <div class="az-header-center"></div>
            <div class="az-header-right">
                <div class="dropdown az-profile-menu">
                    <h2 class="az-content-title">Interagir Chamado</h2>
                </div>

            </div>
        </div>
    </header>

    <!-- ===== CONTEÚDO ===== -->
    <main class="az-content">
        <div class="az-content-body">

            <!-- CARD INFORMAÇÕES -->
            <div class="card-dashboard" id="cardInfo">
                <div class="card-loading" id="cardLoading">
                    <i class="fas fa-spinner fa-spin"></i> Carregando informações do chamado...
                </div>
                <div class="info-grid" id="infoGrid" style="display: none;">
                    <div class="info-item">
                        <span class="label">Chamado</span>
                        <span class="value"><strong id="chamadoId">--</strong> <span id="chamadoTipo">(Carregando...)</span></span>
                        <span class="label" style="margin-top: 6px;">Técnico responsável</span>
                        <span class="value" id="tecnicoNome">Carregando...</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Última interação</span>
                        <span class="value" id="ultimaInteracao">Carregando...</span>
                        <span class="label" style="margin-top: 6px;">Status</span>
                        <span class="value"><span id="statusChamado" class="status-badge">carregando...</span></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Data abertura</span>
                        <span class="value" id="dataAbertura">Carregando...</span>
                        <span class="label" style="margin-top: 6px;">Equipamento</span>
                        <span class="value" id="equipamentoInfo">Carregando...</span>
                    </div>
                </div>
            </div>

            <!-- CONTAINER CHAT -->
            <div id="container-chat">
                <div id="azChatBody" class="az-chat-body">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i> Carregando mensagens...
                    </div>
                </div>
            </div>

            <!-- FORMULÁRIO -->
            <div class="form-group">
                <div class="form-row">
                    <div class="input-wrap">
                        <input type="text" id="iptNovaMsg" placeholder="Nova mensagem...">
                    </div>
                    <div class="actions-wrap">
                        <input type="file" id="arquivo" name="arquivo" class="d-none">
                        <button type="button" id="btnAnexo" class="btn btn-outline" onclick="document.getElementById('arquivo').click()">
                            <i class="fas fa-paperclip"></i> Anexo
                        </button>
                        <button type="button" id="btnEnviarMsg" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar
                        </button>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="az-footer">
                Copyright &copy; 2002-2026 <strong>Genial Sistemas</strong> Ltda. Todos os direitos reservados.
            </div>

        </div>
    </main>

    <!-- ============================================================ -->
    <!-- SCRIPT PRINCIPAL                                              -->
    <!-- ============================================================ -->
    <script>
        (function() {
            'use strict';

            // ============================================================
            // CONFIGURAÇÕES
            // ============================================================
            const POLLING_INTERVAL = 5000;
            const ID_CHAMADO = <?= (int) $idChamado ?>;

            // ============================================================
            // URLS DA API
            // ============================================================
            const API_BASE = `/index.php?page=visitante-chamados-interacoes&id=${ID_CHAMADO}&seguranca=<?= rawurlencode($seguranca) ?>`;
            const URL_BUSCAR_EVENTOS = `${API_BASE}&endpoint=eventos`;
            const URL_BUSCAR_INFO_CHAMADO = `${API_BASE}&endpoint=info`;
            const URL_ENVIAR_EVENTO = `${API_BASE}&endpoint=enviar`;

            // ============================================================
            // REFERÊNCIAS DOM
            // ============================================================
            const chatBody = document.getElementById('azChatBody');
            const inputMsg = document.getElementById('iptNovaMsg');
            const btnEnviar = document.getElementById('btnEnviarMsg');
            const inputFile = document.getElementById('arquivo');
            const containerChat = document.getElementById('container-chat');

            const cardLoading = document.getElementById('cardLoading');
            const infoGrid = document.getElementById('infoGrid');
            const spanChamadoId = document.getElementById('chamadoId');
            const spanChamadoTipo = document.getElementById('chamadoTipo');
            const spanTecnico = document.getElementById('tecnicoNome');
            const spanUltimaInteracao = document.getElementById('ultimaInteracao');
            const spanStatus = document.getElementById('statusChamado');
            const spanDataAbertura = document.getElementById('dataAbertura');
            const spanEquipamento = document.getElementById('equipamentoInfo');

            // ============================================================
            // ESTADO LOCAL
            // ============================================================
            let mensagensCache = [];
            let ultimoIdEvento = 0;
            let primeiraCarga = true;
            let isPolling = false;
            let timeoutPolling = null;
            let nomeTecnicoAtual = 'Técnico'; // Nome do técnico responsável

            // ============================================================
            // FUNÇÃO: FORMATAR DATA
            // ============================================================
            function formatarData(data) {
                if (!data || data === '0000-00-00' || data === '') return 'Não informado';

                if (data.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    return data;
                }

                if (data.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const partes = data.split('-');
                    return `${partes[2]}/${partes[1]}/${partes[0]}`;
                }

                if (data.includes(' ')) {
                    const dataPart = data.split(' ')[0];
                    if (dataPart.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        const partes = dataPart.split('-');
                        return `${partes[2]}/${partes[1]}/${partes[0]}`;
                    }

                    const match = data.match(/(\d{4})-(\d{2})-(\d{2})/);
                    if (match) {
                        return `${match[3]}/${match[2]}/${match[1]}`;
                    }
                }

                return data;
            }

            // ============================================================
            // FUNÇÃO: FORMATAR DATA E HORA
            // ============================================================
            function formatarDataHora(data, hora) {
                const dataFormatada = formatarData(data);
                if (hora && hora !== '00:00:00' && hora !== '') {
                    return `${dataFormatada} às ${hora}`;
                }
                return dataFormatada;
            }

            // ============================================================
            // FUNÇÃO: BUSCAR INFO DO CHAMADO
            // ============================================================
            function buscarInfoChamado() {
                console.log('📋 Buscando informações do chamado #' + ID_CHAMADO + '...');

                const url = `${URL_BUSCAR_INFO_CHAMADO}&id_chamado=${ID_CHAMADO}`;

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('📋 Resposta da API de info:', data);

                        cardLoading.style.display = 'none';
                        infoGrid.style.display = 'grid';

                        if (data.success && data.info) {
                            const info = data.info;

                            spanChamadoId.textContent = info.id_chamado || ID_CHAMADO;
                            spanChamadoTipo.textContent = info.tipo_chamado ? `(${info.tipo_chamado})` : '(Helpdesk)';
                            
                            // Armazena o nome do técnico responsável
                            if (info.tecnico_responsavel && info.tecnico_responsavel !== 'Não informado') {
                                nomeTecnicoAtual = info.tecnico_responsavel;
                            }
                            spanTecnico.textContent = info.tecnico_responsavel || 'Não informado';
                            
                            spanDataAbertura.textContent = formatarData(info.data_abertura);
                            spanEquipamento.textContent = info.equipamento || '[00|00|00] -';

                            if (info.situacao && info.situacao !== 'Não informado') {
                                const statusTexto = info.situacao.toLowerCase();
                                spanStatus.textContent = info.situacao;
                                spanStatus.className = 'status-badge';

                                if (statusTexto === 'em atendimento' || statusTexto === 'atendimento') {
                                    spanStatus.classList.add('atendimento');
                                } else if (statusTexto === 'finalizado' || statusTexto === 'fechado') {
                                    spanStatus.classList.add('finalizado');
                                } else if (statusTexto === 'aberto') {
                                    spanStatus.classList.add('aberto');
                                } else if (statusTexto === 'aguardando') {
                                    spanStatus.classList.add('aguardando');
                                } else {
                                    spanStatus.classList.add('pendente');
                                }
                            } else {
                                spanStatus.textContent = 'Não informado';
                                spanStatus.className = 'status-badge';
                            }

                            if (info.ultima_interacao) {
                                spanUltimaInteracao.textContent = formatarDataHora(
                                    info.ultima_interacao.data,
                                    info.ultima_interacao.hora
                                );
                            } else {
                                spanUltimaInteracao.textContent = 'Nenhuma interação';
                            }

                            console.log('✅ Informações do chamado carregadas com sucesso');
                            console.log('👤 Técnico responsável:', nomeTecnicoAtual);
                        } else {
                            console.warn('⚠️ Dados da API incompletos:', data);
                            spanChamadoId.textContent = ID_CHAMADO;
                            spanChamadoTipo.textContent = '(Helpdesk)';
                            spanTecnico.textContent = 'Não informado';
                            spanDataAbertura.textContent = 'Não informado';
                            spanEquipamento.textContent = '[00|00|00] -';
                            spanStatus.textContent = 'Não informado';
                            spanStatus.className = 'status-badge';
                            spanUltimaInteracao.textContent = 'Nenhuma interação';
                        }
                    })
                    .catch(error => {
                        console.error('❌ Erro ao buscar informações:', error);

                        cardLoading.style.display = 'none';
                        infoGrid.style.display = 'grid';

                        spanChamadoId.textContent = ID_CHAMADO;
                        spanChamadoTipo.textContent = '(Helpdesk)';
                        spanTecnico.textContent = 'Erro ao carregar';
                        spanDataAbertura.textContent = 'Erro ao carregar';
                        spanEquipamento.textContent = 'Erro ao carregar';
                        spanStatus.textContent = 'erro';
                        spanStatus.className = 'status-badge';
                        spanUltimaInteracao.textContent = 'Erro ao carregar';
                    });
            }

            // ============================================================
            // FUNÇÃO: BUSCAR EVENTOS
            // ============================================================
            function buscarEventosDoBanco() {
                return new Promise((resolve, reject) => {
                    const url = `${URL_BUSCAR_EVENTOS}&id_chamado=${ID_CHAMADO}&_=${Date.now()}`;

                    fetch(url)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success && data.eventos) {
                                resolve(data.eventos);
                            } else {
                                resolve([]);
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar eventos:', error);
                            reject(error);
                        });
                });
            }

            // ============================================================
            // FUNÇÃO: RENDERIZAR MENSAGENS
            // ============================================================
            function renderizarMensagens(eventos) {
                const spinner = chatBody.querySelector('.loading-spinner');
                if (spinner) spinner.remove();

                if (!eventos || eventos.length === 0) {
                    if (chatBody.children.length === 0) {
                        chatBody.innerHTML = '<div class="loading-spinner"><i class="fas fa-info-circle"></i> Nenhuma mensagem encontrada</div>';
                    }
                    return;
                }

                eventos.sort((a, b) => a.id_evento - b.id_evento);

                const novosEventos = eventos.filter(e => e.id_evento > ultimoIdEvento);

                if (novosEventos.length === 0 && !primeiraCarga) {
                    return;
                }

                if (eventos.length > 0) {
                    const ultimo = eventos[eventos.length - 1];
                    if (ultimo.id_evento > ultimoIdEvento) {
                        ultimoIdEvento = ultimo.id_evento;
                    }
                }

                if (primeiraCarga || mensagensCache.length === 0) {
                    chatBody.innerHTML = '';
                    mensagensCache = [];
                    primeiraCarga = false;

                    eventos.forEach(evento => {
                        mensagensCache.push(evento);
                        adicionarMensagemNoChat(evento);
                    });
                } else {
                    novosEventos.forEach(evento => {
                        const jaExiste = mensagensCache.some(m => m.id_evento === evento.id_evento);
                        if (!jaExiste) {
                            mensagensCache.push(evento);
                            adicionarMensagemNoChat(evento);
                        }
                    });
                }

                if (eventos.length > 0) {
                    const ultimo = eventos[eventos.length - 1];
                    if (ultimo.data && ultimo.hora) {
                        spanUltimaInteracao.textContent = formatarDataHora(ultimo.data, ultimo.hora);
                    }
                }

                scrollToBottom();
            }

            // ============================================================
            // FUNÇÃO: ADICIONAR MENSAGEM NO DOM (CORRIGIDA)
            // ============================================================
            function adicionarMensagemNoChat(evento) {
                // MENSAGEM DE SISTEMA (status 0)
                if (evento.status == 0) {
                    const sysDiv = document.createElement('div');
                    sysDiv.className = `system-message`;

                    const bubble = document.createElement('div');
                    bubble.className = 'bubble';

                    let descricao = evento.descricao || '';
                    
                    // CORREÇÃO: Substitui "Técnico" pelo nome real em TODAS as ocorrências
                    if (nomeTecnicoAtual && nomeTecnicoAtual !== 'Técnico') {
                        // Substitui "Técnico:" por "Nome do Técnico:"
                        descricao = descricao.replace(/Técnico:/g, nomeTecnicoAtual + ':');
                        // Substitui "Técnico " por "Nome do Técnico " (para casos sem :)
                        descricao = descricao.replace(/Técnico\s/g, nomeTecnicoAtual + ' ');
                    }

                    if (descricao && descricao.toLowerCase().includes('invalida')) {
                        sysDiv.classList.add('invalida');
                        bubble.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${descricao}`;
                    } else {
                        bubble.innerHTML = `<i class="fas fa-clock"></i> ${descricao}`;
                    }

                    sysDiv.appendChild(bubble);
                    chatBody.appendChild(sysDiv);
                    return;
                }

                // MENSAGEM NORMAL
                const isTecnico = !!evento.id_pessoa;

                let dataExibicao = formatarData(evento.data);

                const separadores = chatBody.querySelectorAll('.date-separator span');
                let temData = false;
                separadores.forEach(sep => {
                    if (sep.textContent === dataExibicao) temData = true;
                });

                if (!temData && dataExibicao && dataExibicao !== 'Não informado') {
                    const sepDiv = document.createElement('div');
                    sepDiv.className = 'date-separator';
                    const span = document.createElement('span');
                    span.textContent = dataExibicao;
                    sepDiv.appendChild(span);
                    chatBody.appendChild(sepDiv);
                }

                const wrapperDiv = document.createElement('div');
                wrapperDiv.className = `message ${isTecnico ? 'received' : 'sent'}`;

                // Nome do remetente
                let nome = '';
                if (isTecnico) {
                    nome = evento.nome_tecnico || nomeTecnicoAtual || 'Técnico';
                } else {
                    nome = 'Você';
                }

                // Avatar
                const avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.textContent = nome.charAt(0).toUpperCase();

                // Balão da mensagem
                const bubble = document.createElement('div');
                bubble.className = 'bubble';

                if (isTecnico && nome) {
                    const authorSpan = document.createElement('strong');
                    authorSpan.className = 'msg-author';
                    authorSpan.textContent = nome + ': ';
                    bubble.appendChild(authorSpan);
                }

                const descricao = evento.descricao || 'Sem descrição';

                // Verifica se é um arquivo
                const isArquivo = descricao.match(/\.(png|jpg|jpeg|gif|pdf|doc|docx|xls|xlsx|zip|rar|txt|mp4|avi|mkv|mp3|wav)$/i) ||
                    descricao.includes('chamados/eventos/');

                if (isArquivo && !evento.anexo) {
                    const iconSpan = document.createElement('span');
                    iconSpan.textContent = '📎 ';
                    bubble.appendChild(iconSpan);

                    const linkSpan = document.createElement('a');
                    linkSpan.href = '/' + descricao;
                    linkSpan.target = '_blank';

                    const partes = descricao.split('/');
                    linkSpan.textContent = partes[partes.length - 1];
                    linkSpan.style.color = '#2b6cb0';
                    linkSpan.style.textDecoration = 'underline';

                    bubble.appendChild(linkSpan);

                } else if (evento.anexo) {
                    const attachDiv = document.createElement('div');
                    attachDiv.className = 'file-attachment';

                    const textoMsg = descricao
                        .replace(evento.anexo, '')
                        .replace(/\s*-\s*$/, '')
                        .trim();

                    if (textoMsg) {
                        const textSpan = document.createElement('span');
                        textSpan.textContent = textoMsg + ' ';
                        attachDiv.appendChild(textSpan);
                    }

                    const linkSpan = document.createElement('a');
                    linkSpan.href = '/' + evento.anexo;
                    linkSpan.target = '_blank';

                    const partes = evento.anexo.split('/');
                    linkSpan.textContent = '📎 ' + partes[partes.length - 1];

                    attachDiv.appendChild(linkSpan);
                    bubble.appendChild(attachDiv);

                } else {
                    const textSpan = document.createElement('span');
                    textSpan.textContent = descricao;
                    bubble.appendChild(textSpan);
                }

                wrapperDiv.appendChild(avatar);
                wrapperDiv.appendChild(bubble);

                // Meta (hora)
                if (evento.hora) {
                    const metaDiv = document.createElement('div');
                    metaDiv.className = 'meta';

                    const timeSpan = document.createElement('span');
                    timeSpan.className = 'time';
                    timeSpan.textContent = evento.hora;
                    metaDiv.appendChild(timeSpan);

                    if (isTecnico && nome) {
                        const authorSpan = document.createElement('span');
                        authorSpan.className = 'author';
                        authorSpan.textContent = nome;
                        metaDiv.appendChild(authorSpan);
                    }

                    wrapperDiv.appendChild(metaDiv);
                }

                chatBody.appendChild(wrapperDiv);
            }

            // ============================================================
            // FUNÇÃO: POLLING
            // ============================================================
            function polling() {
                if (isPolling) return;
                isPolling = true;

                buscarEventosDoBanco()
                    .then(eventos => {
                        if (eventos && eventos.length > 0) {
                            renderizarMensagens(eventos);
                        }
                        isPolling = false;

                        if (timeoutPolling) {
                            clearTimeout(timeoutPolling);
                        }
                        timeoutPolling = setTimeout(polling, POLLING_INTERVAL);
                    })
                    .catch(err => {
                        console.error('Erro no polling:', err);
                        isPolling = false;

                        if (timeoutPolling) {
                            clearTimeout(timeoutPolling);
                        }
                        timeoutPolling = setTimeout(polling, 5000);
                    });
            }

            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    if (timeoutPolling) {
                        clearTimeout(timeoutPolling);
                    }
                    polling();
                }
            });

            window.addEventListener('focus', function() {
                if (timeoutPolling) {
                    clearTimeout(timeoutPolling);
                }
                polling();
            });

            // ============================================================
            // FUNÇÃO: ENVIAR MENSAGEM
            // ============================================================
            function enviarMensagem() {
                const texto = inputMsg.value.trim();
                const temArquivo = inputFile.files.length > 0;

                if (!texto && !temArquivo) {
                    alert('Digite uma mensagem ou selecione um arquivo.');
                    return;
                }

                const agora = new Date();
                const ano = agora.getFullYear();
                const mes = String(agora.getMonth() + 1).padStart(2, '0');
                const dia = String(agora.getDate()).padStart(2, '0');
                const dataFormatada = `${ano}-${mes}-${dia}`;

                const horas = String(agora.getHours()).padStart(2, '0');
                const minutos = String(agora.getMinutes()).padStart(2, '0');
                const segundos = String(agora.getSeconds()).padStart(2, '0');
                const horaFormatada = `${horas}:${minutos}:${segundos}`;

                let nomeArquivo = '';
                if (temArquivo) {
                    nomeArquivo = inputFile.files[0].name;
                }

                if (temArquivo) {
                    const formData = new FormData();
                    formData.append('id_chamado', ID_CHAMADO);
                    formData.append('descricao', texto || '');
                    formData.append('status', 7);
                    formData.append('data', dataFormatada);
                    formData.append('hora', horaFormatada);
                    formData.append('arquivo', inputFile.files[0]);

                    console.log('📤 Enviando arquivo:', nomeArquivo);
                    console.log('📤 Tamanho:', inputFile.files[0].size, 'bytes');

                    fetch(URL_ENVIAR_EVENTO, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(text => {
                            console.log('📥 Resposta bruta do servidor:', text);
                            try {
                                const data = JSON.parse(text);
                                if (data.success) {
                                    console.log('✅ Arquivo enviado com sucesso:', data);

                                    let descricaoFinal = texto || '';
                                    if (data.arquivo) {
                                        if (descricaoFinal) {
                                            descricaoFinal += ' - ' + data.arquivo;
                                        } else {
                                            descricaoFinal = data.arquivo;
                                        }
                                    }

                                    const eventoLocal = {
                                        id_evento: data.id_evento || Date.now(),
                                        id_chamado: ID_CHAMADO,
                                        descricao: descricaoFinal,
                                        status: 7,
                                        data: dataFormatada,
                                        hora: horaFormatada,
                                        anexo: data.arquivo || null,
                                        id_pessoa: 0,
                                        nome_tecnico: nomeTecnicoAtual
                                    };
                                    adicionarEventoLocal(eventoLocal);
                                    setTimeout(polling, 1000);
                                } else {
                                    console.error('❌ Erro ao enviar arquivo:', data);
                                    alert('Erro ao enviar arquivo: ' + (data.message || 'Tente novamente.'));
                                }
                            } catch (e) {
                                console.error('❌ Erro ao parsear JSON:', e);
                                alert('Erro no servidor. Verifique o console para mais detalhes.');
                            }
                        })
                        .catch(error => {
                            console.error('❌ Erro na requisição:', error);
                            alert('Erro ao enviar arquivo. Verifique sua conexão.');
                        });

                } else {
                    const dados = {
                        id_chamado: ID_CHAMADO,
                        descricao: texto || '(Anexo enviado)',
                        status: 7,
                        data: dataFormatada,
                        hora: horaFormatada
                    };

                    console.log('📤 Enviando mensagem:', dados);

                    fetch(URL_ENVIAR_EVENTO, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(dados)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('✅ Mensagem enviada com sucesso');
                                const eventoLocal = {
                                    id_evento: data.id_evento || Date.now(),
                                    id_chamado: ID_CHAMADO,
                                    descricao: dados.descricao,
                                    status: dados.status,
                                    data: dados.data,
                                    hora: dados.hora,
                                    id_pessoa: 0,
                                    nome_tecnico: nomeTecnicoAtual
                                };
                                adicionarEventoLocal(eventoLocal);
                                setTimeout(polling, 1000);
                            } else {
                                console.error('❌ Erro ao enviar:', data.message);
                                alert('Erro ao enviar mensagem. Tente novamente.');
                            }
                        })
                        .catch(error => {
                            console.error('❌ Erro na requisição:', error);
                            alert('Erro ao enviar mensagem. Verifique sua conexão.');
                        });
                }

                inputMsg.value = '';
                inputFile.value = '';
            }

            // ============================================================
            // FUNÇÃO: ADICIONAR EVENTO LOCAL
            // ============================================================
            function adicionarEventoLocal(eventoLocal) {
                const jaExiste = mensagensCache.some(m => m.id_evento === eventoLocal.id_evento);
                if (!jaExiste) {
                    mensagensCache.push(eventoLocal);
                    adicionarMensagemNoChat(eventoLocal);
                    if (eventoLocal.id_evento > ultimoIdEvento) {
                        ultimoIdEvento = eventoLocal.id_evento;
                    }
                    scrollToBottom();
                }
            }

            // ============================================================
            // FUNÇÃO: SCROLL
            // ============================================================
            function scrollToBottom() {
                if (containerChat) {
                    containerChat.scrollTop = containerChat.scrollHeight;
                }
            }

            // ============================================================
            // EVENTOS
            // ============================================================
            btnEnviar.addEventListener('click', enviarMensagem);

            inputMsg.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    enviarMensagem();
                }
            });

            inputFile.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const nome = this.files[0].name;
                    inputMsg.placeholder = `Arquivo: ${nome}`;
                    setTimeout(() => {
                        inputMsg.placeholder = 'Nova mensagem...';
                    }, 2500);
                }
            });

            // ============================================================
            // INICIALIZAÇÃO
            // ============================================================
            console.log('🚀 Sistema de Chat Iniciado');
            console.log(`📌 Chamado #${ID_CHAMADO}`);

            spanChamadoId.textContent = ID_CHAMADO;

            buscarInfoChamado();
            polling();

        })();
    </script>

</body>

</html>