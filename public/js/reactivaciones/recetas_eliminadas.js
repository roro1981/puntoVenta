$(document).ready(function () {
    cargarRecetasEliminadas();
});

function cargarRecetasEliminadas() {
    $('#tabla_recetas_eliminadas').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: '/reactivaciones/traeRecetasEliminadas',
            type: 'GET'
        },
        columns: [
            { data: 'codigo' },
            { data: 'nombre' },
            { data: 'descripcion_categoria' },
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

$(document).on('click', '.reactivar-receta', function (event) {
    event.preventDefault();

    var uuid = $(this).data('uuid');
    var nombreReceta = $(this).data('nomreceta');

    Swal.fire({
        title: 'Reactivar receta',
        text: '¿Deseas reactivar la receta ' + nombreReceta + '?',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Si, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'PUT',
                url: '/reactivaciones/receta/' + uuid + '/reactivar',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (data) {
                    toastr.success(data.message);
                    $('#contenido').load('/reactivaciones/recetas_elim');
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
