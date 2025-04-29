$(document).ready(function () {
    cargaUsuarios();
    $('#name').on('focus', function () {
        $(this).removeAttr('readonly');
    });
    $('#password_edit').on('focus', function () {
        $(this).removeAttr('readonly');
    });
    $(document).on('click', '.editar', function () {
        var rolId = $(this).data('rol');
        $('#role_id_edit option').each(function () {
            if ($(this).val() == rolId) {
                $(this).prop('selected', true);
            }
        });
    });
});
function cargaUsuarios() {
    var fechaActual = new Date();
    var dia = fechaActual.getDate();
    var mes = fechaActual.getMonth() + 1; // +1 porque los meses van de 0 a 11
    var ano = fechaActual.getFullYear();

    var fechaFormateada = `${dia.toString().padStart(2, '0')}-${mes.toString().padStart(2, '0')}-${ano}`;

    $('#tabla_usuarios').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/users",
            "type": "GET"
        },
        "columns": [
            { "data": "name" },
            { "data": "name_complete" },
            { "data": "role_name" },
            { "data": "created_at" },
            { "data": "updated_at" },
            { "data": "actions" }
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        "pageLength": 10,
        "searching": true,
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "dom": 'Bfrtip',
        "buttons": [
            {
                "extend": 'excelHtml5',
                "text": 'Exportar a Excel',
                className: 'btn btn-success',
                title: 'Listado de usuarios al ' + fechaFormateada,
                filename: 'Listado de usuarios al ' + fechaFormateada,
                exportOptions: {
                    columns: ':visible:not(:eq(5))'
                }
            },
            {
                "extend": 'print',
                "text": 'Imprimir',
                className: 'btn btn-primary',
                exportOptions: {
                    columns: ':visible:not(:eq(5))'
                },
                title: 'Listado de usuarios al ' + fechaFormateada,
                customize: function (win) {
                    var last = null;
                    var current = null;
                    var bod = [];

                    var css = '@page { size: landscape; }',
                        head = win.document.head || win.document.getElementsByTagName('head')[0],
                        style = win.document.createElement('style');

                    style.type = 'text/css';
                    style.media = 'print';

                    if (style.styleSheet) {
                        style.styleSheet.cssText = css;
                    } else {
                        style.appendChild(win.document.createTextNode(css));
                    }

                    head.appendChild(style);
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'Exportar a PDF',
                className: 'btn btn-danger',
                exportOptions: {
                    columns: ':visible:not(:eq(5))'
                },
                title: 'Listado de usuarios al ' + fechaFormateada,
                filename: 'Listado de usuarios al ' + fechaFormateada,
                orientation: 'landscape',
                customize: function (doc) {
                    doc.pageMargins = [20, 20, 20, 20];
                    doc.pageSize = 'A4';
                    doc.pageOrientation = 'landscape';
                }
            }
        ]
    });
}
function togglePasswordVisibility(passwordInput, passwordEyeIcon) {

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordEyeIcon.classList.add('fa-eye');
        passwordEyeIcon.classList.remove('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        passwordEyeIcon.classList.add('fa-eye-slash');
        passwordEyeIcon.classList.remove('fa-eye');
    }
}
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

