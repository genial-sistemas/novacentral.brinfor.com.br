
const map = {
    '1': '#helpdesk',
    '2': '#contrato-referencia',
    '4': '#contrato-referencia',
    '5': '#contrato-referencia',
    '6': '#contrato-referencia',
    '7': '#contrato-referencia'
};

$(document).ready(function() {
    const allSections = $('#helpdesk, #contrato-referencia');

    $('#id_tipo_contrato').change(function(){
        const val = $(this).val();

        allSections.hide().find('select').prop('required', false);
        const $contrato = $('#contrato_id');
        $contrato.prop('disabled', true).val('0');
        $contrato.find('option[data-tipo]').each(function() {
            const correspondeAoTipo = $(this).data('tipo').toString() === val;
            $(this).prop('hidden', !correspondeAoTipo);
        });

        if (map[val]) {
            $(map[val]).show().find('select').prop('required', true);
            if (val !== '1') {
                $contrato.prop('disabled', false);
            }
        }
    });

    $('#id_tipo_contrato').trigger('change');
});

$(document).ready(function() {
    $('#codigo_equipamento').on('change', function() {
        const selected = $(this).find('option:selected');
        const nome = selected.data('nome') || '';
        const email = selected.data('email') || '';
        const telefone = selected.data('telefone') || '';

        $('#etiqueta_codigo_equipamento').val(selected.data('codigo') || '');
        $('#nome').val(nome);
        $('#email').val(email);
        $('#telefone').val(telefone);

        // Eu aviso quando faltam dados de contato para a pessoa completar o que ficou vazio.
        const contatoIncompleto = !nome || !email || !telefone;
        $('#aviso-contato-equipamento').prop('hidden', selected.val() === '0' || !contatoIncompleto);
    });

    // Trigger once on page load in case something is pre-selected
    $('#codigo_equipamento').trigger('change');
});


$(document).ready(function() {
    // Automatically close the alert after 5 seconds (5000 milliseconds)
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 5000);
});