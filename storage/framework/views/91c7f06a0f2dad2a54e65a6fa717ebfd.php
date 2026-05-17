<script type="text/javascript" src="/js/restaurant/comandas.js"></script>
<link rel="stylesheet" href="/css/restaurant/comandas.css">

<div class='row'>
  <input type="hidden" name="_token" id="token" value="<?php echo e(csrf_token()); ?>">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fa fa-utensils"></i> Atención de Mesas</h2>
                <div class="text-center">
                    <div class="contador-comensales">
                        <i class="fa fa-users"></i> Comensales en Restaurant: 
                        <span id="total_comensales" class="badge badge-info" style="font-size: 18px;">0</span>
                    </div>
                </div>
                <div class="mesas-header-panel">
                    <div class="mesas-estado-badges">
                        <span class="badge mr-2 badge-estado-libre"><i class="fa fa-circle"></i> Libre: <span id="total_mesas_libres">0</span></span>
                        <span class="badge mr-2 badge-estado-reservada"><i class="fa fa-circle"></i> Reservada: <span id="total_mesas_reservadas">0</span></span>
                        <span class="badge mr-2 badge-estado-ocupada"><i class="fa fa-circle"></i> Ocupada: <span id="total_mesas_ocupadas">0</span></span>
                        <span class="badge mr-2 badge-estado-pendiente"><i class="fa fa-circle"></i> Pendientes pago: <span id="total_mesas_pendientes_pago">0</span></span>
                    </div>
                    <div class="mesas-header-actions">
                        <button class="btn btn-info" id="btn_ver_plano_mesas" style="margin-right:6px;">
                            <i class="fa fa-map"></i> Ver plano de mesas
                        </button>
                        <button class="btn btn-warning" id="btn_abrir_cambio_mesa" style="margin-right:6px;">
                            <i class="fa fa-random"></i> Cambiar mesa
                        </button>
                        <button class="btn btn-primary" id="refrescar_mesas">
                            <i class="fa fa-sync"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <div class="mesas-container-wrapper">
            <div id="mesas-container">
                <!-- Las mesas se cargarán aquí dinámicamente -->
            </div>
        </div>
      </div>
    </div>
</div>

