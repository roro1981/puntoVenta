$(document).ready(function() {
    cargarBorradores();
    let cart = [];
    let alerts = [];

    function pushAlertFromResp(resp, context = {}) {
        try {
            if (!resp || typeof resp !== 'object') return;
            const ts = Date.now();
            if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
                const p = resp.product || {};
                alerts.push({
                    id: 'prod-' + (p.uuid || ts) + '-' + ts,
                    type: 'product',
                    title: p.descripcion || p.codigo || 'Producto',
                    requested: context.requested || resp.requested || 0,
                    available: p.stock || 0,
                    message: resp.message || 'No hay stock suficiente para este producto',
                    raw: resp
                });
            } else if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                const promo = resp.promotion || {};
                alerts.push({
                    id: 'promo-' + (promo.uuid || ts) + '-' + ts,
                    type: 'promo',
                    title: promo.nombre || promo.codigo || 'Promoción',
                    message: resp.message || 'Componentes sin stock en la promoción',
                    items: resp.items || [],
                    raw: resp
                });
            }
            renderAlerts();
            openAlertsTab();
        } catch (e) {
            console.error('pushAlertFromResp error', e);
        }
    }

    function removeAlertByUuid(uuid) {
        alerts = alerts.filter(a => {
            if (a.type === 'product') return a.raw?.product?.uuid !== uuid;
            if (a.type === 'promo') return a.raw?.promotion?.uuid !== uuid;
            return true;
        });
        renderAlerts();
    }

    function renderAlerts() {
        const $list = $('#alerts-list');
        if (!$list.length) return;
        if (alerts.length === 0) {
            $list.html('<div class="text-muted">No hay alertas por el momento.</div>');
            updateAlertsBadge();
            return;
        }

        let html = '';
        alerts.forEach(a => {
            if (a.type === 'product') {
                html += `<div class="card mb-2">
                    <div class="card-body p-2">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong>Producto sin stock suficiente</strong>
                                <div style="font-size:0.95rem;margin-top:4px;color:#333">${a.title}</div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-weight:600;color:#b33">Solicitado: ${a.requested}</div>
                                <div style="color:#666">Disponible: ${a.available}</div>
                            </div>
                        </div>
                        <div style="margin-top:8px;color:#555;font-size:1.2em">${a.message}</div>
                    </div>
                </div>`;
            } else if (a.type === 'promo') {
                html += `<div class="card mb-2">
                    <div class="card-body p-2">
                        <div><strong>Promoción con faltantes</strong></div>
                        <div style="font-size:0.95rem;margin-top:6px;color:#333">${a.title}</div>
                        <div style="margin-top:8px;background:#f8d7da;padding:8px;border-radius:4px;color:#721c24">
                            <div style="font-weight:600;margin-bottom:6px">Detalle de componentes con faltantes:</div>
                            <ul style="margin:0 0 0 18px;padding:0;color:#721c24">
                `;
                (a.items || []).forEach(it => {
                    const mark = it.sufficient ? '' : ' (Faltante)';
                    html += `<li>${it.descripcion} — requerido: ${it.required_total}, disponible: ${it.stock}${mark}</li>`;
                });
                html += `</ul></div>
                        <div style="margin-top:6px;color:#555;font-size:1.2rem">${a.message}</div>
                    </div>
                </div>`;
            }
        });
        $list.html(html);
        updateAlertsBadge();
    }

    function updateAlertsBadge() {
        const $badge = $('#alerts-count');
        if (!$badge.length) return;
        const count = alerts.length || 0;
        if (count > 0) {
            $badge.text(count).show();
        } else {
            $badge.text('0').hide();
        }
    }

    function openAlertsTab() {
        const $btn = $('.tab-btn[data-tab="alerts"]');
        if ($btn.length) {
            if (!$btn.hasClass('active')) $btn.trigger('click');
        }
    }

    function getCartTotalQuantity(uuid, excludeIndex) {
        return cart.reduce((sum, it, idx) => {
            if (!it || !it.uuid) return sum;
            if (it.uuid === uuid && idx !== excludeIndex) {
                return sum + (parseFloat(it.quantity) || 0);
            }
            return sum;
        }, 0);
    }

    function formatCurrency(amount) {
        return '$ ' + new Intl.NumberFormat('es-CL').format(amount);
    }

    // Llama al endpoint backend para verificar stock de producto o promocion
    function verificarStock(uuid, cantidad) {
        // devolver el jqXHR para poder encadenar .done()/.fail()
        return $.ajax({
            url: '/ventas/verificar-stock',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                uuid: uuid,
                cantidad: cantidad
            }
        });
    }

    function updateTotal() {
        let total = cart.reduce((sum, item) => {
            let discount = parseFloat(item.discount) || 0;
            return sum + (item.precio_venta * item.quantity * (1 - discount/100));
        }, 0);
        // calcular total de descuentos (valor monetario descontado)
        let totalDiscount = cart.reduce((sum, item) => {
            let discount = parseFloat(item.discount) || 0;
            return sum + (item.precio_venta * item.quantity * (discount/100));
        }, 0);

        $('#cart-total').text(formatCurrency(Math.round(total)));
        $('#discount-amount').text(formatCurrency(Math.round(totalDiscount)));

        // Deshabilitar PAGAR si hay items con stock insuficiente
        const anyInsufficient = cart.some(it => it.insufficient === true);
        $('#pay-btn').prop('disabled', anyInsufficient);
    }

    function renderCart() {
        let cartHtml = cart.map((item, index) => {
            let discount = parseFloat(item.discount) || 0;
            let lineTotal = Math.round(item.precio_venta * item.quantity * (1 - discount/100));
            const rowStyle = item.insufficient ? 'background-color:#ffe6e6;' : '';
            const insuffBadge = item.insufficient ? '<span class="badge badge-danger" style="margin-left:8px;">Faltante</span>' : '';
            return `
            <div class="product-row" data-id="${item.uuid}" data-index="${index}" style="${rowStyle}">
                <div class="quantity-controls">
                    <button class="quantity-btn minus-btn">-</button>
                    <input type="text" class="quantity-input" value="${item.quantity}">
                    <button class="quantity-btn plus-btn">+</button>
                </div>
                <div class="product-info">
                    <div class="product-name">${item.descripcion}</div>
                    <div class="product-price">$/unidad: ${formatCurrency(item.precio_venta)} ${insuffBadge}</div>
                </div>
                <div class="product-total">${formatCurrency(lineTotal)}</div>
                <select class="discount-select">
                    <option value="0" ${discount === 0 ? 'selected' : ''}>0 %</option>
                    <option value="5" ${discount === 5 ? 'selected' : ''}>5 %</option>
                    <option value="10" ${discount === 10 ? 'selected' : ''}>10 %</option>
                    <option value="15" ${discount === 15 ? 'selected' : ''}>15 %</option>
                </select>
                <button class="delete-btn">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `}).join('');
        $('#cart-items').html(cartHtml);
        updateTotal();
    }

    $('#product-code').on('keypress', function(e) {
        if(e.which === 13) { 
            let code = $(this).val();
            let input = $(this);
            
            $.ajax({
                url: '/ventas/buscarProducto',
                method: 'GET',
                data: { q: code, tipo:1 },
                success: function(product) {
                    if(product.length > 0){
                        // verificar stock antes de añadir (asíncrono) - incluyendo cantidad total del mismo producto en carrito
                        const existingQty = getCartTotalQuantity(product[0].uuid);
                        const totalRequested = existingQty + 1;
                        verificarStock(product[0].uuid, totalRequested).done(function(resp){
                            if (resp.status === 'OK') {
                                cart.push({
                                    uuid: product[0].uuid,
                                    descripcion: product[0].descripcion,
                                    precio_venta: product[0].precio_venta,
                                    quantity: 1,
                                    discount: 0
                                });
                                renderCart();
                                input.val('');
                            } else {
                                // mostrar mensaje con detalle si disponible
                                if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
                                    toastr.error('Sin stock: ' + (resp.product.descripcion || resp.product.codigo || product[0].descripcion));
                                    pushAlertFromResp(resp, { requested: totalRequested });
                                } else if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                                    let msg = 'Promoción con faltantes:\n';
                                    resp.items.forEach(function(it){
                                        if (!it.sufficient) {
                                            msg += `${it.descripcion} - stock: ${it.stock}, requerido: ${it.required_total}\n`;
                                        }
                                    });
                                    toastr.error(msg);
                                    pushAlertFromResp(resp);
                                } else {
                                    toastr.error(resp.message || 'No se puede agregar el producto');
                                }
                            }
                        }).fail(function(){
                            toastr.error('Error verificando stock');
                        });
                        
                    }else{
                        toastr.error("Producto no existe");
                    }
                    
                }
            });
        }
    });

    $(document).on('click', '.plus-btn', function() {
        let index = $(this).closest('.product-row').data('index');
        // incrementar y verificar stock (incluyendo cantidad de otros items del mismo producto)
        const newQty = cart[index].quantity + 1;
        const otherQty = getCartTotalQuantity(cart[index].uuid, index);
        const totalRequested = otherQty + newQty;
        verificarStock(cart[index].uuid, totalRequested).done(function(resp){
            if (resp.status === 'OK') {
                cart[index].quantity = newQty;
                cart[index].insufficient = false;
                removeAlertByUuid(cart[index].uuid);
            } else if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
                // Permitir aumentar la cantidad pero marcar como insuficiente
                const available = resp.product.stock || 0;
                cart[index].quantity = newQty;
                cart[index].insufficient = (available < totalRequested);
                toastr.warning('Cantidad mayor al stock. Solo hay ' + available + ' disponibles para ' + cart[index].descripcion);
                pushAlertFromResp(resp, { requested: totalRequested });
            } else if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                // Permitir aumentar la cantidad de la promoción pero marcar como insuficiente
                cart[index].quantity = newQty;
                cart[index].insufficient = true;
                toastr.error('Promoción con faltantes. Revise los componentes.');
                pushAlertFromResp(resp);
            }
            renderCart();
        }).fail(function(){
            toastr.error('Error verificando stock');
        });
    });

    $(document).on('click', '.minus-btn', function() {
        let index = $(this).closest('.product-row').data('index');
        if(cart[index].quantity > 1) {
            const newQty = cart[index].quantity - 1;
            // reducir no suele fallar por stock, aplicarlo directamente
            cart[index].quantity = newQty;
            cart[index].insufficient = false;
            // si al reducir queda sin insuficiencia, limpiar alerta asociada
            removeAlertByUuid(cart[index].uuid);
            renderCart();
        }
    });

    $(document).on('change', '.discount-select', function() {
        let index = $(this).closest('.product-row').data('index');
        cart[index].discount = parseInt($(this).val());
        renderCart();
    });

    $(document).on('click', '.delete-btn', function() {
        let index = $(this).closest('.product-row').data('index');
        // Antes de eliminar el item del carrito, quitar cualquier alerta asociada
        const item = cart[index];
        if (item && item.uuid) {
            removeAlertByUuid(item.uuid);
        }
        cart.splice(index, 1);
        renderCart();
    });

    $('#product-search').on('keyup', function() {
        let query = $(this).val().toLowerCase();
        if (!query){
            $('#product-list').html("");
            return false;
        }
        $.ajax({
            url: '/ventas/buscarProducto',
            method: 'GET',
            data: { q: query, tipo:2 },
            success: function(products) {
                
                let productsHtml = products.map(product => `
                    <div class="product-item">
                        <span>${product.descripcion}</span>
                        <div class="product-actions">
                            <i class="fa fa-plus action-icon add-to-cart" data-uuid="${product.uuid}" data-name="${product.descripcion}" data-price="${product.precio_venta}"></i>
                        </div>
                    </div>
                `).join('');
                
                $('#product-list').html(productsHtml);
            }
        });
    });

    $(document).on('click', '.add-to-cart', function() {
        let product = {
            uuid: $(this).data('uuid'),
            descripcion: $(this).data('name'),
            precio_venta: $(this).data('price'),
            quantity: 1,
            discount: 0
        };

        // verificar stock antes de añadir - incluyendo cantidad total del mismo producto en carrito
        const existingQty = getCartTotalQuantity(product.uuid);
        const totalRequested = existingQty + 1;
        verificarStock(product.uuid, totalRequested).done(function(resp){
            if (resp.status === 'OK') {
                cart.push(product);
                renderCart();
            } else {
                if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
                    toastr.error('Sin stock: ' + (resp.product.descripcion || resp.product.codigo || product.descripcion));
                    pushAlertFromResp(resp, { requested: totalRequested });
                } else if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                    let msg = 'Promoción con faltantes:\n';
                    resp.items.forEach(function(it){
                        if (!it.sufficient) {
                            msg += `${it.descripcion} - stock: ${it.stock}, requerido: ${it.required_total}\n`;
                        }
                    });
                    toastr.error(msg);
                    pushAlertFromResp(resp);
                } else {
                    toastr.error(resp.message || 'No se puede agregar el producto');
                }
            }
        }).fail(function(){
            toastr.error('Error verificando stock');
        });
    });

    $('#cancel-btn').click(function() {
        Swal.fire({
            title: "Cancelar venta",
            text: "¿Está seguro de cancelar la venta?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sí",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
            } else {
                toastr.error("Eliminación cancelada");
            }
        });
    });

    $('#save-draft-btn').click(function() {

        if (cart.length === 0) {
            toastr.warning('El carrito está vacío. Agrega productos antes de guardar el borrador.');
            return;
        }

        let uuid_borrador = crypto.randomUUID(); 
        const now = new Date();
        const timezoneOffset = now.getTimezoneOffset() * 60000;
        const localTime = new Date(now.getTime() - timezoneOffset);
        const fecha = localTime.toISOString().slice(0, 19).replace('T', ' ');
    
        let productos = cart.map(item => ({
            product_uuid: item.uuid,
            descripcion: item.descripcion,
            precio_venta: item.precio_venta,
            cantidad: item.quantity,
            descuento: item.discount,
            uuid_borrador: uuid_borrador,
            fec_creacion: fecha
        }));
       
        $.ajax({
            url: '/ventas/guardar-borrador',
            method: 'POST',
            data: {
                _token: $("#token").val(),
                productos: productos
            },
            success: function (response) {
                if (response.status === 'OK') {
                    toastr.success(response.message);
                    // Limpiar carrito y alertas al guardar el borrador
                    cart = [];
                    alerts = [];
                    renderCart();
                    renderAlerts();
                    cargarBorradores();
                } else {
                    toastr.warning(response.message);
                }
            }
        });
    });

    // Variable para almacenar pagos mixtos
    let pagosMixtos = [];

    // Manejador del botón PAGAR
    $('#pay-btn').click(function() {
        if(cart.length === 0) {
            toastr.warning('Agregue productos al carrito para continuar');
            return;
        }

        const formaPago = $('#forma-pago').val();
        if (!formaPago) {
            toastr.warning('Debe seleccionar una forma de pago');
            return;
        }

        const anyInsufficient = cart.some(it => it.insufficient === true);
        if (anyInsufficient) {
            toastr.error('No puede procesar: hay productos con stock insuficiente');
            return;
        }

        // Si es MIXTO, abrir modal
        if (formaPago === 'MIXTO') {
            pagosMixtos = [];
            const total = parseInt($('#cart-total').text().replace(/[^\d]/g, ''));
            $('#totalPagoMixto').text(formatCurrency(total));
            $('#pendientePagoMixto').text(formatCurrency(total));
            $('#tablaPagoMixto tbody').html('');
            $('#montoPagoMixto').val('');
            $('#formaPagoMixtoSelect').val('');
            $('#modalPagoMixto').modal('show');
            return;
        }

        // Pago simple (no mixto)
        procesarPago(formaPago, null);
    });

    // Agregar forma de pago al desglose MIXTO
    $('#btnAgregarFormaPago').click(function() {
        const forma = $('#formaPagoMixtoSelect').val();
        const monto = parseInt($('#montoPagoMixto').val() || 0);

        if (!forma) {
            toastr.warning('Selecciona una forma de pago');
            return;
        }

        if (monto <= 0) {
            toastr.warning('Ingresa un monto válido');
            return;
        }

        const total = parseInt($('#cart-total').text().replace(/[^\d]/g, ''));
        const sumaMontosActuales = pagosMixtos.reduce((sum, p) => sum + p.monto, 0);

        if (sumaMontosActuales + monto > total) {
            toastr.error('El monto total no puede exceder al total de la venta');
            return;
        }

        pagosMixtos.push({ forma: forma, monto: monto, id: Date.now() });
        actualizarTablaPagoMixto();
        $('#formaPagoMixtoSelect').val('');
        $('#montoPagoMixto').val('');
    });

    // Actualizar tabla de pagos mixtos
    function actualizarTablaPagoMixto() {
        const total = parseInt($('#cart-total').text().replace(/[^\d]/g, ''));
        const sumaMontosActuales = pagosMixtos.reduce((sum, p) => sum + p.monto, 0);
        const pendiente = total - sumaMontosActuales;

        let html = '';
        pagosMixtos.forEach(pago => {
            const formaLabel = {
                'EFECTIVO': '💵 Efectivo',
                'TARJETA_DEBITO': '🏦 Tarjeta Débito',
                'TARJETA_CREDITO': '💳 Tarjeta Crédito',
                'TRANSFERENCIA': '🔄 Transferencia',
                'CHEQUE': '📋 Cheque'
            }[pago.forma] || pago.forma;

            html += `
                <tr>
                    <td><strong>${formaLabel}</strong></td>
                    <td style="text-align: right; font-weight: 600;">${formatCurrency(pago.monto)}</td>
                    <td style="text-align: right;">
                        <button class="btn btn-sm btn-danger btn-eliminar-pago" data-id="${pago.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#tablaPagoMixto tbody').html(html);
        $('#pendientePagoMixto').text(formatCurrency(pendiente));

        // Habilitar/deshabilitar botón de confirmar
        $('#btnConfirmarPagoMixto').prop('disabled', pendiente > 0);

        // Cambiar color del pendiente
        const $pendiente = $('#pendientePagoMixto');
        if (pendiente === 0) {
            $pendiente.css('color', '#155724');
        } else if (pendiente > 0) {
            $pendiente.css('color', '#856404');
        }

        // Agregar eventos de eliminar
        $('.btn-eliminar-pago').off('click').on('click', function() {
            const id = $(this).data('id');
            pagosMixtos = pagosMixtos.filter(p => p.id !== id);
            actualizarTablaPagoMixto();
        });
    }

    // Confirmar pago MIXTO
    $('#btnConfirmarPagoMixto').click(function() {
        if (pagosMixtos.length === 0) {
            toastr.warning('Agrega al menos una forma de pago');
            return;
        }

        const total = parseInt($('#cart-total').text().replace(/[^\d]/g, ''));
        const sumaMontosActuales = pagosMixtos.reduce((sum, p) => sum + p.monto, 0);

        if (sumaMontosActuales !== total) {
            toastr.error('El monto total no coincide con la venta');
            return;
        }

        $('#modalPagoMixto').modal('hide');
        procesarPago('MIXTO', pagosMixtos);
    });

    // Función para procesar el pago
    function procesarPago(formaPago, desglosePagos) {
        // Calcular totales
        const totalVenta = parseInt($('#cart-total').text().replace(/[^\d]/g, ''));
        const totalDescuentos = parseInt($('#discount-amount').text().replace(/[^\d]/g, ''));

        // Preparar detalles de la venta
        const detalles = cart.map(item => {
            const cantidad = parseFloat(item.quantity);
            const precioUnitario = parseInt(item.precio_venta);
            const descuentoPorcentaje = parseFloat(item.discount) || 0;
            const subtotalLinea = Math.round(cantidad * precioUnitario * (1 - descuentoPorcentaje / 100));

            return {
                producto_uuid: item.uuid,
                descripcion_producto: item.descripcion,
                cantidad: cantidad,
                precio_unitario: precioUnitario,
                descuento_porcentaje: descuentoPorcentaje,
                subtotal_linea: subtotalLinea
            };
        });

        // Preparar datos de la venta
        const datosVenta = {
            _token: $('#token').val(),
            total: totalVenta,
            total_descuentos: totalDescuentos,
            forma_pago: formaPago,
            estado: 'completada',
            detalles: detalles
        };

        // Si es pago MIXTO, agregar el desglose
        if (formaPago === 'MIXTO' && desglosePagos) {
            datosVenta.formas_pago_desglose = desglosePagos;
        }

        // Enviar al backend
        $.ajax({
            url: '/ventas/procesar-venta',
            method: 'POST',
            data: datosVenta,
            beforeSend: function() {
                $('#pay-btn').prop('disabled', true).text('Procesando...');
            },
            success: function(response) {
                if (response.status === 'OK') {
                    toastr.success(response.message);
                    
                    // Limpiar carrito y alertas
                    cart = [];
                    alerts = [];
                    pagosMixtos = [];
                    renderCart();
                    renderAlerts();
                    
                    // Resetear forma de pago
                    $('#forma-pago').val('');
                    
                    // Mostrar información de la venta
                    if (response.numero_venta) {
                        toastr.info('Venta Nº: ' + response.numero_venta, 'Venta registrada', {
                            timeOut: 5000
                        });
                    }
                } else {
                    toastr.error(response.message || 'Error al procesar la venta');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al procesar la venta';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    // Mostrar errores de validación
                    if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            toastr.error(errors[key][0]);
                        });
                        return;
                    }
                }
                
                toastr.error(errorMsg);
            },
            complete: function() {
                $('#pay-btn').prop('disabled', false).text('PAGAR');
            }
        });
    }

    $('.tab-btn').on('click', function() {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        const tab = $(this).data('tab');
        $('#tab-' + tab).addClass('active');
    });

    $('#cart-items').on('keypress', '.quantity-input', function (e) {
        let char = String.fromCharCode(e.which);
        let allowed = /^[0-9.]$/;
    
        if (!allowed.test(char)) {
            e.preventDefault(); // bloquea letras y símbolos
        }
    
        // Solo permitir un punto decimal
        if (char === '.' && $(this).val().includes('.')) {
            e.preventDefault();
        }
    })

    $('#cart-items').on('input', '.quantity-input', function () {
        let $input = $(this);
        let index = $input.closest('.product-row').data('index');

        let value = $input.val().replace(/[^0-9.]/g, '');
        let parts = value.split('.');

        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        if (parts[1]?.length > 1) {
            parts[1] = parts[1].substring(0, 1);
            value = parts[0] + '.' + parts[1];
        }

        $input.val(value);

        let newQuantity = parseFloat(value);
        if (isNaN(newQuantity) || newQuantity <= 0) {
            newQuantity = 1;
        }

        // Verificar stock para la cantidad ingresada (incluyendo cantidad de otros items del mismo producto)
        const otherQty = getCartTotalQuantity(cart[index].uuid, index);
        const totalRequested = otherQty + newQuantity;
        verificarStock(cart[index].uuid, totalRequested).done(function(resp){
            if (resp.status === 'OK') {
                cart[index].quantity = newQuantity;
                cart[index].insufficient = false;
                removeAlertByUuid(cart[index].uuid);
            } else if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
                // Permitir la cantidad solicitada, pero marcar insuficiente y mostrar alerta
                const available = resp.product.stock || 0;
                cart[index].quantity = newQuantity;
                cart[index].insufficient = (available < totalRequested);
                toastr.warning('Cantidad mayor al stock. Solo hay ' + available + ' disponibles para ' + cart[index].descripcion);
                pushAlertFromResp(resp, { requested: totalRequested });
            } else if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                // Permitir la cantidad solicitada para la promoción, marcar insuficiente y mostrar alerta
                cart[index].quantity = newQuantity;
                cart[index].insufficient = true;
                toastr.error('Promoción con faltantes. Revise los componentes.');
                pushAlertFromResp(resp);
            }

            // Actualizar solo el total de ese producto
            let total = Math.round(cart[index].precio_venta * cart[index].quantity * (1 - (parseFloat(cart[index].discount)||0)/100));
            $input.closest('.product-row').find('.product-total').text(formatCurrency(total));

            // Actualizar el total general y re-renderizar para reflejar la etiqueta Faltante
            updateTotal();
            renderCart();
        }).fail(function(){
            toastr.error('Error verificando stock');
        });
    });

    $(document).on('click', '.ver-borrador', function () {
        const uuid = $(this).data('uuid');
        $.ajax({
            url: `/ventas/borrador/${uuid}/productos`,
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                let tbody = '';
                data.forEach(item => {
                    tbody += `
                        <tr uuid="${item.producto_uuid}">
                            <td>${item.cantidad}</td>
                            <td>${item.producto}</td>
                            <td>${item.precio_venta}</td>
                            <td>${item.descuento}</td>
                        </tr>
                    `;
                });
                $('#detalle-borrador-body').html(tbody);
                $('#uuid_borrador').val(uuid);
                $('#modalDetalleBorrador').modal('show');
                }
            });
    });
    $('#btnCargarVenta').on('click', function () {

        // Recolectar filas y verificar stock en paralelo
        const rows = [];
        $('#detalle-borrador-body tr').each(function () {
            rows.push({
                uuid: $(this).attr('uuid'),
                cantidad: parseFloat($(this).find('td:eq(0)').text()),
                descripcion: $(this).find('td:eq(1)').text(),
                precio_venta: parseFloat($(this).find('td:eq(2)').text()),
                descuento: parseInt($(this).find('td:eq(3)').text())
            });
        });

        if (rows.length === 0) {
            $('#modalDetalleBorrador').modal('hide');
            return;
        }

        const promises = rows.map(r => {
            const existingQty = getCartTotalQuantity(r.uuid);
            const totalRequested = existingQty + r.cantidad;
            return verificarStock(r.uuid, totalRequested);
        });

        Promise.all(promises).then(function(results) {
            results.forEach(function(resp, idx) {
                const r = rows[idx];
                if (resp.status === 'OK') {
                    cart.push({ uuid: r.uuid, descripcion: r.descripcion, precio_venta: r.precio_venta, quantity: r.cantidad, discount: r.descuento, insufficient: false });
                } else if (resp.code === 'OUT_OF_STOCK_PRODUCT') {
                        const available = resp.product.stock || 0;
                        const existingQty = getCartTotalQuantity(r.uuid);
                        const totalRequested = existingQty + r.cantidad;
                        // No ajustar la cantidad al cargar el borrador; mantener la solicitada y marcar insuficiente
                        cart.push({ uuid: r.uuid, descripcion: r.descripcion, precio_venta: r.precio_venta, quantity: r.cantidad, discount: r.descuento, insufficient: (available < totalRequested) });
                        toastr.error('Stock insuficiente para ' + r.descripcion + '. Solo hay ' + available + ' disponibles.');
                        pushAlertFromResp(resp, { requested: totalRequested });
                } else if (resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                    // marcar promocion como insuficiente y adjuntar detalles
                    cart.push({ uuid: r.uuid, descripcion: r.descripcion, precio_venta: r.precio_venta, quantity: r.cantidad, discount: r.descuento, insufficient: true, missingItems: resp.items });
                    let msg = 'Promoción contiene productos con faltante:\n';
                    resp.items.forEach(function(it){
                        if (!it.sufficient) msg += `${it.descripcion} - stock:${it.stock}, requerido:${it.required_total}\n`;
                    });
                    toastr.error(msg);
                    pushAlertFromResp(resp);
                } else {
                    toastr.error('No se pudo cargar el ítem: ' + (r.descripcion || r.uuid));
                }
            });

            renderCart();
            $('#modalDetalleBorrador').modal('hide');
            eliminarBorrador($('#uuid_borrador').val());
        }).catch(function(){
            toastr.error('Error verificando stock al cargar borrador');
        });
    });


});
function cargarBorradores() {
    $.ajax({
        url: '/ventas/traer-borradores',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            let tablaHtml = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Productos</th>
                            <th>Total venta</th>
                            <th>Fecha venta</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            data.forEach(function(item) {
                tablaHtml += `
                    <tr data-uuid_prod="${item.producto_uuid}">
                        <td style="text-align: center;">${item.total_cantidad}</td>
                        <td style="text-align: center;">${item.total}</td>
                        <td>${item.fecha_creacion}</td>
                                <td style="white-space: nowrap;">
                                    <button class="btn btn-sm btn-primary ver-borrador" title="Detalle productos" data-uuid="${item.uuid_borrador}" style="margin-right:6px">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger eliminar-borrador" title="Eliminar borrador" data-uuid="${item.uuid_borrador}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                    </tr>
                `;
            });
            tablaHtml += `</tbody></table>`;
            $('#tab-borradores').html(tablaHtml);
        }
    });
}

function eliminarBorrador(uuid_borrador) {
    $.ajax({
        url: '/ventas/eliminar-borrador/' + uuid_borrador,
        method: 'DELETE',
        data: { _token: $("#token").val()},
        success: function(response) {
            if (response.status === 'OK') {
                cargarBorradores();
            } else {
                toastr.error('Error al eliminar el borrador: ' + response.message);
            }
        },
        error: function() {
            toastr.error('Error al eliminar el borrador');
        }
    });
}
