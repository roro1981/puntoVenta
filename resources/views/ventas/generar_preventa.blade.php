<script type="text/javascript" src="js/ventas/generar_preventa.js"></script>
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

            <div class="total-section">
                <span style="font-size: 2rem;" class="total-label">Descuentos</span>
                <span style="margin-right:15px" class="total-amount" id="discount-amount">$ 0</span>
                <span style="font-size: 2rem;" class="total-label">Total</span>
                <span class="total-amount" id="cart-total">$ 0</span>
                <button class="pay-btn" id="pay-btn">Generar Preventa</button>
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
                    <div id="alerts-list"></div>
                </div>
            </div>
            <div class="tab-content" id="tab-borradores">
            </div>
            <div class="tab-content" id="tab-preventas-pendientes">
            </div>
        </div>

        <div class="tabs-footer">
            <button class="tab-btn active" title="Listado productos" data-tab="products"><i class="fa fa-th"></i></button>
            <button class="tab-btn" title="Alertas" data-tab="alerts" style="position:relative;">
                <i class="fa fa-exclamation-triangle"></i>
                <span id="alerts-count" style="position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:#d9534f;color:#fff;font-size:11px;display:none;align-items:center;justify-content:center;">0</span>
            </button>
            <button class="tab-btn" title="Borradores" data-tab="borradores"><i class="fa fa-file"></i></button>
            <button class="tab-btn" title="Preventas pendientes" data-tab="preventas-pendientes"><i class="fa fa-list"></i></button>
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
                    <tbody id="detalle-borrador-body"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="btnCargarVenta">Cargar a Preventa</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar ticket preventa -->
<div class="modal fade" id="modalTicketPreventa" tabindex="-1" aria-labelledby="tituloTicketPreventa" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tituloTicketPreventa">Ticket de Preventa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <iframe id="ticketPreventaFrame" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>
