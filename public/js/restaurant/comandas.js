// ==================== VARIABLES GLOBALES ====================
var posProductos = window.posProductos || [];
var posCarrito = window.posCarrito || [];
var posMesaActual = window.posMesaActual || null;
var posComandaActual = window.posComandaActual || null;
var posCapacidadOriginal = window.posCapacidadOriginal || 0;
var posFechaApertura = window.posFechaApertura || null; // Almacena la fecha/hora de apertura de la comanda
var posPropinaPorcentaje = typeof window.posPropinaPorcentaje !== 'undefined' ? parseFloat(window.posPropinaPorcentaje) : 10;
var POS_EVENT_NS = window.POS_EVENT_NS || '.posComandas';
var POS_STOCK_CACHE_TTL_MS = window.POS_STOCK_CACHE_TTL_MS || 1200;
var POS_PRECIO_CACHE_TTL_MS = window.POS_PRECIO_CACHE_TTL_MS || 1200;
var posStockCache = window.posStockCache instanceof Map ? window.posStockCache : new Map();
var posStockInFlight = window.posStockInFlight instanceof Map ? window.posStockInFlight : new Map();
var posPrecioCache = window.posPrecioCache instanceof Map ? window.posPrecioCache : new Map();
var posPrecioInFlight = window.posPrecioInFlight instanceof Map ? window.posPrecioInFlight : new Map();
var posSearchTimer = window.posSearchTimer || null;
var posLayoutMesasActual = window.posLayoutMesasActual || null;
var posLayoutDragState = window.posLayoutDragState || null;
var posReservaExpiracionMinutos = typeof window.posReservaExpiracionMinutos !== 'undefined' ? parseInt(window.posReservaExpiracionMinutos, 10) : 15;
var posImpresionSeparada = typeof window.posImpresionSeparada !== 'undefined' ? parseInt(window.posImpresionSeparada, 10) : 0;
var POS_LAYOUT_MESA_FONT_SIZE_PX = typeof window.POS_LAYOUT_MESA_FONT_SIZE_PX !== 'undefined' ? parseInt(window.POS_LAYOUT_MESA_FONT_SIZE_PX, 10) : 18;
var _garzonesCache = null;

window.posProductos = posProductos;
window.posCarrito = posCarrito;
window.posMesaActual = posMesaActual;
window.posComandaActual = posComandaActual;
window.posCapacidadOriginal = posCapacidadOriginal;
window.posFechaApertura = posFechaApertura;
window.posPropinaPorcentaje = posPropinaPorcentaje;
window.POS_EVENT_NS = POS_EVENT_NS;
window.POS_STOCK_CACHE_TTL_MS = POS_STOCK_CACHE_TTL_MS;
window.POS_PRECIO_CACHE_TTL_MS = POS_PRECIO_CACHE_TTL_MS;
window.posStockCache = posStockCache;
window.posStockInFlight = posStockInFlight;
window.posPrecioCache = posPrecioCache;
window.posPrecioInFlight = posPrecioInFlight;
window.posSearchTimer = posSearchTimer;
window.posLayoutMesasActual = posLayoutMesasActual;
window.posLayoutDragState = posLayoutDragState;
window.posReservaExpiracionMinutos = posReservaExpiracionMinutos;
window.posImpresionSeparada = posImpresionSeparada;
window.POS_LAYOUT_MESA_FONT_SIZE_PX = POS_LAYOUT_MESA_FONT_SIZE_PX;

if (window.comandasRefreshInterval) {
    clearInterval(window.comandasRefreshInterval);
    window.comandasRefreshInterval = null;
}

function limpiarIntervaloComandas() {
    if (window.comandasRefreshInterval) {
        clearInterval(window.comandasRefreshInterval);
        window.comandasRefreshInterval = null;
    }
}

function iniciarIntervaloComandas() {
    limpiarIntervaloComandas();

    if (!$('#mesas-container').length) {
        return;
    }

    window.comandasRefreshInterval = setInterval(function() {
        if (!$('#mesas-container').length) {
            limpiarIntervaloComandas();
            return;
        }
        cargarMesas();
    }, 60000);
}

$(document).ready(function() {
    if (!$('#mesas-container').length) {
        limpiarIntervaloComandas();
        return;
    }

    cargarMesas();
    iniciarIntervaloComandas();
    
    // Evento para refrescar manualmente
    $('#refrescar_mesas').on('click', function() {
        cargarMesas();
    });

    $('#btn_abrir_cambio_mesa').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        abrirModalCambioMesa();
    });

    $('#btn_ver_plano_mesas').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        $('#modalPlanoMesas').modal('show');
    });

    $('#modalPlanoMesas')
        .off('shown.bs.modal' + POS_EVENT_NS)
        .on('shown.bs.modal' + POS_EVENT_NS, function() {
            if (posLayoutMesasActual) {
                renderizarPreviewLayoutMesas(posLayoutMesasActual);
                return;
            }
            cargarLayoutMesasJson();
        });

    $(window)
        .off('resize' + POS_EVENT_NS)
        .on('resize' + POS_EVENT_NS, function() {
            if ($('#modalPlanoMesas').is(':visible') && posLayoutMesasActual) {
                renderizarPreviewLayoutMesas(posLayoutMesasActual);
            }
        });

    $('#btn_cargar_layout_json').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        cargarLayoutMesasJson();
    });


    $('#btn_agregar_texto_layout').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        if (!posLayoutMesasActual) {
            cargarLayoutMesasJson();
            return;
        }
        abrirDialogoTextoLayout();
    });

    $('#btn_guardar_texto_layout_modal').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        if (!posLayoutMesasActual) {
            return;
        }

        const texto = $.trim($('#layout_text_input').val() || '');
        const color = normalizarColorHexLayout($('#layout_text_bg_color').val(), '#fff7d6');
        const rawIndex = $.trim($('#layout_text_edit_index').val() || '');
        const editando = rawIndex !== '' && !Number.isNaN(parseInt(rawIndex, 10));
        const indice = editando ? parseInt(rawIndex, 10) : -1;

        if (!texto) {
            Swal.fire('Atención', 'Debes ingresar un texto', 'warning');
            return;
        }

        if (!Array.isArray(posLayoutMesasActual.labels)) {
            posLayoutMesasActual.labels = [];
        }

        if (editando && posLayoutMesasActual.labels[indice]) {
            posLayoutMesasActual.labels[indice].text = texto;
            posLayoutMesasActual.labels[indice].width = calcularAnchoLabelLayout(texto);
            posLayoutMesasActual.labels[indice].bgColor = color;
        } else {
            const canvasHeight = parseInt(posLayoutMesasActual.canvas && posLayoutMesasActual.canvas.height, 10) || 700;
            posLayoutMesasActual.labels.push({
                id: crearIdTemporalLayout('label'),
                text: texto,
                x: 50,
                y: Math.min(Math.max(40, 44 + (posLayoutMesasActual.labels.length * 42)), canvasHeight - 60),
                width: calcularAnchoLabelLayout(texto),
                height: 34,
                fontSize: 18,
                bgColor: color
            });
        }

        $('#modalTextoLayout').modal('hide');
        actualizarTextoLayoutMesas();
        renderizarPreviewLayoutMesas(posLayoutMesasActual);
    });

    $('#modalTextoLayout').off('shown.bs.modal' + POS_EVENT_NS).on('shown.bs.modal' + POS_EVENT_NS, function() {
        $('#layout_text_input').trigger('focus');
    });

    $(document)
        .off('click' + POS_EVENT_NS, '.layout-color-swatch')
        .on('click' + POS_EVENT_NS, '.layout-color-swatch', function() {
            const color = normalizarColorHexLayout($(this).attr('data-color'), '#fff7d6');
            $('#layout_text_bg_color').val(color).trigger('change');
        });

    $('#btn_guardar_layout_json').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        guardarLayoutMesasJson();
    });

    $('#btn_agregar_linea_layout').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        abrirDialogoLineaLayout(null);
    });

    $('#btn_uniformizar_mesas_layout').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        if (!posLayoutMesasActual || !Array.isArray(posLayoutMesasActual.mesas) || posLayoutMesasActual.mesas.length === 0) {
            return;
        }
        var totalW = 0, totalH = 0;
        posLayoutMesasActual.mesas.forEach(function(m) { totalW += parseFloat(m.width) || 108; totalH += parseFloat(m.height) || 72; });
        var avgW = Math.round(totalW / posLayoutMesasActual.mesas.length);
        var avgH = Math.round(totalH / posLayoutMesasActual.mesas.length);
        Swal.fire({
            title: 'Uniformizar tamaños',
            html: '<p style="margin-bottom:10px">Define el tamaño para <strong>todas</strong> las mesas:<br><small style="color:#6b7280">Promedio actual: ' + avgW + ' × ' + avgH + ' px</small></p>' +
                  '<div style="display:flex;gap:12px;justify-content:center;align-items:center">' +
                  '<label style="margin:0">Ancho:<br><input id="swal_mesa_w" type="number" min="60" max="400" value="' + avgW + '" style="width:90px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;font-size:15px"></label>' +
                  '<label style="margin:0">Alto:<br><input id="swal_mesa_h" type="number" min="40" max="300" value="' + avgH + '" style="width:90px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;font-size:15px"></label>' +
                  '</div>',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-check"></i> Aplicar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b',
            preConfirm: function() {
                var w = parseInt(document.getElementById('swal_mesa_w').value, 10);
                var h = parseInt(document.getElementById('swal_mesa_h').value, 10);
                if (!w || w < 60 || !h || h < 40) {
                    Swal.showValidationMessage('Ingresa valores válidos (ancho mín 60, alto mín 40)');
                    return false;
                }
                return { w: w, h: h };
            }
        }).then(function(result) {
            if (!result.isConfirmed) return;
            posLayoutMesasActual.mesas = posLayoutMesasActual.mesas.map(function(m) {
                return $.extend({}, m, { width: result.value.w, height: result.value.h });
            });
            renderizarPreviewLayoutMesas(posLayoutMesasActual);
        });
    });
});

function crearIdTemporalLayout(prefix) {
    return prefix + '-' + Date.now() + '-' + Math.floor(Math.random() * 10000);
}

function calcularAnchoLabelLayout(texto) {
    const largo = $.trim(texto || '').length;
    return Math.max(120, Math.min(260, 28 + (largo * 10)));
}

function normalizarColorHexLayout(color, fallback) {
    const valor = $.trim((color || '').toString());
    const porDefecto = fallback || '#fff7d6';

    if (!valor) {
        return porDefecto;
    }

    if (/^#[0-9a-fA-F]{6}$/.test(valor)) {
        return valor.toLowerCase();
    }

    if (/^#[0-9a-fA-F]{3}$/.test(valor)) {
        return ('#' + valor[1] + valor[1] + valor[2] + valor[2] + valor[3] + valor[3]).toLowerCase();
    }

    return porDefecto;
}

function obtenerEstiloLabelLayout(bgColor) {
    const bg = normalizarColorHexLayout(bgColor, '#fff7d6');
    const hex = bg.replace('#', '');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const luminancia = (0.299 * r) + (0.587 * g) + (0.114 * b);
    const textoOscuro = luminancia > 162;

    return {
        bg: bg,
        text: textoOscuro ? '#3b2f1d' : '#f8fafc',
        border: textoOscuro ? 'rgba(120, 53, 15, 0.55)' : 'rgba(248, 250, 252, 0.60)',
        remove: textoOscuro ? '#9a3412' : '#fef3c7'
    };
}

