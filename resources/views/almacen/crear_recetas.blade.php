<style>
    .table-fixed thead {
  width: 97%;
  }
  .table-fixed tbody {
  height: 300px;
  overflow-x: auto;
  overflow-y: auto;
  width: 99%;
  }
  .table-fixed thead, .table-fixed tbody, .table-fixed tr, .table-fixed td, .table-fixed th {
  display: block;
  }
  .table-fixed tbody td, .table-fixed thead > tr> th {
  display: inline-block;
  border-bottom-width: 0;
  }
  </style>
  <script type="text/javascript" src="js/almacen/crear_recetas.js"></script>
  </head>
  
  <div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class='col-md-8' >
   <form class="form-inline" role="form">      
       <div class="form-group">
       <span style="width:140px;font-size:15px;font-weight: bold;" class="form-control bg-aqua">Ingrese codigo</span><input type='text' class="form-control" id='insumo' autocomplete="off" style="width:150px;font-size:15px; text-align:center;font-weight: bold;" autofocus tabindex="1">	
           <button type='button' id="busca_ins" class='btn btn-success'><i class="fa fa-search"></i></button>
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
           
           <div class='box-body table-responsive' >
              
           <table id='tabla_recetas' class='table table-striped table-responsive table-hover table-fixed' >
            <thead>
            <tr>
            <th class='center col-xs-2'>COD</th><th class='center col-xs-5'>DESCRIP</th><th class='center col-xs-1'>CANT</th><th class='center col-xs-2'>UNITARIO</th><th class='center col-xs-1'>TOTAL</th><th class="col-xs-1"></th>
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
              <span style="width:118.63px" class="form-control bg-blue-gradient">C&oacute;digo Receta</span><input type='text' class="form-control" id='cod_receta' autocomplete="off" style="width:217px;font-size:15px;"> 
              <span style="width:118.63px" class="form-control bg-blue-gradient">Nombre Receta</span><input type='text' class="form-control" id='nom_receta' autocomplete="off" style="width:217px;font-size:15px;"> 
              <span style="width:118.63px" class="form-control bg-blue-gradient">Margen (%)</span><input type='text' class="form-control" id="margen" title="Ingrese margen y luego presione ENTER para calcular precio de venta (Siempre y cuando el costo de la receta sea mayor a 0)" autocomplete="off"  style="width:217px;font-size:15px; text-align:center;font-weight: bold;" onkeypress="return soloNumeros2(event)" >
              <span style="width:118.63px" class="form-control bg-blue-gradient">Precio Venta</span><input type='text' class="form-control" id='precio_venta' title="Ingrese precio de venta y luego presione ENTER para calcular margen de ganancia (Siempre y cuando el costo de la receta sea mayor a 0)" autocomplete="off" onkeypress="return soloNumeros2(event)" style="width:217px;font-size:15px;text-align:center;font-weight: bold"> 
              <span style="width:118.63px" class="form-control bg-blue-gradient">Categor&iacute;a</span>
              <select class="form-control" id='categoria' style="width:214px;font-size:15px;font-weight: bold"> 
              <option value=0></option>
              @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->descripcion_categoria }}</option>
              @endforeach
             </select> 
             <textarea id="desc" style="margin-top:5px" placeholder="Preparacion receta (opcional)" class="md-textarea form-control" rows="5" cols="49"></textarea>
             <form enctype="multipart/form-data">
                     <div class="card" style="width: 355px;border:solid 1px">
                      <img class="card-img-top" style="border:1px solid blue" src="/img/fotos_prod/sin_imagen.jpg" width="70" height="70" >
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