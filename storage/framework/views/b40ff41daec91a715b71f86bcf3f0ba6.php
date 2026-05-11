<link rel="stylesheet" href="css/configuracion/datos_globales.css" />
<script type="text/javascript" src="js/configuracion/datos_globales.js"></script>
<input type="hidden" name="_token" id="token" value="<?php echo e(csrf_token()); ?>">
<div class='row'>
  <div class='col-xs-12'>
      <div style="width:100%">
        <table id='tabla_variables' class="display" style="width:100%">
          <thead>
          <tr style="background-color: #01338d;color:white">
              <th>VARIABLE</th><th>VALOR VARIABLE</th><th>DESCRICPCION VARIABLE</th><th>ACCIONES</th>
          </tr>
          </thead>
          <tbody class="datos">
          </tbody>
        </table>
      </div>
  </div>
</div>

<?php echo $__env->make('partials.modal_ayuda', ['modulo' => 'config_datos_glob'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>





        

<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/configuration/globals_var.blade.php ENDPATH**/ ?>