function normalizarLayoutMesas(layout) {
    const safe = (layout && typeof layout === 'object') ? layout : {};
    const versionActual = parseInt(safe.version || 1, 10) || 1;

    if (!safe.canvas || typeof safe.canvas !== 'object') {
        safe.canvas = { width: 1200, height: 700, grid: 20 };
    }
    if (!Array.isArray(safe.mesas)) {
        safe.mesas = [];
    }
    if (!Array.isArray(safe.labels)) {
        safe.labels = [];
    }

    safe.mesas = safe.mesas.map(function(mesa, index) {
        let width = parseFloat(mesa.width);
        let height = parseFloat(mesa.height);

        width = Number.isFinite(width) && width > 0 ? width : 108;
        height = Number.isFinite(height) && height > 0 ? height : 72;

        if (versionActual < 2) {
            if (width >= 120) {
                width = Math.round(width * 0.84);
            }
            if (height >= 84) {
                height = Math.round(height * 0.84);
            }
        }

        return {
            mesa_id: mesa.mesa_id || mesa.id || (index + 1),
            nombre: mesa.nombre || ('Mesa ' + (mesa.mesa_id || index + 1)),
            capacidad: mesa.capacidad || 0,
            x: parseInt(mesa.x, 10) || 0,
            y: parseInt(mesa.y, 10) || 0,
            width: Math.max(88, Math.round(width)),
            height: Math.max(58, Math.round(height)),
            shape: mesa.shape === 'circle' ? 'circle' : 'rect',
            rotation: parseFloat(mesa.rotation) || 0
        };
    });

    safe.labels = safe.labels.map(function(label, index) {
        const texto = $.trim(label.text || label.nombre || ('Texto ' + (index + 1)));
        return {
            id: label.id || crearIdTemporalLayout('label'),
            text: texto,
            x: parseInt(label.x, 10) || 0,
            y: parseInt(label.y, 10) || 0,
            width: Math.max(120, parseInt(label.width, 10) || calcularAnchoLabelLayout(texto)),
            height: Math.max(32, parseInt(label.height, 10) || 34),
            fontSize: Math.max(14, parseInt(label.fontSize, 10) || 18),
            bgColor: normalizarColorHexLayout(label.bgColor || label.backgroundColor || '#fff7d6', '#fff7d6')
        };
    });


    safe.lines = Array.isArray(layout.lines) ? layout.lines.map(function(line, index) {
        var orientacion = (line.orientacion === 'horizontal' || line.orientacion === 'vertical') ? line.orientacion : 'horizontal';
        var largo = Math.max(40, parseInt(line.largo, 10) || (orientacion === 'horizontal' ? 200 : 200));
        var grosor = Math.max(1, Math.min(20, parseInt(line.grosor, 10) || 3));
        return {
            id: line.id || crearIdTemporalLayout('line'),
            orientacion: orientacion,
            x: parseInt(line.x, 10) || 0,
            y: parseInt(line.y, 10) || 0,
            largo: largo,
            grosor: grosor,
            color: normalizarColorHexLayout(line.color, '#374151')
        };
    }) : [];

    safe.version = 2;
    return safe;
}

function actualizarTextoLayoutMesas() {
    const $summary = $('#layout_text_summary');
    if (!$summary.length) {
        return;
    }

    const totalMesas = posLayoutMesasActual && Array.isArray(posLayoutMesasActual.mesas)
        ? posLayoutMesasActual.mesas.length
        : 0;
    const totalTextos = posLayoutMesasActual && Array.isArray(posLayoutMesasActual.labels)
        ? posLayoutMesasActual.labels.length
        : 0;

    const totalLineas = posLayoutMesasActual && Array.isArray(posLayoutMesasActual.lines)
        ? posLayoutMesasActual.lines.length
        : 0;

    $summary.text('Mesas: ' + totalMesas + ' | Textos: ' + totalTextos + ' | Líneas: ' + totalLineas);
}

function obtenerConfigElementoLayout($element) {
    if (!posLayoutMesasActual) {
        return null;
    }

    const tipo = $element.attr('data-layout-type') ||
        ($element.hasClass('pos-layout-label') ? 'label' : ($element.hasClass('pos-layout-line') ? 'line' : 'mesa'));
    const attrIndice = tipo === 'label' ? 'data-label-index' : (tipo === 'line' ? 'data-line-index' : 'data-mesa-index');
    const indice = parseInt($element.attr(attrIndice), 10);

    if (Number.isNaN(indice)) {
        return null;
    }

    const collection = tipo === 'label' ? posLayoutMesasActual.labels
        : (tipo === 'line' ? posLayoutMesasActual.lines : posLayoutMesasActual.mesas);
    if (!Array.isArray(collection) || !collection[indice]) {
        return null;
    }

    const isLine = tipo === 'line';
    const lineItem = isLine ? collection[indice] : null;
    const isH = isLine && lineItem && lineItem.orientacion === 'horizontal';
    return {
        tipo: tipo,
        indice: indice,
        collection: collection,
        item: collection[indice],
        defaultWidth: tipo === 'label' ? 160 : (isLine ? (isH ? (lineItem.largo || 200) : (lineItem.grosor || 3)) : 108),
        defaultHeight: tipo === 'label' ? 34 : (isLine ? (isH ? (lineItem.grosor || 3) : (lineItem.largo || 200)) : 72)
    };
}

function abrirDialogoTextoLayout(indiceLabel) {
    const editando = Number.isInteger(indiceLabel);
    const labelActual = editando && posLayoutMesasActual && posLayoutMesasActual.labels
        ? posLayoutMesasActual.labels[indiceLabel]
        : null;

    if (!posLayoutMesasActual) {
        return;
    }

    $('#layout_text_edit_index').val(editando ? indiceLabel : '');
    $('#layout_text_input').val(labelActual ? (labelActual.text || '') : '');
    $('#layout_text_bg_color').val(normalizarColorHexLayout(labelActual ? labelActual.bgColor : '#fff7d6', '#fff7d6'));
    $('#modalTextoLayout').modal('show');
}

function abrirDialogoLineaLayout(indiceLinea) {
    if (!posLayoutMesasActual) {
        return;
    }

    var modalConstructor = $.fn.modal && $.fn.modal.Constructor ? $.fn.modal.Constructor : null;
    var enforceFocusOriginal = null;
    if (modalConstructor && modalConstructor.prototype && typeof modalConstructor.prototype.enforceFocus === 'function') {
        enforceFocusOriginal = modalConstructor.prototype.enforceFocus;
        // Bootstrap 3 roba el foco cuando hay un modal abierto; esto bloquea inputs de SweetAlert.
        modalConstructor.prototype.enforceFocus = function() {};
    }

    var editando = Number.isInteger(indiceLinea) && Array.isArray(posLayoutMesasActual.lines) && !!posLayoutMesasActual.lines[indiceLinea];
    var lineaActual = editando ? posLayoutMesasActual.lines[indiceLinea] : null;
    var orientacion = lineaActual && lineaActual.orientacion === 'vertical' ? 'vertical' : 'horizontal';
    var largo = Math.max(40, parseInt(lineaActual && lineaActual.largo, 10) || 300);
    var grosor = Math.max(1, parseInt(lineaActual && lineaActual.grosor, 10) || 3);
    var color = normalizarColorHexLayout(lineaActual && lineaActual.color, '#374151');

    Swal.fire({
        title: editando ? 'Editar línea' : 'Agregar línea',
        target: document.getElementById('modalPlanoMesas') || document.body,
        heightAuto: false,
        html:
            '<div style="display:flex;flex-direction:column;gap:14px;text-align:left">' +
            '<div><label style="font-weight:600;margin-bottom:4px;display:block">Orientación</label>' +
            '<div style="display:flex;gap:10px">' +
            '<label style="cursor:pointer"><input type="radio" name="swal_linea_ori" value="horizontal" ' + (orientacion === 'horizontal' ? 'checked' : '') + ' style="margin-right:4px">Horizontal</label>' +
            '<label style="cursor:pointer"><input type="radio" name="swal_linea_ori" value="vertical" ' + (orientacion === 'vertical' ? 'checked' : '') + ' style="margin-right:4px">Vertical</label>' +
            '</div></div>' +
            '<div style="display:flex;gap:12px">' +
            '<label style="flex:1;font-weight:600">Largo (px lógicos)<br><input id="swal_linea_largo" type="tel" inputmode="numeric" autocomplete="off" value="' + largo + '" style="width:100%;padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:15px"></label>' +
            '<label style="width:80px;font-weight:600">Grosor<br><input id="swal_linea_grosor" type="tel" inputmode="numeric" autocomplete="off" value="' + grosor + '" style="width:100%;padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:15px"></label>' +
            '</div>' +
            '<label style="font-weight:600">Color<br>' +
            '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:4px">' +
            '<input type="color" id="swal_linea_color" value="' + color + '" style="width:42px;height:34px;padding:2px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer">' +
            ['#374151', '#6366f1', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#000000'].map(function(c) {
                return '<span data-lcolor="' + c + '" style="display:inline-block;width:24px;height:24px;border-radius:50%;background:' + c + ';cursor:pointer;border:2px solid #e5e7eb" title="' + c + '"></span>';
            }).join('') +
            '</div></label>' +
            '</div>',
        didOpen: function() {
            $(document).on('click.swalline', '[data-lcolor]', function() {
                document.getElementById('swal_linea_color').value = $(this).data('lcolor');
            });

            var $largoInput = $('#swal_linea_largo');
            var $grosorInput = $('#swal_linea_grosor');

            $largoInput.prop('readonly', false).prop('disabled', false);
            $grosorInput.prop('readonly', false).prop('disabled', false);

            function limpiarNumerico($el) {
                var limpio = String($el.val() || '').replace(/[^0-9]/g, '');
                $el.val(limpio);
            }

            $largoInput.on('input', function() { limpiarNumerico($largoInput); });
            $grosorInput.on('input', function() { limpiarNumerico($grosorInput); });

            $largoInput.add($grosorInput).on('keydown keypress keyup click mousedown', function(e) {
                e.stopPropagation();
            });

            $largoInput.trigger('focus');
            try {
                if ($largoInput[0] && typeof $largoInput[0].setSelectionRange === 'function') {
                    $largoInput[0].setSelectionRange(0, String($largoInput.val() || '').length);
                }
            } catch (err) {
                // Sin acción: algunos navegadores restringen setSelectionRange en ciertos modos.
            }
        },
        willClose: function() {
            $(document).off('click.swalline');
            if (enforceFocusOriginal && modalConstructor && modalConstructor.prototype) {
                modalConstructor.prototype.enforceFocus = enforceFocusOriginal;
            }
        },
        showCancelButton: true,
        showDenyButton: editando,
        confirmButtonText: editando ? '<i class="fa fa-save"></i> Guardar' : '<i class="fa fa-plus"></i> Agregar',
        denyButtonText: '<i class="fa fa-trash"></i> Eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#6b7280',
        denyButtonColor: '#dc2626',
        preConfirm: function() {
            var ori = $('input[name="swal_linea_ori"]:checked').val() || 'horizontal';
            var valorLargo = parseInt(document.getElementById('swal_linea_largo').value, 10);
            var valorGrosor = parseInt(document.getElementById('swal_linea_grosor').value, 10);
            var valorColor = document.getElementById('swal_linea_color').value || '#374151';
            if (!valorLargo || valorLargo < 40) {
                Swal.showValidationMessage('Largo mínimo: 40');
                return false;
            }
            if (!valorGrosor || valorGrosor < 1) {
                Swal.showValidationMessage('Grosor mínimo: 1');
                return false;
            }
            return { ori: ori, largo: valorLargo, grosor: valorGrosor, color: valorColor };
        }
    }).then(function(result) {
        if (result.isDenied) {
            if (editando && Array.isArray(posLayoutMesasActual.lines) && posLayoutMesasActual.lines[indiceLinea]) {
                posLayoutMesasActual.lines.splice(indiceLinea, 1);
                actualizarTextoLayoutMesas();
                renderizarPreviewLayoutMesas(posLayoutMesasActual);
            }
            return;
        }

        if (!result.isConfirmed) {
            return;
        }

        if (!Array.isArray(posLayoutMesasActual.lines)) {
            posLayoutMesasActual.lines = [];
        }

        if (editando) {
            posLayoutMesasActual.lines[indiceLinea] = $.extend({}, posLayoutMesasActual.lines[indiceLinea], {
                orientacion: result.value.ori,
                largo: result.value.largo,
                grosor: result.value.grosor,
                color: result.value.color
            });
        } else {
            posLayoutMesasActual.lines.push({
                id: crearIdTemporalLayout('line'),
                orientacion: result.value.ori,
                x: 100,
                y: 100,
                largo: result.value.largo,
                grosor: result.value.grosor,
                color: result.value.color
            });
        }

        actualizarTextoLayoutMesas();
        renderizarPreviewLayoutMesas(posLayoutMesasActual);
    });
}

