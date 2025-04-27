$(document).ready(function () {
    cargaProductos();
    $('#codigo').on('keyup', function () {
        let query = $(this).val().trim();

        if (query.length >= 2) {
            $.ajax({
                url: "/almacen/searchProductos",
                type: "GET",
                data: { q: query },
                success: function (data) {
                    mostrarSugerencias(data);
                },
                error: function (xhr, status, error) {
                    toastr.error(error);
                }
            });
        } else {
            $('#listaProductos').empty();
        }
    });

});

function mostrarSugerencias(productos) {
    let html = '';

    if (productos.length === 0) {
        html = '<p>Sin resultados</p>';
    } else {
        html += '<table style="width:100%;">';
        html += '<thead>';
        html += '<tr><th>Código</th><th>Descripción</th><th>Precio venta</th></tr>';
        html += '</thead>';
        html += '<tbody>';

        $.each(productos, function (index, producto) {
            html += '<tr class="fila-sugerencia" data-codigo="' + producto.codigo + '" data-desc="' + producto.descripcion + '"data-precio="' + producto.precio_venta + '"data-uuid="' + producto.uuid + '">';
            html += '<td>' + producto.codigo + '</td>';
            html += '<td>' + producto.descripcion + '</td>';
            html += '<td>' + producto.precio_venta + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
    }

    $('#listaProductos').html(html).show();

    $('.fila-sugerencia').on('click', function () {
        const codigo = $(this).data('codigo');
        const descripcion = $(this).data('desc');
        const precio_venta = $(this).data('precio');
        const uuid = $(this).data('uuid');
        $('#codigo').val(codigo);
        $('#descripcion').val(descripcion);
        $('#precio_actual').val(precio_venta);
        $('#uuid').val(uuid);
        $('#listaProductos').hide();
    });
}
$(document).on('click', '.editar', function () {
    var uuid = $(this).data('uuid');

    $.ajax({
        url: '/almacen/precio_segun_cant/' + uuid + '/editar',
        method: 'GET',
        success: function (response) {
            $('#codigo_act').val(response.producto.codigo);
            $('#descripcion_act').val(response.producto.descripcion);
            $('#precio_actual_act').val(response.producto.precio_venta);
            $("#cant_minima_act").val(Math.floor(response.cantidad_minima));
            $("#cant_maxima_act").val(response.cantidad_maxima ? Math.floor(response.cantidad_maxima) : '');
            $("#precio_rango_act").val(Math.floor(response.precio_unitario));
            $("#uuid_act").val(uuid);
            $("#uuid_prod").val(response.producto.uuid);
        },
        error: function (xhr) {
            toastr.error('Error al cargar los datos del rango:', xhr);
        }
    });
});
$('#guardar_cambios').click(function (event) {
    event.preventDefault();
    var uuid = $('#uuid_act').val();
    var datosRango = {
        cantidad_minima: $('#cant_minima_act').val(),
        cantidad_maxima: $('#cant_maxima_act').val(),
        precio_unitario: $('#precio_rango_act').val(),
        uuid: $('#uuid_prod').val()
    };

    $.ajax({
        url: '/almacen/precio_segun_cant/' + uuid + '/actualizar',
        method: 'PUT',
        data: datosRango,
        headers: {
            'X-CSRF-TOKEN': $("#token_editar").val()
        },
        success: function (response) {
            $('#modalEditarRango').modal('hide');
            toastr.success(response.message);
            $('#contenido').load('/almacen/precio_segun_cant');
        },
        error: function (xhr, status, error) {
            if (xhr.status === 400) {
                toastr.warning(xhr.responseJSON.message, 'Aviso', { timeOut: 7000 });
            } else {
                toastr.error('Error al modificar rango');
            }
        }
    });
});
function cargaProductos() {

    $('#tabla_rangos').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/almacen/productosRangoCarga",
            "type": "GET"
        },
        "columns": [
            { "data": "codigo" },
            { "data": "descripcion" },
            { "data": "cantidad_minima" },
            { "data": "cantidad_maxima" },
            { "data": "precio_unitario" },
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
$('#createRangoForm').submit(function (event) {
    event.preventDefault();

    var formData = new FormData(this);
    $.ajax({
        type: 'POST',
        url: '/almacen/precio_segun_cant/create',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $("#token").val()
        },
        contentType: false,
        processData: false,
        success: function (data) {
            $('#modalNuevoRango').modal('hide');
            toastr.success(data.message);
            $('#contenido').load('/almacen/precio_segun_cant');
        },
        error: function (xhr, status, error) {
            if (xhr.status === 400) {
                toastr.warning(xhr.responseJSON.message, 'Aviso', { timeOut: 7000 });
            } else {
                toastr.error('Error al crear rango');
            }
        }
    });
});
$(document).on('click', '.eliminar', function (event) {
    event.preventDefault();
    var uuid = $(this).data('uuid');
    var nombreProd = $(this).data('nameprod');
    Swal.fire({
        title: "Eliminar rango de producto",
        text: "¿Estás seguro de eliminar el rango de producto " + nombreProd + "?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/almacen/precio_segun_cant/' + uuid + '/delete',
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (response) {
                    toastr.success(response.message);
                    $('#contenido').load('/almacen/precio_segun_cant');
                },
                error: function () {
                    toastr.error('Error al eliminar el rango.');
                }
            });
        } else {
            toastr.error("Eliminación cancelada");
        }
    });

});
