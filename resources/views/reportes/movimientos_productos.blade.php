<head>
    <link rel="stylesheet" href="css/reportes/movimientos_productos.css" />
    <script type="text/javascript" src="js/reportes/movimientos_productos.js"></script>
 </head>
<input type="hidden" id="datos_repo" />
<input type="hidden" id="datos_repo2" />
<input type="hidden" id="tmov" />
<input type="hidden" id="idpr" />
<input type="hidden" id="fde" />
<input type="hidden" id="fha" />
        <div id="cabecera2"> 
             <div style="margin-top:5px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                <label>TIPO MOVIMIENTO(*):</label>
                <select class="form-control" id="tip_movi">
                    <option value="0" selected>-- Seleccione --</option> 
                    <option value="1">TODOS</option>
                    <option value="2">VENTAS</option> 
                    <option value="3">ENTRADAS</option> 
                    <option value="4">SALIDAS</option> 
                    <option value="5">MERMAS</option> 
                    <option value="6">FACTURAS</option> 
                    <option value="7">BOLETAS</option>
                </select>     
            </div>
            <div style="margin-top:5px" class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                            <label>PRODUCTO(*):</label>
                            
                            <select id="produ_movi" class="selectpicker form-control" data-live-search="true" required="campo obligatorio">
                            <option value="0">-- Seleccione producto --</option>  
                                @foreach($productos as $prod)
                                    <option value="{{ $prod->uuid }}">{{ $prod->descripcion }} ({{ $prod->stock }})</option>
                                @endforeach
                            </select>  
                           
            </div>
             <div style="margin-top:5px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                <label>DESDE(*):</label>
                <input id="fecha_desde" class="form-control" readonly required />
             </div>
             <div style="margin-top:5px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                <label>HASTA(*):</label>
                <input id="fecha_hasta" class="form-control" readonly required />
             </div>
              <div style="margin-top:30px" class="form-group col-lg-1 col-md-1 col-sm-1 col-xs-12">
                <button class="btn btn-info" id="btn_ver"><i class="fa fa-archive"></i> Generar</button>
             </div>
             <div style="margin-top:30px" class="form-group col-lg-2 col-md-2 col-sm-2 col-xs-12">
                <button class="btn btn-success" id="excel"><i class="fa fa-file-excel-o"></i> Exportar a excel</button>
             </div>
        </div>
         
      <div id="tabla" class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
        <table id="tbl_movis" class="table table-responsive table-hover table-fixed">
          <thead style="background-color:#A9D0F5">
                <tr>
                <th style="text-align:center">Fecha movimiento</th>
                <th>Producto</th>
                <th style="text-align:center">Tipo movimiento</th>
                <th style="text-align:center">Cantidad</th>
                <th style="text-align:center">Stock</th>
                <th style="text-align:center">Observación</th>
            </tr></thead>
            <tbody class='movis_det'>
              
            </tbody>
        </table>
        
      </div>
      