function inicializarEventosDragLayoutMesas() {
    $('#layout_preview_canvas')
        .off('mousedown' + POS_EVENT_NS, '.pos-layout-mesa, .pos-layout-label, .pos-layout-line')
        .on('mousedown' + POS_EVENT_NS, '.pos-layout-mesa, .pos-layout-label, .pos-layout-line', function(e) {
            if (e.which !== 1 || !posLayoutMesasActual || $(e.target).closest('.pos-layout-label-remove').length) {
                return;
            }

            const config = obtenerConfigElementoLayout($(this));
            if (!config) {
                return;
            }

            const $inner = $('#layout_preview_canvas .pos-layout-inner');
            const scale = parseFloat($inner.attr('data-scale')) || 1;
            const canvasWidth = parseFloat($inner.attr('data-canvas-width')) || 1200;
            const canvasHeight = parseFloat($inner.attr('data-canvas-height')) || 700;
            const grid = parseFloat($inner.attr('data-grid')) || 0;

            posLayoutDragState = {
                tipo: config.tipo,
                indice: config.indice,
                startMouseX: e.pageX,
                startMouseY: e.pageY,
                startLeftPx: parseFloat($(this).css('left')) || 0,
                startTopPx: parseFloat($(this).css('top')) || 0,
                scale: scale,
                canvasWidth: canvasWidth,
                canvasHeight: canvasHeight,
                grid: grid,
                $element: $(this),
                defaultWidth: config.defaultWidth,
                defaultHeight: config.defaultHeight,
                hasMoved: false
            };

            $('body').css('user-select', 'none');
            e.preventDefault();
        })
        .off('dblclick' + POS_EVENT_NS, '.pos-layout-label')
        .on('dblclick' + POS_EVENT_NS, '.pos-layout-label', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const indice = parseInt($(this).attr('data-label-index'), 10);
            if (!Number.isNaN(indice)) {
                abrirDialogoTextoLayout(indice);
            }
        })
        .off('dblclick' + POS_EVENT_NS, '.pos-layout-line')
        .on('dblclick' + POS_EVENT_NS, '.pos-layout-line', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const indice = parseInt($(this).attr('data-line-index'), 10);
            if (!Number.isNaN(indice)) {
                abrirDialogoLineaLayout(indice);
            }
        })
        .off('click' + POS_EVENT_NS, '.pos-layout-label-remove')
        .on('click' + POS_EVENT_NS, '.pos-layout-label-remove', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!posLayoutMesasActual || !Array.isArray(posLayoutMesasActual.labels)) {
                return;
            }

            const indice = parseInt($(this).closest('.pos-layout-label').attr('data-label-index'), 10);
            if (Number.isNaN(indice)) {
                return;
            }

            posLayoutMesasActual.labels.splice(indice, 1);
            actualizarTextoLayoutMesas();
            renderizarPreviewLayoutMesas(posLayoutMesasActual);
        });

    $(document)
        .off('mousemove' + POS_EVENT_NS)
        .on('mousemove' + POS_EVENT_NS, function(e) {
            if (!posLayoutDragState || !posLayoutMesasActual) {
                return;
            }

            const collection = posLayoutDragState.tipo === 'label'
                ? posLayoutMesasActual.labels
                : (posLayoutDragState.tipo === 'line' ? posLayoutMesasActual.lines : posLayoutMesasActual.mesas);
            const item = Array.isArray(collection) ? collection[posLayoutDragState.indice] : null;

            if (!item) {
                return;
            }

            const itemWidth = parseFloat(item.width) || posLayoutDragState.defaultWidth;
            const itemHeight = parseFloat(item.height) || posLayoutDragState.defaultHeight;
            const maxX = Math.max(0, posLayoutDragState.canvasWidth - itemWidth);
            const maxY = Math.max(0, posLayoutDragState.canvasHeight - itemHeight);
            const deltaXPx = e.pageX - posLayoutDragState.startMouseX;
            const deltaYPx = e.pageY - posLayoutDragState.startMouseY;

            if (!posLayoutDragState.hasMoved && (Math.abs(deltaXPx) > 2 || Math.abs(deltaYPx) > 2)) {
                posLayoutDragState.hasMoved = true;
            }

            let nextX = (posLayoutDragState.startLeftPx + deltaXPx) / posLayoutDragState.scale;
            let nextY = (posLayoutDragState.startTopPx + deltaYPx) / posLayoutDragState.scale;

            nextX = Math.max(0, Math.min(maxX, nextX));
            nextY = Math.max(0, Math.min(maxY, nextY));

            if (posLayoutDragState.grid > 0) {
                nextX = Math.round(nextX / posLayoutDragState.grid) * posLayoutDragState.grid;
                nextY = Math.round(nextY / posLayoutDragState.grid) * posLayoutDragState.grid;
                nextX = Math.max(0, Math.min(maxX, nextX));
                nextY = Math.max(0, Math.min(maxY, nextY));
            }

            item.x = Math.round(nextX);
            item.y = Math.round(nextY);

            posLayoutDragState.$element.css({
                left: Math.round(item.x * posLayoutDragState.scale) + 'px',
                top: Math.round(item.y * posLayoutDragState.scale) + 'px'
            });
        })
        .off('mouseup' + POS_EVENT_NS)
        .on('mouseup' + POS_EVENT_NS, function() {
            if (!posLayoutDragState) {
                return;
            }

            const shouldRerender = !!posLayoutDragState.hasMoved;
            posLayoutDragState = null;
            $('body').css('user-select', '');

            if (shouldRerender) {
                actualizarTextoLayoutMesas();
                renderizarPreviewLayoutMesas(posLayoutMesasActual);
            }
        });
}

function cargarLayoutMesasJson() {
    $.ajax({
        url: '/restaurant/comandas/layout-json',
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            if (!resp || !resp.success) {
                Swal.fire('Error', (resp && resp.message) ? resp.message : 'No se pudo cargar el layout', 'error');
                return;
            }

            posLayoutMesasActual = normalizarLayoutMesas(resp.layout || {});
            actualizarTextoLayoutMesas();
            renderizarPreviewLayoutMesas(posLayoutMesasActual);
        },
        error: function(xhr) {
            let msg = 'Error al cargar layout';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire('Error', msg, 'error');
        }
    });
}

function guardarLayoutMesasJson() {
    if (!posLayoutMesasActual || !Array.isArray(posLayoutMesasActual.mesas)) {
        Swal.fire('Atención', 'No hay layout cargado para guardar', 'warning');
        return;
    }

    const parsed = normalizarLayoutMesas(posLayoutMesasActual);

    $.ajax({
        url: '/restaurant/comandas/layout-json',
        type: 'POST',
        dataType: 'json',
        data: {
            _token: $('#token').val(),
            layout: parsed
        },
        success: function(resp) {
            if (!resp || !resp.success) {
                Swal.fire('Error', (resp && resp.message) ? resp.message : 'No se pudo guardar el layout', 'error');
                return;
            }

            posLayoutMesasActual = parsed;
            actualizarTextoLayoutMesas();
            renderizarPreviewLayoutMesas(posLayoutMesasActual);
            Swal.fire('Éxito', 'Layout guardado correctamente', 'success');
        },
        error: function(xhr) {
            let msg = 'Error al guardar layout';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire('Error', msg, 'error');
        }
    });
}

