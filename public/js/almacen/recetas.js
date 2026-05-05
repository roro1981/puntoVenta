$(document).ready(function () {
    cargaRecetas();

    // Set global: persiste UUIDs seleccionados entre cambios de página y de categoría
    window.uuidsRecetaSeleccionados = new Set();

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

    $(document).on('click', '#editarReceta', function (e) {
        e.preventDefault();
        var uuid = $(this).data('uuid');
        $('#contenido').load("/almacen/recetas/" + uuid + "/edit");
    });

    // Marcar/desmarcar checkbox individual → actualiza el Set
    $(document).on('change', '.receta-select', function() {
        var uuid = $(this).data('uuid');
        if ($(this).prop('checked')) {
            window.uuidsRecetaSeleccionados.add(uuid);
        } else {
            window.uuidsRecetaSeleccionados.delete(uuid);
        }
        actualizarContadorEtiquetasRecetas();
        var totalPagina = $('.receta-select').length;
        var chkPagina   = $('.receta-select:checked').length;
        $('#selectAllRecetas').prop('checked', totalPagina > 0 && totalPagina === chkPagina);
    });

    // Seleccionar/deseleccionar todos los de la página visible
    $(document).on('change', '#selectAllRecetas', function() {
        var checked = $(this).prop('checked');
        $('.receta-select').each(function() {
            $(this).prop('checked', checked);
            var uuid = $(this).data('uuid');
            if (checked) { window.uuidsRecetaSeleccionados.add(uuid); }
            else          { window.uuidsRecetaSeleccionados.delete(uuid); }
        });
        actualizarContadorEtiquetasRecetas();
    });

    // Seleccionar TODOS los resultados filtrados (todas las páginas, categoría activa)
    $(document).on('click', '#btnSeleccionarFiltradosRecetas', function() {
        var tabla = $('#tabla_recetas').DataTable();
        tabla.rows({ search: 'applied' }).data().each(function(row) {
            window.uuidsRecetaSeleccionados.add(row.uuid);
        });
        $('.receta-select').prop('checked', true);
        $('#selectAllRecetas').prop('checked', true);
        actualizarContadorEtiquetasRecetas();
    });

    // Limpiar selección completa
    $(document).on('click', '#btnLimpiarSeleccionRecetas', function() {
        window.uuidsRecetaSeleccionados.clear();
        $('.receta-select').prop('checked', false);
        $('#selectAllRecetas').prop('checked', false);
        actualizarContadorEtiquetasRecetas();
    });

    // Botón etiquetas masivas
    $('#btnGenerarEtiquetasRecetas').click(function() {
        var count = window.uuidsRecetaSeleccionados.size;
        if (count === 0) return;
        $('#infoSeleccionadosRecetas').text(count + ' receta(s) seleccionada(s).');
        $('#inputCantidadRecetasMasiva').val(1);
        $('#modalEtiquetasRecetasMasivas').modal('show');
    });

    // Confirmar generación masiva — lee del Set (incluye todas las páginas/categorías)
    $('#btnConfirmarEtiquetasRecetasMasivas').click(function() {
        var form = $('#formEtiquetasRecetasMasivas');
        form.find('input[name="uuids[]"]').remove();
        window.uuidsRecetaSeleccionados.forEach(function(uuid) {
            $('<input>').attr({ type: 'hidden', name: 'uuids[]', value: uuid }).appendTo(form);
        });
        form.find('[name="cantidad"]').val($('#inputCantidadRecetasMasiva').val());
        form.submit();
        $('#modalEtiquetasRecetasMasivas').modal('hide');
    });

    // Botón barcode individual (por fila)
    $(document).on('click', '.btn-barcode-receta', function() {
        var uuid = $(this).data('uuid');
        $('#etiqueta_receta_uuid').val(uuid);
        $('#inputCantidadRecetaIndividual').val(15);
        $('#modalEtiquetaRecetaIndividual').modal('show');
    });

    // Confirmar etiqueta individual → abre PDF en nueva pestaña
    $('#btnConfirmarEtiquetaRecetaIndividual').click(function() {
        var uuid = $('#etiqueta_receta_uuid').val();
        var cantidad = parseInt($('#inputCantidadRecetaIndividual').val()) || 15;
        window.open('/almacen/recetas/' + uuid + '/etiquetas?cantidad=' + cantidad, '_blank');
        $('#modalEtiquetaRecetaIndividual').modal('hide');
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
                    'X-CSRF-TOKEN': $('#token').val()
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

function cargaRecetas(categoria_id = 0) {
    $('#tabla_recetas').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: "/almacen/recetasCarga",
            type: "GET",
            data: { categoria_id: categoria_id }
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: "text-center",
                render: function(data, type, row) {
                    return '<input type="checkbox" class="receta-select" data-uuid="' + row.uuid + '">';
                }
            },
            { data: "imagen", width: "280px", height: "160px" },
            { data: "nombre" },
            { data: "descripcion" },
            { data: "actions" }
        ],
        drawCallback: function() {
            // Restaurar estado visual de checkboxes desde el Set global
            $('.receta-select').each(function() {
                var uuid = $(this).data('uuid');
                $(this).prop('checked', window.uuidsRecetaSeleccionados && window.uuidsRecetaSeleccionados.has(uuid));
            });
            var totalPagina = $('.receta-select').length;
            var chkPagina   = $('.receta-select:checked').length;
            $('#selectAllRecetas').prop('checked', totalPagina > 0 && totalPagina === chkPagina);
            actualizarContadorEtiquetasRecetas();
        },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        pageLength: 10,
        searching: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}

function actualizarContadorEtiquetasRecetas() {
    var count = window.uuidsRecetaSeleccionados ? window.uuidsRecetaSeleccionados.size : 0;
    $('#contadorSeleccionadosRecetas').text('(' + count + ')');
    $('#btnGenerarEtiquetasRecetas').prop('disabled', count === 0);
}


