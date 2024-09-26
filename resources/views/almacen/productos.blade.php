<script type="text/javascript" src="js/almacen/productos.js"></script>
<div class='row'>
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-success" data-toggle="modal" id="nuevo_user" data-target="#createUserModal"><i class='fa fa fa-save'></i>Nuevo Producto</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <table id='tabla_productos' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #2ab9f7;color:white">
              <th>USUARIO</th><th>NOMBRE</th><th>ROL</th><th>FECHA CREACIÓN</th><th>ULTIMA MODIFICACIÓN</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Modal para crear nuevo usuario -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true" data-dismiss="modal" data-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="createUserModalLabel">Crear nuevo usuario</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="createUserForm" autocomplete="off">
                @csrf
                  <div class="form-group">
                      <label for="name">Usuario o name</label>
                      <input type="text" class="form-control" id="name" name="name" autocomplete="off" readonly required>
                  </div>
                  <div class="form-group">
                      <label for="name_complete">Nombre completo</label>
                      <input type="text" class="form-control" id="name_complete" name="name_complete" autocomplete="off" autocorrect="off" required>
                  </div>
                  <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="off" autocorrect="off" required>
                    <span class="password-eye" onclick="togglePasswordVisibility(document.getElementById('password'),document.getElementById('password-eye-icon'))">
                      <i class="fa fa-eye-slash" id="password-eye-icon"></i>
                    </span>
                  </div>
                  <div class="form-group">
                      <label for="role_id">Rol</label>
                      <select class="form-control" id="role_id" name="role_id" required>
                        <option value="" selected disabled>Seleccione rol</option>
                      
                      </select>
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" form="createUserForm">Crear usuario</button>
          </div>
      </div>
  </div>
</div>
<!-- Fin Modal para crear nuevo usuario -->


<!-- Modal para editar usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true" data-dismiss="modal" data-backdrop="false">
 <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="editUserModalLabel">Editar usuario</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="editUserForm" autocomplete="off">
                @csrf
                <input type="hidden" id="user_id">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                  <div class="form-group">
                      <label for="name_edit">Usuario</label>
                      <input type="text" class="form-control" id="name_edit" name="name_edit" readonly>
                  </div>
                  <div class="form-group">
                      <label for="name_complete_edit">Nombre</label>
                      <input type="text" class="form-control" id="name_complete_edit" name="name_complete_edit" autocomplete="off" autocorrect="off" required>
                  </div>
                  <div class="form-group">
                    <label for="password_edit">Contraseña</label>
                    <input type="password" class="form-control" id="password_edit" name="password_edit" autocomplete="off" readonly autocorrect="off">
                    <small class="text-muted">Dejar en blanco para no cambiar la contraseña</small>
                    <span class="password-eye_edit" onclick="togglePasswordVisibility(document.getElementById('password_edit'),document.getElementById('password-eye-icon_edit'))">
                      <i class="fa fa-eye-slash" id="password-eye-icon_edit"></i>
                    </span>
                </div>
                <div class="form-group">
                  <label for="role_id_edit">Rol</label>
                  <select class="form-control" id="role_id_edit" name="role_id_edit" required>
                  
                  </select>
                </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" form="editUserForm">Guardar cambios</button>
          </div>
      </div>
  </div>
 <!-- Fin Modal para editar usuario -->
        

