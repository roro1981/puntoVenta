$(document).ready(function () {
    cargaUsuarios();

});
$(function () {

    $('#rut').on('input', function () {
        const $this = $(this);
        const raw = $this.val().replace(/[^0-9kK]/g, '').toUpperCase();
        const formatted = formatearRut(raw);
        $this.val(formatted);

        if (raw.length < 2) {
            $('#rutFeedback').addClass('d-none');
            return;
        }

        const esValido = validarRut(raw);
        $('#rutFeedback').toggleClass('d-none', esValido);
    });

    function formatearRut(rutClean) {
        const cuerpo = rutClean.slice(0, -1);
        const dv = rutClean.slice(-1);

        const cuerpoConPuntos = cuerpo
            .split('').reverse().join('')
            .replace(/(\d{3})(?=\d)/g, '$1.')
            .split('').reverse().join('');

        return cuerpoConPuntos + (cuerpo ? '-' : '') + dv;
    }

    function validarRut(rutClean) {
        const cuerpo = rutClean.slice(0, -1);
        let dv = rutClean.slice(-1);

        let suma = 0, multiplicador = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += multiplicador * +cuerpo[i];
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        const resto = suma % 11;
        const dvCalc = resto === 1 ? 'K' : resto === 0 ? '0' : String(11 - resto);

        return dv === dvCalc;
    }

});
$(function () {

    $('.campo-mail').on('input', function () {
        const $input = $(this);
        const esValido = this.checkValidity();
        $input.next('.feedback-mail').toggleClass('d-none', esValido);
    });

});
$(function () {

    $('.campo-url').on('input', function () {
        const esValido = this.checkValidity();            // usa pattern + type=url
        $(this).next('.feedback-url')
            .toggleClass('d-none', esValido);
    });

});
function cargaUsuarios() {

    $('#tabla_proveedores').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/compras/proveedores_list",
            "type": "GET"
        },
        "columns": [
            { "data": "razon_social" },
            { "data": "giro" },
            { "data": "region-comuna" },
            { "data": "fec_creacion" },
            { "data": "fec_modificacion" },
            { "data": "actions" }
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        "pageLength": 10,
        "searching": true,
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}
$('#region').on('change', function () {
    const regionId = $(this).val();
    const $comuna = $('#comuna');

    // Limpia el segundo select
    $comuna.empty().append('<option value="">Cargando...</option>');

    if (!regionId) {
        $comuna.html('<option value="">-- Seleccionar región primero --</option>');
        return;
    }

    $.ajax({
        url: "/compras/" + regionId + "/comunas",
        type: 'GET',
        dataType: 'json',

        success: function (data) {
            $comuna.empty().append('<option value="">-- Seleccionar --</option>');
            $.each(data, function (_, item) {
                $comuna.append(
                    $('<option>', { value: item.id, text: item.nom_comuna })
                );
            });
        },

        error: function () {
            $comuna.html('<option value="">Error al cargar comunas</option>');
        }
    });
});
$('#createUserForm').submit(function (event) {
    event.preventDefault(); // Evita que el formulario se envíe por defecto

    var formData = {
        'name': $('#name').val(),
        'name_complete': $('#name_complete').val(),
        'password': $('#password').val(),
        'role_id': $('#role_id').val()
    };

    $.ajax({
        type: 'POST',
        url: '/users/create', // Ruta a la que se envía la solicitud
        data: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Token de seguridad CSRF
        },
        success: function (data) {
            toastr.success(data.message);
            $("#createUserModal").modal("hide");
            $('#contenido').load('/usuarios/usuarios');
        },
        error: function (xhr, status, error) {
            var errorCode = xhr.status;
            var errorResponse = xhr.responseJSON;

            if (errorResponse) {
                var errorMessages = [];

                $.each(errorResponse.errors, function (field, messages) {
                    $.each(messages, function (index, message) {
                        errorMessages.push(message + "<br>");
                    });
                });

                toastr.error(errorMessages, "Error " + errorCode);
            }
        }
    });
});

$('#editUserForm').submit(function (event) {
    event.preventDefault();

    var formData = {
        'name_complete_edit': $('#name_complete_edit').val(),
        'password_edit': $('#password_edit').val(),
        'role_id_edit': $('#role_id_edit').val()
    };

    var uuid = $("#user_uuid").val();

    $.ajax({
        type: 'PUT',
        url: '/users/' + uuid + '/edit',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('#token').val()
        },
        success: function (data) {
            toastr.success(data.message);
            $("#editUserModal").modal("hide");
            $('#contenido').load('/usuarios/usuarios');
        },
        error: function (xhr, status, error) {
            var errorCode = xhr.status;
            var errorResponse = xhr.responseJSON;

            if (errorResponse) {
                var errorMessages = [];

                $.each(errorResponse.errors, function (field, messages) {
                    $.each(messages, function (index, message) {
                        errorMessages.push(message + "<br>");
                    });
                });

                toastr.error(errorMessages, "Error " + errorCode);
            }
        }
    });
});
$('#editUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var uuid = button.data('uuid');
    var modal = $(this);

    $.ajax({
        type: 'GET',
        url: '/users/' + uuid + '/show',
        success: function (data) {
            modal.find('#name_edit').val(data.name);
            modal.find('#name_complete_edit').val(data.name_complete);
            modal.find('#role_id_edit').val(data.role_id);
            modal.find('#user_uuid').val(uuid);
        }
    });
});
$(document).on('click', '.eliminar', function (event) {
    event.preventDefault();
    var uuid = $(this).data('uuid');
    var nombreUser = $(this).data('nameuser');
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Estás seguro de eliminar al usuario " + nombreUser + "?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'DELETE',
                url: '/users/' + uuid + '/delete',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (data) {
                    toastr.success(data.message);
                    $('#contenido').load('/usuarios/usuarios');
                },
                error: function (xhr, status, error) {
                    toastr.error("Error " + xhr.responseJSON.error + "<br>" + xhr.responseJSON.message);
                }
            })
        } else {
            toastr.error("Eliminación cancelada");
        }
    });

});

