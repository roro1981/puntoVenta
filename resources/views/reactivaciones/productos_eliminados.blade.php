<script type="text/javascript" src="js/reactivaciones/productos_eliminados.js"></script>
<div class='row'>
  <div class='col-xs-12'>
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <div style="width:100%">
      <div class='box-header' style="width:100%">
        <h4>Productos eliminados</h4>
        <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
      </div>

      <table id='tabla_productos_eliminados' class="display" style="width:100%">
        <thead>
          <tr style="background-color: #2ab9f7;color:white">
            <th>CODIGO</th>
            <th>DESCRIPCION</th>
            <th>CATEGORIA</th>
            <th>FECHA ELIMINACION</th>
            <th>USUARIO ELIMINACION</th>
            <th>ACCIONES</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

@include('partials.modal_ayuda', ['modulo' => 'reactiv_productos'])
