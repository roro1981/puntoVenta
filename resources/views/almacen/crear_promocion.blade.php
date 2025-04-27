
<script type="text/javascript" src="js/almacen/crear_promocion.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/crear_promocion.css">

<div class='row'>
<input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
<div class='col-md-8' >
 <form class="form-inline" role="form">      
  <div class="form-group" style="position: relative; display: inline-block;">
    <span style="width:140px; font-size:15px; font-weight:bold; display:inline-block;" 
          class="form-control bg-aqua">
        Ingrese código
    </span>

    <!-- Campo de texto -->
    <input 
        type="text" 
        class="form-control" 
        id="producto" 
        autocomplete="off"
        style="width:150px; font-size:15px; text-align:center; font-weight:bold; display:inline-block;"
        autofocus 
        tabindex="1"
    >

    <div 
        id="listaProductos"
        class="suggestion-box"
        style="display: none;">
    </div>
</div>
     <div class="form-group">
     <span style="width:85px;font-size:15px;font-weight: bold;" class="form-control bg-blue">Cantidad</span><input type='text' class="form-control" autocomplete="off" id='cant_producto' onkeypress="return soloNumeros(event)" style="width:90px;font-size:15px; text-align:center; font-weight: bold;" tabindex="2">
         </div>
           <button type="button" id="btn_guardar_promo" class="btn btn-primary">Grabar nueva promocion</button>
 </form>
</div>
  
    <div class='col-md-8'>
   <form class="form-inline" role="form">
         <div>
         <div class='box-header'>
         <h3 class='box-title'>Detalle de promocion</h3>
         </div>
         
         <div class="box-body table-responsive" style="max-height: calc(100vh - 250px); overflow-y: auto;">
            
        <table id="tabla_detalle_promo" class="table table-striped table-hover mb-0">
            <thead class="sticky-top bg-light">
            <tr>
            <th class='center col-xs-2'>COD</th><th class='center col-xs-4'>DESCRIP</th><th title="Unidad de medida" class='center col-xs-1'>UM</th><th class='center col-xs-1'>CANT</th><th class='center col-xs-2'>UNITARIO</th><th class='center col-xs-1'>TOTAL</th><th class="col-xs-1">ACCIONES</th>
            </tr>
            </thead>
            <tbody class="listado_prods">
            </tbody>
         </table>
                
         </div>
         </div>
        
   </form>
    </div>
         <div class="col-md-4" style="margin-top:-40px">
             <!-- small box -->
             <div class="small-box active" style="margin-bottom:10px; background-color:cyan">
               <div class="inner">
                 <h3><div id='costo_promo'>0</div></h3>
                 <p>Costo promoción</p>
               </div>
               <div class="icon">
                 <i class="fa fa-cutlery"></i>
               </div>
               <a href="#" style="color:black" class="small-box-footer">
                 <div id='total_items'>Items: 0</div>
               </a>
               
             </div>
            <form class="form-inline" role="form">
                    <span style="width:140.63px" class="form-control bg-blue-gradient">C&oacute;digo (*)</span><input type='text' class="form-control" id='cod_promo' autocomplete="off" style="width:252px;font-size:15px;"> 
                    <span style="width:140.63px" class="form-control bg-blue-gradient">Nombre (*)</span><input type='text' class="form-control" id='nom_promo' autocomplete="off" style="width:252px;font-size:15px;"> 
                    <span style="width:140.63px" class="form-control bg-blue-gradient">Margen (%)</span><input type='text' class="form-control" id="margen" title="Ingrese margen y se calculará automáticamente el precio de venta" autocomplete="off"  style="width:252px;font-size:15px; text-align:center;font-weight: bold;" >
                    <span style="width:140.63px" class="form-control bg-blue-gradient">Precio Venta (*)</span><input type='text' class="form-control" id='precio_venta' title="Ingrese precio de venta y se calculará automáticamente el margen de ganancia" autocomplete="off" style="width:252px;font-size:15px;text-align:center;font-weight: bold"> 
                    <span style="width:140.63px" class="form-control bg-blue-gradient">Categor&iacute;a (*)</span>
                    <select class="form-control" id='categoria' style="width:250px;font-size:15px;font-weight: bold"> 
                        <option value=0></option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->descripcion_categoria }}</option>
                        @endforeach
                    </select>
            </form>
            <div class="card mt-3" style="border:1px solid #ccc; padding:15px; background-color: #f9f9f9;">
                <h4 class="text-primary" style="margin-bottom:15px;">
                    <i class="fa fa-calendar-alt"></i> Vigencia de la promoción
                </h4>
            
                <div class="form-group mb-2">
                    <label for="fecha_inicio" class="font-weight-bold">Fecha de inicio</label>
                    <input type="date" id="fecha_inicio" class="form-control" style="font-size:14px;">
                </div>
            
                <div class="form-group mb-2">
                    <label for="fecha_termino" class="font-weight-bold">Fecha de término</label>
                    <input type="date" id="fecha_termino" class="form-control" style="font-size:14px;">
                </div>
            
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" value="" id="sin_fecha">
                    <label class="form-check-label" for="sin_fecha">
                        Esta promoción estará siempre activa
                    </label>
                </div>
            </div>
        </div>
    </div>