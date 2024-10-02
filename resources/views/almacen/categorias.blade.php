<script type="text/javascript" src="js/almacen/categorias.js"></script>
<div class='row'>
  <div class='col-xs-12'>
     <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-success" data-toggle="modal" id="nuevo_user" data-target="#createCatModal"><i class='fa fa fa-save'></i>Nueva categoria</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <table id='tabla_categorias' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #2ab9f7;color:white">
              <th>ID CATEGORIA</th><th>NOMBRE CATEGORIA</th><th>PRODUCTOS ASOCIADOS</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos_cate">
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Modal para crear nueva categoria -->
<div class="modal fade" id="createCatModal" tabindex="-1" aria-labelledby="createCatModalLabel" aria-hidden="true" data-dismiss="modal" data-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="createCatModalLabel">Crear nueva categoría</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="createCatForm" autocomplete="off">
                @csrf
                  <div class="form-group">
                      <label for="descripcion_categoria">Nombre categoria</label>
                      <input type="text" class="form-control" id="descripcion_categoria" name="descripcion_categoria" autocomplete="off" required>
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" form="createCatForm">Crear categoria</button>
          </div>
      </div>
  </div>
</div>
<!-- Fin Modal para crear nueva categoria -->


<!-- Modal para editar categoria -->
<div class="modal fade" id="editCatModal" tabindex="-1" aria-labelledby="editCatModalLabel" aria-hidden="true" data-dismiss="modal" data-backdrop="false">
 <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="editCatModalLabel">Editar categoria</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="editCatForm" autocomplete="off">
                @csrf
                <input type="hidden" id="cat_id">
                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                  <div class="form-group">
                      <label for="id_edit">Id categoria</label>
                      <input type="text" class="form-control" id="id_edit" name="id_edit" readonly>
                  </div>
                  <div class="form-group">
                      <label for="descripcion_categoria_edit">Nombre categoria</label>
                      <input type="text" class="form-control" id="descripcion_categoria_edit" name="descripcion_categoria_edit" autocomplete="off" autocorrect="off" required>
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary" form="editCatForm">Guardar cambios</button>
          </div>
      </div>
  </div>
 <!-- Fin Modal para editar categoria -->
        

