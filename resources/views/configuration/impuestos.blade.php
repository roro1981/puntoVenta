<link rel="stylesheet" href="css/configuracion/impuestos.css" />
<script type="text/javascript" src="js/configuracion/impuestos.js"></script>
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
<div class='row'>
  <div class='col-xs-12'>
      <div style="width:100%">
        <table id='tabla_impuestos' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #01338d;color:white">
              <th>NOMBRE IMPUESTO</th><th>VALOR IMPUESTO</th><th>DESCRICPCION DESCRIPCION</th><th>ULTIMA ACTUALIZACIÓN</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>



        

