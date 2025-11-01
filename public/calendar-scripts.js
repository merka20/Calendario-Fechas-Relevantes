jQuery(document).ready(function($) {
    
    console.log('🚀 Inicializando calendario MK20');
    
    // Buscar o crear el tooltip
    var $tooltip = $('#MK20-cfr-tooltip');
    
    if ($tooltip.length === 0) {
        console.error('❌ ERROR: Tooltip no encontrado, creándolo...');
        $('body').append('<div id="MK20-cfr-tooltip" class="MK20-cfr-tooltip"><div class="MK20-cfr-tooltip-title"></div><div class="MK20-cfr-tooltip-description"></div></div>');
        $tooltip = $('#MK20-cfr-tooltip');
    }
    
    console.log('✅ Tooltip encontrado/creado');
    
    // FORZAR estilos base del tooltip desde JavaScript
    $tooltip.css({
        'position': 'fixed',
        'background': '#2c3e50',
        'color': 'white',
        'padding': '15px 20px',
        'border-radius': '8px',
        'max-width': '320px',
        'min-width': '200px',
        'z-index': '2147483647',
        'box-shadow': '0 8px 16px rgba(0,0,0,0.8)',
        'pointer-events': 'none',
        'display': 'none',
        'font-family': 'Arial, sans-serif',
        'font-size': '14px',
        'border': '2px solid #3498db'
    });
    
    var $tooltipTitle = $tooltip.find('.MK20-cfr-tooltip-title');
    var $tooltipDescription = $tooltip.find('.MK20-cfr-tooltip-description');
    
    // Estilos para el título y descripción
    $tooltipTitle.css({
        'font-weight': 'bold',
        'font-size': '16px',
        'margin-bottom': '8px',
        'border-bottom': '2px solid #3498db',
        'padding-bottom': '6px',
        'color': '#fff'
    });
    
    $tooltipDescription.css({
        'font-size': '14px',
        'line-height': '1.6',
        'color': '#ecf0f1',
        'margin': '0'
    });
    
    console.log('✅ Estilos del tooltip aplicados');
    
    // ========== EVENTO MOUSEENTER - SOLO FECHAS PERSONALIZADAS ==========
    // Excluimos festivos y fines de semana usando :not()
    $(document).on('mouseenter', '.MK20-cfr-day-event:not(.MK20-cfr-day-holiday):not(.MK20-cfr-day-weekend)', function(e) {
        var $this = $(this);
        var title = $this.attr('data-title');
        var description = $this.attr('data-description');
        
        console.log('👉 MOUSEENTER (Fecha personalizada) - Título:', title, 'Descripción:', description);
        
        if (title) {
            // Establecer contenido
            $tooltipTitle.html(title);
            $tooltipDescription.html(description || 'Sin descripción');
            
            // MOSTRAR con inline styles (fuerza la visualización)
            $tooltip.css({
                'display': 'block',
                'opacity': '1',
                'visibility': 'visible'
            });
            
            // Posicionar
            posicionarTooltip(e);
            
            console.log('✅ Tooltip VISIBLE en posición:', $tooltip.css('left'), $tooltip.css('top'));
        }
    });
    
    // ========== EVENTO MOUSEMOVE - SOLO FECHAS PERSONALIZADAS ==========
    $(document).on('mousemove', '.MK20-cfr-day-event:not(.MK20-cfr-day-holiday):not(.MK20-cfr-day-weekend)', function(e) {
        if ($tooltip.css('display') === 'block') {
            posicionarTooltip(e);
        }
    });
    
    // ========== EVENTO MOUSELEAVE - SOLO FECHAS PERSONALIZADAS ==========
    $(document).on('mouseleave', '.MK20-cfr-day-event:not(.MK20-cfr-day-holiday):not(.MK20-cfr-day-weekend)', function() {
        console.log('👈 MOUSELEAVE - Ocultando tooltip');
        
        $tooltip.css({
            'display': 'none',
            'opacity': '0',
            'visibility': 'hidden'
        });
    });
    
    // ========== FUNCIÓN DE POSICIONAMIENTO ==========
    function posicionarTooltip(e) {
        var tooltipWidth = $tooltip.outerWidth();
        var tooltipHeight = $tooltip.outerHeight();
        var offset = 20;
        
        var mouseX = e.clientX;
        var mouseY = e.clientY;
        
        var windowWidth = $(window).width();
        var windowHeight = $(window).height();
        
        var left = mouseX + offset;
        var top = mouseY + offset;
        
        // Ajustar si se sale por la derecha
        if (left + tooltipWidth > windowWidth) {
            left = mouseX - tooltipWidth - offset;
        }
        
        // Ajustar si se sale por abajo
        if (top + tooltipHeight > windowHeight) {
            top = mouseY - tooltipHeight - offset;
        }
        
        // Asegurar que no sea negativo
        if (left < 0) left = 10;
        if (top < 0) top = 10;
        
        $tooltip.css({
            'left': left + 'px',
            'top': top + 'px'
        });
    }
    
    // ========== TEST VISUAL ==========
    console.log('🧪 Para probar manualmente, ejecuta en consola: testTooltip()');
    
    window.testTooltip = function() {
        console.log('🧪 Ejecutando test del tooltip...');
        $tooltipTitle.html('✅ TEST EXITOSO');
        $tooltipDescription.html('El tooltip funciona. Añade una fecha personalizada en el admin y pasa el mouse sobre ella.');
        $tooltip.css({
            'display': 'block',
            'opacity': '1',
            'visibility': 'visible',
            'left': '50%',
            'top': '50%',
            'transform': 'translate(-50%, -50%)',
            'background': '#27ae60',
            'border': '3px solid #2ecc71'
        });
        
        setTimeout(function() {
            $tooltip.css({
                'display': 'none',
                'transform': 'none',
                'background': '#2c3e50',
                'border': '2px solid #3498db'
            });
            console.log('✅ Test completado');
        }, 4000);
    };
    
    console.log('📊 Elementos encontrados:', {
        'Fechas personalizadas (con tooltip)': $('.MK20-cfr-day-event:not(.MK20-cfr-day-holiday):not(.MK20-cfr-day-weekend)').length,
        'Festivos (sin tooltip)': $('.MK20-cfr-day-holiday').length,
        'Fines de semana (sin tooltip)': $('.MK20-cfr-day-weekend').length,
        'Tooltip existe': $tooltip.length > 0
    });
    
    console.log('👆 TOOLTIP solo aparece en fechas personalizadas (NO en festivos ni fines de semana)');
});