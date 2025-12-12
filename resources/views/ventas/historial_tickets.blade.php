<link rel="stylesheet" href="css/ventas/historial_tickets.css" />
<script type="text/javascript" src="js/ventas/historial_tickets.js"></script>
<input type="hidden" id="token" value="{{ csrf_token() }}">

<div class='row'>
    <div class='col-xs-12'>
        <div style="width:100%">
            <div class='box-header' style="width:100%">
                <h3 style="margin: 0; padding: 10px 0;">
                    Historial de Tickets
                </h3>
                <hr style="height:1px;background-color: brown;width:100%;margin-top: 10px;" />
            </div>
            
            <!-- Filtros de fecha -->
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-3">
                    <label for="fecha_desde">Fecha Desde:</label>
                    <input type="date" id="fecha_desde" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="fecha_hasta">Fecha Hasta:</label>
                    <input type="date" id="fecha_hasta" class="form-control">
                </div>
                <div class="col-md-3">
                    <button id="btnFiltrar" class="btn btn-primary" style="margin-top: 25px;">
                        <i class="fa fa-filter"></i> Filtrar
                    </button>
                    <button id="btnLimpiar" class="btn btn-secondary" style="margin-top: 25px;">
                        <i class="fa fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>
            
            <table id='tabla_tickets' class="display" style="width:100%">
                <thead>
                    <tr style="background-color: #01338d;color:white">
                        <th>Nº TICKET</th>
                        <th>FECHA</th>
                        <th>VENDEDOR</th>
                        <th>TOTAL</th>
                        <th>FORMA PAGO</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="datos">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver ticket PDF -->
<div class="modal fade" id="modalTicket" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #01338d; color: white;">
                <h5 class="modal-title">
                    <i class="fa fa-print"></i> Ticket de Venta
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="ticketFrame" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para anular ticket -->
<div class="modal fade" id="modalAnularTicket" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #dc3545; color: white;">
                <h5 class="modal-title">
                    <i class="fa fa-exclamation-triangle"></i> Anular Ticket
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <h5><i class="fa fa-info-circle"></i> Importante</h5>
                    <p><strong>Al anular este ticket:</strong></p>
                    <ul>
                        <li>Se devolverá el stock de los productos seleccionados</li>
                        <li>Si hay promociones, se devolverá el stock de todos los productos que la componen</li>
                        <li>Si hay recetas, se devolverá el stock de todos los ingredientes</li>
                        <li>Se restará el monto correspondiente del total de ventas de la caja del día</li>
                        <li><strong class="text-danger">Esta acción NO se puede deshacer</strong></li>
                    </ul>
                </div>
                
                <div id="detalleTicketAnular">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>Cargando información...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnAnularCompleto">
                    <i class="fa fa-ban"></i> Anular Todo el Ticket
                </button>
                <button type="button" class="btn btn-warning" id="btnAnularSeleccionados" style="display: none;">
                    <i class="fa fa-check"></i> Anular Productos Seleccionados
                </button>
            </div>
        </div>
    </div>
</div>