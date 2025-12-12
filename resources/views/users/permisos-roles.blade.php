<script type="text/javascript" src="js/permisos/permisos-roles.js"></script>
<link rel="stylesheet" href="css/users/permisos.css" />
<div class="container">
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <div class="row">
        <h4>Permisos asociados a cada rol</h4>
        <select id="roleSelect" class="form-select">
            <option value="">Seleccione rol</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->role_name }}</option>
            @endforeach
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
