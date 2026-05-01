<script type="text/javascript" src="js/almacen/productos.js"></script>
<div class='row'>
  <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header' style="width:100%">
            <button class="btn btn-success" data-toggle="modal" id="nuevo_user" data-target="#modalNuevoProducto"><i class='fa fa fa-save'></i>Nuevo Producto</button>
          <button class="btn btn-info" type="button" data-toggle="modal" data-target="#modalCargaMasivaProductos"><i class="fa fa-upload"></i>Carga Masiva Excel</button>
          <a class="btn btn-primary" href="{{ route('productos.plantilla.xlsx') }}"><i class="fa fa-download"></i>Descargar Plantilla Excel</a>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        
        <table id='tabla_productos' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #2ab9f7;color:white">
              <th>CODIGO</th><th>PRODUCTO</th><th>PRECIO VENTA</th><th>CATEGORIA</th><th>FOTO</th><th>FECHA CREACION</th><th>ULTIMA MODIFICACIÓN</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>

<!-- Modal para crear nuevo producto -->
<div class="modal fade" id="modalNuevoProducto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel">Nuevo Producto</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <form id="createProdForm" autocomplete="off">
          <div class="form-group">
            <label for="codigo">Código</label>
            <input type="text" class="form-control" id="codigo" name="codigo" maxlength="255">
          </div>
          <div class="form-group">
            <label for="descripcion">Descripción</label>
            <input type="text" class="form-control" id="descripcion" name="descripcion" maxlength="255">
          </div>
          <div class="form-group">
            <label for="precio_compra_neto">Precio Compra Neto</label>
            <input type="number" class="form-control" oninput="calcula3();" id="precio_compra_neto" name="precio_compra_neto" step="0.01" >
          </div>
          <div class="form-group">
            <label for="impuesto_1">Impuesto</label>
            <select class="form-control" onchange="calcula(this.value);" id="impuesto_1" name="impuesto_1" >
              <option value="0">Seleccione opción</option>
              @foreach($impuesto_iva as $impuesto)
                <option value="{{ $impuesto->id }}">{{ $impuesto->nom_imp }} ({{ $impuesto->valor_imp }}%)</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="impuesto_2">Impuesto adicional</label>
            <select class="form-control" onchange="calcula2(this.value);" id="impuesto_2" name="impuesto_2">
              <option value="0" selected>Seleccione opción</option>
              @foreach($impuesto_ad as $impuesto)
                <option value="{{ $impuesto->id }}">{{ $impuesto->nom_imp }} ({{ $impuesto->valor_imp }}%)</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="precio_compra_bruto">Precio Compra Bruto</label>
            <input type="number" class="form-control" id="precio_compra_bruto" name="precio_compra_bruto" disabled >
          </div>
          <div class="form-group">
            <label for="margen">Margen</label>
            <div style="display: flex">
              <input type="number" class="form-control" id="margen" step="0.01">
              <button type="button" class="btn btn-success" id="calcular-margen">Calcular</button>
            </div>
           
          </div>
          <div class="form-group">
            <label for="precio_venta">Precio Venta Público</label>
            <input type="number" class="form-control" id="precio_venta" name="precio_venta" step="1" >
          </div>
          <div class="form-group">
            <label for="categoria">Categoría</label>
            <select class="form-control" id="categoria" name="categoria">
              <option value="">Seleccione opción</option>
              @foreach($categorias as $categoria)
              <option value="{{ $categoria->id }}">{{ $categoria->descripcion_categoria }}</option>
            @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="stock_minimo">Stock Mínimo</label>
            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" step="1">
          </div>
          <div class="form-group">
            <label for="unidad_medida">Unidad de medida</label>
            <select class="form-control" id="unidad_medida" name="unidad_medida" >
              <option value="0">Seleccione opción</option>
              <option value="UN">UNIDAD</option>
              <option value="L">LITRO</option>
              <option value="KG">KILOGRAMO</option>
              <option value="CJ">CAJA</option>
            </select>
          </div>
          <div class="form-group">
            <label for="tipo">Tipo</label>
            <select class="form-control" id="tipo" name="tipo" >
              <option value="0">Seleccione opción</option>
              <option value="P">PRODUCTO</option>
              <option value="S">NO AFECTO A STOCK</option>
              <option value="I">INSUMO</option>
            </select>
          </div>
          <div class="form-group">
            <label for="image">Foto producto</label>
            <div class="card" style="width: auto;">
                <div style="display: flex;gap: 20px;">
                  <img class="card-img-top" style="border:1px solid blue;margin-left: 15px;" src="/img/fotos_prod/sin_imagen.jpg" width="200" height="100" >
                  <input type="file" style="margin-top:30px" title="Solo formato jpg,png o gif" class="form-control-file" id="image">
                </div>
                <div class="form-group" style="text-align: center;">
                  <input type="button" style="margin-top:10px" class="btn btn-success upload" value="Subir">
                  <input type="hidden" id="nom_foto" name="nom_foto">
                </div> 
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" form="createProdForm">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!-- Fin Modal para crear nuevo producto -->
<!-- Modal de Edición producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" role="dialog" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalEditarProductoLabel">Editar Producto</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_token" id="token_editar" value="{{ csrf_token() }}">
        <input type="hidden" id="producto_uuid" name="producto_uuid"> <!-- Campo oculto para el ID del producto -->
        <form id="editProdForm" autocomplete="off">
          <div class="form-group">
            <label for="codigo_editar">Código</label>
            <input type="text" class="form-control" id="codigo_editar" name="codigo_editar" maxlength="255" disabled>
          </div>
          <div class="form-group">
            <label for="descripcion_editar">Descripción</label>
            <input type="text" class="form-control" id="descripcion_editar" name="descripcion_editar" maxlength="255">
          </div>
          <div class="form-group">
            <label for="precio_compra_neto_editar">Precio Compra Neto</label>
            <input type="number" oninput="calcula3();" class="form-control" id="precio_compra_neto_editar" name="precio_compra_neto_editar" step="0.01">
          </div>
          <div class="form-group">
            <label for="impuesto_1_editar">Impuesto</label>
            <select class="form-control" onchange="calcula(this.value);" id="impuesto_1_editar" name="impuesto_1_editar">
              <option value="0">Seleccione opción</option>
              @foreach($impuesto_iva as $impuesto)
                <option value="{{ $impuesto->id }}">{{ $impuesto->nom_imp }} ({{ $impuesto->valor_imp }}%)</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="impuesto_2_editar">Impuesto adicional</label>
            <select class="form-control" onchange="calcula2(this.value);" id="impuesto_2_editar" name="impuesto_2_editar">
              <option value="0" selected>Seleccione opción</option>
              @foreach($impuesto_ad as $impuesto)
                <option value="{{ $impuesto->id }}">{{ $impuesto->nom_imp }} ({{ $impuesto->valor_imp }}%)</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="precio_compra_bruto_editar">Precio Compra Bruto</label>
            <input type="number" class="form-control" id="precio_compra_bruto_editar" name="precio_compra_bruto_editar" disabled>
          </div>
          <div class="form-group">
            <label for="margen_editar">Margen</label>
            <div style="display: flex">
              <input type="number" class="form-control" id="margen_editar" step="0.01">
              <button type="button" class="btn btn-success" id="calcular-margen-editar">Calcular</button>
            </div>
          </div>
          <div class="form-group">
            <label for="precio_venta_editar">Precio Venta Público</label>
            <input type="number" class="form-control" id="precio_venta_editar" name="precio_venta_editar" step="1">
          </div>
          <div class="form-group">
            <label for="categoria_editar">Categoría</label>
            <select class="form-control" id="categoria_editar" name="categoria_editar">
              <option value="">Seleccione opción</option>
              @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->descripcion_categoria }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="stock_minimo_editar">Stock Mínimo</label>
            <input type="number" class="form-control" id="stock_minimo_editar" name="stock_minimo_editar" step="1">
          </div>
          <div class="form-group">
            <label for="unidad_medida_editar">Unidad de medida</label>
            <select class="form-control" id="unidad_medida_editar" name="unidad_medida_editar" >
              <option value="0">Seleccione opción</option>
              <option value="UN">UNIDAD</option>
              <option value="L">LITRO</option>
              <option value="KG">KILOGRAMO</option>
              <option value="CJ">CAJA</option>
            </select>
          </div>
          <div class="form-group">
            <label for="tipo_editar">Tipo</label>
            <select class="form-control" id="tipo_editar" name="tipo_editar">
              <option value="0">Seleccione opción</option>
              <option value="P">PRODUCTO</option>
              <option value="S">NO AFECTO A STOCK</option>
              <option value="I">INSUMO</option>
            </select>
          </div>
          <div class="form-group">
            <label for="image_editar">Foto producto</label>
            <div class="card" style="width: auto;">
              <div style="display: flex;gap: 20px;">
                <img class="card-img-top" style="border:1px solid blue;margin-left: 15px;" id="imagen_editar" src="/img/fotos_prod/sin_imagen.jpg" width="200" height="100">
                <input type="file" style="margin-top:30px" title="Solo formato jpg,png o gif" class="form-control-file" id="image_editar">
              </div>
              <div class="form-group" style="text-align: center;">
                <input type="button" style="margin-top:10px" class="btn btn-success upload" value="Subir">
                <input type="hidden" id="nom_foto_editar" name="nom_foto_editar">
              </div> 
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="guardarCambios">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>
        

