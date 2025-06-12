
<script type="text/javascript" src="js/compras/entradas_salidas.js"></script>
<link rel="stylesheet" type="text/css" href="css/compras/entradas_salidas.css">
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
<div id="cabecera2"> 
    <div style="margin-top:5px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
       <label>TIPO MOVIMIENTO(*):</label>
       <select class="form-control" id="tip_movi">
           <option value="0" selected>-- Seleccione --</option> 
           <option value="E">ENTRADA (+)</option> 
           <option value="S">SALIDA (-)</option> 
           <option value="M">MERMA (-)</option> 
       </select>     
   </div>
   <div style="margin-top:5px" class="form-group col-lg-6 col-md-2 col-sm-2 col-xs-12">
      <label>PRODUCTO(*):</label>
      <input type="text" class="form-control" id="produ_movi" required>
      <div id="listaProds" class="suggestion-box" style="display: none;"></div>
      <input type="hidden" id="cod">
      <input type="hidden" id="descrip">
   </div>
    <div style="margin-top:5px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
       <label>CANTIDAD(*):</label>
       <input type="number" class="form-control" id="canti_mov" required>
    </div>
     <div style="margin-top:30px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
       <button class="btn btn-primary" id="btn_cargar"><i class="fa fa-tasks"></i> Cargar</button>
    </div>
</div>

<div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
<table id="tbl_movis" class="table table-responsive table-hover table-fixed">
 <thead style="background-color:#A9D0F5">
       <tr><th style="width:10px"><button id="grabar_movs" type="button" class="btn btn-danger"> Grabar movimientos</button></th>
       <th>Código Producto</th>
       <th>Producto</th>
       <th style="text-align:center">Cantidad</th>
       <th style="text-align:center">Tipo mov.</th>
       <th style="text-align:center">Observación</th>
   </tr></thead>
   <tbody class='movis'>
     
   </tbody>
</table>
</div>