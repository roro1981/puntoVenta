$(document).ready(function () {
    cargarProveedoresEliminados();
});

function cargarProveedoresEliminados() {
    $('#tabla_proveedores_eliminados').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: '/reactivaciones/traeProveedoresEliminados',
            type: 'GET'
        },
        columns: [
            { data: 'rut' },
            { data: 'razon_social' },
            { data: 'region_comuna' },
            { data: 'fec_eliminacion' },
            { data: 'user_eliminacion' },
            { data: 'actions' }
        ],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'Todos']],
        pageLength: 10,
        searching: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
        }
    });
}

$(document).on('click', '.reactivar-prov', function (event) {
    event.preventDefault();

    var uuid = $(this).data('uuid');
    var nombreProv = $(this).data('nameprov');

    Swal.fire({
        title: 'Reactivar proveedor',
        text: '¿Deseas reactivar el proveedor ' + nombreProv + '?',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Si, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'PUT',
                url: '/reactivaciones/prov/' + uuid + '/reactivar',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (data) {
                    toastr.success(data.message);
                    $('#contenido').load('/reactivaciones/provs_elim');
                },
                error: function (xhr) {
                    toastr.error('Error ' + xhr.responseJSON.error + '<br>' + xhr.responseJSON.message);
                }
            });
        } else {
            toastr.error('Reactivacion cancelada');
        }
    });
});
