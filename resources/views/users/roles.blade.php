<link rel="stylesheet" href="css/users/roles.css" />
<script type="text/javascript" src="js/users/roles.js"></script>
<div class='row'>
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-dropbox" data-toggle="modal" id="nuevo_rol" data-target="#createRolModal"><i class='fa fa fa-save'></i>Nuevo Rol</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <table id='tabla_roles' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #01338d;color:white">
              <th>ROL</th><th>MODULOS ASOCIADOS</th><th>FECHA CREACIÓN</th><th>ULTIMA MODIFICACIÓN</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Modal para crear nuevo rol -->
<div class="modal fade" id="createRolModal" tabindex="-1" aria-labelledby="createRolModalLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="createRolModalLabel">Crear nuevo rol</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="createRolForm" autocomplete="off">
                @csrf
                  <div class="form-group">
                      <label for="nombre_rol">Nombre rol</label>
                      <input type="text" class="form-control" id="nombre_rol" name="nombre_rol" autocomplete="off" required>
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" form="createRolForm">Crear rol</button>
          </div>
      </div>
  </div>
</div>
<!-- Fin Modal para crear nuevo rol -->

<!-- Modal Ver -->
<div class="modal fade" id="roleMenusModal" tabindex="-1" role="dialog" aria-labelledby="roleMenusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="roleMenusModalLabel">Menús y Submenús del Rol</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="roleMenusContent" class="list-group"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin Modal Ver -->



        

