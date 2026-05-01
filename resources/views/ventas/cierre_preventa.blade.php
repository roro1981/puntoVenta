<script type="text/javascript" src="js/ventas/cierre_preventa.js"></script>
<link rel="stylesheet" type="text/css" href="css/ventas/generar_ventas.css">
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

<style>
  :root {
    --cpv-bg: #f4f6fb;
    --cpv-card: #ffffff;
    --cpv-border: #e6ebf2;
    --cpv-text: #1f2937;
    --cpv-muted: #6b7280;
    --cpv-primary: #0b5ed7;
    --cpv-primary-soft: #eaf2ff;
    --cpv-success-soft: #ecfdf3;
    --cpv-radius: 14px;
  }

  .cpv-page {
    background: radial-gradient(circle at 20% 0%, #ffffff 0%, var(--cpv-bg) 55%);
    min-height: calc(100vh - 100px);
    padding: 18px 14px;
  }

  .cpv-card {
    background: var(--cpv-card);
    border: 1px solid var(--cpv-border);
    border-radius: var(--cpv-radius);
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
    overflow: hidden;
  }

  .cpv-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    font-size: 18px;
    font-weight: 700;
    color: var(--cpv-text);
    border-bottom: 1px solid var(--cpv-border);
    background: #ffffff;
  }

  .cpv-card-head .icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--cpv-primary-soft);
    color: var(--cpv-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
  }

  .cpv-card-body {
    padding: 18px;
  }

  .cpv-label {
    display: block;
    margin-bottom: 6px;
    color: var(--cpv-text);
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.2px;
  }

  #preventa-code,
  #forma-pago {
    border: 1px solid #d8e0ec;
    border-radius: 10px;
    height: 46px;
    font-size: 16px;
    font-weight: 500;
  }

  #preventa-code:focus,
  #forma-pago:focus {
    border-color: #8ab4ff;
    box-shadow: 0 0 0 3px rgba(11, 94, 215, 0.15);
  }

  #forma-pago option {
    font-size: 1.6rem;
    font-weight: 500;
    padding: 10px 15px;
    color: #333;
  }

  .cpv-input-group .input-group-text {
    border: 1px solid #d8e0ec;
    border-right: 0;
    background: #f8faff;
    color: #5c6b85;
    border-radius: 10px 0 0 10px;
  }

  .cpv-input-group .btn {
    border-radius: 0 10px 10px 0;
    padding: 0 16px;
    font-weight: 600;
  }

  .cpv-helper {
    color: var(--cpv-muted);
    margin-top: 7px;
  }

  .cpv-search-row {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .cpv-search-row #preventa-code {
    flex: 0 1 520px;
    max-width: 520px;
    min-width: 0;
  }

  .cpv-search-row #btnBuscarPreventa {
    flex: 0 0 auto;
    height: 46px;
    border-radius: 10px;
    padding: 0 16px;
    font-weight: 600;
    white-space: nowrap;
  }

  #preventaEmptyState {
    border: 1px dashed #b8cff5;
    background: #f4f8ff;
    color: #33507b;
    border-radius: 10px;
    padding: 14px;
  }

  #preventaMeta {
    background: #f8fafc;
    border: 1px solid var(--cpv-border);
    border-radius: 10px;
    color: #344054;
  }

  .cpv-table-wrap {
    border: 1px solid var(--cpv-border);
    border-radius: 10px;
    overflow: hidden;
  }

  .cpv-table-wrap .table {
    margin-bottom: 0;
  }

  .cpv-table-wrap .thead-light th {
    background: #f7f9fd;
    border-bottom: 1px solid var(--cpv-border);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #4b5563;
  }

  .cpv-total-box {
    background: var(--cpv-success-soft);
    border: 1px solid #b8efd2;
    border-radius: 12px;
    padding: 14px;
    text-align: center;
    margin-bottom: 12px;
  }

  .cpv-total-box .label {
    display: block;
    color: #166534;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
  }

  .cpv-total-box .value {
    font-size: 28px;
    line-height: 1;
    color: #14532d;
    font-weight: 800;
  }

  .cpv-actions .btn {
    border-radius: 10px;
    height: 44px;
    font-weight: 600;
    margin-bottom: 9px;
  }

  #btnCerrarPreventa {
    height: 50px;
    font-size: 16px;
    letter-spacing: 0.2px;
  }

  #modalInfoCaja .modal-content,
  #modalPagoMixto .modal-content,
  #modalCierreCaja .modal-content,
  #modalAperturaCaja .modal-content,
  #modalVerificarPassword .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 14px 42px rgba(15, 23, 42, 0.22);
  }

  @media (max-width: 991px) {
    .cpv-page {
      padding: 12px 10px;
    }

    .cpv-card {
      margin-bottom: 12px;
    }
  }
</style>

