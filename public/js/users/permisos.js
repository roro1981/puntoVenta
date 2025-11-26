$(document).ready(function() {
    $('#roleSelect').change(function() {
        var roleId = $(this).val();
        if (roleId) {
            $.ajax({
                url: "/permisos/get-menus", 
                type: "POST",
                data: {
                    role_id: roleId
                },
                headers: {
                    'X-CSRF-TOKEN': $("#token").val()
                },
                success: function(data) {
                    var menusContainer = $('#menusContainer');
                    menusContainer.empty();
                    $.each(data.submenus, function(menuId, submenus) {
                        if (submenus.length > 0) {
                            // Crear la card para el menú
                          var card = $(`
                            <div class="menu-card">
                                <div class="card card-with-border">
                                    <div class="card-body">
                                        <h3 class="card-title">${submenus[0].menu_name}</h3>
                                        <div class="form-check-container"></div>
                                    </div>
                                </div>
                            </div>
                        `);
                
                            // Agregar los submenús como checkboxes dentro de la card
                            var formCheckContainer = card.find('.form-check-container');
                            $.each(submenus, function(index, submenu) {
                                var isChecked = data.selectedSubmenus.includes(submenu.id) ? 'checked' : '';
                                formCheckContainer.append(`
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="${submenu.id}" id="submenu_${submenu.id}" ${isChecked}>
                                        <label class="form-check-label" for="submenu_${submenu.id}">
                                            ${submenu.submenu_name}
                                        </label>
                                    </div>
                                `);
                            });
                
                            // Agregar la card al contenedor
                            menusContainer.append(card);
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching menus:', error);
                }
            });
        } else {
            $('#menusContainer').empty();  // Si no hay rol seleccionado, limpia el contenedor
        }
    });

    $('#permisos_act').click(function() {
        var roleId = $('#roleSelect').val();
        var selectedCheckboxes = [];

        // Validar que se haya seleccionado un rol y que haya al menos un checkbox marcado
        if (roleId != 0) {
            $('input[type="checkbox"]:checked').each(function() {
                var id = $(this).attr('id').split('_')[1]; // Obtener el ID del checkbox (parte después del '_')
                selectedCheckboxes.push(id);
            });

            if (selectedCheckboxes.length > 0) {
                // Realizar la llamada AJAX
                $.ajax({
                    url: "/permisos/save",  // Ruta al controlador de Laravel
                    type: "POST",
                    data: {
                        role_id: roleId,
                        selected_submenus: selectedCheckboxes
                    },
                    headers: {
                        'X-CSRF-TOKEN': $("#token").val()
                    },
                    success: function(response) {
                        $('#contenido').load('/usuarios/permisos');
                        toastr.success(response.message);
                    },
                    error: function(xhr, status, error) {
                        toastr.error("Error "+xhr.responseJSON.error+"<br>"+xhr.responseJSON.message);
                    }
                });
            } else {
                toastr.error('Por favor, selecciona al menos un permiso.');
            }
        } else {
            toastr.error('Por favor, selecciona un rol.');
        }
    });
    $(document).on("click", "#toggleChecks", function () {

        var checkboxes = $("#menusContainer .form-check-input");

        var allChecked = checkboxes.length > 0 && checkboxes.filter(":checked").length === checkboxes.length;

        if (allChecked) {
            checkboxes.prop("checked", false);
            $("#toggleChecks").text("Seleccionar todos");
        } else {
            checkboxes.prop("checked", true);
            $("#toggleChecks").text("Deseleccionar todos");
        }
    });
});