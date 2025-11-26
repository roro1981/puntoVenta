<script type="text/javascript" src="js/ventas/generar_ventas.js"></script>
<link rel="stylesheet" type="text/css" href="css/ventas/generar_ventas.css">
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
<div class="pos-container">
    <div class="left-panel">
        <div class="search-bar">
            <i class="fa fa-barcode"></i>
            <input type="text" id="product-code" placeholder="Ingresa aquí el código de producto o servicio">
        </div>

        <div class="cart-items" id="cart-items">
            <!-- Cart items will be added here dynamically -->
        </div>

        <div class="footer">
            <div style="margin-left:240px" class="left-section">
                <div class="action-buttons">
                    <button class="action-btn" id="cancel-btn">
                        <i class="fa fa-times"></i>
                        cancelar
                    </button>
                    <button class="action-btn" id="save-draft-btn">
                        <i class="fa fa-save"></i>
                        guardar borrador
                    </button>
                </div>
            </div>

            <div class="payment-method-section">
                <label for="forma-pago" style="font-weight: 600; font-size: 1.3rem; white-space: nowrap;">Forma de Pago</label>
                <select id="forma-pago" class="payment-select" required>
                    <option value="">-- Selecciona una forma de pago --</option>
                    <option value="EFECTIVO">💵 Efectivo</option>
                    <option value="TARJETA_DEBITO">🏦 Tarjeta Débito</option>
                    <option value="TARJETA_CREDITO">💳 Tarjeta Crédito</option>
                    <option value="TRANSFERENCIA">🔄 Transferencia</option>
                    <option value="CHEQUE">📋 Cheque</option>
                    <option value="MIXTO">🔀 Mixto</option>
                </select>
            </div>

            <div class="total-section">
                <span style="font-size: 2rem;" class="total-label">Descuentos</span>
                <span style="margin-right:15px" class="total-amount" id="discount-amount">$ 0</span>
                <span style="font-size: 2rem;" class="total-label">Total</span>
                <span class="total-amount" id="cart-total">$ 0</span>
              <button class="pay-btn" id="pay-btn">PAGAR</button>
            </div>
        </div>
    </div>

    <div class="right-panel">
        
    
        <div class="products-section" id="products-tab">
            <div class="tab-content active" id="tab-products">
                <div class="search-bar">
                    <i class="fa fa-search"></i>
                    <input type="text" id="product-search" placeholder="Productos/Servicios">
                </div>
                <div class="product-list" id="product-list"></div>
            </div>
            <div class="tab-content" id="tab-alerts">
              <div class="alerts-section">
                <h5>Problemas / Alertas</h5>
                <div id="alerts-list">
                  
                </div>
              </div>
            </div>
            <div class="tab-content" id="tab-borradores">
            </div>
            <div class="tab-content" id="tab-clients">
                <p>Listado de clientes aquí...</p>
            </div>
        </div>
    
        <div class="tabs-footer">
            <button class="tab-btn active" title="Listado productos" data-tab="products"><i class="fa fa-th"></i></button>
            <button class="tab-btn" title="Alertas" data-tab="alerts" style="position:relative;">
              <i class="fa fa-exclamation-triangle"></i>
              <span id="alerts-count" style="position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#d9534f;color:#fff;font-size:11px;display:none;align-items:center;justify-content:center;">0</span>
            </button>
            <button class="tab-btn" title="Borradores" data-tab="borradores"><i class="fa fa-file"></i></button>
            <button class="tab-btn" title="Clientes" data-tab="clients"><i class="fa fa-user"></i></button>
        </div>
    </div>
</div>
<!-- Modal ver detalle borrador -->
<div class="modal fade" id="modalDetalleBorrador" tabindex="-1" aria-labelledby="tituloDetalleBorrador" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="tituloDetalleBorrador">Detalle de Productos del Borrador</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="uuid_borrador">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Cantidad</th>
              <th>Producto</th>
              <th>Precio Venta</th>
              <th>Descuento</th>
            </tr>
          </thead>
          <tbody id="detalle-borrador-body">
            <!-- Filas dinámicas -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="btnCargarVenta">Cargar a Venta</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Pago Mixto -->
<div class="modal fade" id="modalPagoMixto" tabindex="-1" aria-labelledby="tituloPagoMixto" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="tituloPagoMixto">Desglose de Pago Mixto</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>Total a pagar:</strong> <span id="totalPagoMixto" style="font-size: 1.3rem; font-weight: bold;">$ 0</span>
        </div>
        
        <table class="table table-borderless" id="tablaPagoMixto">
          <tbody>
            <!-- Filas dinámicas de formas de pago -->
          </tbody>
        </table>

        <div class="form-group mt-3">
          <label>Agregar otra forma de pago:</label>
          <div style="display: flex; gap: 0.5rem;">
            <select id="formaPagoMixtoSelect" class="form-control" style="flex: 1;">
              <option value="">-- Selecciona una forma de pago --</option>
              <option value="EFECTIVO">💵 Efectivo</option>
              <option value="TARJETA_DEBITO">🏦 Tarjeta Débito</option>
              <option value="TARJETA_CREDITO">💳 Tarjeta Crédito</option>
              <option value="TRANSFERENCIA">🔄 Transferencia</option>
              <option value="CHEQUE">📋 Cheque</option>
            </select>
            <input type="number" id="montoPagoMixto" class="form-control" placeholder="Monto" style="flex: 0 0 120px;" min="0">
            <button class="btn btn-primary" id="btnAgregarFormaPago">Agregar</button>
          </div>
        </div>

        <div class="alert alert-warning mt-3">
          <strong>Pendiente:</strong> <span id="pendientePagoMixto" style="font-size: 1.2rem; font-weight: bold; color: #856404;">$ 0</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarPagoMixto" disabled>Confirmar Pago</button>
      </div>
    </div>
  </div>
</div>