<!-- El modal anterior de detalle de comanda se eliminó, ahora se usa el modal POS para ver/editar --><!-- Modal POS para tomar pedido -->
<div class="modal fade" id="modalTomarPedido" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="pos-header">
                <div class="pos-header-row">
                    <div class="pos-mesa-info">
                        <h2><i class="fa fa-utensils"></i> <span id="pos_mesa_nombre">Mesa</span></h2>
                        <div class="pos-hora-badge">
                            <i class="fa fa-clock"></i> Apertura: <span id="pos_hora_inicio"></span>
                        </div>
                    </div>
                    
                    <div class="pos-controles-grupo">
                        <div class="pos-garzon-control">
                            <div class="pos-control-label">
                                <i class="fa fa-user-tie"></i> Garzón
                            </div>
                            <select id="pos_garzon_id" class="pos-select-garzon">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                    </div>

                    <div class="pos-header-actions">
                        <button type="button" class="pos-btn-solicitar-cuenta" id="btn_solicitar_cuenta">
                            <i class="fa fa-file-invoice-dollar"></i> Solicitar cuenta
                        </button>

                        <button type="button" class="pos-btn-cerrar" id="pos_btn_cerrar">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs de navegación: solo visibles en móvil/tablet (display controlado por CSS) -->
            <div class="pos-mobile-tabs">
                <button type="button" class="pos-tab-btn active" id="pos-tab-productos">
                    <i class="fa fa-th-large"></i> Productos
                </button>
                <button type="button" class="pos-tab-btn" id="pos-tab-pedido">
                    <i class="fa fa-shopping-cart"></i> Pedido
                    <span class="pos-tab-order-badge" id="pos-tab-order-badge"></span>
                </button>
            </div>

            <div class="pos-body">
                <!-- Panel izquierdo: Productos -->
                <div class="pos-products-panel">
                    <div class="pos-search-box">
                        <input type="text" id="pos_buscar_producto" class="pos-search-input" placeholder="Buscar por nombre o código..." autocomplete="off">
                    </div>
                    <div class="pos-products-grid" id="pos_products_grid">
                        <!-- Productos se cargarán aquí dinámicamente -->
                    </div>
                </div>

                <!-- Panel derecho: Pedido actual -->
                <div class="pos-order-panel">
                    <div class="pos-order-items" id="pos_order_items">
                        <!-- Items se cargarán aquí -->
                    </div>
                </div>

                <div class="pos-order-footer">
                    <div class="pos-footer-top-row">
                        <div class="pos-obs-comanda-wrap">
                            <label class="pos-obs-comanda-label"><i class="fa fa-sticky-note-o"></i> Nota del pedido</label>
                            <input type="text" id="pos_obs_comanda" class="pos-obs-comanda-input"
                                placeholder="Ej: alérgico a mariscos, silla para bebé..."
                                maxlength="300">
                        </div>

                        <div class="pos-propina-section">
                            <label class="pos-propina-checkbox">
                                <input type="checkbox" id="pos_incluye_propina">
                                <span>Incluir propina <?php echo e(rtrim(rtrim(number_format($porcentajePropina ?? 10, 2, '.', ''), '0'), '.')); ?>%</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="pos-summary-split">
                        <div class="pos-comensales-summary">
                            <div class="pos-control-label">
                                <i class="fa fa-users"></i> Comensales
                            </div>
                            <div class="pos-comensales-controls">
                                <button type="button" class="pos-btn-comensales" id="pos_btn_menos_comensales">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <span class="pos-comensales-numero" id="pos_comensales_numero">0</span>
                                <button type="button" class="pos-btn-comensales" id="pos_btn_mas_comensales">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pos-total-section">
                            <div class="pos-total-row">
                                <span class="pos-total-label">Subtotal:</span>
                                <span class="pos-total-value">$<span id="pos_subtotal">0</span></span>
                            </div>
                            <div class="pos-total-row" id="pos_propina_row" style="display:none;">
                                <span class="pos-total-label">Propina (<?php echo e(rtrim(rtrim(number_format($porcentajePropina ?? 10, 2, '.', ''), '0'), '.')); ?>%):</span>
                                <span class="pos-total-value">$<span id="pos_propina">0</span></span>
                            </div>
                            <div class="pos-total-row total-final">
                                <span class="pos-total-label total-final-label">TOTAL:</span>
                                <span class="pos-total-value total-final-value">$<span id="pos_total">0</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="pos-action-buttons">
                        <button type="button" class="pos-btn pos-btn-save" id="btn_guardar_pedido"
                            title="Guardar pedido">
                            <i class="fa fa-floppy-o"></i>
                        </button>
                        <button type="button" class="pos-btn pos-btn-print" id="btn_imprimir_comanda" disabled
                            title="Imprimir comanda">
                            <i class="fa fa-print"></i>
                        </button>
                        <?php if((int)($impresionSeparada ?? 0) === 1): ?>
                            <button type="button" class="pos-btn pos-btn-cocina" id="btn_ticket_cocina" disabled
                                title="Imprimir ticket de cocina">
                                <i class="fa fa-cutlery"></i>
                            </button>
                            <button type="button" class="pos-btn pos-btn-cocina" id="btn_ticket_barra" disabled
                                title="Imprimir ticket de barra">
                                <i class="fa fa-glass"></i>
                            </button>
                            <button type="button" class="pos-btn pos-btn-cocina" id="btn_ticket_ambos" disabled
                                title="Imprimir tickets de cocina y barra">
                                <i class="fa fa-clone"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="pos-btn pos-btn-cocina" id="btn_ticket_cocina" disabled
                                title="Imprimir ticket de preparacion">
                                <i class="fa fa-bell"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="pos_mesa_id" value="">
<input type="hidden" id="pos_comanda_id" value="">
<input type="hidden" id="pos_capacidad_original" value="">

