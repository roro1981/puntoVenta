
  <script type="text/javascript" src="js/almacen/crear_recetas.js"></script>
  <link rel="stylesheet" type="text/css" href="css/almacen/crear_recetas.css">
  
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
          id="insumo" 
          autocomplete="off"
          style="width:150px; font-size:15px; text-align:center; font-weight:bold; display:inline-block;"
          autofocus 
          tabindex="1"
      >
  
      <div 
          id="listaResultados"
          class="suggestion-box"
          style="display: none;">
      </div>
  </div>
       <div class="form-group">
       <span style="width:85px;font-size:15px;font-weight: bold;" class="form-control bg-blue">Cantidad</span><input type='text' class="form-control" autocomplete="off" id='cant_insumo' onkeypress="return soloNumeros(event)" style="width:90px;font-size:15px; text-align:center; font-weight: bold;" tabindex="2">		   
           </div>
             <button type="button" id="btn_guardar_rec" class="btn btn-primary">Grabar nueva receta</button>
   </form>
    </div>	
    
      <div class='col-md-8'>
     <form class="form-inline" role="form">
           <div>
           <div class='box-header'>
           <h3 class='box-title'>Detalle de receta</h3>
           </div>
           
           <div class="box-body table-responsive" style="max-height: calc(100vh - 250px); overflow-y: auto;">
            
            <table id="tabla_recetas" class="table table-striped table-hover mb-0">
                <thead class="sticky-top bg-light">
            <tr>
            <th class='center col-xs-2'>COD</th><th class='center col-xs-4'>DESCRIP</th><th title="Unidad de medida" class='center col-xs-1'>UM</th><th class='center col-xs-1'>CANT</th><th class='center col-xs-2'>UNITARIO</th><th class='center col-xs-1'>TOTAL</th><th class="col-xs-1">ACCIONES</th>
            </tr>
            </thead>
            <tbody class="listado">
            </tbody>
           </table>
                  
           </div>
           </div>
          
     </form>
      </div>
           <div class="col-md-4" style="margin-top:-40px">
               <!-- small box -->
               <div class="small-box bg-olive-active" style="margin-bottom:10px">
                 <div class="inner">
                   <h3><div id='costo_receta'>0</div></h3>
                   <p>Costo receta</p>
                 </div>
                 <div class="icon">
                   <i class="fa fa-cutlery"></i>
                 </div>
                 <a href="#" class="small-box-footer">
                   <div id='total_items'>Items: 0</div>
                 </a>
                 
               </div>
             <form class="form-inline" role="form">  
              <span style="width:140.63px" class="form-control bg-blue-gradient">C&oacute;digo Receta (*)</span><input type='text' class="form-control" id='cod_receta' autocomplete="off" style="width:252px;font-size:15px;"> 
              <span style="width:140.63px" class="form-control bg-blue-gradient">Nombre Receta (*)</span><input type='text' class="form-control" id='nom_receta' autocomplete="off" style="width:252px;font-size:15px;"> 
              <span style="width:140.63px" class="form-control bg-blue-gradient">Margen (%)</span><input type='text' class="form-control" id="margen" title="Ingrese margen y se calculará automáticamente el precio de venta" autocomplete="off"  style="width:252px;font-size:15px; text-align:center;font-weight: bold;" >
              <span style="width:140.63px" class="form-control bg-blue-gradient">Precio Venta (*)</span><input type='text' class="form-control" id='precio_venta' title="Ingrese precio de venta y se calculará automáticamente el margen de ganancia" autocomplete="off" style="width:252px;font-size:15px;text-align:center;font-weight: bold"> 
              <span style="width:140.63px" class="form-control bg-blue-gradient">Categor&iacute;a (*)</span>
              <select class="form-control" id='categoria' style="width:250px;font-size:15px;font-weight: bold"> 
              <option value=0></option>
              @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->descripcion_categoria }}</option>
              @endforeach
             </select> 
             <textarea id="desc_receta" style="margin-top:5px" placeholder="Preparacion receta (opcional)" class="md-textarea form-control" rows="5" cols="55"></textarea>
              <form enctype="multipart/form-data">
                      <div class="card" style="width: 390px;border:solid 1px">
                        <img class="card-img-top" style="border:1px solid blue" src="/img/fotos_prod/sin_imagen.jpg" width="100" height="100" >
                          <div class="card-body">
                              <div class="form-group">
                                  <label for="image" style="width:150px">Foto receta</label>
                                  <input type="file" title="Solo formato jpg,png o gif" class="form-control-file" id="image">
                              </div>
                              <input type="button" class="btn btn-primary upload" value="Subir">
                              <input type="hidden" id="foto_receta" name="foto_receta">
                          </div>
                          
                      </div>
                  </form>
                  <input id="nom_foto_rec" type="hidden" />
             </form>
             </div>
           </div>