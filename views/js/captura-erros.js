(function() {
    'use strict';

    var endpoint = '/index.php?page=registrar-erro-cliente';

    function enviar(origem, mensagem) {
        if (typeof mensagem !== 'string' || mensagem.length === 0) {
            return;
        }

        var dados = JSON.stringify({
            origem: origem,
            mensagem: mensagem.slice(0, 4000),
            rota: window.location.pathname
        });

        if (navigator.sendBeacon && navigator.sendBeacon(endpoint, new Blob([dados], {type: 'application/json'}))) {
            return;
        }

        fetch(endpoint, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: dados,
            keepalive: true,
            credentials: 'same-origin'
        }).catch(function() {});
    }

    function textoErro(valor) {
        if (valor instanceof Error) {
            return valor.name + ': ' + valor.message + (valor.stack ? '\n' + valor.stack : '');
        }

        if (typeof valor === 'string') {
            return valor;
        }

        try {
            return JSON.stringify(valor);
        } catch (erro) {
            return String(valor);
        }
    }

    window.addEventListener('error', function(evento) {
        var mensagem = evento.message || textoErro(evento.error);
        if (evento.filename) {
            mensagem += ' (' + evento.filename.split('?')[0] + ':' + evento.lineno + ')';
        }
        enviar('browser.error', mensagem);
    });

    window.addEventListener('unhandledrejection', function(evento) {
        enviar('browser.rejection', textoErro(evento.reason));
    });

    var consoleErrorOriginal = window.console && window.console.error;
    if (consoleErrorOriginal) {
        window.console.error = function() {
            var mensagem = Array.prototype.map.call(arguments, textoErro).join(' ');
            enviar('browser.console', mensagem);
            consoleErrorOriginal.apply(window.console, arguments);
        };
    }
})();
