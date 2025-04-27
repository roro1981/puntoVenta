<script type="text/javascript" src="js/almacen/promociones.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/promociones.css">
<div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header minimal-spacing' style="width:100%">
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        <ul class="nav nav-pills category-pills minimal-spacing" id="categoriasNav">
            <li class="nav-item">
              <a class="nav-link active filter-pill" href="#" data-categoria="">Todas</a>
            </li>
            @foreach($categorias as $categoria)
              <li class="nav-item">
                <a class="nav-link active filter-pill" href="#" data-id="{{ $categoria->id }}" data-categoria="{{ $categoria->descripcion_categoria }}">{{ $categoria->descripcion_categoria }}</a>
              </li>
            @endforeach
        </ul>
        <div id="categoriaSeleccionada">Categoría: Todas</div>
        <table id="tabla_promociones" class="display table table-striped table-bordered" style="width:100%">
            <thead>
              <tr>
                <th>Codigo</th>
                <th>Nombre</th>
                <th>Precio costo</th>
                <th>Precio venta</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>


        