function renderizarPreviewLayoutMesas(layout) {
    const $canvas = $('#layout_preview_canvas');
    if (!$canvas.length) return;

    const safe = normalizarLayoutMesas(layout);
    const width = parseInt(safe.canvas.width || 1200, 10);
    const height = parseInt(safe.canvas.height || 700, 10);
    const grid = parseInt(safe.canvas.grid || 0, 10);
    const canvasWrapWidth = Math.max(380, ($('#layout_preview_canvas').innerWidth() || 420) - 20);
    const scale = Math.min(1, canvasWrapWidth / Math.max(width, 1));
    const esTabletPlano = window.matchMedia('(max-width: 991px)').matches;
    const esMovilPlano = window.matchMedia('(max-width: 767px)').matches;
    const viewportHeight = window.innerHeight || 800;
    let canvasHeight = 620;

    if (window.matchMedia('(max-width: 767px)').matches) {
        canvasHeight = Math.max(280, Math.round(viewportHeight * 0.50));
    } else if (window.matchMedia('(max-width: 991px)').matches) {
        canvasHeight = Math.max(320, Math.round(viewportHeight * 0.54));
    }

    $canvas.empty();
    $canvas.css({
        width: '100%',
        height: canvasHeight + 'px',
        overflow: 'auto'
    });

    const inner = $('<div></div>');
    inner.addClass('pos-layout-inner');
    inner.attr('data-scale', scale);
    inner.attr('data-canvas-width', width);
    inner.attr('data-canvas-height', height);
    inner.attr('data-grid', grid);
    inner.css({
        position: 'relative',
        width: Math.max(380, Math.round(width * scale)) + 'px',
        height: Math.max(500, Math.round(height * scale)) + 'px',
        background: '#fff',
        border: '1px solid #ececec',
        margin: '6px auto'
    });

    safe.mesas.forEach(function(mesa, index) {
        const x = Math.round((parseFloat(mesa.x) || 0) * scale);
        const y = Math.round((parseFloat(mesa.y) || 0) * scale);
        const w = Math.max(36, Math.round((parseFloat(mesa.width) || 108) * scale));
        const h = Math.max(26, Math.round((parseFloat(mesa.height) || 72) * scale));
        const nombre = mesa.nombre || ('Mesa ' + (mesa.mesa_id || ''));
        const mesaFontSize = Math.max(9, Math.round((POS_LAYOUT_MESA_FONT_SIZE_PX || 12) * Math.max(0.85, scale)));

        const $mesa = $('<div></div>');
        $mesa.addClass('pos-layout-mesa');
        $mesa.attr('data-layout-type', 'mesa');
        $mesa.attr('data-mesa-index', index);
        $mesa.css({
            position: 'absolute',
            left: x + 'px',
            top: y + 'px',
            width: w + 'px',
            height: h + 'px',
            border: '1px solid #4f46e5',
            borderRadius: (mesa.shape === 'circle') ? '999px' : '6px',
            background: '#eef2ff',
            color: '#3730a3',
            fontSize: mesaFontSize + 'px',
            fontWeight: '700',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            textAlign: 'center',
            lineHeight: '1.1',
            padding: '2px',
            cursor: 'move',
            userSelect: 'none',
            zIndex: 2,
            boxShadow: '0 1px 2px rgba(79, 70, 229, 0.18)'
        });
        $mesa.text(nombre);
        inner.append($mesa);
    });

    safe.lines.forEach(function(line, index) {
        var isH = line.orientacion === 'horizontal';
        var x = Math.round((parseFloat(line.x) || 0) * scale);
        var y = Math.round((parseFloat(line.y) || 0) * scale);
        var largo = Math.max(20, Math.round((parseFloat(line.largo) || 200) * scale));
        var grosor = Math.max(1, Math.round((parseFloat(line.grosor) || 3) * scale));
        var color = line.color || '#374151';
        var hitPadding = 8;
        var boxWidth = isH ? largo : Math.max(grosor, 8);
        var boxHeight = isH ? Math.max(grosor, 8) : largo;

        var $line = $('<div></div>');
        $line.addClass('pos-layout-line');
        $line.attr('data-layout-type', 'line');
        $line.attr('data-line-index', index);
        $line.css({
            position: 'absolute',
            left: x + 'px',
            top: y + 'px',
            width: boxWidth + 'px',
            height: boxHeight + 'px',
            background: 'transparent',
            borderRadius: '4px',
            cursor: 'move',
            userSelect: 'none',
            zIndex: 3,
            boxSizing: 'border-box',
            outline: '1px dashed rgba(148, 163, 184, 0.35)',
            outlineOffset: '4px'
        });

        var $stroke = $('<div></div>');
        $stroke.addClass('pos-layout-line-stroke');
        $stroke.css({
            position: 'absolute',
            left: isH ? '0' : '50%',
            top: isH ? '50%' : '0',
            width: isH ? boxWidth + 'px' : grosor + 'px',
            height: isH ? grosor + 'px' : boxHeight + 'px',
            marginLeft: isH ? '0' : (-Math.round(grosor / 2)) + 'px',
            marginTop: isH ? (-Math.round(grosor / 2)) + 'px' : '0',
            background: color,
            borderRadius: '999px',
            boxShadow: '0 0 0 1px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.12)',
            pointerEvents: 'none'
        });

        $line.append($stroke);
        inner.append($line);
    });

    safe.labels.forEach(function(label, index) {
        const x = Math.round((parseFloat(label.x) || 0) * scale);
        const y = Math.round((parseFloat(label.y) || 0) * scale);
        const maxLabelWidth = esMovilPlano
            ? Math.max(120, Math.round(canvasWrapWidth * 0.72))
            : (esTabletPlano ? Math.max(140, Math.round(canvasWrapWidth * 0.54)) : Math.round(width * scale));
        const w = Math.min(maxLabelWidth, Math.max(esMovilPlano ? 78 : 90, Math.round((parseFloat(label.width) || 160) * scale)));
        const h = Math.max(24, Math.round((parseFloat(label.height) || 34) * scale));
        const fontSize = Math.max(esMovilPlano ? 9 : 11, Math.round((parseFloat(label.fontSize) || 18) * scale));
        const estiloColor = obtenerEstiloLabelLayout(label.bgColor);
        const labelPadY = esMovilPlano ? 5 : 6;
        const labelPadLeft = esMovilPlano ? 8 : 10;
        const labelPadRight = esMovilPlano ? 22 : 26;

        const $label = $('<div></div>');
        $label.addClass('pos-layout-label');
        $label.attr('data-layout-type', 'label');
        $label.attr('data-label-index', index);
        $label.css({
            position: 'absolute',
            left: x + 'px',
            top: y + 'px',
            width: w + 'px',
            minHeight: h + 'px',
            maxWidth: maxLabelWidth + 'px',
            padding: labelPadY + 'px ' + labelPadRight + 'px ' + labelPadY + 'px ' + labelPadLeft + 'px',
            borderRadius: '8px',
            background: estiloColor.bg,
            border: '1px dashed ' + estiloColor.border,
            color: estiloColor.text,
            fontSize: fontSize + 'px',
            fontWeight: '700',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            textAlign: 'center',
            cursor: 'move',
            userSelect: 'none',
            zIndex: 4,
            boxShadow: '0 2px 6px rgba(0, 0, 0, 0.08)',
            lineHeight: '1.15'
        });

        const $labelText = $('<span></span>').text(label.text || 'Texto');
        $labelText.css({
            display: 'block',
            width: '100%',
            textAlign: 'center',
            whiteSpace: 'normal',
            wordBreak: 'break-word',
            overflowWrap: 'anywhere'
        });
        const $remove = $('<button type="button" class="pos-layout-label-remove" aria-label="Eliminar texto">×</button>');
        $remove.css({
            position: 'absolute',
            top: '2px',
            right: '4px',
            border: 'none',
            background: 'transparent',
            color: estiloColor.remove,
            fontSize: '16px',
            lineHeight: '1',
            fontWeight: '700',
            padding: '0 2px',
            cursor: 'pointer'
        });

        $label.append($labelText).append($remove);
        inner.append($label);
    });

    $canvas.append(inner);
}

inicializarEventosDragLayoutMesas();

function cargarMesas() {
    $.ajax({
        url: '/restaurant/comandas/obtener-mesas',
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#mesas-container').addClass('loading');
        },
        success: function(response) {
            if (response.success) {
                if (typeof response.minutos_expiracion_reserva !== 'undefined' && response.minutos_expiracion_reserva !== null) {
                    posReservaExpiracionMinutos = parseInt(response.minutos_expiracion_reserva, 10) || 15;
                    window.posReservaExpiracionMinutos = posReservaExpiracionMinutos;
                }
                renderizarMesas(response.mesas);
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudieron cargar las mesas', 'error');
        },
        complete: function() {
            $('#mesas-container').removeClass('loading');
        }
    });
}

function calcularMinutosRestantesReserva(mesa) {
    const minutosConfigurados = parseInt(posReservaExpiracionMinutos, 10) || 15;
    const reservadaAt = mesa && mesa.reservada_at_iso ? new Date(mesa.reservada_at_iso) : null;

    if (!reservadaAt || Number.isNaN(reservadaAt.getTime())) {
        return null;
    }

    const transcurridos = Math.max(0, Math.floor((Date.now() - reservadaAt.getTime()) / 60000));
    const restantes = minutosConfigurados - transcurridos;

    return Math.max(0, restantes);
}

function renderizarMesas(mesas) {
    const container = $('#mesas-container');
    container.empty();
    
    let totalComensales = 0;
    let totalLibres = 0;
    let totalReservadas = 0;
    let totalOcupadas = 0;
    let totalPendientesPago = 0;
    
    mesas.forEach(function(mesa) {
        const estadoTexto = mesa.estado || 'LIBRE';
        const estado = estadoTexto.toLowerCase();
        const estadoClass = estado.replace(/\s+/g, '-');
        const esLibre = estado === 'libre';
        const esReservada = estado === 'reservada';

        if (estado === 'libre') {
            totalLibres++;
        } else if (estado === 'reservada') {
            totalReservadas++;
        } else if (estado === 'ocupada') {
            totalOcupadas++;
        } else if (estado === 'pendiente de pago') {
            totalPendientesPago++;
        }
        
        if (!esLibre && mesa.comanda) {
            totalComensales += mesa.comanda.comensales || 0;
        }
        
        let consumoHtml = '';
        let comensalesControl = '';
        
        if (!esLibre && mesa.comanda) {
            const comensales = mesa.comanda.comensales || 0;
            const impresionSeparada = parseInt(window.posImpresionSeparada || 0, 10) === 1;
            
            comensalesControl = `
                <div class="comensales-control">
                    <button class="btn-menos" data-comanda-id="${mesa.comanda.id}" ${comensales <= 0 ? 'disabled' : ''}>
                        <i class="fa fa-minus"></i>
                    </button>
                    <span class="numero-comensales">${comensales}</span>
                    <button class="btn-mas" data-comanda-id="${mesa.comanda.id}" ${comensales >= mesa.capacidad ? 'disabled' : ''}>
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            `;
            
            consumoHtml = `
                ${comensalesControl}
                <div class="mesa-consumo">
                    <div class="mesa-consumo-total">$${mesa.comanda.total}</div>
                    <div class="mesa-consumo-items">${mesa.comanda.cantidad_items} items</div>
                </div>
                <div class="mesa-footer-comanda">
                    <span class="mesa-tiempo">
                        <i class="fa fa-clock"></i> ${mesa.comanda.tiempo}
                    </span>
                    <span class="mesa-mesero">
                        <i class="fa fa-user"></i> ${mesa.comanda.mesero}
                    </span>
                </div>
                <div class="mesa-acciones-comanda">
                    <button type="button" class="btn-imprimir-preventa" data-comanda-id="${mesa.comanda.id}"
                        title="Imprimir preventa">
                        <i class="fa fa-print"></i>
                    </button>
                    <button type="button" class="btn-cocina-mesa" data-comanda-id="${mesa.comanda.id}"
                        title="Imprimir ticket de ${impresionSeparada ? 'cocina' : 'preparacion'}">
                        <i class="fa ${impresionSeparada ? 'fa-cutlery' : 'fa-bell'}"></i>
                    </button>
                    ${impresionSeparada ? `
                    <button type="button" class="btn-barra-mesa" data-comanda-id="${mesa.comanda.id}"
                        title="Imprimir ticket de barra">
                        <i class="fa fa-glass"></i>
                    </button>
                    <button type="button" class="btn-ambos-mesa" data-comanda-id="${mesa.comanda.id}"
                        title="Imprimir tickets de cocina y barra">
                        <i class="fa fa-clone"></i>
                    </button>
                    ` : ''}
                </div>
            `;
        } else if (esReservada) {
            const reservadaPor = mesa.reservada_por || 'Otro garzón';
            const reservadaAt = mesa.reservada_at || '';
            const minutosRestantes = calcularMinutosRestantesReserva(mesa);
            const expiracionHtml = minutosRestantes !== null
                ? `<div class="mesa-reserva-detalle"><strong>Expira en:</strong> ${minutosRestantes} min</div>`
                : '';
            consumoHtml = `
                <div class="mesa-reserva-box">
                    <div class="mesa-reserva-titulo"><i class="fa fa-bookmark"></i> Mesa reservada</div>
                    <div class="mesa-reserva-detalle"><strong>Por:</strong> ${reservadaPor}</div>
                    ${reservadaAt ? `<div class="mesa-reserva-detalle"><strong>Desde:</strong> ${reservadaAt}</div>` : ''}
                    ${expiracionHtml}
                    <div class="mesa-acciones-reserva">
                        ${mesa.es_reserva_propia ? `<button type="button" class="btn-liberar-reserva" data-mesa-id="${mesa.id}"><i class="fa fa-unlock"></i> Liberar</button>` : ''}
                    </div>
                </div>
            `;
        } else {
            consumoHtml = `
                <div class="mesa-acciones-reserva">
                    <button type="button" class="btn-reservar-mesa" data-mesa-id="${mesa.id}">
                        <i class="fa fa-bookmark"></i> Reservar
                    </button>
                </div>
            `;
        }
        
        const numeroComandaHtml = mesa.comanda && mesa.comanda.numero_comanda
            ? `<p><i class="fa fa-receipt"></i> ${mesa.comanda.numero_comanda}</p>`
            : '';

        const mesaHtml = `
            <div class="mesa-card-comanda ${estadoClass}" data-mesa-id="${mesa.id}" data-estado="${estado}" data-reserva-propia="${mesa.es_reserva_propia ? '1' : '0'}" data-reservada-por="${mesa.reservada_por || ''}">
                <div class="mesa-header-comanda">
                    <h3><i class="fa fa-chair"></i> ${mesa.nombre}</h3>
                    <span class="mesa-status-badge ${estadoClass}">${estadoTexto}</span>
                </div>
                <div class="mesa-info">
                    <p class="capacidad-mesa"><i class="fa fa-users"></i> Capacidad: ${mesa.capacidad}</p>
                    ${numeroComandaHtml}
                </div>
                ${consumoHtml}
            </div>
        `;
        container.append(mesaHtml);
    });
    
    // Actualizar contador total de comensales
    $('#total_comensales').text(totalComensales);
    $('#total_mesas_libres').text(totalLibres);
    $('#total_mesas_reservadas').text(totalReservadas);
    $('#total_mesas_ocupadas').text(totalOcupadas);
    $('#total_mesas_pendientes_pago').text(totalPendientesPago);
    
    // Eventos para las mesas
    $('.mesa-card-comanda').on('click', function(e) {
        // Evitar abrir modal si se hizo clic en botones de comensales
        if ($(e.target).closest('.comensales-control, .btn-imprimir-preventa, .btn-cocina-mesa, .btn-barra-mesa, .btn-ambos-mesa, .btn-reservar-mesa, .btn-liberar-reserva').length > 0) {
            return;
        }
        
        const mesaId = $(this).data('mesa-id');
        const estado = $(this).data('estado');
        const esReservaPropia = String($(this).data('reserva-propia')) === '1';
        const reservadaPor = $(this).data('reservada-por');
        
        if (estado === 'ocupada' || estado === 'pendiente de pago') {
            verComanda(mesaId);
        } else if (estado === 'reservada') {
            if (esReservaPropia) {
                iniciarComanda(mesaId);
            } else {
                Swal.fire('Mesa reservada', 'Esta mesa está reservada por ' + (reservadaPor || 'otro garzón'), 'info');
            }
        } else {
            iniciarComanda(mesaId);
        }
    });

    $('.btn-reservar-mesa').on('click', function(e) {
        e.stopPropagation();
        const mesaId = $(this).data('mesa-id');

        $.ajax({
            url: '/restaurant/comandas/reservar-mesa/' + mesaId,
            type: 'POST',
            dataType: 'json',
            data: { _token: $('#token').val() },
            success: function(response) {
                if (!response.success) {
                    Swal.fire('Atención', response.message || 'No se pudo reservar la mesa', 'warning');
                    return;
                }
                cargarMesas();
            },
            error: function(xhr) {
                let mensaje = 'Error al reservar mesa';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                Swal.fire('Error', mensaje, 'error');
            }
        });
    });

    $('.btn-liberar-reserva').on('click', function(e) {
        e.stopPropagation();
        const mesaId = $(this).data('mesa-id');

        $.ajax({
            url: '/restaurant/comandas/reservar-mesa/' + mesaId,
            type: 'DELETE',
            dataType: 'json',
            data: { _token: $('#token').val() },
            success: function(response) {
                if (!response.success) {
                    Swal.fire('Atención', response.message || 'No se pudo liberar la reserva', 'warning');
                    return;
                }
                cargarMesas();
            },
            error: function(xhr) {
                let mensaje = 'Error al liberar reserva';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                Swal.fire('Error', mensaje, 'error');
            }
        });
    });
    
    // Eventos para botones de comensales
    $('.btn-mas').on('click', function(e) {
        e.stopPropagation();
        const comandaId = $(this).data('comanda-id');
        const spanComensales = $(this).siblings('.numero-comensales');
        const comensalesActual = parseInt(spanComensales.text());
        actualizarComensales(comandaId, comensalesActual + 1);
    });
    
    $('.btn-menos').on('click', function(e) {
        e.stopPropagation();
        const comandaId = $(this).data('comanda-id');
        const spanComensales = $(this).siblings('.numero-comensales');
        const comensalesActual = parseInt(spanComensales.text());
        if (comensalesActual > 0) {
            actualizarComensales(comandaId, comensalesActual - 1);
        }
    });

    $('.btn-imprimir-preventa').on('click', function(e) {
        e.stopPropagation();
        const comandaId = $(this).data('comanda-id');
        abrirTicketComanda(comandaId);
    });

    $('.btn-cocina-mesa').on('click', function(e) {
        e.stopPropagation();
        const comandaId = $(this).data('comanda-id');
        abrirTicketCocina(comandaId);
    });

    $('.btn-barra-mesa').on('click', function(e) {
        e.stopPropagation();
        const comandaId = $(this).data('comanda-id');
        abrirTicketBarra(comandaId);
    });

    $('.btn-ambos-mesa').on('click', function(e) {
        e.stopPropagation();
        const comandaId = $(this).data('comanda-id');
        imprimirTicketsPreparacion(comandaId);
    });
}

