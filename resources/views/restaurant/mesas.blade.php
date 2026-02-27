<script type="text/javascript" src="/js/restaurant/mesas.js"></script>
<link rel="stylesheet" href="/css/restaurant/mesas.css">

<div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-success" data-toggle="modal" id="nueva_mesa" data-target="#modalNuevaMesa">
                <i class='fa fa-plus'></i> Nueva Mesa
            </button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <div id="mesas-container" class="mesas-grid">
            <!-- Las mesas se cargarán dinámicamente aquí -->
        </div>
      </div>
  </div>
</div>

<!-- Modal para crear/editar mesa -->
<div class="modal fade" id="modalNuevaMesa" tabindex="-1" role="dialog" aria-labelledby="modalMesaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalMesaLabel">Nueva Mesa</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mesa_id" value="">
        <form id="mesaForm" autocomplete="off">
          <div class="form-group">
            <label for="nombre_mesa">Nombre de la Mesa</label>
            <input type="text" class="form-control" id="nombre_mesa" name="nombre" maxlength="50" required>
          </div>
          <div class="form-group">
            <label for="capacidad_mesa">Capacidad (personas)</label>
            <input type="number" class="form-control" id="capacidad_mesa" name="capacidad" min="1" max="20" value="4" required>
          </div>
          <div class="form-group" id="estado-group" style="display:none;">
            <label for="activa_mesa">Estado</label>
            <select class="form-control" id="activa_mesa" name="activa">
              <option value="1">Activa</option>
              <option value="0">Inactiva</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_mesa">Guardar</button>
      </div>
    </div>
  </div>
</div>
