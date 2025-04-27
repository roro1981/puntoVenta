$(document).ready(function () {
    cargaPromociones();
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

    $(document).on('click', '#editarPromo', function (e) {
        e.preventDefault();
        var uuid = $(this).data('uuid');
        $('#contenido').load("/almacen/promociones/" + uuid + "/edit");
    });
});
$(document).on('click', '.filter-pill', function (e) {
    e.preventDefault();
    let categoriaId = $(this).data('id');
    cargaPromociones(categoriaId);
});

$(document).on('click', '.eliminar', function (e) {
    e.preventDefault();

    let uuid = $(this).data('uuid');
    let nombre = $(this).data('namepromo');

    Swal.fire({
        title: "Eliminar promoción",
        text: "¿Estás seguro de eliminar la promoción " + nombre + "?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/almacen/promociones/${uuid}/delete`,
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (response) {
                    if (response.status === 200) {
                        toastr.success(response.message);
                        cargaPromociones();
                    } else {
                        toastr.error(response.message || 'No se pudo eliminar la promocion.');
                    }
                },
                error: function () {
                    toastr.error('Ocurrió un error al intentar eliminar la promoción.');
                }
            });
        } else {
            toastr.error("Eliminación cancelada");
        }
    });
});
function cargaPromociones() {

    $('#tabla_promociones').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/almacen/promocionesCarga",
            "type": "GET"
        },
        "columns": [
            { "data": "codigo" },
            { "data": "nombre" },
            { "data": "precio_costo" },
            { "data": "precio_venta" },
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

function cargaPromociones(categoria_id = 0) {
    $('#tabla_promociones').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: "/almacen/promocionesCarga",
            type: "GET",
            data: {
                categoria_id: categoria_id
            }
        },
        columns: [
            { "data": "codigo" },
            { "data": "nombre" },
            { "data": "precio_costo" },
            { "data": "precio_venta" },
            { "data": "actions" }
        ],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        pageLength: 10,
        searching: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}


