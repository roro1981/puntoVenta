<script type="text/javascript" src="/js/restaurant/comandas.js"></script>
<link rel="stylesheet" href="/css/restaurant/comandas.css">

<div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
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
                <div>
                    <span class="badge badge-success mr-2"><i class="fa fa-circle"></i> Libre</span>
                    <span class="badge badge-danger mr-2"><i class="fa fa-circle"></i> Ocupada</span>
                    <button class="btn btn-primary" id="refrescar_mesas">
                        <i class="fa fa-sync"></i> Actualizar
                    </button>
                </div>
            </div>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <div class="mesas-container-wrapper">
            <div class="row" id="mesas-container">
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
                        
                        <div class="pos-comensales-control">
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
                    </div>
                    
                    <button type="button" class="pos-btn-cerrar" id="pos_btn_cerrar">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
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
                    <div class="pos-order-header">
                        <h3><i class="fa fa-shopping-cart"></i> Pedido</h3>
                    </div>
                    
                    <div class="pos-order-items" id="pos_order_items">
                        <!-- Items se cargarán aquí -->
                    </div>

                    <div class="pos-order-footer">
                        <div class="pos-propina-section">
                            <label class="pos-propina-checkbox">
                                <input type="checkbox" id="pos_incluye_propina">
                                <span>Incluir propina 10%</span>
                            </label>
                        </div>
                        
                        <div class="pos-total-section">
                            <div class="pos-total-row">
                                <span class="pos-total-label">Subtotal:</span>
                                <span class="pos-total-value">$<span id="pos_subtotal">0</span></span>
                            </div>
                            <div class="pos-total-row" id="pos_propina_row" style="display:none;">
                                <span class="pos-total-label">Propina (10%):</span>
                                <span class="pos-total-value">$<span id="pos_propina">0</span></span>
                            </div>
                            <div class="pos-total-row total-final">
                                <span class="pos-total-label total-final-label">TOTAL:</span>
                                <span class="pos-total-value total-final-value">$<span id="pos_total">0</span></span>
                            </div>
                        </div>

                        <div class="pos-action-buttons">
                            <button type="button" class="pos-btn pos-btn-save" id="btn_guardar_pedido">
                                <i class="fa fa-save"></i> Guardar
                            </button>
                            <button type="button" class="pos-btn pos-btn-print" id="btn_imprimir_comanda" disabled>
                                <i class="fa fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="pos_mesa_id" value="">
<input type="hidden" id="pos_comanda_id" value="">
<input type="hidden" id="pos_capacidad_original" value="">

