$(document).ready(function () {
    cargaRecetas();
    $('.filter-pill').on('click', function (e) {
        e.preventDefault(); // Evita saltos de página
        let categoria = $(this).data('categoria');
        let texto = categoria === '' ? 'Todas' : categoria;
        $('#categoriaSeleccionada').text('Categoría: ' + texto);

    });
    $('.category-pills').on('wheel', function (e) {
        e.preventDefault();
        this.scrollLeft += e.originalEvent.deltaY;
    });

    $(document).on('click', '#editarReceta', function (e) {
        e.preventDefault();
        var uuid = $(this).data('uuid');
        $('#contenido').load("/almacen/recetas/" + uuid + "/edit");
    });
});
$(document).on('click', '.filter-pill', function (e) {
    e.preventDefault();
    let categoriaId = $(this).data('id');
    cargaRecetas(categoriaId);
});

$(document).on('click', '.eliminar', function (e) {
    e.preventDefault();

    let uuid = $(this).data('uuid');
    let nombre = $(this).data('namerec');

    Swal.fire({
        title: "Eliminar receta",
        text: "¿Estás seguro de eliminar la receta " + nombre + "?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/almacen/recetas/${uuid}/delete`,
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val() // asegúrate de tener este token en el HTML
                },
                success: function (response) {
                    if (response.status === 200) {
                        toastr.success(response.message);
                        cargaRecetas();
                    } else {
                        toastr.error(response.message || 'No se pudo eliminar la receta.');
                    }
                },
                error: function () {
                    toastr.error('Ocurrió un error al intentar eliminar la receta.');
                }
            });
        } else {
            toastr.error("Eliminación cancelada");
        }
    });
});
function cargaRecetas() {

    $('#tabla_recetas').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/almacen/recetasCarga",
            "type": "GET"
        },
        "columns": [
            { "data": "imagen", width: "280px", height: "160px" },
            { "data": "nombre" },
            { "data": "descripcion" },
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

function cargaRecetas(categoria_id = 0) {
    $('#tabla_recetas').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: "/almacen/recetasCarga",
            type: "GET",
            data: {
                categoria_id: categoria_id
            }
        },
        columns: [
            { data: "imagen", width: "280px", height: "160px" },
            { data: "nombre" },
            { data: "descripcion" },
            { data: "actions" }
        ],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        pageLength: 10,
        searching: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}