function verComanda(mesaId) {
    $.ajax({
        url: '/restaurant/comandas/ver/' + mesaId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Pasar datos de mesa para evitar segunda llamada AJAX redundante a obtener-mesas
                abrirModalPOSConComanda(mesaId, response.comanda, response.mesa || null);
            }
        },
        error: function(xhr) {
            let mensaje = 'Error al cargar la comanda';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }
    });
}


function iniciarComanda(mesaId) {
    Swal.fire({
        title: 'Nueva Comanda',
        text: '¿Desea iniciar una nueva comanda en esta mesa?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, iniciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            abrirModalPOS(mesaId);
        }
    });
}

function abrirModalPOSConComanda(mesaId, comanda, mesaData) {
    function _setup(mesa) {
        posMesaActual = mesa;
        posCapacidadOriginal = mesa.capacidad;
        posComandaActual = comanda.id;
        $('#pos_mesa_nombre').text(mesa.nombre);
        $('#pos_comensales_numero').text(comanda.comensales || mesa.capacidad);
        $('#pos_mesa_id').val(mesa.id);
        $('#pos_comanda_id').val(comanda.id);
        $('#pos_capacidad_original').val(mesa.capacidad);

        let fechaHoraApertura;
        if (comanda.fecha_apertura) {
            const partes = comanda.fecha_apertura.split(' ');
            const fecha = partes[0].split('/');
            const hora = partes[1];
            const dia = fecha[0].padStart(2, '0');
            const mes = fecha[1].padStart(2, '0');
            const anio = fecha[2];
            fechaHoraApertura = `${dia}-${mes}-${anio} ${hora}`;
        } else {
            const ahora = new Date();
            const dia = String(ahora.getDate()).padStart(2, '0');
            const mes = String(ahora.getMonth() + 1).padStart(2, '0');
            const anio = ahora.getFullYear();
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            fechaHoraApertura = `${dia}-${mes}-${anio} ${horas}:${minutos}`;
        }
        posFechaApertura = fechaHoraApertura;
        $('#pos_hora_inicio').text(fechaHoraApertura);

        cargarGarzones(comanda.garzon_id, comanda.garzon_nombre || '');

        posCarrito = [];
        if (comanda.detalles && comanda.detalles.length > 0) {
            comanda.detalles.forEach(function(detalle) {
                const precio = parseFloat(detalle.precio_unitario);
                const cantidad = parseInt(detalle.cantidad);
                const uuidItem = (detalle.origen === 'RECETA' && detalle.receta_uuid)
                    ? ('RECETA-' + detalle.receta_uuid)
                    : (detalle.uuid || null);
                posCarrito.push({
                    id: detalle.producto_id,
                    uuid: uuidItem,
                    origen: detalle.origen || 'PRODUCTO',
                    codigo: detalle.codigo || '',
                    descripcion: detalle.producto,
                    precio: precio,
                    cantidad: cantidad,
                    subtotal: precio * cantidad,
                    observaciones: detalle.observaciones || ''
                });
            });
        }

        const incluyePropina = comanda.incluye_propina == 1 || comanda.incluye_propina === true;
        $('#pos_incluye_propina').prop('checked', incluyePropina);
        if (incluyePropina) {
            $('#pos_propina_row').show();
        } else {
            $('#pos_propina_row').hide();
        }

        $('#pos_obs_comanda').val(comanda.observaciones || '');

        renderizarCarrito();
        $('#pos_buscar_producto').val('');
        $('#pos_products_grid').html(`
            <div class="pos-no-results">
                <i class="fa fa-search" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                <p>Escribe para buscar más productos</p>
            </div>
        `);
        $('#modalTomarPedido').modal('show');
        setTimeout(() => $('#pos_buscar_producto').focus(), 300);
    }

    // Si se pasó mesaData, úsala directamente (evita AJAX redundante a obtener-mesas)
    if (mesaData) {
        _setup(mesaData);
        return;
    }

    // Fallback: obtener datos de mesa vía AJAX
    $.ajax({
        url: '/restaurant/comandas/obtener-mesas',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const mesa = response.mesas.find(m => m.id == mesaId);
                if (mesa) {
                    _setup(mesa);
                }
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo cargar la información de la mesa', 'error');
        }
    });
}

// ==================== FUNCIONALIDAD POS ====================

function abrirModalPOS(mesaId) {
    // Obtener datos de la mesa
    $.ajax({
        url: '/restaurant/comandas/obtener-mesas',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const mesa = response.mesas.find(m => m.id == mesaId);
                if (mesa) {
                    posMesaActual = mesa;
                    posCapacidadOriginal = mesa.capacidad;
                    
                    // IMPORTANTE: Limpiar ID de comanda para crear una nueva
                    posComandaActual = null;
                    
                    // Configurar modal
                    $('#pos_mesa_nombre').text(mesa.nombre);
                    $('#pos_comensales_numero').text(mesa.capacidad);
                    $('#pos_mesa_id').val(mesa.id);
                    $('#pos_comanda_id').val('');
                    $('#pos_capacidad_original').val(mesa.capacidad);
                    
                    // Mostrar fecha y hora actual en formato dd-mm-yyyy hh:mm (esta será la fecha de apertura)
                    const ahora = new Date();
                    const dia = String(ahora.getDate()).padStart(2, '0');
                    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
                    const anio = ahora.getFullYear();
                    const horas = String(ahora.getHours()).padStart(2, '0');
                    const minutos = String(ahora.getMinutes()).padStart(2, '0');
                    const fechaHoraFormateada = `${dia}-${mes}-${anio} ${horas}:${minutos}`;
                    posFechaApertura = fechaHoraFormateada; // Guardar la fecha de apertura
                    $('#pos_hora_inicio').text(fechaHoraFormateada);
                    
                    // Limpiar carrito y propina
                    posCarrito = [];
                    $('#pos_incluye_propina').prop('checked', false);
                    $('#pos_propina_row').hide();
                    $('#pos_obs_comanda').val('');
                    renderizarCarrito();
                    
                    // Limpiar campo de búsqueda
                    $('#pos_buscar_producto').val('');
                    
                    // NO cargar productos automáticamente
                    // Mostrar mensaje de búsqueda
                    $('#pos_products_grid').html(`
                        <div class="pos-no-results">
                            <i class="fa fa-search" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                            <p>Escribe para buscar productos</p>
                        </div>
                    `);
                    
                    // Cargar garzones
                    cargarGarzones();
                    
                    // Mostrar modal y enfocar búsqueda
                    $('#modalTomarPedido').modal('show');
                    setTimeout(() => $('#pos_buscar_producto').focus(), 300);
                }
            }
        }
    });
}

