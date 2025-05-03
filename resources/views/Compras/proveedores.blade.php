<link rel="stylesheet" href="css/compras/proveedores.css" />
<script type="text/javascript" src="js/compras/proveedores.js"></script>
<div class='row'>
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-dropbox" data-toggle="modal" id="nuevo_proveedor" data-target="#createProveedorModal"><i class='fa fa fa-save'></i>Nuevo Proveedor</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <table id='tabla_proveedores' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #01338d;color:white">
              <th>RAZON SOCIAL</th><th>GIRO</th><th>REGION-COMUNA</th><th>FECHA CREACIÓN</th><th>ULTIMA MODIFICACIÓN</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Modal para crear nuevo proveedor -->
<div class="modal fade" id="createProveedorModal" tabindex="-1" aria-labelledby="createProveedorModalLabel" aria-hidden="true" data-dismiss="modal" data-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h2 class="modal-title" id="createProveedorModalLabel">Crear nuevo proveedor</h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="createProveedorForm" autocomplete="off">
                @csrf
                  <div class="form-group">
                      <label for="name">Rut (*)</label>
                      <input type="text" class="form-control" id="rut" name="rut" autocomplete="off" required>
                      <small id="rutFeedback" class="text-danger d-none">RUT no válido</small>
                  </div>
                  <div class="form-group">
                      <label for="razon_social">Razón social (*)</label>
                      <input type="text" class="form-control" id="razon_social" name="razon_social" autocomplete="off" autocorrect="off" required>
                  </div>
                  <div class="form-group">
                    <label for="nombre_fantasia">Nombre fantasia</label>
                    <input type="text" class="form-control" id="nombre_fantasia" name="nombre_fantasia" autocomplete="off" autocorrect="off">
                  </div>
                  <div class="form-group">
                    <label for="giro">Giro</label>
                    <input type="text" class="form-control" id="giro" name="giro" autocomplete="off" autocorrect="off">
                  </div>
                  <div class="form-group">
                    <label for="direccion">Dirección (*)</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" autocomplete="off" autocorrect="off" required>
                  </div>
                  <div class="form-group">
                      <label for="region">Region (*)</label>
                      <select class="form-control" id="region" name="region" required>
                        <option value="" selected disabled>Seleccione Región</option>
                          @foreach($regiones as $region)
                              <option value="{{ $region->id }}">{{ $region->nom_region }}</option>
                          @endforeach
                      </select>
                  </div>
                  <div class="form-group">
                    <label for="comuna">Comuna (*)</label>
                    <select class="form-control" id="comuna" name="comuna" required>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" autocomplete="off" autocorrect="off">
                  </div>
                  <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" class="form-control campo-mail" id="email" name="email" autocomplete="off" autocorrect="off" pattern="^[^\s@]+@[^\s@]+\.[^\s@]{2,}$">
                    <small class="text-danger d-none feedback-mail">Correo no válido</small>
                  </div>
                  <div class="form-group">
                    <label for="pagina_web">Pagina Web</label>
                    <input type="text" class="form-control campo-url" id="pagina_web" name="pagina_web" autocomplete="off" autocorrect="off" pattern="^(https?:\/\/)?([\w\-]+\.)+[a-z]{2,}(\/[^\s]*)?$">
                    <small class="text-danger d-none feedback-url">URL no válida</small>
                  </div>
                  <div class="form-group">
                    <label for="contacto_nombre">Nombre contacto</label>
                    <input type="text" class="form-control" id="contacto_nombre" name="contacto_nombre" autocomplete="off" autocorrect="off">
                  </div>
                  <div class="form-group">
                    <label for="contacto_email">Email contacto</label>
                    <input type="email" class="form-control campo-mail" id="contacto_email" name="contacto_email" autocomplete="off" pattern="^[^\s@]+@[^\s@]+\.[^\s@]{2,}$" autocorrect="off">
                    <small class="text-danger d-none feedback-mail">Correo no válido</small>
                 </div>
                  <div class="form-group">
                    <label for="contacto_telefono">Telefono contacto</label>
                    <input type="text" class="form-control" id="contacto_telefono" name="contacto_telefono" autocomplete="off" autocorrect="off">
                  </div>
                  <text style="color:blue">(*) Campos obligatorios</text>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" form="createProveedorForm">Crear proveedor</button>
          </div>
      </div>
  </div>
</div>
<!-- Fin Modal para crear nuevo proveedor -->


<!-- Modal para editar usuario 
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
                <input type="hidden" id="user_uuid">
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
  </div>-->
 <!-- Fin Modal para editar usuario -->
        

