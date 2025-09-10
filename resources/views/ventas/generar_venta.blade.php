<script type="text/javascript" src="js/ventas/generar_ventas.js"></script>
<link rel="stylesheet" type="text/css" href="css/ventas/generar_ventas.css">
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
<div class="pos-container">
    <div class="left-panel">
        <div class="search-bar">
            <i class="fa fa-barcode"></i>
            <input type="text" id="product-code" placeholder="Ingresa aquí el producto o servicio">
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
                <span style="font-size: 2rem;" class="total-label">Total</span>
                <span class="total-amount" id="cart-total">$ 0</span>
                <button class="pay-btn" id="pay-btn">PAGAR</button>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="search-bar">
            <i class="fa fa-search"></i>
            <input type="text" id="product-search" placeholder="Productos/Servicios">
        </div>
    
        <div class="products-section" id="products-tab">
            <div class="tab-content active" id="tab-products">
                <div class="product-list" id="product-list"></div>
            </div>
            <div class="tab-content" id="tab-favorites">
                <p>Favoritos aquí...</p>
            </div>
            <div class="tab-content" id="tab-history">
                <p>Historial de ventas aquí...</p>
            </div>
            <div class="tab-content" id="tab-clients">
                <p>Listado de clientes aquí...</p>
            </div>
        </div>
    
        <div class="tabs-footer">
            <button class="tab-btn active" title="Listado productos" data-tab="products"><i class="fa fa-th"></i></button>
            <button class="tab-btn" title="Productos vendidos recientemente" data-tab="favorites"><i class="fa fa-star"></i></button>
            <button class="tab-btn" title="Borradores" data-tab="history"><i class="fa fa-file"></i></button>
            <button class="tab-btn" title="Clientes" data-tab="clients"><i class="fa fa-user"></i></button>
        </div>
    </div>
</div>