function cargarProductosPOS(busqueda) {
    $.ajax({
        url: '/restaurant/comandas/obtener-productos',
        type: 'GET',
        data: {
            q: busqueda || ''
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                posProductos = response.productos;

                if (!posProductos || posProductos.length === 0) {
                    $('#pos_products_grid').html(`
                        <div class="pos-no-results">
                            <i class="fa fa-times-circle" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                            <p>No se encontraron productos</p>
                        </div>
                    `);
                    return;
                }

                renderizarProductos(posProductos);
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudieron cargar los productos', 'error');
        }
    });
}

function cargarGarzones(garzonIdSeleccionado = null, garzonNombreSeleccionado = '') {
    function _renderGarzones(garzones, esGarzon) {
        const select = $('#pos_garzon_id');
        select.empty();

        if (esGarzon) {
            if (garzones.length === 0) {
                select.append('<option value="">Sin garzón asignado</option>');
                select.prop('disabled', true);
                return;
            }

            const propio = garzones[0];
            const propioId = String(propio.id);
            const seleccionadoId = garzonIdSeleccionado !== null && garzonIdSeleccionado !== undefined
                ? String(garzonIdSeleccionado)
                : '';

            // Si el garzón abre una mesa de otro, mostrar arriba el garzón real asignado.
            if (seleccionadoId && seleccionadoId !== propioId) {
                const nombreReal = (garzonNombreSeleccionado || '').trim() || `Garzón asignado #${seleccionadoId}`;
                select.append(`<option value="${seleccionadoId}" selected>${nombreReal}</option>`);
                select.prop('disabled', true);
                return;
            }

            select.append(`<option value="${propioId}" selected>${propio.nombre_completo}</option>`);
            select.prop('disabled', true);
            return;
        }

        select.prop('disabled', false);
        select.append('<option value="">Seleccionar...</option>');
        garzones.forEach(function(garzon) {
            const selected = garzonIdSeleccionado && garzon.id == garzonIdSeleccionado ? 'selected' : '';
            select.append(`<option value="${garzon.id}" ${selected}>${garzon.nombre_completo}</option>`);
        });
    }

    if (_garzonesCache) {
        _renderGarzones(_garzonesCache.garzones || [], !!_garzonesCache.es_garzon);
        return;
    }

    $.ajax({
        url: '/restaurant/comandas/obtener-garzones',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                _garzonesCache = {
                    es_garzon: !!response.es_garzon,
                    garzones: response.garzones || []
                };
                _renderGarzones(_garzonesCache.garzones, _garzonesCache.es_garzon);
            }
        }
    });
}

function renderizarProductos(productos) {
    const grid = $('#pos_products_grid');
    grid.empty();
    
    if (productos.length === 0) {
        grid.html('<div style="grid-column: 1/-1; text-align:center; padding:40px; color:#999;">No hay productos disponibles</div>');
        return;
    }
    
    let html = '';
    productos.forEach(function(producto) {
        const esReceta = producto.origen === 'RECETA';
        const tipoProducto = String(producto.tipo || '').toUpperCase().trim();
        const mostrarStock = !esReceta && tipoProducto !== 'S';
        const stockBajo = mostrarStock && (producto.stock < 5);
        const stockHtml = !mostrarStock
            ? ''
            : `<div class="pos-product-stock ${stockBajo ? 'stock-bajo' : ''}">Stock: ${producto.stock}</div>`;
        html += `
            <div class="pos-product-card" data-uuid="${producto.uuid}">
                <div class="pos-product-name">${producto.descripcion}</div>
                <div class="pos-product-code">${producto.codigo}</div>
                <div class="pos-product-footer">
                    <div class="pos-product-price">$${formatearPrecio(producto.precio_venta)}</div>
                    ${stockHtml}
                </div>
            </div>
        `;
    });
    grid.html(html);
}

function agregarAlCarrito(productoUuid) {
    const producto = posProductos.find(p => p.uuid === productoUuid);
    
    if (!producto) return;

    const itemExistente = posCarrito.find(item => item.uuid === producto.uuid);

    if (itemExistente) {
        const nuevaCantidad = itemExistente.cantidad + 1;
        actualizarPrecioRangoComanda(itemExistente, nuevaCantidad)
            .then(function() {
                renderizarCarrito();
            })
            .catch(function() {
                Swal.fire('Error', 'No se pudo aplicar precio por rango', 'error');
            });
        return;
    }

    const nuevoItem = {
        id: producto.id,
        uuid: producto.uuid,
        origen: producto.origen || 'PRODUCTO',
        codigo: producto.codigo,
        descripcion: producto.descripcion,
        precio: parseFloat(producto.precio_venta),
        cantidad: 1,
        subtotal: parseFloat(producto.precio_venta),
        observaciones: ''
    };

    posCarrito.push(nuevoItem);
    actualizarPrecioRangoComanda(nuevoItem, 1)
        .then(function() {
            renderizarCarrito();
        })
        .catch(function() {
            Swal.fire('Error', 'No se pudo aplicar precio por rango', 'error');
        });
}

function renderizarCarrito() {
    const container = $('#pos_order_items');
    container.empty();
    
    if (posCarrito.length === 0) {
        container.html(`
            <div class="pos-order-empty">
                <i class="fa fa-shopping-cart"></i>
                <p>No hay productos agregados</p>
            </div>
        `);
        actualizarTotales();
        return;
    }
    
    let html = '';
    posCarrito.forEach(function(item, index) {
        const obsRaw = item.observaciones || '';
        const obsEsc = obsRaw.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        const hasObs = obsRaw.length > 0;
        const obsDisplay = hasObs ? `<div class="pos-item-obs-display"><i class="fa fa-comment"></i> ${obsEsc}</div>` : '';
        html += `
            <div class="pos-order-item" data-index="${index}">
                <div class="pos-item-header">
                    <div class="pos-item-name">${item.descripcion}</div>
                    <div class="pos-item-header-btns">
                        <button class="pos-item-nota-btn${hasObs ? ' has-obs' : ''}" data-index="${index}" title="${hasObs ? 'Editar nota' : 'Agregar nota al plato'}">
                            <i class="fa fa-${hasObs ? 'comment' : 'comment-o'}"></i>
                        </button>
                        <button class="pos-item-remove" data-index="${index}">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                ${obsDisplay}
                <div class="pos-item-obs-wrap" style="display:none;">
                    <input type="text" class="pos-item-obs-input" data-index="${index}"
                        placeholder="Ej: a tres cuartos, sin cebolla..."
                        value="${obsEsc}" maxlength="200">
                </div>
                <div class="pos-item-body">
                    <div class="pos-item-quantity">
                        <button class="pos-qty-btn pos-qty-minus" data-index="${index}">
                            <i class="fa fa-minus"></i>
                        </button>
                        <span class="pos-qty-number">${item.cantidad}</span>
                        <button class="pos-qty-btn pos-qty-plus" data-index="${index}">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                    <div class="pos-item-price">$${formatearPrecio(item.subtotal)}</div>
                </div>
            </div>
        `;
    });
    container.html(html);
    
    actualizarTotales();
}

function actualizarTotales() {
    const subtotal = posCarrito.reduce((sum, item) => sum + item.subtotal, 0);
    const incluyePropina = $('#pos_incluye_propina').is(':checked');
    const porcentaje = isNaN(posPropinaPorcentaje) ? 10 : posPropinaPorcentaje;
    const propina = incluyePropina ? Math.round(subtotal * (porcentaje / 100)) : 0;
    const total = subtotal + propina;
    
    $('#pos_subtotal').text(formatearPrecio(subtotal));
    $('#pos_propina').text(formatearPrecio(propina));
    $('#pos_total').text(formatearPrecio(total));
    
    // Mostrar/ocultar fila de propina
    if (incluyePropina) {
        $('#pos_propina_row').show();
    } else {
        $('#pos_propina_row').hide();
    }
    
    // Habilitar/deshabilitar botones
    const hayProductos = posCarrito.length > 0;
    $('#btn_guardar_pedido').prop('disabled', !hayProductos);
    $('#btn_imprimir_comanda').prop('disabled', !hayProductos);
    $('#btn_ticket_cocina').prop('disabled', !hayProductos);
    $('#btn_ticket_barra').prop('disabled', !hayProductos);
    $('#btn_ticket_ambos').prop('disabled', !hayProductos);
}

function formatearPrecio(valor) {
    return parseFloat(valor).toLocaleString('es-CL', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

// Búsqueda de productos
$('#pos_buscar_producto').off('input' + POS_EVENT_NS).on('input' + POS_EVENT_NS, function() {
    const valor = $(this).val();
    clearTimeout(posSearchTimer);

    posSearchTimer = setTimeout(function() {
        const busqueda = (valor || '').toLowerCase().trim();
    
        if (busqueda === '') {
            $('#pos_products_grid').html(`
                <div class="pos-no-results">
                    <i class="fa fa-search" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                    <p>Escribe para buscar productos</p>
                </div>
            `);
            return;
        }
    
        cargarProductosPOS(busqueda);
    }, 120);
});

// Botones de comensales en el modal POS
$('#pos_btn_menos_comensales').on('click', function() {
    const actual = parseInt($('#pos_comensales_numero').text());
    if (actual > 0) {
        $('#pos_comensales_numero').text(actual - 1);
    }
});

$('#pos_btn_mas_comensales').on('click', function() {
    const actual = parseInt($('#pos_comensales_numero').text());
    const maximo = parseInt($('#pos_capacidad_original').val());
    if (actual < maximo + 5) { // Permitir hasta 5 más de la capacidad
        $('#pos_comensales_numero').text(actual + 1);
    }
});

// Evento para checkbox de propina
$('#pos_incluye_propina').on('change', function() {
    actualizarTotales();
});

// Botón cerrar modal
$('#pos_btn_cerrar').on('click', function() {
    $('#modalTomarPedido').modal('hide');
});

// Botón solicitar cuenta
$('#btn_solicitar_cuenta').on('click', function() {
    if (!posMesaActual) {
        Swal.fire('Atención', 'Debe seleccionar una mesa primero', 'warning');
        return;
    }

    if (!posComandaActual) {
        Swal.fire('Atención', 'Debe guardar el pedido antes de solicitar la cuenta', 'warning');
        return;
    }

    Swal.fire({
        title: 'Solicitar cuenta',
        text: '¿Deseas solicitar la cuenta para esta mesa?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, solicitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/restaurant/comandas/solicitar-cuenta/' + posComandaActual,
                type: 'PUT',
                data: {
                    _token: $('#token').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Cuenta solicitada', response.message || 'La comanda quedó pendiente de pago', 'success')
                            .then(() => {
                                $('#modalTomarPedido').modal('hide');
                                cargarMesas();
                            });
                    } else {
                        Swal.fire('Atención', response.message || 'No se pudo solicitar la cuenta', 'warning');
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error al solicitar la cuenta';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }
    });
});

function abrirModalCambioMesa() {
    $.ajax({
        url: '/restaurant/comandas/obtener-mesas',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response.success || !Array.isArray(response.mesas)) {
                Swal.fire('Error', 'No se pudieron obtener las mesas', 'error');
                return;
            }

            const mesasOcupadas = response.mesas.filter(function(mesa) {
                const estadoMesa = String(mesa.estado || '').toUpperCase();
                return (estadoMesa === 'OCUPADA' || estadoMesa === 'PENDIENTE DE PAGO') && mesa.comanda && mesa.comanda.id;
            });
            const mesasLibres = response.mesas.filter(function(mesa) {
                const estadoMesa = String(mesa.estado || '').toUpperCase();
                return estadoMesa === 'LIBRE';
            });

            const $origen = $('#cambio_mesa_desde');
            const $destino = $('#cambio_mesa_hacia');

            $origen.empty().append('<option value="">Seleccionar mesa origen...</option>');
            $destino.empty().append('<option value="">Seleccionar mesa destino...</option>');

            mesasOcupadas.forEach(function(mesa) {
                const etiqueta = mesa.nombre + ' - ' + (mesa.comanda.numero_comanda || 'Comanda') + ' (' + mesa.estado + ')';
                $origen.append('<option value="' + mesa.id + '" data-comanda-id="' + mesa.comanda.id + '">' + etiqueta + '</option>');
            });

            mesasLibres.forEach(function(mesa) {
                const etiqueta = mesa.nombre + ' (capacidad ' + mesa.capacidad + ')';
                $destino.append('<option value="' + mesa.id + '">' + etiqueta + '</option>');
            });

            if (!mesasOcupadas.length) {
                Swal.fire('Sin mesas con comanda', 'No hay mesas ocupadas o pendientes para cambiar', 'info');
                return;
            }

            if (!mesasLibres.length) {
                Swal.fire('Sin mesas libres', 'No hay mesas libres disponibles como destino', 'info');
                return;
            }

            $('#modalCambioMesa').modal('show');
        },
        error: function() {
            Swal.fire('Error', 'No se pudo consultar la disponibilidad de mesas', 'error');
        }
    });
}

$('#btn_confirmar_cambio_mesa').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
    const mesaOrigenId = $('#cambio_mesa_desde').val();
    const mesaDestinoId = $('#cambio_mesa_hacia').val();
    const comandaId = $('#cambio_mesa_desde option:selected').data('comanda-id');

    if (!mesaOrigenId || !comandaId) {
        Swal.fire('Atención', 'Debe seleccionar una mesa origen válida', 'warning');
        return;
    }

    if (!mesaDestinoId) {
        Swal.fire('Atención', 'Debe seleccionar una mesa destino', 'warning');
        return;
    }

    if (String(mesaOrigenId) === String(mesaDestinoId)) {
        Swal.fire('Atención', 'La mesa origen y destino deben ser distintas', 'warning');
        return;
    }

    $.ajax({
        url: '/restaurant/comandas/cambiar-mesa/' + comandaId,
        type: 'PUT',
        dataType: 'json',
        data: {
            mesa_id_destino: mesaDestinoId,
            _token: $('#token').val()
        },
        success: function(resp) {
            if (!resp.success) {
                Swal.fire('Atención', resp.message || 'No se pudo cambiar de mesa', 'warning');
                return;
            }

            $('#modalCambioMesa').modal('hide');
            Swal.fire('Mesa actualizada', resp.message || 'La comanda se movió correctamente', 'success');
            cargarMesas();
        },
        error: function(xhr) {
            let mensaje = 'Error al cambiar la comanda de mesa';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }
    });
});

