<script type="text/javascript" src="js/almacen/recetas.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/recetas.css">
<div class='row'>
  <input type="hidden" name="_token" id="token" value="<?php echo e(csrf_token()); ?>">
  <div class='col-xs-12'>
      <div style="width:100%">
        <div class='box-header minimal-spacing' style="width:100%">
          <button class="btn btn-warning" id="btnGenerarEtiquetasRecetas" disabled title="Generar etiquetas de las recetas seleccionadas"><i class="fa fa-barcode"></i> Etiquetas <span id="contadorSeleccionadosRecetas">(0)</span></button>
          <button class="btn btn-default" id="btnSeleccionarFiltradosRecetas" title="Selecciona todas las recetas visibles (incluye todas las páginas)"><i class="fa fa-check-square-o"></i> Sel. todos filtrados</button>
          <button class="btn btn-default" id="btnLimpiarSeleccionRecetas" title="Limpia toda la selección actual"><i class="fa fa-square-o"></i> Limpiar selección</button>
            <hr style="height:1px;background-color: brown;width:100%;margin-top: 2pt;" />
        </div>
        <ul class="nav nav-pills category-pills minimal-spacing" id="categoriasNav">
            <li class="nav-item">
              <a class="nav-link active filter-pill" href="#" data-categoria="">Todas</a>
            </li>
            <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <li class="nav-item">
                <a class="nav-link active filter-pill" href="#" data-id="<?php echo e($categoria->id); ?>" data-categoria="<?php echo e($categoria->descripcion_categoria); ?>"><?php echo e($categoria->descripcion_categoria); ?></a>
              </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <div id="categoriaSeleccionada">Categoría: Todas</div>
        <table id="tabla_recetas" class="display table table-striped table-bordered" style="width:100%">
            <thead>
              <tr>
                <th style="width:30px"><input type="checkbox" id="selectAllRecetas" title="Seleccionar/deseleccionar todos"></th>
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

<?php echo $__env->make('partials.modal_ayuda', ['modulo' => 'almacen_recetas'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<!-- Formulario oculto para etiquetas masivas de recetas -->
<form id="formEtiquetasRecetasMasivas" action="<?php echo e(route('recetas.etiquetas.masivas')); ?>" method="POST" target="_blank" style="display:none">
  <?php echo csrf_field(); ?>
  <input type="hidden" name="cantidad" id="etiquetas_recetas_cantidad" value="1">
</form>

<!-- Modal etiqueta individual receta -->
<div class="modal fade" id="modalEtiquetaRecetaIndividual" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-barcode"></i> Generar etiquetas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="etiqueta_receta_uuid">
        <div class="form-group">
          <label for="inputCantidadRecetaIndividual">Cantidad de etiquetas</label>
          <input type="number" class="form-control" id="inputCantidadRecetaIndividual" value="15" min="1" max="100">
          <small class="text-muted">Se imprimirán en grilla (3 por fila) en hoja A4.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="btnConfirmarEtiquetaRecetaIndividual"><i class="fa fa-file-pdf-o"></i> Generar PDF</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal etiquetas masivas recetas -->
<div class="modal fade" id="modalEtiquetasRecetasMasivas" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-barcode"></i> Etiquetas masivas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label for="inputCantidadRecetasMasiva">Copias por receta</label>
          <input type="number" class="form-control" id="inputCantidadRecetasMasiva" value="1" min="1" max="50">
          <small class="text-muted" id="infoSeleccionadosRecetas"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="btnConfirmarEtiquetasRecetasMasivas"><i class="fa fa-file-pdf-o"></i> Generar PDF</button>
      </div>
    </div>
  </div>
</div>

        

<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/almacen/recetas.blade.php ENDPATH**/ ?>