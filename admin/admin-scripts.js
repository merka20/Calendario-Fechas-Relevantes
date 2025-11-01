jQuery(document).ready(function($) {
    
    // Inicializar color pickers
    $('.MK20-cfr-color-picker').wpColorPicker({
        change: function(event, ui) {
            // Si es un color de cabecera, actualizar preview
            if ($(this).attr('id') === 'MK20_cfr_header_color_1' || $(this).attr('id') === 'MK20_cfr_header_color_2') {
                updateGradientPreview();
            }
        }
    });
    
    let dateIndex = $('.MK20-cfr-date-row').length;
    
    // Añadir nueva fecha
    $('#MK20-cfr-add-date').on('click', function() {
        const template = $('#MK20-cfr-date-row-template').html();
        const newRow = template.replace(/INDEX/g, dateIndex);
        $('#MK20-cfr-dates-container').append(newRow);
        $('#MK20-cfr-dates-container .MK20-cfr-date-row:last-child .MK20-cfr-color-picker').wpColorPicker();
        dateIndex++;
    });
    
    // Eliminar fecha
    $(document).on('click', '.MK20-cfr-remove-date', function() {
        const confirmMessage = MK20_cfrAdmin.confirmDelete || 'Are you sure you want to delete this date?';
        if (confirm(confirmMessage)) {
            $(this).closest('.MK20-cfr-date-row').fadeOut(300, function() {
                $(this).remove();
            });
        }
    });
    
    // Reindexar antes de enviar el formulario
    $('#MK20-cfr-settings-form').on('submit', function() {
        $('.MK20-cfr-date-row').each(function(index) {
            $(this).find('input, textarea').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    });
    
    // ========== NUEVO: Actualizar preview del gradiente en tiempo real ==========
    function updateGradientPreview() {
        const color1 = $('#MK20_cfr_header_color_1').val();
        const color2 = $('#MK20_cfr_header_color_2').val();
        
        if (color1 && color2) {
            const gradient = 'linear-gradient(135deg, ' + color1 + ' 0%, ' + color2 + ' 100%)';
            $('.MK20-cfr-gradient-preview').css('background', gradient);
        }
    }
    
    // Actualizar preview cuando cambian los inputs manualmente
    $('#MK20_cfr_header_color_1, #MK20_cfr_header_color_2').on('input change', function() {
        updateGradientPreview();
    });
});