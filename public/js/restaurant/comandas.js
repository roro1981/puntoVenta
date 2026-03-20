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
    }, 30000);
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
        cargarLayoutMesasJson();
    });

    $('#btn_cargar_layout_json').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        cargarLayoutMesasJson();
    });

    $('#btn_guardar_layout_json').off('click' + POS_EVENT_NS).on('click' + POS_EVENT_NS, function() {
        guardarLayoutMesasJson();
    });
});

function normalizarLayoutMesas(layout) {
    const safe = (layout && typeof layout === 'object') ? layout : {};
    if (!safe.canvas || typeof safe.canvas !== 'object') {
        safe.canvas = { width: 1200, height: 700, grid: 20 };
    }
    if (!Array.isArray(safe.mesas)) {
        safe.mesas = [];
    }
    return safe;
}

function actualizarTextoLayoutMesas() {
    return;
}

function inicializarEventosDragLayoutMesas() {
    $('#layout_preview_canvas')
        .off('mousedown' + POS_EVENT_NS, '.pos-layout-mesa')
        .on('mousedown' + POS_EVENT_NS, '.pos-layout-mesa', function(e) {
            if (e.which !== 1 || !posLayoutMesasActual) {
                return;
            }

            const indiceMesa = parseInt($(this).attr('data-mesa-index'), 10);
            if (Number.isNaN(indiceMesa) || !posLayoutMesasActual.mesas[indiceMesa]) {
                return;
            }

            const $inner = $('#layout_preview_canvas .pos-layout-inner');
            const scale = parseFloat($inner.attr('data-scale')) || 1;
            const canvasWidth = parseFloat($inner.attr('data-canvas-width')) || 1200;
            const canvasHeight = parseFloat($inner.attr('data-canvas-height')) || 700;
            const grid = parseFloat($inner.attr('data-grid')) || 0;

            posLayoutDragState = {
                indiceMesa,
                startMouseX: e.pageX,
                startMouseY: e.pageY,
                startLeftPx: parseFloat($(this).css('left')) || 0,
                startTopPx: parseFloat($(this).css('top')) || 0,
                scale,
                canvasWidth,
                canvasHeight,
                grid,
                $element: $(this)
            };

            $('body').css('user-select', 'none');
            e.preventDefault();
        });

    $(document)
        .off('mousemove' + POS_EVENT_NS)
        .on('mousemove' + POS_EVENT_NS, function(e) {
            if (!posLayoutDragState || !posLayoutMesasActual) {
                return;
            }

            const mesa = posLayoutMesasActual.mesas[posLayoutDragState.indiceMesa];
            if (!mesa) {
                return;
            }

            const mesaWidth = parseFloat(mesa.width) || 130;
            const mesaHeight = parseFloat(mesa.height) || 90;
            const maxX = Math.max(0, posLayoutDragState.canvasWidth - mesaWidth);
            const maxY = Math.max(0, posLayoutDragState.canvasHeight - mesaHeight);

            const deltaXPx = e.pageX - posLayoutDragState.startMouseX;
            const deltaYPx = e.pageY - posLayoutDragState.startMouseY;

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

            mesa.x = Math.round(nextX);
            mesa.y = Math.round(nextY);

            posLayoutDragState.$element.css({
                left: Math.round(mesa.x * posLayoutDragState.scale) + 'px',
                top: Math.round(mesa.y * posLayoutDragState.scale) + 'px'
            });
        })
        .off('mouseup' + POS_EVENT_NS)
        .on('mouseup' + POS_EVENT_NS, function() {
            if (!posLayoutDragState) {
                return;
            }

            posLayoutDragState = null;
            $('body').css('user-select', '');
            actualizarTextoLayoutMesas();
            renderizarPreviewLayoutMesas(posLayoutMesasActual);
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

    $canvas.empty();
    $canvas.css({
        width: '100%',
        height: '620px',
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
        const w = Math.max(40, Math.round((parseFloat(mesa.width) || 130) * scale));
        const h = Math.max(30, Math.round((parseFloat(mesa.height) || 90) * scale));
        const nombre = mesa.nombre || ('Mesa ' + (mesa.mesa_id || ''));

        const $mesa = $('<div></div>');
        $mesa.addClass('pos-layout-mesa');
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
            fontSize: '11px',
            fontWeight: '600',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            textAlign: 'center',
            padding: '2px',
            cursor: 'move',
            userSelect: 'none'
        });
        $mesa.text(nombre);
        inner.append($mesa);
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
                renderizarMesas(response.mesas);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar mesas:', xhr);
            Swal.fire('Error', 'No se pudieron cargar las mesas', 'error');
        },
        complete: function() {
            $('#mesas-container').removeClass('loading');
        }
    });
}