<div class="modal fade" id="modalCambioMesa" tabindex="-1" role="dialog" aria-labelledby="tituloCambioMesa" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tituloCambioMesa"><i class="fa fa-random"></i> Cambiar comanda de mesa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="cambio_mesa_desde">Mesa desde</label>
                    <select id="cambio_mesa_desde" class="form-control">
                        <option value="">Seleccionar mesa origen...</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="cambio_mesa_hacia">Mesa hacia</label>
                    <select id="cambio_mesa_hacia" class="form-control">
                        <option value="">Seleccionar mesa destino...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn_confirmar_cambio_mesa">
                    <i class="fa fa-check"></i> Confirmar cambio
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTicketComanda" tabindex="-1" aria-labelledby="tituloTicketComanda" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header" style="display:flex; align-items:center; justify-content:space-between;">
                <h4 class="modal-title" id="tituloTicketComanda">Ticket Comanda</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top:0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="ticketFrameComanda" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTicketCocina" tabindex="-1" aria-labelledby="tituloTicketCocina" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header" style="display:flex; align-items:center; justify-content:space-between;">
                <h4 class="modal-title" id="tituloTicketCocina"><i class="fa fa-cutlery"></i> Ticket Cocina</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top:0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="ticketFrameCocina" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTicketBarra" tabindex="-1" aria-labelledby="tituloTicketBarra" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header" style="display:flex; align-items:center; justify-content:space-between;">
                <h4 class="modal-title" id="tituloTicketBarra"><i class="fa fa-glass"></i> Ticket Barra</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top:0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="ticketFrameBarra" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTicketAmbos" tabindex="-1" aria-labelledby="tituloTicketAmbos" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 860px;">
        <div class="modal-content">
            <div class="modal-header" style="display:flex; align-items:center; justify-content:space-between;">
                <h4 class="modal-title" id="tituloTicketAmbos"><i class="fa fa-clone"></i> Tickets Cocina y Barra</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top:0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="display:flex; gap:10px; padding: 8px;">
                <div style="flex:1; min-width:0; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden;">
                    <div style="padding:6px 8px; font-weight:700; border-bottom:1px solid #e5e7eb;"><i class="fa fa-cutlery"></i> Cocina</div>
                    <iframe id="ticketFrameAmbosCocina" style="width: 100%; height: 600px; border: none;"></iframe>
                </div>
                <div style="flex:1; min-width:0; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden;">
                    <div style="padding:6px 8px; font-weight:700; border-bottom:1px solid #e5e7eb;"><i class="fa fa-glass"></i> Barra</div>
                    <iframe id="ticketFrameAmbosBarra" style="width: 100%; height: 600px; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPlanoMesas" tabindex="-1" role="dialog" aria-labelledby="tituloPlanoMesas" aria-hidden="true">
    <div class="modal-dialog modal-lg plano-mesas-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tituloPlanoMesas"><i class="fa fa-map"></i> Plano de Mesas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body plano-mesas-body">
                <div class="plano-mesas-toolbar">
                    <div id="layout_text_summary" style="font-weight:600;color:#374151;">Mesas: 0 | Textos: 0</div>
                    <div class="plano-mesas-actions">
                        <button type="button" class="btn btn-info" id="btn_agregar_texto_layout">
                            <i class="fa fa-font"></i> Agregar texto
                        </button>
                        <button type="button" class="btn btn-default" id="btn_agregar_linea_layout" title="Agrega una línea divisoria horizontal o vertical al plano">
                            <i class="fa fa-minus"></i> Agregar línea
                        </button>
                        <button type="button" class="btn btn-warning" id="btn_uniformizar_mesas_layout" title="Iguala el ancho y alto de todas las mesas al tamaño de la mesa seleccionada o al promedio">
                            <i class="fa fa-expand"></i> Uniformizar tamaños
                        </button>
                        <button type="button" class="btn btn-success" id="btn_guardar_layout_json">
                            <i class="fa fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </div>
                <div class="plano-mesas-hint">Arrastra mesas, textos y líneas. Doble clic en texto o línea para editar.</div>
                <div id="layout_preview_canvas"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTextoLayout" tabindex="-1" role="dialog" aria-labelledby="tituloTextoLayout" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 460px; margin: 1.25rem auto;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tituloTextoLayout"><i class="fa fa-font"></i> Texto del plano</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="layout_text_edit_index" value="">
                <div class="form-group" style="margin-bottom: 12px;">
                    <label for="layout_text_input" style="font-weight: 600;">Texto</label>
                    <input type="text" class="form-control" id="layout_text_input" maxlength="80" placeholder="Ej: Caja, Baño, Entrada" autocomplete="off">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="layout_text_bg_color" style="font-weight: 600; display:block;">Color de fondo</label>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <input type="color" id="layout_text_bg_color" value="#fff7d6" style="width: 56px; height: 38px; border: 1px solid #d1d5db; border-radius: 6px; padding: 2px; cursor: pointer;">
                        <div id="layout_text_color_palette" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <button type="button" class="layout-color-swatch" data-color="#fff7d6" title="Amarillo suave" style="width:22px;height:22px;border-radius:50%;border:1px solid #d1d5db;background:#fff7d6;padding:0;"></button>
                            <button type="button" class="layout-color-swatch" data-color="#dbeafe" title="Celeste" style="width:22px;height:22px;border-radius:50%;border:1px solid #d1d5db;background:#dbeafe;padding:0;"></button>
                            <button type="button" class="layout-color-swatch" data-color="#dcfce7" title="Verde suave" style="width:22px;height:22px;border-radius:50%;border:1px solid #d1d5db;background:#dcfce7;padding:0;"></button>
                            <button type="button" class="layout-color-swatch" data-color="#ffe4e6" title="Rosa" style="width:22px;height:22px;border-radius:50%;border:1px solid #d1d5db;background:#ffe4e6;padding:0;"></button>
                            <button type="button" class="layout-color-swatch" data-color="#ede9fe" title="Lila" style="width:22px;height:22px;border-radius:50%;border:1px solid #d1d5db;background:#ede9fe;padding:0;"></button>
                            <button type="button" class="layout-color-swatch" data-color="#e5e7eb" title="Gris" style="width:22px;height:22px;border-radius:50%;border:1px solid #d1d5db;background:#e5e7eb;padding:0;"></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn_guardar_texto_layout_modal">
                    <i class="fa fa-check"></i> Guardar texto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.posPropinaPorcentaje = <?php echo json_encode((float)($porcentajePropina ?? 10), 15, 512) ?>;
    window.posImpresionSeparada = <?php echo json_encode((int)($impresionSeparada ?? 0), 15, 512) ?>;
