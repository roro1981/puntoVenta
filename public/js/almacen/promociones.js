$(document).ready(function () {
    cargaPromociones();

    // Set global: persiste UUIDs seleccionados entre cambios de página y de categoría
    window.uuidsPromoSeleccionados = new Set();

    $('.filter-pill').on('click', function (e) {
        e.preventDefault();
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

    // Marcar/desmarcar checkbox individual → actualiza el Set
    $(document).on('change', '.promo-select', function() {
        var uuid = $(this).data('uuid');
        if ($(this).prop('checked')) {
            window.uuidsPromoSeleccionados.add(uuid);
        } else {
            window.uuidsPromoSeleccionados.delete(uuid);
        }
        actualizarContadorEtiquetasPromos();
        var totalPagina = $('.promo-select').length;
        var chkPagina   = $('.promo-select:checked').length;
        $('#selectAllPromos').prop('checked', totalPagina > 0 && totalPagina === chkPagina);
    });

    // Seleccionar/deseleccionar todos los de la página visible
    $(document).on('change', '#selectAllPromos', function() {
        var checked = $(this).prop('checked');
        $('.promo-select').each(function() {
            $(this).prop('checked', checked);
            var uuid = $(this).data('uuid');
            if (checked) { window.uuidsPromoSeleccionados.add(uuid); }
            else          { window.uuidsPromoSeleccionados.delete(uuid); }
        });
        actualizarContadorEtiquetasPromos();
    });

    // Seleccionar TODOS los resultados filtrados (todas las páginas, categoría activa)
    $(document).on('click', '#btnSeleccionarFiltradosPromos', function() {
        var tabla = $('#tabla_promociones').DataTable();
        tabla.rows({ search: 'applied' }).data().each(function(row) {
            window.uuidsPromoSeleccionados.add(row.uuid);
        });
        $('.promo-select').prop('checked', true);
        $('#selectAllPromos').prop('checked', true);
        actualizarContadorEtiquetasPromos();
    });

    // Limpiar selección completa
    $(document).on('click', '#btnLimpiarSeleccionPromos', function() {
        window.uuidsPromoSeleccionados.clear();
        $('.promo-select').prop('checked', false);
        $('#selectAllPromos').prop('checked', false);
        actualizarContadorEtiquetasPromos();
    });

    // Botón etiquetas masivas
    $('#btnGenerarEtiquetasPromos').click(function() {
        var count = window.uuidsPromoSeleccionados.size;
        if (count === 0) return;
        $('#infoSeleccionadosPromos').text(count + ' promoción(es) seleccionada(s).');
        $('#inputCantidadPromosMasiva').val(1);
        $('#modalEtiquetasPromosMasivas').modal('show');
    });

    // Confirmar generación masiva — lee del Set (incluye todas las páginas/categorías)
    $('#btnConfirmarEtiquetasPromosMasivas').click(function() {
        var form = $('#formEtiquetasPromosMasivas');
        form.find('input[name="uuids[]"]').remove();
        window.uuidsPromoSeleccionados.forEach(function(uuid) {
            $('<input>').attr({ type: 'hidden', name: 'uuids[]', value: uuid }).appendTo(form);
        });
        form.find('[name="cantidad"]').val($('#inputCantidadPromosMasiva').val());
        form.submit();
        $('#modalEtiquetasPromosMasivas').modal('hide');
    });

    // Botón barcode individual (por fila)
    $(document).on('click', '.btn-barcode-promo', function() {
        var uuid = $(this).data('uuid');
        $('#etiqueta_promo_uuid').val(uuid);
        $('#inputCantidadPromoIndividual').val(15);
        $('#modalEtiquetaPromoIndividual').modal('show');
    });

    // Confirmar etiqueta individual → abre PDF en nueva pestaña
    $('#btnConfirmarEtiquetaPromoIndividual').click(function() {
        var uuid = $('#etiqueta_promo_uuid').val();
        var cantidad = parseInt($('#inputCantidadPromoIndividual').val()) || 15;
        window.open('/almacen/promociones/' + uuid + '/etiquetas?cantidad=' + cantidad, '_blank');
        $('#modalEtiquetaPromoIndividual').modal('hide');
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

function cargaPromociones(categoria_id = 0) {
    $('#tabla_promociones').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: "/almacen/promocionesCarga",
            type: "GET",
            data: { categoria_id: categoria_id }
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: "text-center",
                render: function(data, type, row) {
                    return '<input type="checkbox" class="promo-select" data-uuid="' + row.uuid + '">';
                }
            },
            { data: "codigo" },
            { data: "nombre" },
            { data: "precio_costo" },
            { data: "precio_venta" },
            { data: "actions" }
        ],
        drawCallback: function() {
            // Restaurar estado visual de checkboxes desde el Set global
            $('.promo-select').each(function() {
                var uuid = $(this).data('uuid');
                $(this).prop('checked', window.uuidsPromoSeleccionados && window.uuidsPromoSeleccionados.has(uuid));
            });
            var totalPagina = $('.promo-select').length;
            var chkPagina   = $('.promo-select:checked').length;
            $('#selectAllPromos').prop('checked', totalPagina > 0 && totalPagina === chkPagina);
            actualizarContadorEtiquetasPromos();
        },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        pageLength: 10,
        searching: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}

function actualizarContadorEtiquetasPromos() {
    var count = window.uuidsPromoSeleccionados ? window.uuidsPromoSeleccionados.size : 0;
    $('#contadorSeleccionadosPromos').text('(' + count + ')');
    $('#btnGenerarEtiquetasPromos').prop('disabled', count === 0);
}

