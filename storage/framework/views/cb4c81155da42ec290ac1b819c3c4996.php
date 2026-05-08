<script type="text/javascript" src="js/permisos/permisos-roles.js"></script>
<link rel="stylesheet" href="css/users/permisos.css" />
<div class="container">
    <input type="hidden" name="_token" id="token" value="<?php echo e(csrf_token()); ?>">
    <div class="row">
        <h4>Permisos asociados a cada rol</h4>
        <select id="roleSelect" class="form-select">
            <option value="">Seleccione rol</option>
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($role->id); ?>"><?php echo e($role->role_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button style="margin-left:100px" class="btn btn-dropbox" data-toggle="modal" id="actualizarPermisos"><i class='fa fa fa-list'></i> Actualizar permisos</button>
        <button id="toggleChecks" class="btn btn-secondary ms-2">Seleccionar todos</button>
    </div>    
    <br>
      
</div>
<div class="container-fluid px-0">
    <div id="permisosContainer" class="row">
    
    </div>  
</div>

<?php echo $__env->make('partials.modal_ayuda', ['modulo' => 'permisos_roles'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/users/permisos-roles.blade.php ENDPATH**/ ?>