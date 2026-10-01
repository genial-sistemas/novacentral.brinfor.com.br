
const map = {
    '1': '#helpdesk',
    '2': '#hospedagem',
    '3': '#bhclouderp',
    '4': '#dominio',
    '5': '#backup',
    '6': '#suporte',
    '7': '#locacao'
};

$(document).ready(function() {
    const allSections = $(Object.values(map).join(', '));

    $('#id_tipo_contrato').change(function(){
        const val = $(this).val();

        allSections.hide().find('select').attr('required', false);

        if (map[val]) {
            $(map[val]).show().find('select').attr('required', true);
        }
    });

    $('#id_tipo_contrato').trigger('change');
});

$(document).ready(function() {
    $('#codigo_equipamento').on('change', function() {
        // Get the selected option
        const selected = $(this).find('option:selected');
        // Set input value to data-codigo (or empty if undefined)
        $('#etiqueta_codigo_equipamento').val(selected.data('codigo') || '');
        $('#nome').val(selected.data('nome') || '');
        $('#email').val(selected.data('email') || '');
        $('#telefone').val(selected.data('telefone') || '');
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