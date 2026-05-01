$(document).ready(function() {
    const EVENT_NS = '.preventaGenerar';

    // Evita doble binding cuando se navega por modulos via AJAX y quedan listeners de ventas.
    $(document).off('click.ventasGenerar', '.plus-btn');
    $(document).off('click.ventasGenerar', '.minus-btn');

    cargarBorradores();
    // Quitar carga inmediata de preventas pendientes
    // cargarPreventasPendientes(); 
    let cart = [];
    let alerts = [];
    let addByCodeInProgress = false;
    let quantityInputTimers = {};
    let lastPriceSignature = '';
    let inFlightPriceSignature = '';
    let inFlightPricePromise = null;
    const productByCodeCache = new Map();
    const stockInFlight = new Map();
    const stockCache = new Map();
    const STOCK_CACHE_TTL_MS = 1200;

    function pushAlertFromResp(resp, context) {
        context = context || {};
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
        alerts = alerts.filter(function(a) {
            if (a.type === 'product') return a.raw && a.raw.product && a.raw.product.uuid !== uuid;
            if (a.type === 'promo') return a.raw && a.raw.promotion && a.raw.promotion.uuid !== uuid;
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
        alerts.forEach(function(a) {
            if (a.type === 'product') {
                html += '<div class="card mb-2"><div class="card-body p-2">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                    '<div><strong>Producto sin stock suficiente</strong>' +
                    '<div style="font-size:0.95rem;margin-top:4px;color:#333">' + a.title + '</div></div>' +
                    '<div style="text-align:right">' +
                    '<div style="font-weight:600;color:#b33">Solicitado: ' + a.requested + '</div>' +
                    '<div style="color:#666">Disponible: ' + a.available + '</div></div></div>' +
                    '<div style="margin-top:8px;color:#555;font-size:1.2em">' + a.message + '</div>' +
                    '</div></div>';
            } else if (a.type === 'promo') {
                html += '<div class="card mb-2"><div class="card-body p-2">' +
                    '<div><strong>Promoción con faltantes</strong></div>' +
                    '<div style="font-size:0.95rem;margin-top:6px;color:#333">' + a.title + '</div>' +
                    '<div style="margin-top:8px;background:#f8d7da;padding:8px;border-radius:4px;color:#721c24">' +
                    '<div style="font-weight:600;margin-bottom:6px">Detalle de componentes con faltantes:</div>' +
                    '<ul style="margin:0 0 0 18px;padding:0;color:#721c24">';
                (a.items || []).forEach(function(it) {
                    var mark = it.sufficient ? '' : ' (Faltante)';
                    html += '<li>' + it.descripcion + ' — requerido: ' + it.required_total + ', disponible: ' + it.stock + mark + '</li>';
                });
                html += '</ul></div>' +
                    '<div style="margin-top:6px;color:#555;font-size:1.2rem">' + a.message + '</div>' +
                    '</div></div>';
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
        if ($btn.length && !$btn.hasClass('active')) {
            $btn.trigger('click');
        }
    }

    function getCartTotalQuantity(uuid, excludeIndex) {
        return cart.reduce(function(sum, it, idx) {
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

    function getProductImageUrl(product) {
        if (!product || !product.imagen) return '/img/fotos_prod/sin_imagen.jpg';
        var image = String(product.imagen).trim();
        if (!image) return '/img/fotos_prod/sin_imagen.jpg';
        if (image.startsWith('http://') || image.startsWith('https://') || image.startsWith('/')) return image;
        return '/img/fotos_prod/' + image;
    }

    function showProductPreview(product) {
        if (!product) return;
        var nombre = product.descripcion || product.nombre || 'Producto';
        var codigo = product.codigo ? '<div style="color:#666;margin-top:4px">Código: ' + product.codigo + '</div>' : '';
        var precio = typeof product.precio_venta !== 'undefined'
            ? '<div style="margin-top:6px;font-weight:600">Precio: ' + formatCurrency(product.precio_venta) + '</div>'
            : '';
        Swal.fire({
            title: nombre,
            imageUrl: getProductImageUrl(product),
            imageWidth: 150, imageHeight: 150,
            html: codigo + precio,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar'
        });
    }

    function verificarStock(uuid, cantidad) {
        var normalizedQty = (parseFloat(cantidad) || 0).toFixed(4);
        var key = uuid + '|' + normalizedQty;
        var now = Date.now();

        var cached = stockCache.get(key);
        if (cached && (now - cached.ts) <= STOCK_CACHE_TTL_MS) {
            return $.Deferred().resolve(cached.resp).promise();
        }
        if (stockInFlight.has(key)) return stockInFlight.get(key);

        var jqxhr = $.ajax({
            url: '/ventas/verificar-stock',
            method: 'POST',
            data: { _token: $('#token').val(), uuid: uuid, cantidad: cantidad }
        });

        stockInFlight.set(key, jqxhr);
        jqxhr.done(function(resp) {
            stockCache.set(key, { resp: resp, ts: Date.now() });
        }).always(function() {
            stockInFlight.delete(key);
        });

        return jqxhr;
    }

    function validarStockCarritoParaPreventa() {
        alerts = [];
        cart.forEach(function(item) { item.insufficient = false; });
        renderAlerts();

        var cantidadPorUuid = {};
        var descripcionPorUuid = {};
        cart.forEach(function(item) {
            if (!item || !item.uuid) return;
            cantidadPorUuid[item.uuid] = (cantidadPorUuid[item.uuid] || 0) + (parseFloat(item.quantity) || 0);
            if (!descripcionPorUuid[item.uuid]) descripcionPorUuid[item.uuid] = item.descripcion || item.uuid;
        });

        var rows = Object.keys(cantidadPorUuid).map(function(uuid) {
            return {
                uuid: uuid,
                cantidad: cantidadPorUuid[uuid],
                descripcion: descripcionPorUuid[uuid]
            };
        });

        if (!rows.length) return Promise.resolve(true);

        var promises = rows.map(function(r) {
            return verificarStock(r.uuid, r.cantidad)
                .then(function(resp) { return { ok: true, resp: resp, row: r }; })
                .catch(function(xhr) { return { ok: false, xhr: xhr, row: r }; });
        });

        return Promise.all(promises).then(function(results) {
            var hasError = false;

            results.forEach(function(result) {
                var r = result.row;
                var resp = result.ok
                    ? result.resp
                    : (result.xhr && result.xhr.responseJSON ? result.xhr.responseJSON : null);

                if (resp && resp.status === 'OK') {
                    return;
                }

                hasError = true;
                cart.forEach(function(item) {
                    if (item.uuid === r.uuid) item.insufficient = true;
                });

                if (resp && resp.code === 'OUT_OF_STOCK_PRODUCT') {
                    toastr.error('Sin stock suficiente para ' + (resp.product && (resp.product.descripcion || resp.product.codigo) || r.descripcion));
                    pushAlertFromResp(resp, { requested: r.cantidad });
                } else if (resp && resp.code === 'PROMO_INSUFFICIENT_STOCK') {
                    toastr.error('Promoción con faltantes: ' + (resp.promotion && (resp.promotion.nombre || resp.promotion.codigo) || r.descripcion));
                    pushAlertFromResp(resp, { requested: r.cantidad });
                } else {
                    toastr.error('Error verificando stock para ' + r.descripcion);
                }
            });

            renderCart();
            return !hasError;
        });
    }

    function obtenerPrecioPorCantidad(uuid, cantidad, precioBase) {
        return $.ajax({
            url: '/ventas/precio-por-cantidad',
            method: 'POST',
            dataType: 'json',
            data: { _token: $('#token').val(), uuid: uuid, cantidad: cantidad, precio_base: precioBase }
        });
    }

    function actualizarPreciosPorRangoCarrito(callback) {
        if (!cart.length) {
            lastPriceSignature = '';
            if (typeof callback === 'function') callback();
            return;
        }

        var cantidadPorUuid = {};
        cart.forEach(function(item) {
            if (!item.uuid) return;
            cantidadPorUuid[item.uuid] = (cantidadPorUuid[item.uuid] || 0) + (parseFloat(item.quantity) || 0);
        });

        var uuids = Object.keys(cantidadPorUuid).sort();
        if (!uuids.length) { if (typeof callback === 'function') callback(); return; }

        var signature = uuids.map(function(u) {
            return u + ':' + (parseFloat(cantidadPorUuid[u]) || 0).toFixed(4);
        }).join('|');

        if (signature === lastPriceSignature) { if (typeof callback === 'function') callback(); return; }

        if (inFlightPricePromise && inFlightPriceSignature === signature) {
            inFlightPricePromise.finally(function() { if (typeof callback === 'function') callback(); });
            return;
        }

        var basePorUuid = {};
        cart.forEach(function(item) {
            if (!item.uuid) return;
            if (typeof basePorUuid[item.uuid] === 'undefined') basePorUuid[item.uuid] = parseFloat(item.precio_venta) || 0;
        });

        inFlightPriceSignature = signature;
        var promesas = uuids.map(function(uuid) {
            return obtenerPrecioPorCantidad(uuid, cantidadPorUuid[uuid], basePorUuid[uuid])
                .then(function(resp) {
                    if (resp && resp.status === 'OK' && typeof resp.precio_unitario !== 'undefined')
                        return { uuid: uuid, precio: parseFloat(resp.precio_unitario) };
                    return null;
                }).catch(function() { return null; });
        });

        var currentPromise = Promise.all(promesas).then(function(results) {
            if (inFlightPriceSignature !== signature) return;
            var nuevosPrecios = {};
            (results || []).forEach(function(row) {
                if (!row || typeof row.uuid === 'undefined' || isNaN(row.precio)) return;
                nuevosPrecios[row.uuid] = row.precio;
            });
            if (Object.keys(nuevosPrecios).length) {
                cart.forEach(function(item) {
                    if (typeof nuevosPrecios[item.uuid] !== 'undefined') item.precio_venta = nuevosPrecios[item.uuid];
                });
            }
            lastPriceSignature = signature;
        });

        inFlightPricePromise = currentPromise;
        currentPromise.finally(function() {
            if (inFlightPricePromise === currentPromise) { inFlightPricePromise = null; inFlightPriceSignature = ''; }
            if (typeof callback === 'function') callback();
        });
    }

    function actualizarPrecioPorRangoDeUuid(uuid, callback) {
        if (!uuid) { if (typeof callback === 'function') callback(); return; }
        var cantidadTotal = getCartTotalQuantity(uuid);
        if (cantidadTotal <= 0) { if (typeof callback === 'function') callback(); return; }
        var itemBase = cart.find(function(item) { return item.uuid === uuid; });
        var precioBase = itemBase ? (parseFloat(itemBase.precio_venta) || 0) : 0;
        obtenerPrecioPorCantidad(uuid, cantidadTotal, precioBase)
            .done(function(resp) {
                if (resp && resp.status === 'OK' && typeof resp.precio_unitario !== 'undefined') {
                    var nuevoPrecio = parseFloat(resp.precio_unitario);
                    if (!isNaN(nuevoPrecio)) {
                        cart.forEach(function(item) {
                            if (item.uuid === uuid) item.precio_venta = nuevoPrecio;
                        });
                    }
                }
            })
            .always(function() { if (typeof callback === 'function') callback(); });
    }

    function updateTotal() {
        var total = cart.reduce(function(sum, item) {
            var discount = parseFloat(item.discount) || 0;
            return sum + (item.precio_venta * item.quantity * (1 - discount / 100));
        }, 0);
        var totalDiscount = cart.reduce(function(sum, item) {
            var discount = parseFloat(item.discount) || 0;
            return sum + (item.precio_venta * item.quantity * (discount / 100));
        }, 0);
        $('#cart-total').text(formatCurrency(Math.round(total)));
        $('#discount-amount').text(formatCurrency(Math.round(totalDiscount)));
        var anyInsufficient = cart.some(function(it) { return it.insufficient === true; });
        $('#pay-btn').prop('disabled', anyInsufficient);
    }

    function renderCart() {
        var cartHtml = cart.map(function(item, index) {
            var discount = parseFloat(item.discount) || 0;
            var lineTotal = Math.round(item.precio_venta * item.quantity * (1 - discount / 100));
            var rowStyle = item.insufficient ? 'background-color:#ffe6e6;' : '';
            var insuffBadge = item.insufficient ? '<span class="badge badge-danger" style="margin-left:8px;">Faltante</span>' : '';
            var photoBtn = item.imagen
                ? '<button type="button" class="btn btn-link p-0 view-photo-btn" data-index="' + index + '" title="Ver foto" style="margin-left:8px;color:#007bff;vertical-align:middle;"><i class="fa fa-camera"></i></button>'
                : '';
            return '<div class="product-row" data-id="' + item.uuid + '" data-index="' + index + '" style="' + rowStyle + '">' +
                '<div class="quantity-controls">' +
                '<button class="quantity-btn minus-btn">-</button>' +
                '<input type="text" class="quantity-input" value="' + item.quantity + '">' +
                '<button class="quantity-btn plus-btn">+</button>' +
                '</div>' +
                '<div class="product-info">' +
                '<div class="product-name">' + item.descripcion + photoBtn + '</div>' +
                '<div class="product-price">$/unidad: ' + formatCurrency(item.precio_venta) + ' ' + insuffBadge + '</div>' +
                '</div>' +
                '<div class="product-total">' + formatCurrency(lineTotal) + '</div>' +
                '<select class="discount-select">' +
                '<option value="0" ' + (discount === 0 ? 'selected' : '') + '>0 %</option>' +
                '<option value="5" ' + (discount === 5 ? 'selected' : '') + '>5 %</option>' +
                '<option value="10" ' + (discount === 10 ? 'selected' : '') + '>10 %</option>' +
                '<option value="15" ' + (discount === 15 ? 'selected' : '') + '>15 %</option>' +
                '</select>' +
                '<button class="delete-btn"><i class="fa fa-trash"></i></button>' +
                '</div>';
        }).join('');
        $('#cart-items').html(cartHtml);
        updateTotal();
    }

    // ── Búsqueda por código ──────────────────────────────────────────────────
    $('#product-code').off('keypress' + EVENT_NS).on('keypress' + EVENT_NS, function(e) {
        if (e.which !== 13) return;
        e.preventDefault();
        if (addByCodeInProgress) return;

        var input = $(this);
        var inputValue = input.val().trim();
        if (!inputValue) return;

        var quantity = 1;
        var code = inputValue;

        if (inputValue.includes('*')) {
            var parts = inputValue.split('*');
            if (parts.length === 2) {
                var parsedQty = parseFloat(parts[0]);
                if (!isNaN(parsedQty) && parsedQty > 0) {
                    quantity = parsedQty;
                    code = parts[1].trim();
                }
            }
        }

        var cachedProduct = productByCodeCache.get(code);
        if (cachedProduct) {
            cart.push({ uuid: cachedProduct.uuid, descripcion: cachedProduct.descripcion, precio_venta: cachedProduct.precio_venta, imagen: cachedProduct.imagen || '', quantity: quantity, discount: 0, insufficient: false });
            renderCart();
            actualizarPrecioPorRangoDeUuid(cachedProduct.uuid, function() { renderCart(); });
            input.val('');
            addByCodeInProgress = false;
            return;
        }

        addByCodeInProgress = true;
        $.ajax({
            url: '/ventas/buscarProducto',
            method: 'GET',
            data: { q: code, tipo: 1 },
            success: function(product) {
                if (product.length > 0) {
                    productByCodeCache.set(code, product[0]);
                    cart.push({ uuid: product[0].uuid, descripcion: product[0].descripcion, precio_venta: product[0].precio_venta, imagen: product[0].imagen || '', quantity: quantity, discount: 0, insufficient: false });
                    renderCart();
                    actualizarPrecioPorRangoDeUuid(product[0].uuid, function() { renderCart(); });
                    input.val('');
                    addByCodeInProgress = false;
                } else {
                    toastr.error('Producto no existe');
                    addByCodeInProgress = false;
                }
            },
            error: function() {
                toastr.error('Error buscando producto');
                addByCodeInProgress = false;
            }
        });
    });

    // ── Controles de cantidad ────────────────────────────────────────────────
    $(document).off('click' + EVENT_NS, '.plus-btn').on('click' + EVENT_NS, '.plus-btn', function() {
        var index = $(this).closest('.product-row').data('index');
        cart[index].quantity = cart[index].quantity + 1;
        cart[index].insufficient = false;
        removeAlertByUuid(cart[index].uuid);
        actualizarPrecioPorRangoDeUuid(cart[index].uuid, function() { renderCart(); });
    });

    $(document).off('click' + EVENT_NS, '.minus-btn').on('click' + EVENT_NS, '.minus-btn', function() {
        var index = $(this).closest('.product-row').data('index');
        if (cart[index].quantity > 1) {
            cart[index].quantity--;
            cart[index].insufficient = false;
            removeAlertByUuid(cart[index].uuid);
            actualizarPrecioPorRangoDeUuid(cart[index].uuid, function() { renderCart(); });
        }
    });

    $('#cart-items').off('input' + EVENT_NS, '.quantity-input').on('input' + EVENT_NS, '.quantity-input', function() {
        var index = $(this).closest('.product-row').data('index');
        var newQuantity = parseFloat($(this).val());
        if (isNaN(newQuantity) || newQuantity <= 0) return;

        clearTimeout(quantityInputTimers[index]);
        quantityInputTimers[index] = setTimeout(function() {
            if (!cart[index]) return;
            cart[index].quantity = newQuantity;
            cart[index].insufficient = false;
            removeAlertByUuid(cart[index].uuid);
            actualizarPrecioPorRangoDeUuid(cart[index].uuid, function() { renderCart(); });
        }, 180);
    });

    $('#cart-items').off('blur' + EVENT_NS, '.quantity-input').on('blur' + EVENT_NS, '.quantity-input', function() {
        var val = parseFloat($(this).val());
        if (isNaN(val) || val <= 0) {
            $(this).val('1');
            var index = $(this).closest('.product-row').data('index');
            cart[index].quantity = 1;
            actualizarPrecioPorRangoDeUuid(cart[index].uuid, function() { renderCart(); });
        }
    });

    $(document).on('change', '.discount-select', function() {
        var $row = $(this).closest('.product-row');
        var index = parseInt($row.data('index'));
        var uuid = $row.data('id');
        
        // Doble validación: por índice Y por UUID
        if (index >= 0 && index < cart.length && cart[index] && cart[index].uuid === uuid) {
            cart[index].discount = parseInt($(this).val()) || 0;
        } else {
            // Fallback: buscar por UUID si el índice no funciona
            var foundIndex = cart.findIndex(function(item) { return item.uuid === uuid; });
            if (foundIndex !== -1) {
                cart[foundIndex].discount = parseInt($(this).val()) || 0;
            } else {
                console.warn('Error: No se pudo encontrar el producto en el carrito', { index: index, uuid: uuid });
                renderCart(); // Refrescar carrito para sincronizar
                return;
            }
        }
        renderCart();
    });

    $(document).on('click', '.delete-btn', function() {
        var index = $(this).closest('.product-row').data('index');
        var item = cart[index];
        if (item && item.uuid) removeAlertByUuid(item.uuid);
        var uuidAfectado = item ? item.uuid : null;
        cart.splice(index, 1);
        actualizarPrecioPorRangoDeUuid(uuidAfectado, function() { renderCart(); });
    });

    // ── Búsqueda de productos ────────────────────────────────────────────────
    var productSearchTimer = null;
    var productSearchXhr = null;

    $('#product-search').on('keyup', function() {
        var query = $(this).val().toLowerCase();
        clearTimeout(productSearchTimer);
        if (productSearchXhr) { productSearchXhr.abort(); productSearchXhr = null; }
        if (!query) { $('#product-list').html(''); return; }
        productSearchTimer = setTimeout(function() {
            productSearchXhr = $.ajax({
                url: '/ventas/buscarProducto',
                method: 'GET',
                data: { q: query, tipo: 2 },
                success: function(products) {
                    if (!Array.isArray(products)) { $('#product-list').html(''); return; }
                    var html = products.map(function(p) {
                        return '<div class="product-item">' +
                            '<span>' + p.descripcion + '</span>' +
                            '<div class="product-actions">' +
                            '<i class="fa fa-plus action-icon add-to-cart" data-uuid="' + p.uuid + '" data-code="' + (p.codigo || '') + '" data-name="' + p.descripcion + '" data-price="' + p.precio_venta + '" data-image="' + (p.imagen || '') + '"></i>' +
                            '</div></div>';
                    }).join('');
                    $('#product-list').html(html);
                }
            });
        }, 300);
    });

    $(document).off('click' + EVENT_NS, '.add-to-cart').on('click' + EVENT_NS, '.add-to-cart', function() {
        var product = {
            uuid: $(this).data('uuid'),
            codigo: $(this).data('code'),
            descripcion: $(this).data('name'),
            precio_venta: $(this).data('price'),
            imagen: $(this).data('image'),
            quantity: 1,
            discount: 0
        };
        var existingQty = getCartTotalQuantity(product.uuid);
        if (existingQty >= 0) {
            product.insufficient = false;
            cart.push(product);
            renderCart();
            actualizarPrecioPorRangoDeUuid(product.uuid, function() { renderCart(); });
        }
    });

    $(document).off('click' + EVENT_NS, '.view-photo-btn').on('click' + EVENT_NS, '.view-photo-btn', function() {
        var index = $(this).data('index');
        var item = cart[index];
        if (!item || !item.imagen) { toastr.info('Este producto no tiene foto'); return; }
        showProductPreview(item);
    });

    // ── Cancelar ─────────────────────────────────────────────────────────────
    $('#cancel-btn').click(function() {
        Swal.fire({
            title: 'Cancelar',
            text: '¿Está seguro de cancelar la preventa?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Sí',
            cancelButtonText: 'No'
        }).then(function(result) {
            if (result.isConfirmed) {
                cart = [];
                alerts = [];
                renderCart();
                renderAlerts();
            }
        });
    });

    // ── Guardar borrador ──────────────────────────────────────────────────────
    $('#save-draft-btn').click(function() {
        if (cart.length === 0) {
            toastr.warning('El carrito está vacío. Agrega productos antes de guardar el borrador.');
            return;
        }
        var uuid_borrador = crypto.randomUUID();
        var now = new Date();
        var localTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
        var fecha = localTime.toISOString().slice(0, 19).replace('T', ' ');

        var productos = cart.map(function(item) {
            return {
                product_uuid: item.uuid,
                descripcion: item.descripcion,
                precio_venta: item.precio_venta,
                cantidad: item.quantity,
                descuento: item.discount,
                uuid_borrador: uuid_borrador,
                fec_creacion: fecha
            };
        });

        $.ajax({
            url: '/ventas/guardar-borrador',
            method: 'POST',
            data: { _token: $('#token').val(), productos: productos },
            success: function(response) {
                if (response.status === 'OK') {
                    toastr.success(response.message);
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

    // ── Botón Generar Preventa ────────────────────────────────────────────────
    $('#pay-btn').click(function() {
        if (cart.length === 0) {
            toastr.warning('Agregue productos al carrito para continuar');
            return;
        }

        var $btn = $('#pay-btn');
        $btn.prop('disabled', true).text('Validando stock...');

        validarStockCarritoParaPreventa().then(function(ok) {
            if (!ok) {
                toastr.error('No puede generar preventa: hay productos con stock insuficiente');
                return;
            }
            generarPreventa();
        }).catch(function() {
            toastr.error('Error al validar stock antes de generar la preventa');
        }).finally(function() {
            if ($btn.text() === 'Validando stock...') {
                $btn.prop('disabled', false).text('Generar Preventa');
            }
        });
    });

    function generarPreventa() {
        var totalVenta      = parseInt($('#cart-total').text().replace(/[^\d]/g, ''));
        var totalDescuentos = parseInt($('#discount-amount').text().replace(/[^\d]/g, ''));

        var detalles = cart.map(function(item) {
            var cantidad           = parseFloat(item.quantity);
            var precioUnitario     = parseInt(item.precio_venta);
            var descuentoPorcentaje = parseFloat(item.discount) || 0;
            var subtotalLinea      = Math.round(cantidad * precioUnitario * (1 - descuentoPorcentaje / 100));
            return {
                producto_uuid:       item.uuid,
                descripcion_producto: item.descripcion,
                cantidad:            cantidad,
                precio_unitario:     precioUnitario,
                descuento_porcentaje: descuentoPorcentaje,
                subtotal_linea:      subtotalLinea
            };
        });

        $.ajax({
            url: '/ventas/procesar-preventa',
            method: 'POST',
            data: {
                _token:           $('#token').val(),
                total:            totalVenta,
                total_descuentos: totalDescuentos,
                detalles:         detalles
            },
            beforeSend: function() {
                $('#pay-btn').prop('disabled', true).text('Generando preventa...');
            },
            success: function(response) {
                if (response.status === 'OK') {
                    // Mostrar ticket de preventa en modal
                    if (response.venta_id) {
                        var ticketUrl = '/ventas/ticket-preventa-pdf/' + response.venta_id;
                        $('#ticketPreventaFrame').attr('src', ticketUrl);
                        $('#modalTicketPreventa').modal('show');
                    }

                    cart = [];
                    alerts = [];
                    renderCart();
                    renderAlerts();
                    cargarPreventasPendientes();

                    toastr.info('Preventa Nº: ' + response.numero_preventa, 'Preventa generada', { timeOut: 5000 });
                } else {
                    toastr.error(response.message || 'Error al generar la preventa');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Error al generar la preventa';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    if (xhr.responseJSON.errors) {
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            toastr.error(xhr.responseJSON.errors[key][0]);
                        });
                        return;
                    }
                }
                toastr.error(errorMsg);
            },
            complete: function() {
                $('#pay-btn').prop('disabled', false).text('Generar Preventa');
            }
        });
    }

    // ── Tabs con carga lazy ──────────────────────────────────────────────────
    $('.tab-btn').on('click', function() {
        var tab = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
        
        // Carga lazy: solo cargar cuando se hace click en la pestaña
        if (tab === 'preventas-pendientes') {
            cargarPreventasPendientes();
        }
    });

    // ── Borradores ────────────────────────────────────────────────────────────
    $(document).on('click', '.ver-borrador', function() {
        var uuid = $(this).data('uuid');
        $.ajax({
            url: '/ventas/borrador/' + uuid + '/productos',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var tbody = '';
                data.forEach(function(item) {
                    tbody += '<tr uuid="' + item.producto_uuid + '">' +
                        '<td>' + item.cantidad + '</td>' +
                        '<td>' + item.producto + '</td>' +
                        '<td>' + item.precio_venta + '</td>' +
                        '<td>' + item.descuento + '</td>' +
                        '</tr>';
                });
                $('#detalle-borrador-body').html(tbody);
                $('#uuid_borrador').val(uuid);
                $('#modalDetalleBorrador').modal('show');
            }
        });
    });

    $('#btnCargarVenta').off('click' + EVENT_NS).on('click' + EVENT_NS, function() {
        var rows = [];
        $('#detalle-borrador-body tr').each(function() {
            rows.push({
                uuid:        $(this).attr('uuid'),
                cantidad:    parseFloat($(this).find('td:eq(0)').text()),
                descripcion: $(this).find('td:eq(1)').text(),
                precio_venta: parseFloat($(this).find('td:eq(2)').text()),
                descuento:   parseInt($(this).find('td:eq(3)').text())
            });
        });

        if (rows.length === 0) { $('#modalDetalleBorrador').modal('hide'); return; }

        Promise.resolve().then(function() {
            rows.forEach(function(r) {
                cart.push({ uuid: r.uuid, descripcion: r.descripcion, precio_venta: r.precio_venta, quantity: r.cantidad, discount: r.descuento, insufficient: false });
            });
            actualizarPreciosPorRangoCarrito(function() { renderCart(); });
            $('#modalDetalleBorrador').modal('hide');
            eliminarBorrador($('#uuid_borrador').val());
        }).catch(function() {
            toastr.error('Error al cargar borrador en el carrito');
        });
    });

    $(document).on('click', '.eliminar-borrador', function() {
        eliminarBorrador($(this).data('uuid'));
    });

    $(document).on('click', '.ver-ticket-preventa', function() {
        var ventaId = $(this).data('id');
        if (!ventaId) {
            toastr.warning('No se pudo identificar la preventa');
            return;
        }
        $('#ticketPreventaFrame').attr('src', '/ventas/ticket-preventa-pdf/' + ventaId);
        $('#modalTicketPreventa').modal('show');
    });
});

// ── Funciones globales (borradores) ─────────────────────────────────────────
function cargarBorradores() {
    $.ajax({
        url: '/ventas/traer-borradores',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            var tablaHtml = '<table class="table"><thead><tr>' +
                '<th>N° Productos</th><th>Total venta</th><th>Fecha venta</th><th></th>' +
                '</tr></thead><tbody>';
            data.forEach(function(item) {
                tablaHtml += '<tr>' +
                    '<td style="text-align:center;">' + item.total_cantidad + '</td>' +
                    '<td style="text-align:center;">' + item.total + '</td>' +
                    '<td>' + item.fecha_creacion + '</td>' +
                    '<td style="white-space:nowrap;">' +
                    '<button class="btn btn-sm btn-primary ver-borrador" data-uuid="' + item.uuid_borrador + '" style="margin-right:6px"><i class="fa fa-eye"></i></button>' +
                    '<button class="btn btn-sm btn-danger eliminar-borrador" data-uuid="' + item.uuid_borrador + '"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
            });
            tablaHtml += '</tbody></table>';
            $('#tab-borradores').html(tablaHtml);
        }
    });
}

function eliminarBorrador(uuid_borrador) {
    $.ajax({
        url: '/ventas/eliminar-borrador/' + uuid_borrador,
        method: 'DELETE',
        data: { _token: $('#token').val() },
        success: function(response) {
            if (response.status === 'OK') {
                cargarBorradores();
            } else {
                toastr.error('Error al eliminar el borrador: ' + response.message);
            }
        },
        error: function() { toastr.error('Error al eliminar el borrador'); }
    });
}

function cargarPreventasPendientes() {
    $.ajax({
        url: '/ventas/preventas-pendientes',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            var data = response && response.preventas ? response.preventas : [];
            var usuario = response && response.usuario_actual ? response.usuario_actual : 'Usuario';

            if (!data.length) {
                $('#tab-preventas-pendientes').html(
                    '<div class="text-muted" style="padding:12px;">' +
                    '<div style="text-align:center;margin-bottom:10px;font-weight:600;color:#666">' +
                    '<i class="fa fa-user"></i> Mis Preventas Pendientes' +
                    '</div>' +
                    'No tienes preventas pendientes.' +
                    '</div>'
                );
                return;
            }

            var tablaHtml = '<div style="margin-bottom:10px;padding:8px;background:#f8f9fa;border-radius:4px;text-align:center;">' +
                '<i class="fa fa-user" style="color:#3bb3e0;margin-right:5px"></i>' +
                '<span style="font-weight:600;color:#495057">Mis Preventas Pendientes (' + data.length + ')</span>' +
                '</div>' +
                '<table class="table"><thead><tr>' +
                '<th>N° Ticket</th><th>Monto</th><th></th>' +
                '</tr></thead><tbody>';

            data.forEach(function(item) {
                var monto = '$ ' + new Intl.NumberFormat('es-CL').format(parseInt(item.total || 0));
                tablaHtml += '<tr>' +
                    '<td style="text-align:center;">' + item.numero_preventa + '</td>' +
                    '<td style="text-align:center;">' + monto + '</td>' +
                    '<td style="white-space:nowrap; text-align:center;">' +
                    '<button class="btn btn-sm btn-primary ver-ticket-preventa" data-id="' + item.venta_id + '" title="Ver ticket PDF"><i class="fa fa-eye"></i></button>' +
                    '</td></tr>';
            });

            tablaHtml += '</tbody></table>';
            $('#tab-preventas-pendientes').html(tablaHtml);
        },
        error: function() {
            $('#tab-preventas-pendientes').html('<div class="text-danger" style="padding:12px;">Error al cargar preventas pendientes.</div>');
        }
    });
}