// Guardar pedido
$('#btn_guardar_pedido').on('click', function() {
    if (posCarrito.length === 0) {
        Swal.fire('Atención', 'Debe agregar al menos un producto', 'warning');
        return;
    }
    
    const garzonId = $('#pos_garzon_id').val();
    if (!garzonId) {
        Swal.fire('Atención', 'Debe seleccionar un garzón', 'warning');
        return;
    }
    
    const mesaId = $('#pos_mesa_id').val();
    const comensales = parseInt($('#pos_comensales_numero').text());
    const incluyePropina = $('#pos_incluye_propina').is(':checked');

    validarStockCarritoComanda().then(function(stockValido) {
        if (!stockValido) {
            return;
        }
    
        Swal.fire({
            title: 'Guardando pedido...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    
        // Verificar si es una comanda existente o nueva
        if (posComandaActual) {
        // ACTUALIZAR comanda existente
        $.ajax({
            url: '/restaurant/comandas/actualizar/' + posComandaActual,
            type: 'PUT',
            data: {
                garzon_id: garzonId,
                comensales: comensales,
                incluye_propina: incluyePropina ? 1 : 0,
                observaciones: $('#pos_obs_comanda').val().trim(),
                _token: $('#token').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    guardarProductosComanda(true); // true = es actualización
                } else {
                    Swal.fire('Error', response.message || 'No se pudo actualizar la comanda', 'error');
                }
            },
            error: function(xhr) {
                let mensaje = 'Error al actualizar la comanda';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                Swal.fire('Error', mensaje, 'error');
            }
        });
        } else {
        // CREAR nueva comanda
        $.ajax({
            url: '/restaurant/comandas/crear',
            type: 'POST',
            data: {
                mesa_id: mesaId,
                garzon_id: garzonId,
                comensales: comensales,
                incluye_propina: incluyePropina ? 1 : 0,
                observaciones: $('#pos_obs_comanda').val().trim(),
                _token: $('#token').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    posComandaActual = response.comanda.id;
                    guardarProductosComanda(false); // false = es creación
                } else {
                    Swal.fire('Error', response.message || 'No se pudo crear la comanda', 'error');
                }
            },
            error: function(xhr) {
                let mensaje = 'Error al crear la comanda';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                Swal.fire('Error', mensaje, 'error');
            }
        });
        }
    });
});

function verificarStockComanda(uuid, cantidad) {
    const normalizedQty = (parseFloat(cantidad) || 0).toFixed(4);
    const key = `${uuid}|${normalizedQty}`;
    const now = Date.now();

    const cached = posStockCache.get(key);
    if (cached && (now - cached.ts) <= POS_STOCK_CACHE_TTL_MS) {
        return $.Deferred().resolve(cached.resp).promise();
    }

    if (posStockInFlight.has(key)) {
        return posStockInFlight.get(key);
    }

    const isReceta = String(uuid).indexOf('RECETA-') === 0;

    const jqxhr = $.ajax({
        url: isReceta ? '/restaurant/comandas/verificar-stock-receta' : '/ventas/verificar-stock',
        method: 'POST',
        dataType: 'json',
        data: {
            uuid: uuid,
            cantidad: cantidad,
            _token: $('#token').val()
        }
    });

    posStockInFlight.set(key, jqxhr);
    jqxhr.done(function(resp) {
        posStockCache.set(key, { resp: resp, ts: Date.now() });
    }).always(function() {
        posStockInFlight.delete(key);
    });

    return jqxhr;
}

function obtenerPrecioPorCantidadComanda(uuid, cantidad, precioBase) {
    if (String(uuid).indexOf('RECETA-') === 0) {
        return $.Deferred().resolve({
            status: 'OK',
            precio_unitario: precioBase
        }).promise();
    }

    const normalizedQty = (parseFloat(cantidad) || 0).toFixed(4);
    const normalizedBase = (parseFloat(precioBase) || 0).toFixed(4);
    const key = `${uuid}|${normalizedQty}|${normalizedBase}`;
    const now = Date.now();

    const cached = posPrecioCache.get(key);
    if (cached && (now - cached.ts) <= POS_PRECIO_CACHE_TTL_MS) {
        return $.Deferred().resolve(cached.resp).promise();
    }

    if (posPrecioInFlight.has(key)) {
        return posPrecioInFlight.get(key);
    }

    const jqxhr = $.ajax({
        url: '/ventas/precio-por-cantidad',
        method: 'POST',
        dataType: 'json',
        data: {
            uuid: uuid,
            cantidad: cantidad,
            precio_base: precioBase,
            _token: $('#token').val()
        }
    });

    posPrecioInFlight.set(key, jqxhr);
    jqxhr.done(function(resp) {
        posPrecioCache.set(key, { resp: resp, ts: Date.now() });
    }).always(function() {
        posPrecioInFlight.delete(key);
    });

    return jqxhr;
}

$(document)
    .off('click' + POS_EVENT_NS, '.pos-product-card')
    .on('click' + POS_EVENT_NS, '.pos-product-card', function() {
        const uuid = $(this).data('uuid');
        agregarAlCarrito(uuid);
    })
    .off('click' + POS_EVENT_NS, '.pos-qty-plus')
    .on('click' + POS_EVENT_NS, '.pos-qty-plus', function() {
        const index = $(this).data('index');
        const item = posCarrito[index];
        if (!item) return;

        const cantidadObjetivo = item.cantidad + 1;

        if (!item.uuid) {
            item.cantidad++;
            item.subtotal = item.cantidad * item.precio;
            renderizarCarrito();
            return;
        }

        actualizarPrecioRangoComanda(item, cantidadObjetivo)
            .then(function() {
                renderizarCarrito();
            })
            .catch(function() {
                Swal.fire('Error', 'No se pudo aplicar precio por rango', 'error');
            });
    })
    .off('click' + POS_EVENT_NS, '.pos-qty-minus')
    .on('click' + POS_EVENT_NS, '.pos-qty-minus', function() {
        const index = $(this).data('index');
        const item = posCarrito[index];
        if (!item) return;

        if (item.cantidad > 1) {
            const nuevaCantidad = item.cantidad - 1;
            actualizarPrecioRangoComanda(item, nuevaCantidad)
                .then(function() {
                    renderizarCarrito();
                })
                .catch(function() {
                    Swal.fire('Error', 'No se pudo aplicar precio por rango', 'error');
                });
        }
    })
    .off('click' + POS_EVENT_NS, '.pos-item-remove')
    .on('click' + POS_EVENT_NS, '.pos-item-remove', function() {
        const index = $(this).data('index');
        if (typeof index === 'undefined') return;
        const comandaId = $('#pos_comanda_id').val();
        if (comandaId && comandaId !== '') {
            const item = posCarrito[index];
            const cantidadMaxima = item.cantidad || 1;
            $('#anular_producto_index').val(index);
            $('#anular_cantidad').val(cantidadMaxima);
            $('#anular_cantidad').attr('max', cantidadMaxima);
            $('#anular_cantidad_max').text(cantidadMaxima);
            $('#anular_password').val('');
            $('#anular_motivo').val('');
            $('#anular_error').hide();
            $('#modalAnularProductoComanda').modal('show');
        } else {
            posCarrito.splice(index, 1);
            renderizarCarrito();
        }
    })
    .off('click' + POS_EVENT_NS, '.pos-item-nota-btn')
    .on('click' + POS_EVENT_NS, '.pos-item-nota-btn', function(e) {
        e.stopPropagation();
        const index = $(this).data('index');
        const $item = $(this).closest('.pos-order-item');
        const $wrap = $item.find('.pos-item-obs-wrap');
        const $input = $wrap.find('.pos-item-obs-input');
        if ($wrap.is(':visible')) {
            $wrap.hide();
        } else {
            $wrap.show();
            $input.focus();
        }
    })
    .off('keydown' + POS_EVENT_NS, '.pos-item-obs-input')
    .on('keydown' + POS_EVENT_NS, '.pos-item-obs-input', function(e) {
        if (e.key === 'Enter') {
            $(this).blur();
        }
    })
    .off('blur' + POS_EVENT_NS, '.pos-item-obs-input')
    .on('blur' + POS_EVENT_NS, '.pos-item-obs-input', function() {
        const index = parseInt($(this).data('index'));
        if (isNaN(index) || !posCarrito[index]) return;
        posCarrito[index].observaciones = $(this).val().trim();
        renderizarCarrito();
    });

