<?php
/*
 * Componente de conversa do chamado (cliente logado e visitante).
 * Variáveis esperadas:
 *   $conversa_id         int    id do chamado
 *   $conversa_seguranca  string código do link (visitante) ou '' (cliente logado)
 *   $conversa_token      string token anti-CSRF (cliente logado) ou ''
 * Elementos opcionais da página atualizados automaticamente (atributo data-conversa):
 *   situacao, tecnico, ultima, equipamento, tipo, abertura, numero
 */
$conversa_seguranca = (string)($conversa_seguranca ?? '');
$conversa_token = (string)($conversa_token ?? '');
$h = static function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
?>
<style>
.conversa { display: flex; flex-direction: column; height: 100%; min-height: 420px; background: #fff; border: 1px solid #e3e7ed; border-radius: 8px; overflow: hidden; }
.conversa-lista { flex: 1; overflow-y: auto; padding: 16px; background: #f5f7fb; scroll-behavior: smooth; }
.conversa-carregando, .conversa-vazia { text-align: center; color: #7987a1; padding: 30px 0; font-size: 14px; }
.conversa-data { text-align: center; margin: 14px 0 10px; }
.conversa-data span { background: #e3e7ed; color: #596882; font-size: 11px; padding: 3px 10px; border-radius: 10px; }
.conversa-msg { display: flex; margin-bottom: 10px; }
.conversa-msg.cliente { justify-content: flex-end; }
.conversa-balao { max-width: 75%; padding: 8px 12px; border-radius: 12px; font-size: 14px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; box-shadow: 0 1px 1px rgba(0,0,0,.05); }
.conversa-msg.tecnico .conversa-balao { background: #fff; color: #1c273c; border-top-left-radius: 3px; }
.conversa-msg.cliente .conversa-balao { background: #fa9758; color: #fff; border-top-right-radius: 3px; }
.conversa-autor { display: block; font-size: 11px; font-weight: 700; margin-bottom: 2px; opacity: .85; }
.conversa-hora { display: block; font-size: 10px; text-align: right; margin-top: 4px; opacity: .75; }
.conversa-anexo { display: inline-flex; align-items: center; gap: 6px; color: inherit; text-decoration: underline; font-weight: 600; }
.conversa-msg.cliente .conversa-anexo { color: #fff; }
.conversa-aviso { padding: 8px 12px; font-size: 13px; text-align: center; }
.conversa-aviso.erro { background: #fdecea; color: #b71c1c; }
.conversa-aviso.info { background: #fff4e5; color: #8a4b00; }
.conversa-aviso.encerrado { background: #eef0f4; color: #596882; font-weight: 600; }
.conversa-form { border-top: 1px solid #e3e7ed; padding: 10px; background: #fff; }
.conversa-form textarea { width: 100%; resize: none; border: 1px solid #cdd4e0; border-radius: 6px; padding: 8px 10px; font-size: 14px; min-height: 44px; max-height: 160px; }
.conversa-form textarea:focus { outline: none; border-color: #fa9758; }
.conversa-acoes { display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.conversa-acoes .conversa-dica { color: #97a3b9; font-size: 11px; margin-right: auto; }
.conversa-btn { border: 0; border-radius: 6px; padding: 7px 14px; font-size: 13px; cursor: pointer; }
.conversa-btn.anexar { background: #eef0f4; color: #1c273c; }
.conversa-btn.enviar { background: #fa9758; color: #fff; font-weight: 600; }
.conversa-btn:disabled { opacity: .6; cursor: default; }
.conversa-arquivo { display: inline-flex; align-items: center; gap: 6px; background: #eef0f4; border-radius: 12px; padding: 3px 10px; font-size: 12px; }
.conversa-arquivo button { border: 0; background: none; font-size: 14px; line-height: 1; cursor: pointer; color: #596882; }
@media (max-width: 576px) { .conversa-balao { max-width: 88%; } }
</style>

<div class="conversa" id="conversa"
     data-id="<?=$h($conversa_id)?>"
     data-seguranca="<?=$h($conversa_seguranca)?>"
     data-token="<?=$h($conversa_token)?>">
    <div class="conversa-aviso erro" id="conversa-erro" hidden></div>
    <div class="conversa-lista" id="conversa-lista" aria-live="polite">
        <div class="conversa-carregando" id="conversa-carregando">Carregando conversa…</div>
    </div>
    <div class="conversa-aviso encerrado" id="conversa-encerrado" hidden>
        Chamado encerrado. Não é possível enviar novas mensagens.
    </div>
    <form class="conversa-form" id="conversa-form" hidden>
        <label for="conversa-texto" class="sr-only">Mensagem</label>
        <textarea id="conversa-texto" rows="2" maxlength="10000" placeholder="Escreva sua mensagem…"></textarea>
        <div class="conversa-acoes">
            <span class="conversa-dica">Enter envia · Shift+Enter quebra linha</span>
            <span class="conversa-arquivo" id="conversa-arquivo" hidden>
                <span id="conversa-arquivo-nome"></span>
                <button type="button" id="conversa-arquivo-remover" aria-label="Remover anexo">×</button>
            </span>
            <input type="file" id="conversa-input-arquivo" hidden
                   accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z">
            <button type="button" class="conversa-btn anexar" id="conversa-anexar">📎 Anexar</button>
            <button type="submit" class="conversa-btn enviar" id="conversa-enviar">Enviar</button>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';
    var raiz = document.getElementById('conversa');
    var lista = document.getElementById('conversa-lista');
    var carregando = document.getElementById('conversa-carregando');
    var erroBox = document.getElementById('conversa-erro');
    var encerradoBox = document.getElementById('conversa-encerrado');
    var form = document.getElementById('conversa-form');
    var texto = document.getElementById('conversa-texto');
    var botaoEnviar = document.getElementById('conversa-enviar');
    var botaoAnexar = document.getElementById('conversa-anexar');
    var inputArquivo = document.getElementById('conversa-input-arquivo');
    var chipArquivo = document.getElementById('conversa-arquivo');
    var nomeArquivo = document.getElementById('conversa-arquivo-nome');

    var ID = raiz.dataset.id, SEGURANCA = raiz.dataset.seguranca, TOKEN = raiz.dataset.token;
    var INTERVALO = 5000, TAMANHO_MAXIMO = 10 * 1024 * 1024;
    var EXTENSOES = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','zip','rar','7z'];

    var ultimoId = 0, idsExibidos = {}, ultimaData = '', timer = null, buscando = false, enviando = false, primeiraCarga = true;

    function formatarData(iso) {
        var p = String(iso || '').slice(0, 10).split('-');
        return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : '';
    }
    function pertoDoFim() {
        return lista.scrollHeight - lista.scrollTop - lista.clientHeight < 80;
    }
    function rolarParaFim() {
        lista.scrollTop = lista.scrollHeight;
    }
    function mostrarErro(msg) {
        erroBox.textContent = msg || '';
        erroBox.hidden = !msg;
    }
    function preencher(chave, valor) {
        var alvos = document.querySelectorAll('[data-conversa="' + chave + '"]');
        for (var i = 0; i < alvos.length; i++) {
            if (valor !== undefined && valor !== null) alvos[i].textContent = valor;
        }
    }

    function adicionarEvento(ev) {
        if (idsExibidos[ev.id]) return;
        idsExibidos[ev.id] = true;
        if (ev.id > ultimoId) ultimoId = ev.id;

        var data = String(ev.data || '').slice(0, 10);
        if (data && data !== ultimaData) {
            var sep = document.createElement('div');
            sep.className = 'conversa-data';
            var s = document.createElement('span');
            s.textContent = formatarData(data);
            sep.appendChild(s);
            lista.appendChild(sep);
            ultimaData = data;
        }

        var msg = document.createElement('div');
        msg.className = 'conversa-msg ' + (ev.autor === 'cliente' ? 'cliente' : 'tecnico');
        var balao = document.createElement('div');
        balao.className = 'conversa-balao';

        var autor = document.createElement('span');
        autor.className = 'conversa-autor';
        autor.textContent = ev.nome || (ev.autor === 'cliente' ? 'Você' : 'Técnico');
        balao.appendChild(autor);

        if (ev.anexo) {
            var link = document.createElement('a');
            link.className = 'conversa-anexo';
            link.href = ev.anexo;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = '📎 ' + (ev.anexo_nome || 'Abrir anexo');
            balao.appendChild(link);
        } else {
            balao.appendChild(document.createTextNode(ev.texto || ''));
        }

        var hora = document.createElement('span');
        hora.className = 'conversa-hora';
        hora.textContent = ev.hora || '';
        balao.appendChild(hora);

        msg.appendChild(balao);
        lista.appendChild(msg);
    }

    function aplicarEstado(dados, rolarSempre) {
        var c = dados.chamado || {};
        preencher('situacao', c.situacao);
        preencher('tecnico', dados.tecnico);
        preencher('ultima', dados.ultima_interacao);
        preencher('tipo', c.tipo_contrato);
        preencher('atendimento', c.tipo_atendimento);
        preencher('abertura', c.data_abertura);
        preencher('numero', c.id);
        if (dados.equipamento !== undefined) preencher('equipamento', dados.equipamento);

        var estavaNoFim = pertoDoFim();
        var eventos = dados.eventos || [];
        for (var i = 0; i < eventos.length; i++) adicionarEvento(eventos[i]);

        if (carregando) {
            carregando.remove();
            carregando = null;
            if (!eventos.length) {
                var vazia = document.createElement('div');
                vazia.className = 'conversa-vazia';
                vazia.id = 'conversa-vazia';
                vazia.textContent = 'Nenhuma interação ainda.';
                lista.appendChild(vazia);
            }
        } else if (eventos.length) {
            var v = document.getElementById('conversa-vazia');
            if (v) v.remove();
        }

        // Só desce sozinho se o cliente já estava no fim (não atrapalha quem está lendo o histórico).
        if (eventos.length && (rolarSempre || estavaNoFim)) rolarParaFim();

        // Chamado encerrado: mostra o histórico completo, mas não deixa responder.
        encerradoBox.hidden = !c.encerrado;
        form.hidden = !!c.encerrado;
    }

    function agendar(ms) {
        clearTimeout(timer);
        timer = setTimeout(buscar, ms);
    }

    function buscar() {
        if (buscando) return;
        buscando = true;
        var url = '/conversa?id=' + encodeURIComponent(ID) + '&desde=' + ultimoId +
            (SEGURANCA ? '&seguranca=' + encodeURIComponent(SEGURANCA) : '');
        fetch(url, { cache: 'no-store', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json().catch(function () { return { ok: false }; }).then(function (d) { d._status = r.status; return d; }); })
            .then(function (dados) {
                if (dados.ok) {
                    mostrarErro('');
                    aplicarEstado(dados, primeiraCarga);
                    primeiraCarga = false;
                } else if (dados._status === 401) {
                    mostrarErro(dados.erro || 'Sessão expirada. Entre novamente.');
                    return; // não insiste sem sessão
                } else if (dados._status === 404) {
                    mostrarErro('Chamado não encontrado.');
                    return;
                } else {
                    mostrarErro(dados.erro || 'Sem conexão com o sistema. Tentando novamente…');
                }
                agendar(INTERVALO);
            })
            .catch(function () {
                mostrarErro('Sem conexão. Tentando novamente…');
                agendar(INTERVALO);
            })
            .then(function () { buscando = false; });
    }

    // Atualiza na hora quando o cliente volta para a aba/janela.
    document.addEventListener('visibilitychange', function () { if (!document.hidden) agendar(0); });
    window.addEventListener('focus', function () { agendar(0); });

    // ---------- anexo ----------
    function limparArquivo() {
        inputArquivo.value = '';
        chipArquivo.hidden = true;
        nomeArquivo.textContent = '';
    }
    botaoAnexar.addEventListener('click', function () { inputArquivo.click(); });
    document.getElementById('conversa-arquivo-remover').addEventListener('click', limparArquivo);
    inputArquivo.addEventListener('change', function () {
        var f = inputArquivo.files[0];
        if (!f) { limparArquivo(); return; }
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        if (EXTENSOES.indexOf(ext) === -1) { mostrarErro('Tipo de arquivo não permitido.'); limparArquivo(); return; }
        if (f.size > TAMANHO_MAXIMO) { mostrarErro('O arquivo deve ter até 10 MB.'); limparArquivo(); return; }
        mostrarErro('');
        nomeArquivo.textContent = '📎 ' + f.name;
        chipArquivo.hidden = false;
    });

    // ---------- envio ----------
    function enviar() {
        if (enviando) return; // evita envio duplicado
        var mensagem = texto.value.trim();
        var arquivo = inputArquivo.files[0];
        if (!mensagem && !arquivo) { texto.focus(); return; }

        enviando = true;
        botaoEnviar.disabled = true;
        botaoAnexar.disabled = true;
        botaoEnviar.textContent = 'Enviando…';

        var dados = new FormData();
        dados.append('id', ID);
        if (SEGURANCA) dados.append('seguranca', SEGURANCA);
        if (TOKEN) dados.append('token', TOKEN);
        dados.append('descricao', mensagem);
        if (arquivo) dados.append('arquivo', arquivo);

        fetch('/conversa', { method: 'POST', body: dados, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
            .then(function (resp) {
                if (resp.ok) {
                    // O texto só é apagado depois que o sistema confirma o recebimento.
                    texto.value = '';
                    limparArquivo();
                    mostrarErro('');
                    primeiraCarga = true; // rola até a mensagem enviada
                    agendar(0);
                } else {
                    mostrarErro(resp.erro || 'Não foi possível enviar. Sua mensagem foi mantida; tente novamente.');
                    if (resp.encerrado) agendar(0);
                }
            })
            .catch(function () {
                mostrarErro('Sem conexão. Sua mensagem foi mantida; tente novamente.');
            })
            .then(function () {
                enviando = false;
                botaoEnviar.disabled = false;
                botaoAnexar.disabled = false;
                botaoEnviar.textContent = 'Enviar';
            });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); enviar(); });
    texto.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); enviar(); }
    });

    buscar();
})();
</script>
