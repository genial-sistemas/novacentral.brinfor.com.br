$(function() {
    $('[data-telefone-br]').each(function() {
        const campo = this;

        const formatar = function() {
            const numeros = campo.value.replace(/\D/g, '').slice(0, 11);
            if (numeros.length <= 2) {
                campo.value = numeros ? '(' + numeros : '';
                return;
            }

            const ddd = numeros.slice(0, 2);
            const telefone = numeros.slice(2);
            if (numeros.length <= 10) {
                campo.value = '(' + ddd + ') ' + telefone.slice(0, 4)
                    + (telefone.length > 4 ? '-' + telefone.slice(4) : '');
                return;
            }

            campo.value = '(' + ddd + ') ' + telefone.slice(0, 5)
                + (telefone.length > 5 ? '-' + telefone.slice(5) : '');
        };

        campo.addEventListener('input', formatar);
        formatar();
    });
});
