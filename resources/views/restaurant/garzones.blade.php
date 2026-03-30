<link rel="stylesheet" href="/css/restaurant/garzones.css">
<script type="text/javascript" src="/js/restaurant/garzones.js"></script>

<div class="row">
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class="col-xs-12">
    <div style="width:100%">
      <div class="box-header" style="width:100%">
        <button class="btn btn-success" data-toggle="modal" id="nuevo_garzon" data-target="#modalGarzon">
          <i class="fa fa-plus"></i> Nuevo Garzón
        </button>
        <hr style="height:1px;background-color:brown;width:100%;margin-top:2pt;">
      </div>

      <div class="table-responsive">
        <table id="tablaGarzones" class="table table-bordered table-hover table-striped" style="width:100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Apellido</th>
              <th>RUT</th>
              <th>Teléfono</th>
              <th>Email</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="garzones-tbody">
            <!-- Se carga por JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal crear / editar garzón -->
<div class="modal fade" id="modalGarzon" tabindex="-1" role="dialog" aria-labelledby="modalGarzonLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="modalGarzonLabel">Nuevo Garzón</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="garzon_id" value="">
        <form id="garzonForm" autocomplete="off">
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="garzon_nombre">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="garzon_nombre" maxlength="100" required>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="garzon_apellido">Apellido <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="garzon_apellido" maxlength="100" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="garzon_rut">RUT <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="garzon_rut" maxlength="20" placeholder="12345678-9" required>
          </div>
          <div class="row">
            <div class="col-sm-6">
              <div class="form-group">
                <label for="garzon_telefono">Teléfono</label>
                <input type="text" class="form-control" id="garzon_telefono" maxlength="20">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label for="garzon_email">Email</label>
                <input type="email" class="form-control" id="garzon_email" maxlength="100">
              </div>
            </div>
          </div>
          <div class="form-group" id="garzon-estado-group" style="display:none;">
            <label for="garzon_estado">Estado</label>
            <select class="form-control" id="garzon_estado">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardar_garzon">Guardar</button>
      </div>
    </div>
  </div>
</div>