function renderizarMesas(mesas) {
    const container = $('#mesas-container');
    container.empty();
    
    let totalComensales = 0;
    let totalLibres = 0;
    let totalOcupadas = 0;
    let totalPendientesPago = 0;
    
    mesas.forEach(function(mesa) {
        const estadoTexto = mesa.estado || 'LIBRE';
        const estado = estadoTexto.toLowerCase();
        const estadoClass = estado.replace(/\s+/g, '-');
        const esLibre = estado === 'libre';

        if (estado === 'libre') {
            totalLibres++;
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
                    <button type="button" class="btn-imprimir-preventa" data-comanda-id="${mesa.comanda.id}">
                        <i class="fa fa-print"></i> Imprimir preventa
                    </button>
                </div>
            `;
        }
        
        const mesaHtml = `
            <div class="mesa-card-comanda ${estadoClass}" data-mesa-id="${mesa.id}" data-estado="${estado}">
                <div class="mesa-header-comanda">
                    <h3><i class="fa fa-chair"></i> ${mesa.nombre}</h3>
                    <span class="mesa-status-badge ${estadoClass}">${estadoTexto}</span>
                </div>
                <div class="mesa-info">
                    <p class="capacidad-mesa"><i class="fa fa-users"></i> Capacidad: ${mesa.capacidad}</p>
                    ${!esLibre ? `<p><i class="fa fa-receipt"></i> ${mesa.comanda.numero_comanda}</p>` : ''}
                </div>
                ${consumoHtml}
            </div>
        `;
        container.append(mesaHtml);
    });
    
    // Actualizar contador total de comensales
    $('#total_comensales').text(totalComensales);
    $('#total_mesas_libres').text(totalLibres);
    $('#total_mesas_ocupadas').text(totalOcupadas);
    $('#total_mesas_pendientes_pago').text(totalPendientesPago);
    
    // Eventos para las mesas
    $('.mesa-card-comanda').on('click', function(e) {
        // Evitar abrir modal si se hizo clic en botones de comensales
        if ($(e.target).closest('.comensales-control, .btn-imprimir-preventa').length > 0) {
            return;
        }
        
        const mesaId = $(this).data('mesa-id');
        const estado = $(this).data('estado');
        
        if (estado !== 'libre') {
            verComanda(mesaId);
        } else {
            iniciarComanda(mesaId);
        }
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
}

function verComanda(mesaId) {
    $.ajax({
        url: '/restaurant/comandas/ver/' + mesaId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Usar el mismo modal POS para ver/editar la comanda
                abrirModalPOSConComanda(mesaId, response.comanda);
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

function abrirModalPOSConComanda(mesaId, comanda) {
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
                    
                    // Configurar modal
                    $('#pos_mesa_nombre').text(mesa.nombre);
                    $('#pos_comensales_numero').text(comanda.comensales || mesa.capacidad);
                    $('#pos_mesa_id').val(mesa.id);
                    $('#pos_capacidad_original').val(mesa.capacidad);
                    
                    // Mostrar hora de apertura de la comanda
                    $('#pos_hora_inicio').text(comanda.hora_apertura || new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' }));
                    
                    // Cargar garzon seleccionado
                    cargarGarzones(comanda.garzon_id);
                    
                    // Cargar productos del carrito desde los detalles de la comanda
                    posCarrito = [];
                    comanda.detalles.forEach(function(detalle) {
                        const uuidItem = (detalle.origen === 'RECETA' && detalle.receta_uuid)
                            ? ('RECETA-' + detalle.receta_uuid)
                            : (detalle.uuid || null);

                        posCarrito.push({
                            id: detalle.producto_id,
                            uuid: uuidItem,
                            origen: detalle.origen || 'PRODUCTO',
                            codigo: detalle.codigo || '',
                            descripcion: detalle.producto,
                            precio: parseFloat(detalle.precio_unitario),
                            cantidad: parseInt(detalle.cantidad),
                            observaciones: detalle.observaciones || ''
                        });
                    });
                    
                    // Configurar propina
                    const incluyePropina = comanda.incluye_propina == 1 || comanda.incluye_propina === true;
                    $('#pos_incluye_propina').prop('checked', incluyePropina);
                    if (incluyePropina) {
                        $('#pos_propina_row').show();
                    } else {
                        $('#pos_propina_row').hide();
                    }
                    
                    // Renderizar carrito con productos existentes
                    renderizarCarrito();
                    
                    // Limpiar campo de búsqueda
                    $('#pos_buscar_producto').val('');
                    
                    // Mostrar mensaje de búsqueda en productos
                    $('#pos_products_grid').html(`
                        <div class="pos-no-results">
                            <i class="fa fa-search" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                            <p>Escribe para buscar más productos</p>
                        </div>
                    `);
                    
                    // Mostrar modal y enfocar búsqueda
                    $('#modalTomarPedido').modal('show');
                    setTimeout(() => $('#pos_buscar_producto').focus(), 300);
                }
            }
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

function abrirModalPOSConComanda(mesaId, comanda) {
    console.log('Comanda recibida:', comanda);
    
    // Obtener datos actualizados de la mesa
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
                    
                    // IMPORTANTE: Guardar el ID de la comanda existente
                    posComandaActual = comanda.id;
                    
                    // Configurar modal
                    $('#pos_mesa_nombre').text(mesa.nombre);
                    $('#pos_comensales_numero').text(comanda.comensales || mesa.capacidad);
                    $('#pos_mesa_id').val(mesa.id);
                    $('#pos_capacidad_original').val(mesa.capacidad);
                    
                    // Mostrar fecha y hora de apertura en formato dd-mm-yyyy hh:mm
                    let fechaHoraApertura;
                    if (comanda.fecha_apertura) {
                        // fecha_apertura viene en formato 'd/m/Y H:i' del servidor
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
                    posFechaApertura = fechaHoraApertura; // Guardar la fecha de apertura (no cambiará)
                    console.log('Fecha/Hora apertura:', fechaHoraApertura);
                    $('#pos_hora_inicio').text(fechaHoraApertura);
                    
                    // Cargar garzones con el garzon seleccionado
                    console.log('Garzon ID:', comanda.garzon_id);
                    cargarGarzones(comanda.garzon_id);
                    
                    // Cargar productos del carrito desde los detalles de la comanda
                    posCarrito = [];
                    if (comanda.detalles && comanda.detalles.length > 0) {
                        console.log('Detalles de comanda:', comanda.detalles);
                        comanda.detalles.forEach(function(detalle) {
                            const precio = parseFloat(detalle.precio_unitario);
                            const cantidad = parseInt(detalle.cantidad);
                            const uuidItem = (detalle.origen === 'RECETA' && detalle.receta_uuid)
                                ? ('RECETA-' + detalle.receta_uuid)
                                : (detalle.uuid || null);
                            const item = {
                                id: detalle.producto_id,
                                uuid: uuidItem,
                                origen: detalle.origen || 'PRODUCTO',
                                codigo: detalle.codigo || '',
                                descripcion: detalle.producto,
                                precio: precio,
                                cantidad: cantidad,
                                subtotal: precio * cantidad,
                                observaciones: detalle.observaciones || ''
                            };
                            console.log('Item agregado al carrito:', item);
                            posCarrito.push(item);
                        });
                    }
                    
                    console.log('Carrito final:', posCarrito);
                    
                    // Configurar propina
                    const incluyePropina = comanda.incluye_propina == 1 || comanda.incluye_propina === true;
                    console.log('Incluye propina:', incluyePropina);
                    $('#pos_incluye_propina').prop('checked', incluyePropina);
                    if (incluyePropina) {
                        $('#pos_propina_row').show();
                    } else {
                        $('#pos_propina_row').hide();
                    }
                    
                    // Renderizar carrito con productos existentes
                    renderizarCarrito();
                    
                    // Limpiar campo de búsqueda
                    $('#pos_buscar_producto').val('');
                    
                    // Mostrar mensaje de búsqueda en productos
                    $('#pos_products_grid').html(`
                        <div class="pos-no-results">
                            <i class="fa fa-search" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                            <p>Escribe para buscar más productos</p>
                        </div>
                    `);
                    
                    // Mostrar modal y enfocar búsqueda
                    $('#modalTomarPedido').modal('show');
                    setTimeout(() => $('#pos_buscar_producto').focus(), 300);
                }
            }
        },
        error: function(xhr) {
            console.error('Error al cargar datos de la mesa:', xhr);
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
        error: function(xhr) {
            console.error('Error al cargar productos:', xhr);
            Swal.fire('Error', 'No se pudieron cargar los productos', 'error');
        }
    });
}

function cargarGarzones(garzonIdSeleccionado = null) {
    $.ajax({
        url: '/restaurant/comandas/obtener-garzones',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $('#pos_garzon_id');
                select.empty();
                select.append('<option value="">Seleccionar...</option>');
                
                response.garzones.forEach(function(garzon) {
                    const selected = garzonIdSeleccionado && garzon.id == garzonIdSeleccionado ? 'selected' : '';
                    select.append(`<option value="${garzon.id}" ${selected}>${garzon.nombre_completo}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error al cargar garzones:', xhr);
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
        const stockBajo = !esReceta && (producto.stock < 5);
        const stockHtml = esReceta
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
    const cantidadObjetivo = itemExistente ? (itemExistente.cantidad + 1) : 1;

    verificarStockComanda(producto.uuid, cantidadObjetivo)
        .done(function(resp) {
            if (resp && resp.status === 'OK') {
                if (itemExistente) {
                    const nuevaCantidad = itemExistente.cantidad + 1;
                    actualizarPrecioRangoComanda(itemExistente, nuevaCantidad)
                        .then(function() {
                            renderizarCarrito();
                        })
                        .catch(function() {
                            Swal.fire('Error', 'No se pudo aplicar precio por rango', 'error');
                        });
                } else {
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
                return;
            }

            mostrarErrorStockComanda(resp, producto.descripcion);
        })
        .fail(function() {
            Swal.fire('Error', 'No se pudo verificar stock del producto', 'error');
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
        html += `
            <div class="pos-order-item" data-index="${index}">
                <div class="pos-item-header">
                    <div class="pos-item-name">${item.descripcion}</div>
                    <button class="pos-item-remove" data-index="${index}">
                        <i class="fa fa-times"></i>
                    </button>
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

        verificarStockComanda(item.uuid, cantidadObjetivo)
            .done(function(resp) {
                if (resp && resp.status === 'OK') {
                    actualizarPrecioRangoComanda(item, cantidadObjetivo)
                        .then(function() {
                            renderizarCarrito();
                        })
                        .catch(function() {
                            Swal.fire('Error', 'No se pudo aplicar precio por rango', 'error');
                        });
                    return;
                }

                mostrarErrorStockComanda(resp, item.descripcion);
            })
            .fail(function() {
                Swal.fire('Error', 'No se pudo verificar stock para actualizar cantidad', 'error');
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
        posCarrito.splice(index, 1);
        renderizarCarrito();
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

    const validaciones = itemsValidables.map(function(item) {
        return verificarStockComanda(item.uuid, item.cantidad)
            .then(function(resp) {
                if (resp && resp.status === 'OK') {
                    return { ok: true };
                }

                return { ok: false, resp: resp, item: item };
            })
            .catch(function() {
                return { ok: false, item: item, error: true };
            });
    });

    return Promise.all(validaciones).then(function(resultados) {
        const fallo = resultados.find(r => !r.ok);

        if (!fallo) {
            return true;
        }

        if (fallo.error) {
            Swal.fire('Error', 'No se pudo validar stock antes de guardar', 'error');
            return false;
        }

        mostrarErrorStockComanda(fallo.resp, fallo.item ? fallo.item.descripcion : 'Producto');
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
            error: function(xhr) {
                console.error('Error al sincronizar productos:', xhr);
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
                error: function(xhr) {
                    console.error('Error al guardar producto:', xhr);
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
        error: function(xhr) {
            console.error('Error al actualizar comensales:', xhr);
            Swal.fire('Error', 'No se pudo actualizar el número de comensales', 'error');
        }
    });
}
