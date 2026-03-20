$(document).ready(function () {
    cargarCategoriasEliminadas();
});

function cargarCategoriasEliminadas() {
    $('#tabla_categorias_eliminadas').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: '/reactivaciones/traeCategoriasEliminadas',
            type: 'GET'
        },
        columns: [
            { data: 'id' },
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

$(document).on('click', '.reactivar-cat', function (event) {
    event.preventDefault();

    var catId = $(this).data('cat');
    var nombreCat = $(this).data('namecat');

    Swal.fire({
        title: 'Reactivar categoria',
        text: '¿Deseas reactivar la categoria ' + nombreCat + '?',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Si, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'PUT',
                url: '/reactivaciones/' + catId + '/reactivar',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (data) {
                    toastr.success(data.message);
                    $('#contenido').load('/reactivaciones/cats_elim');
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
