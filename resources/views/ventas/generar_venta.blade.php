<script type="text/javascript" src="js/ventas/generar_ventas.js"></script>
<link rel="stylesheet" type="text/css" href="css/ventas/generar_ventas.css">
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
<div class="pos-container">
    <div class="left-panel">
        <div class="search-bar">
            <i class="fa fa-barcode"></i>
            <input type="text" id="product-code" placeholder="Ingresa código o cantidad*código (ej: 2*0001 para 2 unidades del codigo 0001)">
        </div>

        <div class="cart-items" id="cart-items">
            <!-- Cart items will be added here dynamically -->
        </div>

        <div class="action-buttons-panel">
            <div class="left-actions">
                <button class="action-btn" id="cancel-btn">
                    <i class="fa fa-times"></i>
                    CANCELAR
                </button>
                <button class="action-btn" id="save-draft-btn">
                    <i class="fa fa-save"></i>
                    GUARDAR BORRADOR
                </button>
            </div>
            <div class="right-actions">
                <div class="discount-container">
                    <span class="total-label-enhanced">DESCUENTOS</span>
                    <span class="total-amount-secondary" id="discount-amount">$ 0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <!-- Header con total, forma de pago y botón centrados -->
        <div class="right-panel-header">
            <div class="main-total-container-centered">
                <span class="total-label-main">TOTAL</span>
                <span class="total-amount-main" id="cart-total">$ 0</span>
            </div>
            
            <div class="payment-method-section-enhanced">
                <label for="forma-pago" class="payment-label-enhanced">Forma de Pago</label>
                <select id="forma-pago" class="payment-select-enhanced" required>
                    <option value="">-- Selecciona una forma de pago --</option>
                    <option value="EFECTIVO">💵 Efectivo</option>
                    <option value="TARJETA_DEBITO">🏦 Tarjeta Débito</option>
                    <option value="TARJETA_CREDITO">💳 Tarjeta Crédito</option>
                    <option value="TRANSFERENCIA">🔄 Transferencia</option>
                    <option value="CHEQUE">📋 Cheque</option>
                    <option value="MIXTO">🔀 Mixto</option>
                </select>
            </div>
            
            <button class="pay-btn-enhanced" id="pay-btn">
                <i class="fa fa-credit-card"></i>
                PAGAR
            </button>
        </div>
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
            <div class="tab-content" id="tab-caja">
                <div class="caja-section" style="padding: 20px;">
                    <div id="caja-content">
                        <!-- Se cargará dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    
        <div class="tabs-footer">
            <button class="tab-btn active" title="Listado productos" data-tab="products"><i class="fa fa-th"></i></button>
            <button class="tab-btn" title="Alertas" data-tab="alerts" style="position:relative;">
              <i class="fa fa-exclamation-triangle"></i>
              <span id="alerts-count" style="position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#d9534f;color:#fff;font-size:11px;display:none;align-items:center;justify-content:center;">0</span>
            </button>
            <button class="tab-btn" title="Borradores" data-tab="borradores"><i class="fa fa-file"></i></button>
            <button class="tab-btn" title="Información de caja" data-tab="caja"><i class="fa fa-money"></i></button>
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

<!-- Modal para visualizar ticket -->
<div class="modal fade" id="modalTicket" tabindex="-1" aria-labelledby="tituloTicket" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="max-width: 400px;">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="tituloTicket">Ticket de Venta</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 0;">
        <iframe id="ticketFrame" style="width: 100%; height: 600px; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Apertura de Caja -->
<div class="modal fade" id="modalAperturaCaja" tabindex="-1" aria-labelledby="tituloAperturaCaja" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="tituloAperturaCaja">
          <i class="fa fa-cash-register"></i> Apertura de Caja
        </h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i> Debes abrir caja antes de comenzar a operar
        </div>
        
        <form id="formAperturaCaja">
          <div class="form-group">
            <label for="montoInicialCaja"><strong>Monto Inicial *</strong></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">$</span>
              </div>
              <input type="number" class="form-control" id="montoInicialCaja" name="monto_inicial" 
                     placeholder="Ingrese el monto inicial" min="0" step="0.01" required autofocus>
            </div>
            <small class="form-text text-muted">Monto en efectivo con el que inicia el turno de caja</small>
          </div>

          <div class="form-group">
            <label for="observacionesApertura">Observaciones (opcional)</label>
            <textarea class="form-control" id="observacionesApertura" name="observaciones" 
                      rows="3" placeholder="Notas o comentarios sobre la apertura"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success btn-lg btn-block" id="btnConfirmarAperturaCaja">
          <i class="fa fa-check"></i> Abrir Caja
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Verificar Contraseña (Acceso a Caja) -->
<div class="modal fade" id="modalVerificarPassword" tabindex="-1" aria-labelledby="tituloVerificarPassword" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h4 class="modal-title" id="tituloVerificarPassword">
          <i class="fa fa-lock"></i> Verificación de Identidad
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fa fa-exclamation-triangle"></i> Ingresa tu contraseña para acceder a la información de caja
        </div>
        
        <form id="formVerificarPassword">
          <div class="form-group">
            <label for="passwordCaja"><strong>Contraseña *</strong></label>
            <input type="password" class="form-control" id="passwordCaja" 
                   placeholder="Ingrese su contraseña" required autofocus>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarPassword">
          <i class="fa fa-unlock"></i> Verificar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Cierre de Caja -->
<div class="modal fade" id="modalCierreCaja" tabindex="-1" aria-labelledby="tituloCierreCaja" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h4 class="modal-title" id="tituloCierreCaja">
          <i class="fa fa-power-off"></i> Cierre de Caja
        </h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger">
          <i class="fa fa-exclamation-circle"></i> <strong>Atención:</strong> Estás a punto de cerrar tu caja. Esta acción no se puede deshacer.
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <h6 class="text-muted">Monto Esperado</h6>
                <h3 class="text-success" id="montoEsperadoCierre">$ 0</h3>
                <small class="text-muted">Inicial + Ventas</small>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card">
              <div class="card-body">
                <h6 class="text-muted">Total Ventas</h6>
                <h3 class="text-primary" id="totalVentasCierre">$ 0</h3>
                <small class="text-muted">Del turno</small>
              </div>
            </div>
          </div>
        </div>
        
        <form id="formCierreCaja">
          <div class="form-group">
            <label for="montoFinalDeclarado"><strong>Monto Final en Caja *</strong></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">$</span>
              </div>
              <input type="number" class="form-control form-control-lg" id="montoFinalDeclarado" 
                     placeholder="Ingrese el monto real en caja" min="0" step="0.01" required autofocus>
            </div>
            <small class="form-text text-muted">Cuente todo el efectivo físico en la caja</small>
          </div>

          <div class="form-group">
            <label>Diferencia</label>
            <h4 id="diferenciaCierre" class="text-muted">$ 0</h4>
            <small class="text-muted">Se calculará automáticamente</small>
          </div>

          <div class="form-group">
            <label for="observacionesCierre">Observaciones de Cierre</label>
            <textarea class="form-control" id="observacionesCierre" 
                      rows="3" placeholder="Notas sobre el cierre de caja, incidencias, etc."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger btn-lg" id="btnConfirmarCierreCaja">
          <i class="fa fa-power-off"></i> Cerrar Caja
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Variable para saber si hay caja abierta
  const cajaAbierta = @json($cajaAbierta);
</script>
