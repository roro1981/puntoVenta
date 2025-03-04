<script type="text/javascript" src="js/almacen/recetas.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/recetas.css">
<div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header minimal-spacing' style="width:100%">
            <button class="btn btn-success" data-toggle="modal" id="nuevo_user" data-target="#modalNuevaReceta"><i class='fa fa fa-save'></i>Nueva Receta</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        <ul class="nav nav-pills category-pills minimal-spacing" id="categoriasNav">
            <li class="nav-item">
              <a class="nav-link active filter-pill" href="#" data-categoria="">Todas</a>
            </li>
            
        </ul>
        <div id="categoriaSeleccionada">Categoría: Todas</div>
        <table id="tabla_recetas" class="display table table-striped table-bordered" style="width:100%">
            <thead>
              <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Detalle</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>


        

