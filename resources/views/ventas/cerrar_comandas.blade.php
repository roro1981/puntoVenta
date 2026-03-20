<script type="text/javascript" src="js/ventas/cerrar_comandas.js"></script>
<link rel="stylesheet" type="text/css" href="css/ventas/cerrar_comandas.css">

<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <button class="btn btn-warning" id="btnVerDetalleCajaCerrarComandas">
                    <i class="fa fa-lock"></i> Ver detalle caja
                </button>
                <button class="btn btn-primary" id="btnRefrescarComandasPendientes">
                    <i class="fa fa-sync"></i> Actualizar
                </button>
            </div>
            <div class="box-body">
                <div id="estadoCierreComandas" class="alert alert-info">
                    Cargando comandas pendientes de pago...
                </div>
                <div id="listaComandasPendientes" class="row"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCerrarComanda" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-receipt"></i> Cerrar <span id="tituloComandaCerrar"></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Mesa:</strong> <span id="modalMesaNombre"></span></div>
                    <div class="col-md-4"><strong>Comensales:</strong> <span id="modalComensales"></span></div>
                    <div class="col-md-4 text-right"><strong>Total:</strong> <span id="modalTotalComanda" class="h4 text-success"></span></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Subtotal:</strong> <span id="modalSubtotalComanda"></span></div>
                    <div class="col-md-4"><strong>Propina:</strong> <span id="modalPropinaComanda"></span></div>
                    <div class="col-md-4 text-right"><strong>Total final:</strong> <span id="modalTotalComandaCalculado" class="h4 text-success"></span></div>
                </div>

                <div class="form-group">
                    <label class="checkbox-inline" style="font-weight:600;">
                        <input type="checkbox" id="cerrar_incluye_propina"> Aplicar propina
                    </label>
                </div>

                <div class="form-group">
                    <label for="cerrar_propina_porcentaje"><strong>Porcentaje de propina (%)</strong></label>
                    <input type="number" min="0" max="100" step="0.01" id="cerrar_propina_porcentaje" class="form-control" value="{{ rtrim(rtrim(number_format($porcentajePropina ?? 10, 2, '.', ''), '0'), '.') }}">
                </div>

                <div class="form-group">
                    <label for="formaPagoCerrarComanda"><strong>Forma de pago</strong></label>
                    <select id="formaPagoCerrarComanda" class="form-control">
                        <option value="">-- Selecciona una forma de pago --</option>
                        <option value="EFECTIVO">💵 Efectivo</option>
                        <option value="TARJETA_DEBITO">🏦 Tarjeta Débito</option>
                        <option value="TARJETA_CREDITO">💳 Tarjeta Crédito</option>
                        <option value="TRANSFERENCIA">🔄 Transferencia</option>
                        <option value="CHEQUE">📋 Cheque</option>
                        <option value="MIXTO">🔀 Mixto</option>
                    </select>
                </div>

                <div id="seccionPagoMixtoComanda" style="display:none;">
                    <div class="alert alert-info">
                        <strong>Total a cubrir:</strong> <span id="totalPagoMixtoComanda"></span>
                    </div>

                    <table class="table table-bordered" id="tablaPagoMixtoComanda">
                        <thead>
                            <tr>
                                <th>Forma de pago</th>
                                <th class="text-right">Monto</th>
                                <th class="text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="row">
                        <div class="col-md-5">
                            <select id="formaPagoMixtoComandaSelect" class="form-control">
                                <option value="">-- Forma de pago --</option>
                                <option value="EFECTIVO">💵 Efectivo</option>
                                <option value="TARJETA_DEBITO">🏦 Tarjeta Débito</option>
                                <option value="TARJETA_CREDITO">💳 Tarjeta Crédito</option>
                                <option value="TRANSFERENCIA">🔄 Transferencia</option>
                                <option value="CHEQUE">📋 Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" min="1" step="1" id="montoPagoMixtoComanda" class="form-control" placeholder="Monto">
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-block" id="btnAgregarPagoMixtoComanda">
                                <i class="fa fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-warning alerta-pendiente-comanda">
                        <strong>Pendiente:</strong> <span id="pendientePagoMixtoComanda"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarCerrarComanda">
                    <i class="fa fa-check"></i> Confirmar cierre
                </button>
            </div>
        </div>
    </div>
</div>

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
                    <i class="fa fa-info-circle"></i> Debes abrir caja antes de cerrar comandas
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

<div class="modal fade" id="modalVerificarPasswordCajaCerrarComandas" tabindex="-1" aria-labelledby="tituloVerificarPasswordCajaCerrarComandas" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h4 class="modal-title" id="tituloVerificarPasswordCajaCerrarComandas">
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

                <div class="form-group">
                    <label for="passwordCajaCerrarComandas"><strong>Contraseña *</strong></label>
                    <input type="password" class="form-control" id="passwordCajaCerrarComandas" placeholder="Ingrese su contraseña" required autofocus>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarPasswordCajaCerrarComandas">
                    <i class="fa fa-unlock"></i> Verificar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleCajaCerrarComandas" tabindex="-1" aria-labelledby="tituloDetalleCajaCerrarComandas" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="tituloDetalleCajaCerrarComandas">
                    <i class="fa fa-info-circle"></i> Detalle de Caja
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detalleCajaCerrarComandasContent">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>Cargando información...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCierreCajaCerrarComandas" tabindex="-1" aria-labelledby="tituloCierreCajaCerrarComandas" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title" id="tituloCierreCajaCerrarComandas">
                    <i class="fa fa-power-off"></i> Cierre de Caja RESTAURANT
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
                                <h3 class="text-success" id="montoEsperadoCierreCerrarComandas">$ 0</h3>
                                <small class="text-muted">Inicial + Ventas</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted">Total Ventas</h6>
                                <h3 class="text-primary" id="totalVentasCierreCerrarComandas">$ 0</h3>
                                <small class="text-muted">Del turno</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="montoFinalDeclaradoCerrarComandas"><strong>Monto Final en Caja *</strong></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control form-control-lg" id="montoFinalDeclaradoCerrarComandas" placeholder="Ingrese el monto real en caja" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Diferencia</label>
                    <h4 id="diferenciaCierreCerrarComandas" class="text-muted">$ 0</h4>
                    <small class="text-muted">Se calculará automáticamente</small>
                </div>

                <div class="form-group">
                    <label for="observacionesCierreCerrarComandas">Observaciones de Cierre</label>
                    <textarea class="form-control" id="observacionesCierreCerrarComandas" rows="3" placeholder="Notas sobre el cierre de caja, incidencias, etc."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-lg" id="btnConfirmarCierreCajaCerrarComandas">
                    <i class="fa fa-power-off"></i> Cerrar Caja
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal para visualizar ticket -->
<div class="modal fade" id="modalTicketRest" tabindex="-1" aria-labelledby="tituloTicket" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="max-width: 400px;">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="tituloTicket">Ticket Cierre Caja</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body" style="padding: 0;">
        <iframe id="ticketFrameRest" style="width: 100%; height: 600px; border: none;"></iframe>
      </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
    </div>
  </div>
</div>
<script>
    window.cajaAbierta = @json($cajaAbierta);
    window.porcentajePropinaGlobal = @json((float)($porcentajePropina ?? 10));
</script>
