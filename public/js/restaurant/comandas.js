// ==================== VARIABLES GLOBALES ====================
let posProductos = [];
let posCarrito = [];
let posMesaActual = null;
let posComandaActual = null;
let posCapacidadOriginal = 0;
let posFechaApertura = null; // Almacena la fecha/hora de apertura de la comanda

$(document).ready(function() {
    cargarMesas();
    
    // Refrescar mesas cada 30 segundos
    setInterval(cargarMesas, 30000);
    
    // Evento para refrescar manualmente
    $('#refrescar_mesas').on('click', function() {
        cargarMesas();
    });
});

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
    
    mesas.forEach(function(mesa) {
        const estado = mesa.estado.toLowerCase();
        const esLibre = estado === 'libre';
        
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
            `;
        }
        
        const mesaHtml = `
            <div class="mesa-card-comanda ${estado}" data-mesa-id="${mesa.id}" data-estado="${estado}">
                <div class="mesa-header-comanda">
                    <h3><i class="fa fa-chair"></i> ${mesa.nombre}</h3>
                    <span class="mesa-status-badge ${estado}">${estado}</span>
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
    
    // Eventos para las mesas
    $('.mesa-card-comanda').on('click', function(e) {
        // Evitar abrir modal si se hizo clic en botones de comensales
        if ($(e.target).closest('.comensales-control').length > 0) {
            return;
        }
        
        const mesaId = $(this).data('mesa-id');
        const estado = $(this).data('estado');
        
        if (estado === 'ocupada') {
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
                        posCarrito.push({
                            id: detalle.producto_id,
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
                            const item = {
                                id: detalle.producto_id,
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
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                posProductos = response.productos;
                
                // Si hay búsqueda, filtrar
                if (busqueda) {
                    const productosFiltrados = posProductos.filter(function(producto) {
                        return producto.descripcion.toLowerCase().includes(busqueda) ||
                               producto.codigo.toLowerCase().includes(busqueda);
                    });
                    
                    if (productosFiltrados.length === 0) {
                        $('#pos_products_grid').html(`
                            <div class="pos-no-results">
                                <i class="fa fa-times-circle" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                                <p>No se encontraron productos</p>
                            </div>
                        `);
                    } else {
                        renderizarProductos(productosFiltrados);
                    }
                } else {
                    renderizarProductos(posProductos);
                }
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
    
    productos.forEach(function(producto) {
        const stockBajo = producto.stock < 5;
        const card = `
            <div class="pos-product-card" data-uuid="${producto.uuid}">
                <div class="pos-product-name">${producto.descripcion}</div>
                <div class="pos-product-code">${producto.codigo}</div>
                <div class="pos-product-footer">
                    <div class="pos-product-price">$${formatearPrecio(producto.precio_venta)}</div>
                    <div class="pos-product-stock ${stockBajo ? 'stock-bajo' : ''}">
                        Stock: ${producto.stock}
                    </div>
                </div>
            </div>
        `;
        grid.append(card);
    });
    
    // Evento click en productos
    $('.pos-product-card').on('click', function() {
        const uuid = $(this).data('uuid');
        agregarAlCarrito(uuid);
    });
}

function agregarAlCarrito(productoUuid) {
    const producto = posProductos.find(p => p.uuid === productoUuid);
    
    if (!producto) return;
    
    // Verificar si ya está en el carrito usando id
    const itemExistente = posCarrito.find(item => item.id === producto.id);
    
    if (itemExistente) {
        itemExistente.cantidad++;
        itemExistente.subtotal = itemExistente.cantidad * itemExistente.precio;
    } else {
        posCarrito.push({
            id: producto.id,
            uuid: producto.uuid,
            codigo: producto.codigo,
            descripcion: producto.descripcion,
            precio: parseFloat(producto.precio_venta),
            cantidad: 1,
            subtotal: parseFloat(producto.precio_venta),
            observaciones: ''
        });
    }
    
    renderizarCarrito();
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
    
    posCarrito.forEach(function(item, index) {
        const itemHtml = `
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
        container.append(itemHtml);
    });
    
    // Eventos para botones
    $('.pos-qty-plus').on('click', function() {
        const index = $(this).data('index');
        posCarrito[index].cantidad++;
        posCarrito[index].subtotal = posCarrito[index].cantidad * posCarrito[index].precio;
        renderizarCarrito();
    });
    
    $('.pos-qty-minus').on('click', function() {
        const index = $(this).data('index');
        if (posCarrito[index].cantidad > 1) {
            posCarrito[index].cantidad--;
            posCarrito[index].subtotal = posCarrito[index].cantidad * posCarrito[index].precio;
            renderizarCarrito();
        }
    });
    
    $('.pos-item-remove').on('click', function() {
        const index = $(this).data('index');
        posCarrito.splice(index, 1);
        renderizarCarrito();
    });
    
    actualizarTotales();
}

function actualizarTotales() {
    const subtotal = posCarrito.reduce((sum, item) => sum + item.subtotal, 0);
    const incluyePropina = $('#pos_incluye_propina').is(':checked');
    const propina = incluyePropina ? Math.round(subtotal * 0.10) : 0;
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
$('#pos_buscar_producto').on('input', function() {
    const busqueda = $(this).val().toLowerCase().trim();
    
    if (busqueda === '') {
        $('#pos_products_grid').html(`
            <div class="pos-no-results">
                <i class="fa fa-search" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                <p>Escribe para buscar productos</p>
            </div>
        `);
        return;
    }
    
    // Si aún no se han cargado los productos, cargarlos
    if (posProductos.length === 0) {
        cargarProductosPOS(busqueda);
    } else {
        // Filtrar productos ya cargados
        const productosFiltrados = posProductos.filter(function(producto) {
            return producto.descripcion.toLowerCase().includes(busqueda) ||
                   producto.codigo.toLowerCase().includes(busqueda);
        });
        
        if (productosFiltrados.length === 0) {
            $('#pos_products_grid').html(`
                <div class="pos-no-results">
                    <i class="fa fa-times-circle" style="font-size:32px;opacity:0.3;margin-bottom:10px;"></i>
                    <p>No se encontraron productos</p>
                </div>
            `);
        } else {
            renderizarProductos(productosFiltrados);
        }
    }
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

function guardarProductosComanda(esActualizacion = false) {
    if (esActualizacion) {
        // Para comandas existentes: sincronizar productos (elimina y recrea)
        const productos = posCarrito.map(item => ({
            producto_id: item.id,
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
    
    window.open('/restaurant/comandas/imprimir/' + posComandaActual, '_blank');
});

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
