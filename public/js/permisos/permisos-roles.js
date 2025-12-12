$(document).ready(function() {
    // Cargar permisos disponibles al inicio
    cargarPermisosDisponibles();

    // Cargar permisos asignados cuando se selecciona un rol
    $('#roleSelect').change(function() {
        var roleId = $(this).val();
        
        if (roleId) {
            cargarPermisosAsignados(roleId);
        } else {
            // Desmarcar todos los checkboxes si no hay rol seleccionado
            $('#permisosContainer .form-check-input').prop('checked', false);
        }
    });

    // Actualizar permisos del rol
    $('#actualizarPermisos').click(function() {
        var roleId = $('#roleSelect').val();
        var permisosSeleccionados = [];

        if (!roleId) {
            toastr.error('Por favor, selecciona un rol.');
            return;
        }

        // Obtener todos los permisos marcados
        $('input[type="checkbox"]:checked').each(function() {
            var codigo = $(this).val();
            var descripcion = $(this).data('descripcion');
            permisosSeleccionados.push({
                codigo: codigo,
                descripcion: descripcion
            });
        });
       
       // Guardar permisos
        $.ajax({
            url: "/permisos/asignar-multiples",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                role_id: roleId,
                permisos: permisosSeleccionados
            }),
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            success: function(response) {
                toastr.success(response.message);
                // Resetear el select a la opción por defecto
                $('#roleSelect').val('').trigger('change');
                // Desmarcar todos los checkboxes
                $('#permisosContainer .form-check-input').prop('checked', false);
            },
            error: function(xhr, status, error) {
                var errorMsg = xhr.responseJSON?.message || 'Error al actualizar permisos';
                toastr.error(errorMsg);
            }
        });
    });

    // Seleccionar/Deseleccionar todos
    $(document).on("click", "#toggleChecks", function () {
        var checkboxes = $("#permisosContainer .form-check-input");

        if (checkboxes.length === 0) {
            toastr.info('Primero selecciona un rol');
            return;
        }

        var allChecked = checkboxes.length > 0 && checkboxes.filter(":checked").length === checkboxes.length;

        if (allChecked) {
            checkboxes.prop("checked", false);
            $("#toggleChecks").text('Seleccionar todos');
        } else {
            checkboxes.prop("checked", true);
            $("#toggleChecks").text('Deseleccionar todos');
        }
    });

    // Función para cargar permisos disponibles al inicio
    function cargarPermisosDisponibles() {
        $.ajax({
            url: "/permisos/disponibles",
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            success: function(permisos) {
                renderizarPermisos(permisos);
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener permisos:', error);
                toastr.error('Error al cargar los permisos disponibles');
            }
        });
    }

    // Función para cargar permisos asignados a un rol
    function cargarPermisosAsignados(roleId) {
        $.ajax({
            url: "/permisos/role/" + roleId,
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            success: function(permisosAsignados) {
                // Desmarcar todos primero
                $('#permisosContainer .form-check-input').prop('checked', false);
                
                // Marcar solo los asignados
                $.each(permisosAsignados, function(index, permiso) {
                    $('#permisosContainer input[value="' + permiso.codigo_permiso + '"]').prop('checked', true);
                });
                
                actualizarBotonToggle();
            },
            error: function(xhr) {
                console.error('Error al obtener permisos asignados:', xhr);
            }
        });
    }

    // Función para renderizar los permisos sin separación por módulo
    function renderizarPermisos(permisos) {
        var container = $('#permisosContainer');
        container.empty();

        var card = $(`
            <div class="permisos-card">
                <div class="card">
                    <div class="card-body">
                        <div class="permisos-list"></div>
                    </div>
                </div>
            </div>
        `);

        var permisosList = card.find('.permisos-list');
        
        // Colores para cada permiso
        var colores = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#feca57', '#ee5a6f', '#c471ed'];
        
        // Agregar cada permiso con diseño creativo
        $.each(permisos, function(index, permiso) {
            var color = colores[index % colores.length];
            permisosList.append(`
                <div class="permiso-item" data-color="${color}">
                    <div class="permiso-badge" style="background-color: ${color}"></div>
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               value="${permiso.codigo}" 
                               id="permiso_${permiso.id}"
                               data-descripcion="${permiso.descripcion}"
                               data-color="${color}">
                        <label class="form-check-label" for="permiso_${permiso.id}">
                            <span class="permiso-code">${permiso.codigo}</span>
                            <span class="permiso-separator">•</span>
                            <span class="permiso-desc">${permiso.descripcion}</span>
                        </label>
                    </div>
                </div>
            `);
        });

        container.append(card);
    }

    function actualizarBotonToggle() {
        var checkboxes = $("#permisosContainer .form-check-input");
        var allChecked = checkboxes.length > 0 && checkboxes.filter(":checked").length === checkboxes.length;
        
        if (allChecked) {
            $("#toggleChecks").text('Deseleccionar todos');
        } else {
            $("#toggleChecks").text('Seleccionar todos');
        }
    }

});
