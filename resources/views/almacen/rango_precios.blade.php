<script type="text/javascript" src="js/almacen/rango_precios.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/rango_precios.css">
<div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-success" data-toggle="modal" id="nuevo_rango" data-target="#modalNuevoRango"><i class='fa fa fa-save'></i>Nuevo Rango</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <table id='tabla_rangos' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #2ab9f7;color:white">
              <th>CODIGO</th><th>DESCRIPCION</th><th>CANT MÍNIMA</th><th>CANT MÁXIMA</th><th>PRECIO VENTA</th><th>ULTIMA MODIFICACIÓN</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Modal para crear nuevo producto -->
<div class="modal fade" id="modalNuevoRango" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel">Nuevo Rango</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <form id="createRangoForm" autocomplete="off">
          <div class="form-group" style="position:relative;display: inline-block;">
            <label for="codigo">Código</label>
            <input type="text" class="form-control" id="codigo" name="codigo" maxlength="255">
            <div id="listaProductos" class="suggestion-box" style="display: none;"></div>
            <input type="hidden" name="uuid" id="uuid" >
          </div>
          <div class="form-group">
            <label for="descripcion">Descripción</label>
            <input type="text" class="form-control" id="descripcion" disabled>
          </div>
          <div class="form-group">
            <label for="precio_actual">Precio Actual</label>
            <input type="number" class="form-control" id="precio_actual" disabled>
          </div>
          <div class="form-group">
            <label for="cant_minima">Cantidad mínima</label>
            <input type="number" class="form-control" id="cant_minima" name="cantidad_minima">
          </div>
          <div class="form-group">
            <label for="cant_maxima">Cantidad máxima</label>
            <input type="number" class="form-control" id="cant_maxima" name="cantidad_maxima">
          </div>
          <div class="form-group">
            <label for="precio_rango">Precio rango</label>
            <input type="number" class="form-control" id="precio_rango" name="precio_unitario">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" form="createRangoForm">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- Fin Modal para crear nuevo rango -->
<!-- Modal de Edición rango -->
<div class="modal fade" id="modalEditarRango" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title" id="exampleModalLabel">Editar Rango</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="token_editar" value="{{ csrf_token() }}">
            <div class="form-group">
              <label for="codigo_act">Código</label>
              <input type="text" class="form-control" id="codigo_act" maxlength="255" disabled>
            </div>
            <div class="form-group">
              <label for="descripcion_act">Descripción</label>
              <input type="text" class="form-control" id="descripcion_act" disabled>
            </div>
            <div class="form-group">
              <label for="precio_actual_act">Precio Actual</label>
              <input type="number" class="form-control" id="precio_actual_act" disabled>
            </div>
            <div class="form-group">
              <label for="cant_minima_act">Cantidad mínima</label>
              <input type="number" class="form-control" id="cant_minima_act">
            </div>
            <div class="form-group">
              <label for="cant_maxima_act">Cantidad máxima</label>
              <input type="number" class="form-control" id="cant_maxima_act">
            </div>
            <div class="form-group">
              <label for="precio_rango_act">Precio rango</label>
              <input type="number" class="form-control" id="precio_rango_act">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button id="guardar_cambios" class="btn btn-primary">Actualizar rango</button>
          <input type="hidden" id="uuid_act" >
          <input type="hidden" id="uuid_prod" >
        </div>
      </div>
    </div>
  </div>
 <!-- Fin Modal para editar nuevo rango -->