</script>

<script>
(function () {
    // Expuesta globalmente para que comandas.js pueda llamarla directamente
    window.actualizarTabBadge = function () {
        var items = document.querySelectorAll('#pos_order_items .pos-order-item');
        var badge = document.getElementById('pos-tab-order-badge');
        if (!badge) return;
        var total = 0;
        items.forEach(function (item) {
            var qty = item.querySelector('.pos-qty-number');
            total += qty ? (parseInt(qty.textContent) || 1) : 1;
        });
        if (total > 0) {
            badge.textContent = total;
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    };

    // Observar cambios en el panel de orden (nuevos items, cambios de cantidad)
    $(function () {
        var orderItems = document.getElementById('pos_order_items');
        if (orderItems && window.MutationObserver) {
            new MutationObserver(function () {
                // setTimeout asegura que el DOM ya terminó de actualizarse
                setTimeout(window.actualizarTabBadge, 50);
            }).observe(orderItems, { childList: true, subtree: true, characterData: true });
        }
    });

    // Control de tabs (solo activo en móvil)
    document.addEventListener('click', function (e) {
        var btnProductos = e.target.closest('#pos-tab-productos');
        var btnPedido    = e.target.closest('#pos-tab-pedido');
        var posBody      = document.querySelector('#modalTomarPedido .pos-body');
        if (!posBody) return;

        if (btnProductos) {
            posBody.classList.remove('show-order');
            document.getElementById('pos-tab-productos').classList.add('active');
            document.getElementById('pos-tab-pedido').classList.remove('active');
        }
        if (btnPedido) {
            posBody.classList.add('show-order');
            document.getElementById('pos-tab-pedido').classList.add('active');
            document.getElementById('pos-tab-productos').classList.remove('active');
        }
    });

    // Resetear al tab de productos cada vez que se abre el modal POS
    $('#modalTomarPedido').on('show.bs.modal', function () {
        var posBody = $(this).find('.pos-body');
        posBody.removeClass('show-order');
        $('#pos-tab-productos').addClass('active');
        $('#pos-tab-pedido').removeClass('active');
        window.actualizarTabBadge();
    });
}());
</script>

<?php echo $__env->make('restaurant.partials.modal_anular_producto_comanda', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('partials.modal_ayuda', ['modulo' => 'comandas'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>



<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/restaurant/comandas.blade.php ENDPATH**/ ?>