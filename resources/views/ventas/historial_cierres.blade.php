<link rel="stylesheet" href="css/ventas/historial_cierres.css" />
<script type="text/javascript" src="js/ventas/historial_cierres.js"></script>
<input type="hidden" id="token" value="{{ csrf_token() }}">

<div class='row'>
    <div class='col-xs-12'>
        <div style="width:100%">
            <div class='box-header' style="width:100%">
                <h3 style="margin: 0; padding: 10px 0;">
                    <i class="fa fa-history"></i> Historial de Cierres de Caja
                </h3>
                <hr style="height:1px;background-color: brown;width:100%;margin-top: 10px;" />
            </div>
            
            <table id='tabla_cierres' class="display" style="width:100%">
                <thead>
                    <tr style="background-color: #01338d;color:white">
                        <th>Nº CIERRE</th>
                        <th>USUARIO</th>
                        <th>APERTURA</th>
                        <th>CIERRE</th>
                        <th>INICIAL</th>
                        <th>VENTAS</th>
                        <th>ESPERADO</th>
                        <th>DECLARADO</th>
                        <th>DIFERENCIA</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="datos">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver detalle del cierre -->
<div class="modal fade" id="modalDetalleCierre" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #01338d; color: white;">
                <h5 class="modal-title">
                    <i class="fa fa-info-circle"></i> Detalle del Cierre de Caja
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detalleCierreContent">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>Cargando información...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver ticket PDF -->
<div class="modal fade" id="modalTicketCierre" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #01338d; color: white;">
                <h5 class="modal-title">
                    <i class="fa fa-print"></i> Ticket de Cierre de Caja
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="ticketCierreFrame" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