<div class="container-fluid cpv-page">
    <div class="row">
        <div class="col-md-8">
      <div class="cpv-card">
        <div class="cpv-card-head">
          <span class="icon"><i class="fa fa-barcode"></i></span>
          <span>Cierre de Preventa</span>
                </div>
        <div class="cpv-card-body">
                    <div class="form-group">
                <div class="cpv-search-row">
                        <input type="text" id="preventa-code" class="form-control form-control-lg" placeholder="Escanea o ingresa el código de preventa y presiona Enter" autofocus>
                        <button class="btn btn-primary" id="btnBuscarPreventa"><i class="fa fa-search"></i> Buscar</button>
                      </div>
                    </div>

                    <div id="preventaEmptyState" class="alert alert-info" style="margin-top:15px;">
                        Ingrese un código para cargar la preventa pendiente.
                    </div>

                    <div id="preventaData" style="display:none;">
                        <div id="preventaMeta" class="alert alert-secondary"></div>

                        <div class="table-responsive cpv-table-wrap">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th class="text-right">P. Unitario</th>
                                        <th class="text-center">Desc.</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="preventa-detalles-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
          <div class="cpv-card">
            <div class="cpv-card-head">
              <span class="icon"><i class="fa fa-credit-card"></i></span>
              <span>Cierre</span>
                </div>
            <div class="cpv-card-body">
                    <input type="hidden" id="preventa-id">

                    <div class="form-group">
                <label for="forma-pago" class="cpv-label">Forma de Pago</label>
                        <select id="forma-pago" class="form-control" required>
                            <option value="">-- Selecciona una forma de pago --</option>
                            <option value="EFECTIVO">💵 Efectivo</option>
                            <option value="TARJETA_DEBITO">🏦 Tarjeta Débito</option>
                            <option value="TARJETA_CREDITO">💳 Tarjeta Crédito</option>
                            <option value="TRANSFERENCIA">🔄 Transferencia</option>
                            <option value="CHEQUE">📋 Cheque</option>
                            <option value="MIXTO">🔀 Mixto</option>
                        </select>
                    </div>

                        <div class="cpv-total-box">
                          <span class="label">Total preventa</span>
                          <span id="totalPreventa" class="value">$ 0</span>
                    </div>

                        <div class="cpv-actions">
                          <button type="button" class="btn btn-info btn-block" id="btnVerCaja">
                            <i class="fa fa-money"></i> Ver Detalle Caja
                          </button>

                          <button type="button" class="btn btn-secondary btn-block" id="btnLimpiarModulo">
                            <i class="fa fa-eraser"></i> Limpiar
                          </button>

                          <button type="button" class="btn btn-success btn-lg btn-block" id="btnCerrarPreventa">
                            <i class="fa fa-check"></i> Generar Venta
                          </button>
                        </div>
                </div>
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
          <span aria-hidden="true">x</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <strong>Total a pagar:</strong> <span id="totalPagoMixto" style="font-size: 1.3rem; font-weight: bold;">$ 0</span>
        </div>

        <table class="table table-borderless" id="tablaPagoMixto">
          <tbody></tbody>
        </table>

        <div class="form-group mt-3">
          <label>Agregar otra forma de pago:</label>
          <div style="display: flex; gap: 0.5rem;">
            <select id="formaPagoMixtoSelect" class="form-control" style="flex: 1;">
              <option value="">-- Selecciona una forma de pago --</option>
              <option value="EFECTIVO">Efectivo</option>
              <option value="TARJETA_DEBITO">Tarjeta Debito</option>
              <option value="TARJETA_CREDITO">Tarjeta Credito</option>
              <option value="TRANSFERENCIA">Transferencia</option>
              <option value="CHEQUE">Cheque</option>
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
          <span aria-hidden="true">x</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 0;">
        <iframe id="ticketFrame" style="width: 100%; height: 600px; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Modal de informacion de caja -->
<div class="modal fade" id="modalInfoCaja" tabindex="-1" aria-labelledby="tituloInfoCaja" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title" id="tituloInfoCaja"><i class="fa fa-money"></i> Detalle de Caja</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">x</span>
        </button>
      </div>
      <div class="modal-body" id="caja-content">
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

<!-- Modal para Verificar Contraseña -->
<div class="modal fade" id="modalVerificarPassword" tabindex="-1" aria-labelledby="tituloVerificarPassword" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h4 class="modal-title" id="tituloVerificarPassword">
          <i class="fa fa-lock"></i> Verificacion de Identidad
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">x</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class="fa fa-exclamation-triangle"></i> Ingresa tu contraseña para acceder a la información de caja
        </div>

        <form id="formVerificarPassword">
          <div class="form-group">
            <label for="passwordCaja"><strong>Contraseña *</strong></label>
            <input type="password" class="form-control" id="passwordCaja" placeholder="Ingrese su contraseña" required autofocus>
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
          <i class="fa fa-exclamation-circle"></i> <strong>Atencion:</strong> Esta accion no se puede deshacer.
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <div class="card"><div class="card-body">
              <h6 class="text-muted">Monto Esperado</h6>
              <h3 class="text-success" id="montoEsperadoCierre">$ 0</h3>
            </div></div>
          </div>
          <div class="col-md-6">
            <div class="card"><div class="card-body">
              <h6 class="text-muted">Total Ventas</h6>
              <h3 class="text-primary" id="totalVentasCierre">$ 0</h3>
            </div></div>
          </div>
        </div>

        <form id="formCierreCaja">
          <div class="form-group">
            <label for="montoFinalDeclarado"><strong>Monto Final en Caja *</strong></label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text">$</span></div>
              <input type="number" class="form-control form-control-lg" id="montoFinalDeclarado" placeholder="Ingrese el monto real en caja" min="0" step="0.01" required autofocus>
            </div>
          </div>

          <div class="form-group">
            <label>Diferencia</label>
            <h4 id="diferenciaCierre" class="text-muted">$ 0</h4>
          </div>

          <div class="form-group">
            <label for="observacionesCierre">Observaciones de Cierre</label>
            <textarea class="form-control" id="observacionesCierre" rows="3" placeholder="Notas sobre el cierre de caja"></textarea>
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
  const cajaAbierta = @json($cajaAbierta);
</script>

@include('partials.modal_ayuda', ['modulo' => 'cierre_preventa'])