<div class="modal fade" id="modalCargaMasivaProductos" tabindex="-1" role="dialog" aria-labelledby="modalCargaMasivaProductosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalCargaMasivaProductosLabel">Carga Masiva de Productos por Excel</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="importProductsExcelForm" enctype="multipart/form-data">
          <div class="form-group">
            <label for="archivo_excel">Archivo Excel</label>
            <input type="file" class="form-control-file" id="archivo_excel" name="archivo_excel" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
            <small class="form-text text-muted">La plantilla trae una hoja llamada Productos para cargar datos y otra hoja separada llamada Instrucciones.</small>
          </div>
        </form>

        <div class="alert alert-info" style="margin-top: 15px;">
          <strong>Columnas que debe completar en la hoja Productos:</strong>
          <br>codigo: obligatorio y único.
          <br>descripcion: obligatoria y única.
          <br>precio_compra_neto: obligatorio.
          <br>impuesto_1: obligatorio. Puede escribir el nombre del impuesto o su ID.
          <br>impuesto_2: opcional. Puede escribir el nombre del impuesto, su ID o dejarlo vacío.
          <br>precio_compra_bruto: opcional. Si lo deja vacío, el sistema lo calcula.
          <br>precio_venta: obligatorio.
          <br>stock_minimo: opcional.
          <br>categoria: obligatoria. Debe escribir el nombre exacto de la categoría; el sistema la convierte al ID.
          <br>unidad_medida: obligatoria. Acepta UN, L, KG, CJ o los textos UNIDAD, LITRO, KILOGRAMO, CAJA.
          <br>tipo: obligatorio. Acepta P, S, I, PR, R o los textos PRODUCTO, NO AFECTO A STOCK, INSUMO, PROMOCION, RECETA.
          <br>nom_foto: opcional. Solo debe informar una ruta ya existente en el sistema.
        </div>

        <div class="alert alert-warning">
          <strong>Categorías activas en el sistema:</strong>
          {{ $categorias->pluck('descripcion_categoria')->implode(', ') }}
          <br><strong>Impuestos disponibles:</strong>
          IVA: {{ $impuesto_iva->pluck('nom_imp')->implode(', ') }}
          @if($impuesto_ad->count() > 0)
            <br>Adicionales: {{ $impuesto_ad->pluck('nom_imp')->implode(', ') }}
          @endif
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-primary" href="{{ route('productos.plantilla.xlsx') }}">Descargar Plantilla</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" id="btnImportProductsExcel">Importar Excel</button>
      </div>
    </div>
  </div>
</div>

@include('partials.modal_ayuda', ['modulo' => 'almacen_productos'])


