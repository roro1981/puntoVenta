<!-- Modal para anulación de producto en comanda -->
<div class="modal fade" id="modalAnularProductoComanda" tabindex="-1" role="dialog" aria-labelledby="tituloAnularProductoComanda" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tituloAnularProductoComanda"><i class="fa fa-lock"></i> Anular producto de comanda</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAnularProductoComanda">
                    <div class="form-group">
                        <label for="anular_cantidad">Cantidad a eliminar</label>
                        <input type="number" class="form-control" id="anular_cantidad" name="anular_cantidad" min="1" max="1" required value="1">
                        <small class="form-text text-muted">Máximo disponible: <span id="anular_cantidad_max">1</span></small>
                    </div>
                    <div class="form-group">
                        <label for="anular_password">Contraseña</label>
                        <input type="password" class="form-control form-control-lg" id="anular_password" name="anular_password" autocomplete="current-password" required style="font-size: 16px; padding: 12px;">
                    </div>
                    <div class="form-group">
                        <label for="anular_motivo">Motivo de anulación</label>
                        <textarea class="form-control" id="anular_motivo" name="anular_motivo" rows="1" required placeholder="Motivo (máx. 100 caracteres)" maxlength="100"></textarea>
                    </div>
                    <input type="hidden" id="anular_producto_index" name="anular_producto_index">
                    <div class="alert alert-danger" id="anular_error" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarAnularProducto">Anular producto</button>
            </div>
        </div>
    </div>
</div>