// Confirmar anulación de producto en comanda
$(document).off('click' + POS_EVENT_NS, '#btnConfirmarAnularProducto').on('click' + POS_EVENT_NS, '#btnConfirmarAnularProducto', function() {
    const index = parseInt($('#anular_producto_index').val());
    const cantidad = parseInt($('#anular_cantidad').val());
    const cantidadMaxima = parseInt($('#anular_cantidad').attr('max'));
    const password = $('#anular_password').val();
    const motivo = $('#anular_motivo').val().trim();
    const comandaId = $('#pos_comanda_id').val();
    if (isNaN(index) || !posCarrito[index]) return;
    const productoId = posCarrito[index].id;
    if (!password || !motivo || isNaN(cantidad) || cantidad <= 0) {
        $('#anular_error').text('Todos los campos son obligatorios.').show();
        return;
    }
    if (cantidad > cantidadMaxima) {
        $('#anular_error').text(`No puedes eliminar más de ${cantidadMaxima} unidades.`).show();
        return;
    }
    $('#anular_error').hide();
    $('#btnConfirmarAnularProducto').prop('disabled', true);
    $.ajax({
        url: '/restaurant/comandas/anular-producto',
        method: 'POST',
        data: {
            _token: $('#token').val(),
            comanda_id: comandaId,
            producto_id: productoId,
            cantidad: cantidad,
            password: password,
            motivo: motivo
        },
        success: function(resp) {
            if (resp.success) {
                // Restar la cantidad del carrito
                posCarrito[index].cantidad -= cantidad;
                if (posCarrito[index].cantidad <= 0) {
                    posCarrito.splice(index, 1);
                }
                renderizarCarrito();
                $('#modalAnularProductoComanda').modal('hide');
                
                // Si el carrito quedó vacío, la comanda se cerró automáticamente
                if (posCarrito.length === 0) {
                    setTimeout(function() {
                        $('#modalTomarPedido').modal('hide');
                        cargarMesas();
                        Swal.fire('Éxito', 'Producto anulado. La comanda se cerró automáticamente y la mesa está disponible.', 'success');
                    }, 500);
                } else {
                    Swal.fire('Éxito', 'Producto anulado correctamente', 'success');
                }
            } else {
                $('#anular_error').text(resp.message || 'Error al anular producto.').show();
            }
        },
        error: function(xhr) {
            let msg = 'Error al anular producto.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).join(' ');
            }
            $('#anular_error').text(msg).show();
        },
        complete: function() {
            $('#btnConfirmarAnularProducto').prop('disabled', false);
        }
    });
});

function actualizarPrecioRangoComanda(item, cantidad) {
    const cantidadNumerica = parseFloat(cantidad);
    const precioBase = parseFloat(item.precio || 0);

    if (!item.uuid || !cantidadNumerica || cantidadNumerica <= 0) {
        item.cantidad = cantidadNumerica > 0 ? cantidadNumerica : 1;
        item.subtotal = item.cantidad * precioBase;
        return Promise.resolve();
    }

    return obtenerPrecioPorCantidadComanda(item.uuid, cantidadNumerica, precioBase)
        .then(function(resp) {
            const precioRango = (resp && resp.status === 'OK' && typeof resp.precio_unitario !== 'undefined')
                ? parseFloat(resp.precio_unitario)
                : precioBase;

            item.cantidad = cantidadNumerica;
            item.precio = precioRango;
            item.subtotal = item.cantidad * item.precio;
        });
}

function mostrarErrorStockComanda(resp, descripcionFallback) {
    if (!resp) {
        Swal.fire('Atención', 'No se pudo validar stock del producto', 'warning');
        return;
    }

    if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
        const nombre = (resp.product && (resp.product.descripcion || resp.product.codigo)) || descripcionFallback || 'Producto';
        const disponible = (resp.product && typeof resp.product.stock !== 'undefined') ? resp.product.stock : 0;
        Swal.fire('Stock insuficiente', `${nombre}. Disponible: ${disponible}`, 'warning');
        return;
    }

    if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
        const faltantes = (resp.items || [])
            .filter(it => !it.sufficient)
            .map(it => `${it.descripcion}: stock ${it.stock}, requerido ${it.required_total}`)
            .join('\n');

        Swal.fire('Stock insuficiente', faltantes || (resp.message || 'La promoción no tiene stock suficiente'), 'warning');
        return;
    }

    if (resp.code === 'RECIPE_INSUFFICIENT_STOCK') {
        const faltantes = (resp.items || [])
            .map(it => `• ${it.descripcion}`)
            .join('\n');

        Swal.fire('Insumos faltantes', faltantes || 'Faltan insumos para preparar la receta', 'warning');
        return;
    }

    if (resp.message) {
        Swal.fire('Atención', resp.message, 'warning');
        return;
    }

    Swal.fire('Atención', 'No hay stock suficiente para esta operación', 'warning');
}

function validarStockCarritoComanda() {
    const itemsValidables = posCarrito.filter(item => !!item.uuid);

    if (itemsValidables.length === 0) {
        return Promise.resolve(true);
    }

    return $.ajax({
        url: '/ventas/verificar-stock-batch',
        method: 'POST',
        dataType: 'json',
        data: {
            _token: $('#token').val(),
            items: itemsValidables.map(function(item) {
                return { uuid: item.uuid, cantidad: item.cantidad };
            })
        }
    }).then(function(batchResp) {
        const resultados = (batchResp && batchResp.results) ? batchResp.results : {};
        const fallo = itemsValidables.find(function(item) {
            const resp = resultados[item.uuid];
            return !resp || resp.status !== 'OK';
        });

        if (!fallo) return true;

        const respFallo = resultados[fallo.uuid] || null;
        mostrarErrorStockComanda(respFallo, fallo.descripcion || 'Producto');
        return false;
    }).catch(function() {
        Swal.fire('Error', 'No se pudo validar stock antes de guardar', 'error');
        return false;
    });
}

function guardarProductosComanda(esActualizacion = false) {
    if (esActualizacion) {
        // Para comandas existentes: sincronizar productos (elimina y recrea)
        const productos = posCarrito.map(item => ({
            producto_id: item.id,
            producto_uuid: item.uuid,
            origen: item.origen || 'PRODUCTO',
            codigo: item.codigo,
            cantidad: item.cantidad,
            observaciones: item.observaciones || ''
        }));

        $.ajax({
            url: '/restaurant/comandas/sincronizar-productos/' + posComandaActual,
            type: 'POST',
            data: {
                productos: productos,
                _token: $('#token').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Pedido actualizado correctamente',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalTomarPedido').modal('hide');
                        cargarMesas();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Error al sincronizar productos', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al sincronizar productos', 'error');
            }
        });
    } else {
        // Para comandas nuevas: agregar productos uno por uno
        let productosGuardados = 0;
        const totalProductos = posCarrito.length;
        
        posCarrito.forEach(function(item) {
            $.ajax({
                url: '/restaurant/comandas/agregar-producto',
                type: 'POST',
                data: {
                    comanda_id: posComandaActual,
                    producto_id: item.id,
                    producto_uuid: item.uuid,
                    origen: item.origen || 'PRODUCTO',
                    codigo: item.codigo,
                    cantidad: item.cantidad,
                    observaciones: item.observaciones || '',
                    _token: $('#token').val()
                },
                dataType: 'json',
                success: function(response) {
                    productosGuardados++;
                    
                    if (productosGuardados === totalProductos) {
                        Swal.fire({
                            title: 'Éxito',
                            text: 'Pedido guardado correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#modalTomarPedido').modal('hide');
                            cargarMesas();
                        });
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Error al guardar algunos productos', 'error');
                }
            });
        });
    }
}

// Imprimir comanda
$('#btn_imprimir_comanda').on('click', function() {
    if (!posComandaActual) {
        Swal.fire('Atención', 'Debe guardar el pedido primero', 'warning');
        return;
    }

    abrirTicketComanda(posComandaActual);
});

// Ticket cocina
$('#btn_ticket_cocina').on('click', function() {
    if (!posComandaActual) {
        Swal.fire('Atención', 'Debe guardar el pedido primero', 'warning');
        return;
    }

    abrirTicketCocina(posComandaActual);
});

// Ticket barra (solo visible cuando IMPRESION_SEPARADA = 1)
$('#btn_ticket_barra').on('click', function() {
    if (!posComandaActual) {
        Swal.fire('Atención', 'Debe guardar el pedido primero', 'warning');
        return;
    }

    abrirTicketBarra(posComandaActual);
});

// Imprimir ambos tickets (solo cuando IMPRESION_SEPARADA = 1)
$('#btn_ticket_ambos').on('click', function() {
    if (!posComandaActual) {
        Swal.fire('Atención', 'Debe guardar el pedido primero', 'warning');
        return;
    }

    imprimirTicketsPreparacion(posComandaActual);
});

function imprimirTicketsPreparacion(comandaId) {
    if (!comandaId) {
        Swal.fire('Atención', 'No se encontró la comanda para imprimir', 'warning');
        return;
    }

    if (parseInt(window.posImpresionSeparada || 0, 10) !== 1) {
        abrirTicketCocina(comandaId);
        return;
    }

    $('#modalTicketAmbos').off('hidden.bs.modal.ticketAmbos').on('hidden.bs.modal.ticketAmbos', function() {
        $('#ticketFrameAmbosCocina').attr('src', 'about:blank');
        $('#ticketFrameAmbosBarra').attr('src', 'about:blank');
    });

    $('#ticketFrameAmbosCocina').attr('src', '/restaurant/comandas/ticket-cocina/' + comandaId);
    $('#ticketFrameAmbosBarra').attr('src', '/restaurant/comandas/ticket-barra/' + comandaId);
    $('#modalTicketAmbos').modal('show');
}

function abrirTicketCocina(comandaId) {
    if (!comandaId) {
        Swal.fire('Atención', 'No se encontró la comanda para imprimir', 'warning');
        return;
    }

    $('#modalTicketCocina').off('hidden.bs.modal.ticketCocina').on('hidden.bs.modal.ticketCocina', function() {
        $('#ticketFrameCocina').attr('src', 'about:blank');
    });

    const tituloModal = parseInt(window.posImpresionSeparada || 0, 10) === 1
        ? '<i class="fa fa-cutlery"></i> Ticket Cocina'
        : '<i class="fa fa-bell"></i> Ticket Preparacion';
    $('#tituloTicketCocina').html(tituloModal);

    $('#ticketFrameCocina').attr('src', '/restaurant/comandas/ticket-cocina/' + comandaId);
    $('#modalTicketCocina').modal('show');
}

function abrirTicketBarra(comandaId) {
    if (!comandaId) {
        Swal.fire('Atención', 'No se encontró la comanda para imprimir', 'warning');
        return;
    }

    $('#modalTicketBarra').off('hidden.bs.modal.ticketBarra').on('hidden.bs.modal.ticketBarra', function() {
        $('#ticketFrameBarra').attr('src', 'about:blank');
    });

    $('#ticketFrameBarra').attr('src', '/restaurant/comandas/ticket-barra/' + comandaId);
    $('#modalTicketBarra').modal('show');
}

function abrirTicketComanda(comandaId) {
    if (!comandaId) {
        Swal.fire('Atención', 'No se encontró la comanda para imprimir', 'warning');
        return;
    }

    $('#modalTicketComanda').off('hidden.bs.modal.ticketComanda').on('hidden.bs.modal.ticketComanda', function() {
        $('#ticketFrameComanda').attr('src', 'about:blank');
    });

    $('#ticketFrameComanda').attr('src', '/restaurant/comandas/imprimir/' + comandaId);
    $('#modalTicketComanda').modal('show');
}

function actualizarComensales(comandaId, nuevoValor) {
    $.ajax({
        url: '/restaurant/comandas/actualizar-comensales/' + comandaId,
        type: 'PUT',
        data: {
            comensales: nuevoValor,
            _token: $('#token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                cargarMesas(); // Recargar para actualizar el contador total
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo actualizar el número de comensales', 'error');
        }
    });
}



