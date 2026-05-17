$(document).ready(function () {
    cargaProductos();
    $("#calcular-margen").click(calcularPrecioVenta);
    $("#calcular-margen-editar").click(calcularPrecioVenta);
    $("#precio_venta").on("input", calcularMargen);
    $("#precio_venta_editar").on("input", calcularMargen);
    $("#btnImportProductsExcel").on('click', importarProductosExcel);

    // Set global: persiste UUIDs seleccionados entre cambios de página
    window.uuidsSeleccionados = new Set();

    // Marcar/desmarcar checkbox individual → actualiza el Set
    $(document).on('change', '.prod-select', function() {
        var uuid = $(this).data('uuid');
        if ($(this).prop('checked')) {
            window.uuidsSeleccionados.add(uuid);
        } else {
            window.uuidsSeleccionados.delete(uuid);
        }
        actualizarContadorEtiquetas();
        var totalPagina = $('.prod-select').length;
        var chkPagina   = $('.prod-select:checked').length;
        $('#selectAllProductos').prop('checked', totalPagina > 0 && totalPagina === chkPagina);
    });

    // Seleccionar/deseleccionar todos los de la página visible
    $(document).on('change', '#selectAllProductos', function() {
        var checked = $(this).prop('checked');
        $('.prod-select').each(function() {
            $(this).prop('checked', checked);
            var uuid = $(this).data('uuid');
            if (checked) { window.uuidsSeleccionados.add(uuid); }
            else          { window.uuidsSeleccionados.delete(uuid); }
        });
        actualizarContadorEtiquetas();
    });

    // Seleccionar TODOS los resultados filtrados (todas las páginas)
    $(document).on('click', '#btnSeleccionarFiltrados', function() {
        var tabla = $('#tabla_productos').DataTable();
        tabla.rows({ search: 'applied' }).data().each(function(row) {
            window.uuidsSeleccionados.add(row.uuid);
        });
        // Marcar visualmente los checkboxes de la página actual
        $('.prod-select').prop('checked', true);
        $('#selectAllProductos').prop('checked', true);
        actualizarContadorEtiquetas();
    });

    // Limpiar selección completa
    $(document).on('click', '#btnLimpiarSeleccion', function() {
        window.uuidsSeleccionados.clear();
        $('.prod-select').prop('checked', false);
        $('#selectAllProductos').prop('checked', false);
        actualizarContadorEtiquetas();
    });

    // Botón etiquetas masivas
    $('#btnGenerarEtiquetas').click(function() {
        var count = window.uuidsSeleccionados.size;
        if (count === 0) return;
        $('#infoSeleccionados').text(count + ' producto(s) seleccionado(s).');
        $('#inputCantidadMasiva').val(1);
        $('#modalEtiquetasMasivas').modal('show');
    });

    // Confirmar generación masiva — lee del Set (incluye todas las páginas)
    $('#btnConfirmarEtiquetasMasivas').click(function() {
        var form = $('#formEtiquetasMasivas');
        form.find('input[name="uuids[]"]').remove();
        window.uuidsSeleccionados.forEach(function(uuid) {
            $('<input>').attr({ type: 'hidden', name: 'uuids[]', value: uuid }).appendTo(form);
        });
        form.find('[name="cantidad"]').val($('#inputCantidadMasiva').val());
        form.submit();
        $('#modalEtiquetasMasivas').modal('hide');
    });

    // Botón barcode individual (por fila)
    $(document).on('click', '.btn-barcode-individual', function() {
        var uuid = $(this).data('uuid');
        $('#etiqueta_individual_uuid').val(uuid);
        $('#inputCantidadIndividual').val(15);
        $('#modalEtiquetaIndividual').modal('show');
    });

    // Confirmar etiqueta individual → abre PDF en nueva pestaña
    $('#btnConfirmarEtiquetaIndividual').click(function() {
        var uuid = $('#etiqueta_individual_uuid').val();
        var cantidad = parseInt($('#inputCantidadIndividual').val()) || 15;
        window.open('/almacen/productos/' + uuid + '/etiquetas?cantidad=' + cantidad, '_blank');
        $('#modalEtiquetaIndividual').modal('hide');
    });

    $(".upload").on('click', function () {
        var formData = new FormData();

        const isEditMode = $("#image_editar").val();
        const image = isEditMode ? "image_editar" : "image";
        const foto = isEditMode ? "nom_foto_editar" : "nom_foto";

        var files = $('#' + image)[0].files[0];

        // Validación client-side: tipo y tamaño antes de enviar al servidor
        if (!files) {
            toastr.warning('Selecciona una imagen antes de subir.');
            return false;
        }
        if (!['image/jpeg', 'image/png'].includes(files.type)) {
            toastr.error('Formato no permitido. Solo se aceptan imágenes JPG o PNG.');
            $('#' + image).val(null);
            return false;
        }
        if (files.size > 5 * 1024 * 1024) {
            toastr.error('La imagen supera el tamaño máximo permitido de 5 MB.');
            $('#' + image).val(null);
            return false;
        }

        formData.append('file', files);
        formData.append('_token', $('#token').val());
        $.ajax({
            url: '/almacen/upload-foto',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log(response);
                if (response != 0) {
                    $(".card-img-top").attr("src", response);
                    var nombre_foto = $.trim(response.replace(/^.*\/\/[^\/]+/, ''));
                    $("#" + foto).val(nombre_foto);
                } else {
                    $("#" + foto).val("");
                    toastr.error('Formato de imagen incorrecto.');
                    $('#' + image).val(null);
                    $(".card-img-top").attr("src", "/img/fotos_prod/sin_imagen.jpg");
                }
            },
            error: function (jqXHR) {
                if (jqXHR.status === 422) {
                    let errors = jqXHR.responseJSON.errors;

                    $.each(errors, function (key, value) {
                        let mensajeError = value.join('<br>')
                            .replace(/El file/g, 'La imagen')
                            .replace(/el file/g, 'la imagen');
                        toastr.error(mensajeError);
                    });
                    $("#" + foto).val("");
                    $('#' + image).val(null);
                    $(".card-img-top").attr("src", "/img/fotos_prod/sin_imagen.jpg");
                } else {
                    toastr.error('Ocurrió un error inesperado. Intenta nuevamente.');
                }
            }
        });
        return false;
    });

});

function toggleSectorImpresionPorTipo(esEdicion) {
    var tipoSelector = esEdicion ? '#tipo_editar' : '#tipo';
    var wrapSelector = esEdicion ? '#sector_impresion_wrap_editar' : '#sector_impresion_wrap_crear';
    var radioName = esEdicion ? 'sector_impresion_editar' : 'sector_impresion';
    var tipo = $(tipoSelector).val();

    if (!$(wrapSelector).length) {
        return;
    }

    if (tipo === 'I') {
        $(wrapSelector).hide();
        $('input[name="' + radioName + '"]').prop('checked', false);
    } else {
        $(wrapSelector).show();
    }
}

$(document).on('change', '#tipo', function () {
    toggleSectorImpresionPorTipo(false);
});

$(document).on('change', '#tipo_editar', function () {
    toggleSectorImpresionPorTipo(true);
});

function limpiarBackdropModal() {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
}

function cerrarModalSeguro(selectorModal, onClosed) {
    var $modal = $(selectorModal);
    $modal.one('hidden.bs.modal', function () {
        // Limpiar SINCRÓNICAMENTE el backdrop para que Bootstrap no tenga estado residual
        limpiarBackdropModal();
        // Pequeño delay antes de abrir otro modal para que Bootstrap
        // complete su ciclo interno antes de iniciar uno nuevo
        if (typeof onClosed === 'function') {
            setTimeout(function () {
                onClosed();
            }, 150);
        }
    });
    $modal.modal('hide');
}

var importProductsInProgress = false;

function setImportProductsLoadingState(isLoading) {
    var $modal = $('#modalCargaMasivaProductos');

    importProductsInProgress = isLoading;
    $('#importProductsLoadingIndicator').toggle(isLoading);

    $modal.find('button, input[type="file"]').prop('disabled', isLoading);
    $modal.find('a.btn')
        .toggleClass('disabled', isLoading)
        .attr('aria-disabled', isLoading ? 'true' : 'false')
        .css('pointer-events', isLoading ? 'none' : 'auto');
}

function resetImportProductsProgressText() {
    $('#importProductsLoadingText').text('Procesando 0 de 1 productos...');
}

function updateImportProductsProgressText(loaded, total) {
    var porcentaje = total > 0 ? Math.round((loaded / total) * 100) : 0;
    porcentaje = Math.max(0, Math.min(100, porcentaje));
    $('#importProductsLoadingText').text('Procesando ' + loaded + ' de ' + total + ' productos (' + porcentaje + '%)...');
}

function showImportProductsResultModal(message, details, title) {
    $('#modalResultadoImportacionProductosLabel').text(title || 'Resultado de Carga Masiva');
    $('#importProductsResultMessage').text(message || 'Proceso finalizado.');

    var $list = $('#importProductsResultList');
    $list.empty();

    if (Array.isArray(details) && details.length > 0) {
        details.forEach(function (item) {
            $list.append('<li>' + $('<div>').text(item).html() + '</li>');
        });
    } else {
        $list.append('<li>Sin incidencias.</li>');
    }

    $('#modalResultadoImportacionProductos').modal('show');
}

$(document).on('click', '#modalCargaMasivaProductos a.disabled', function (event) {
    event.preventDefault();
});

$('#modalCargaMasivaProductos').on('hide.bs.modal', function (event) {
    if (!importProductsInProgress) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
});

$('#modalCargaMasivaProductos').on('hidden.bs.modal', function () {
    importProductsInProgress = false;
    setImportProductsLoadingState(false);
    resetImportProductsProgressText();
    $('#importProductsExcelForm')[0].reset();
});
$('#modalNuevoProducto').on('show.bs.modal', function (event) {
    $('#producto_id').val('');
    $('#codigo_editar').val('');
    $('#descripcion_editar').val('');
    $('#descrip_detallada_editar').val('');
    $('#precio_compra_neto_editar').val('');
    $('#precio_compra_bruto_editar').val('');
    $('#precio_venta_editar').val('');
    $('#margen_editar').val('');
    $('#stock_minimo_editar').val('');
    $('#impuesto_1_editar').val('0');
    $('#impuesto_2_editar').val('0');
    $('#categoria_editar').val('');
    $('#unidad_medida_editar').val('0');
    $('#tipo').val('0');
    $('input[name="sector_impresion"]').prop('checked', false);
    toggleSectorImpresionPorTipo(false);
    $('#imagen_editar').attr('src', '/img/fotos_prod/sin_imagen.jpg');
    $('#image_editar').val('');
    $('#nom_foto_editar').val('');
});
$(document).on('click', '.editar_prod', function () {
    var uuid = $(this).data('uuid');

    $.ajax({
        url: '/almacen/productos/' + uuid + '/editar',
        method: 'GET',
        success: function (response) {
            var precioCompraBruto = parseFloat(response.precio_compra_bruto) || 0;
            var precioVenta = parseFloat(response.precio_venta) || 0;
            var ganancia = precioVenta - precioCompraBruto;
            var margenGanancia = 0;
            if (precioCompraBruto > 0) {
                margenGanancia = (ganancia / precioCompraBruto) * 100;
            }
            $('#producto_uuid').val(response.uuid);
            $('#codigo_editar').val(response.codigo);
            $('#descripcion_editar').val(response.descripcion);
            $('#descrip_detallada_editar').val(response.descrip_detallada || '');
            $('#precio_compra_neto_editar').val(parseInt(response.precio_compra_neto));
            $('#impuesto_1_editar').val(response.impuesto1);
            $('#impuesto_2_editar').val(response.impuesto2);
            $('#precio_compra_bruto_editar').val(response.precio_compra_bruto);
            $('#margen_editar').val(Math.round(margenGanancia));
            $('#precio_venta_editar').val(response.precio_venta);
            $('#categoria_editar').val(response.categoria_id);
            $('#stock_minimo_editar').val(parseFloat(response.stock_minimo) || 0);
            $('#tipo_editar').val(response.tipo);
            $('#unidad_medida_editar').val(response.unidad_medida);
            $('input[name="sector_impresion_editar"]').prop('checked', false);
            if (response.sector_impresion === 'B' || response.sector_impresion === 'C') {
                $('input[name="sector_impresion_editar"][value="' + response.sector_impresion + '"]').prop('checked', true);
            }
            toggleSectorImpresionPorTipo(true);
            $('#imagen_editar').attr('src', response.imagen ? response.imagen : "/img/fotos_prod/sin_imagen.jpg");
            $('#nom_foto_editar').val(response.imagen);

            window.valoresOriginales = {
                descripcion: response.descripcion,
                descrip_detallada: response.descrip_detallada || '',
                precio_compra_neto: parseInt(response.precio_compra_neto),
                impuesto_1: response.impuesto1,
                impuesto_2: response.impuesto2,
                precio_compra_bruto: response.precio_compra_bruto,
                precio_venta: response.precio_venta,
                categoria: response.categoria_id,
                stock_minimo: parseFloat(response.stock_minimo) || 0,
                tipo: response.tipo,
                sector_impresion: response.sector_impresion || '',
                nom_foto: response.imagen
            };

            $('#modalEditarProducto').modal('show');
        },
        error: function (xhr) {
            toastr.error('Error al cargar los datos del producto:', xhr);
        }
    });
});
$('#guardarCambios').click(function (event) {
    event.preventDefault();
    var uuid = $('#producto_uuid').val();
    var tipoEditar = $('#tipo_editar').val();
    var sectorEditar = $('input[name="sector_impresion_editar"]:checked').val() || '';

    if (tipoEditar !== 'I' && $('#sector_impresion_wrap_editar').length && !sectorEditar) {
        toastr.error('Debe seleccionar sector de impresión');
        return;
    }

    var datosProducto = {
        descripcion: $('#descripcion_editar').val(),
        descrip_detallada: $('#descrip_detallada_editar').val(),
        precio_compra_neto: $('#precio_compra_neto_editar').val(),
        impuesto_1: $('#impuesto_1_editar').val(),
        impuesto_2: $('#impuesto_2_editar').val() == 0 ? null : $('#impuesto_2_editar').val(),
        precio_compra_bruto: $('#precio_compra_bruto_editar').val(),
        precio_venta: $('#precio_venta_editar').val(),
        categoria: $('#categoria_editar').val(),
        stock_minimo: $('#stock_minimo_editar').val(),
        unidad_medida: $('#unidad_medida_editar').val(),
        tipo: tipoEditar,
        sector_impresion: (tipoEditar === 'I' ? '' : sectorEditar),
        nom_foto: $('#nom_foto_editar').val()
    };

    let hayCambios = false;

    for (let key in datosProducto) {
        if (datosProducto[key] != window.valoresOriginales[key]) {
            hayCambios = true;
            break;
        }
    }

    if (!hayCambios) {
        toastr.info('No se realizaron cambios en el producto');
        $('#modalEditarProducto').modal('hide');
        return;
    }

    $.ajax({
        url: '/almacen/productos/' + uuid + '/actualizar',
        method: 'PUT',
        data: datosProducto,
        headers: {
            'X-CSRF-TOKEN': $("#token_editar").val()
        },
        success: function (response) {
            cerrarModalSeguro('#modalEditarProducto', function () {
                $('#contenido').load('/almacen/productos');
            });
            toastr.success(response.message);
        },
        error: function (xhr, status, error) {
            if (xhr.status === 422) {
                let errorMessages = '';

                $.each(xhr.responseJSON.errors, function (key, messages) {
                    errorMessages += messages.join('<br>') + '<br>';
                });

                toastr.warning(errorMessages, 'Campos obligatorios', { timeOut: 7000 });
            } else if(xhr.status === 403){
                toastr.error('No tiene permiso para modificar el precio a publico del producto');
            }else{
                toastr.error('Error al actualizar producto');
            }
        }
    });
});
function cargaProductos() {
    var fechaActual = new Date();
    var dia = fechaActual.getDate();
    var mes = fechaActual.getMonth() + 1; // +1 porque los meses van de 0 a 11
    var ano = fechaActual.getFullYear();

    var fechaFormateada = `${dia.toString().padStart(2, '0')}-${mes.toString().padStart(2, '0')}-${ano}`;

    $('#tabla_productos').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/almacen/productosCarga",
            "type": "GET"
        },
        "columns": [
            {
                "data": null,
                "orderable": false,
                "className": "text-center",
                "render": function(data, type, row) {
                    return '<input type="checkbox" class="prod-select" data-uuid="' + row.uuid + '">';
                }
            },
            { "data": "codigo" },
            { "data": "descripcion" },
            { "data": "precio_venta" },
            { "data": "categoria" },
            { "data": "imagen" },
            { "data": { "_": "fec_creacion", "sort": "fec_creacion_sort" } },
            { "data": "fec_modificacion" },
            { "data": "actions", "className": "text-nowrap" }
        ],
        "order": [[6, "desc"], [4, "asc"]],
        "columnDefs": [
            { "targets": 8, "width": "130px", "className": "text-nowrap" }
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        "pageLength": 10,
        "searching": true,
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "drawCallback": function() {
            // Restaurar estado visual de checkboxes desde el Set global
            $('.prod-select').each(function() {
                var uuid = $(this).data('uuid');
                $(this).prop('checked', window.uuidsSeleccionados && window.uuidsSeleccionados.has(uuid));
            });
            var totalPagina = $('.prod-select').length;
            var chkPagina   = $('.prod-select:checked').length;
            $('#selectAllProductos').prop('checked', totalPagina > 0 && totalPagina === chkPagina);
            actualizarContadorEtiquetas();
        },
        "dom": 'Bfrtip',
        "buttons": [
            {
                "extend": 'excelHtml5',
                "text": 'Exportar a Excel',
                className: 'btn btn-success',
                title: 'Listado de productos al ' + fechaFormateada,
                filename: 'Listado de productos al ' + fechaFormateada,
                exportOptions: {
                    columns: ':visible:not(:eq(0),:eq(5),:eq(8))',
                }
            },
            {
                "extend": 'print',
                "text": 'Imprimir',
                className: 'btn btn-primary',
                exportOptions: {
                    columns: ':visible:not(:eq(0),:eq(5),:eq(8))'
                },
                title: 'Listado de productos al ' + fechaFormateada,
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
                    columns: ':visible:not(:eq(0),:eq(5),:eq(8))'
                },
                title: 'Listado de productos al ' + fechaFormateada,
                filename: 'Listado de productos al ' + fechaFormateada,
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
$('#createProdForm').submit(function (event) {
    event.preventDefault();

    var formData = new FormData(this);
    var tipoCrear = $('#tipo').val();
    var sectorCrear = $('input[name="sector_impresion"]:checked').val() || '';

    if (tipoCrear !== 'I' && $('#sector_impresion_wrap_crear').length && !sectorCrear) {
        toastr.error('Debe seleccionar sector de impresión');
        return;
    }
    
    // Validar impuesto_2: si es 0, enviar null
    const impuesto2Value = $('#impuesto_2').val();
    if (impuesto2Value == 0) {
        formData.set('impuesto_2', '');
    }

    if (tipoCrear === 'I') {
        formData.delete('sector_impresion');
    }
    
    formData.append("precio_compra_bruto", $("#precio_compra_bruto").val())
    $.ajax({
        type: 'POST',
        url: '/almacen/productos/create',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $("#token").val()
        },
        contentType: false,
        processData: false,
        success: function (data) {
            toastr.success(data.message);
            cerrarModalSeguro('#modalNuevoProducto', function () {
                $('#contenido').load('/almacen/productos');
            });
        },
        error: function (xhr, status, error) {
            if (xhr.status === 422) {
                let errorMessages = '';

                $.each(xhr.responseJSON.errors, function (key, messages) {
                    errorMessages += messages.join('<br>') + '<br>';
                });

                toastr.warning(errorMessages, 'Campos obligatorios', { timeOut: 7000 });
            } else {
                toastr.error('Error al crear producto');
            }
        }
    });
});
function importarProductosExcel() {
    if (importProductsInProgress) {
        return;
    }

    var inputFile = $('#archivo_excel')[0];

    if (!inputFile.files.length) {
        toastr.warning('Debe seleccionar un archivo Excel para importar.');
        return;
    }

    var selectedExcelFile = inputFile.files[0];
    var progressToken = 'imp_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
    var progressPollingId = null;

    function stopProgressPolling() {
        if (progressPollingId) {
            clearInterval(progressPollingId);
            progressPollingId = null;
        }
    }

    function startProgressPolling(totalProductos) {
        stopProgressPolling();
        progressPollingId = setInterval(function () {
            $.ajax({
                type: 'GET',
                url: '/almacen/productos/importar-xlsx/progreso',
                data: { token: progressToken },
                success: function (progressResp) {
                    var total = parseInt(progressResp.total, 10) || totalProductos || 0;
                    var processed = parseInt(progressResp.processed, 10) || 0;

                    if (total > 0) {
                        updateImportProductsProgressText(Math.min(processed, total), total);
                    }

                    if (progressResp.status === 'done' || progressResp.status === 'failed') {
                        stopProgressPolling();
                    }
                }
            });
        }, 500);
    }

    resetImportProductsProgressText();
    setImportProductsLoadingState(true);

    var formDataConteo = new FormData();
    formDataConteo.append('archivo_excel', selectedExcelFile);
    formDataConteo.append('solo_conteo', '1');

    $.ajax({
        type: 'POST',
        url: '/almacen/productos/importar-xlsx',
        data: formDataConteo,
        headers: {
            'X-CSRF-TOKEN': $('#token').val()
        },
        contentType: false,
        processData: false,
        success: function (conteoResp) {
            var totalProductos = parseInt(conteoResp.total_productos, 10) || 0;

            if (totalProductos <= 0) {
                setImportProductsLoadingState(false);
                cerrarModalSeguro('#modalCargaMasivaProductos', function () {
                    showImportProductsResultModal('La hoja Productos no contiene filas para importar.', [], 'Carga masiva no procesada');
                });
                return;
            }

            $('#importProductsLoadingText').text('Se encontraron ' + totalProductos + ' productos. Iniciando procesamiento...');
            startProgressPolling(totalProductos);

            var formDataImport = new FormData();
            formDataImport.append('archivo_excel', selectedExcelFile);
            formDataImport.append('progress_token', progressToken);

            $.ajax({
                type: 'POST',
                url: '/almacen/productos/importar-xlsx',
                data: formDataImport,
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                contentType: false,
                processData: false,
                success: function (response) {
                    stopProgressPolling();
                    updateImportProductsProgressText(totalProductos, totalProductos);
                    setTimeout(function () {
                        setImportProductsLoadingState(false);
                        cerrarModalSeguro('#modalCargaMasivaProductos', function () {
                            $('#modalResultadoImportacionProductos').one('hidden.bs.modal', function () {
                                limpiarBackdropModal();
                                $('#contenido').load('/almacen/productos');
                            });

                            showImportProductsResultModal(
                                response.message,
                                response.details || response.incidencias || [],
                                'Carga masiva completada'
                            );
                        });
                    }, 350);
                },
                error: function (xhr) {
                    stopProgressPolling();
                    setImportProductsLoadingState(false);
                    
                    console.error('Error en importación (segundo AJAX):', xhr.status, xhr.responseJSON);
                    
                    if (xhr.status === 422) {
                        var mensaje = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'El archivo contiene errores de validación.';
                        var detalles = (xhr.responseJSON && (xhr.responseJSON.details || xhr.responseJSON.incidencias))
                            ? (xhr.responseJSON.details || xhr.responseJSON.incidencias)
                            : [];

                        cerrarModalSeguro('#modalCargaMasivaProductos', function () {
                            showImportProductsResultModal(mensaje, detalles, 'Carga masiva con incidencias');
                        });
                        return;
                    }
                    
                    // Para otros errores (500, etc.)
                    var mensajeError = 'Ocurrió un error al importar el archivo Excel.';
                    var detallesError = [];
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensajeError = xhr.responseJSON.message;
                    }
                    
                    detallesError.push('Error HTTP ' + xhr.status);
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        detallesError.push('Código: ' + xhr.responseJSON.error);
                    }
                    
                    cerrarModalSeguro('#modalCargaMasivaProductos', function () {
                        showImportProductsResultModal(mensajeError, detallesError, 'Carga masiva no procesada');
                    });
                }
            });
        },
        error: function (xhr) {
            stopProgressPolling();
            setImportProductsLoadingState(false);
            
            console.error('Error en preconteo (primer AJAX):', xhr.status, xhr.responseJSON);

            if (xhr.status === 422) {
                var mensaje = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'No se pudo leer el archivo para contar productos.';
                var detalles = (xhr.responseJSON && (xhr.responseJSON.details || xhr.responseJSON.incidencias))
                    ? (xhr.responseJSON.details || xhr.responseJSON.incidencias)
                    : [];

                cerrarModalSeguro('#modalCargaMasivaProductos', function () {
                    showImportProductsResultModal(mensaje, detalles, 'Carga masiva no procesada');
                });
                return;
            }
            
            // Para otros errores (500, etc.)
            var mensajeError = 'No se pudo iniciar la carga masiva.';
            var detallesError = [];
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensajeError = xhr.responseJSON.message;
            }
            
            detallesError.push('Error HTTP ' + xhr.status);
            if (xhr.responseJSON && xhr.responseJSON.error) {
                detallesError.push('Código: ' + xhr.responseJSON.error);
            }
            
            cerrarModalSeguro('#modalCargaMasivaProductos', function () {
                showImportProductsResultModal(mensajeError, detallesError, 'Carga masiva no procesada');
            });
        }
    });
}
$(document).on('click', '.eliminar', function (event) {
    event.preventDefault();
    var prodId = $(this).data('prod');
    var nombreProd = $(this).data('nameprod');
    Swal.fire({
        title: "Eliminar producto",
        text: "¿Estás seguro de eliminar el producto " + nombreProd + "?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'DELETE',
                url: '/almacen/productos/' + prodId + '/delete',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function (data) {
                    toastr.success(data.message);
                    $('#contenido').load('/almacen/productos');
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
function calcula(valor) {
    const isEditMode = $("#precio_compra_neto_editar").val();

    const precioCompraNetoId = isEditMode ? "precio_compra_neto_editar" : "precio_compra_neto";
    const impuesto1Id = isEditMode ? "impuesto_1_editar" : "impuesto_1";
    const impuesto2Id = isEditMode ? "impuesto_2_editar" : "impuesto_2";
    const precioCompraBrutoId = isEditMode ? "precio_compra_bruto_editar" : "precio_compra_bruto";

    if ($("#" + precioCompraNetoId).val() == 0 || $("#" + precioCompraNetoId).val() == "") {
        $("#" + precioCompraBrutoId).val(0);
    } else {
        if (valor == 0) {
            $("#" + impuesto1Id).val(0);
            $("#" + impuesto2Id).val(0);
            $("#" + precioCompraBrutoId).val("");
            return false;
        }
        let porcentaje = $("#" + impuesto1Id).text().match(/\(([^)]+)\)/);
        porcentaje = porcentaje[1].replace('%', '');
        let iva = porcentaje;

        valor_iva = parseInt($("#" + precioCompraNetoId).val() * iva) / 100;

        var calculo1 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_iva);
        $("#" + precioCompraBrutoId).val(calculo1);
    }
    $("#" + impuesto2Id).val(0);
}

function calcula2(valor) {
    const isEditMode = $("#precio_compra_neto_editar").val();

    const precioCompraNetoId = isEditMode ? "precio_compra_neto_editar" : "precio_compra_neto";
    const impuesto1Id = isEditMode ? "impuesto_1_editar" : "impuesto_1";
    const impuesto2Id = isEditMode ? "impuesto_2_editar" : "impuesto_2";
    const precioCompraBrutoId = isEditMode ? "precio_compra_bruto_editar" : "precio_compra_bruto";

    if ($("#" + precioCompraNetoId).val() == 0 || $("#" + precioCompraNetoId).val() == "") {
        $("#" + precioCompraBrutoId).val(0);
    } else {
        if ($("#" + impuesto1Id).val() == 0) {
            toastr.error("Seleccione impuesto 1");
            $("#" + impuesto2Id).val(0);
        } else {
            let porcentaje = $("#" + impuesto1Id).text().match(/\(([^)]+)\)/);
            porcentaje = porcentaje[1].replace('%', '');
            let iva = porcentaje;

            let imp2=0;
            if(valor != 0){
                let porcentaje2 = $("#" + impuesto2Id +" option:selected").text().match(/\(([^)]+)\)/);
                porcentaje2 = porcentaje2[1].replace('%', '');
                imp2 = porcentaje2;
            }
            console.log(imp2);
            let valor_imp1 = parseInt($("#" + precioCompraNetoId).val() * iva) / 100;
            let valor_imp2 = parseInt($("#" + precioCompraNetoId).val() * imp2) / 100;
    
            let calculo2 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_imp1 + valor_imp2);
            $("#" + precioCompraBrutoId).val(calculo2);
        }
    }
}
function calcula3() {
    const isEditMode = $("#precio_compra_neto_editar").val();

    const precioCompraNetoId = isEditMode ? "precio_compra_neto_editar" : "precio_compra_neto";
    const impuesto1Id = isEditMode ? "impuesto_1_editar" : "impuesto_1";
    const impuesto2Id = isEditMode ? "impuesto_2_editar" : "impuesto_2";
    const precioCompraBrutoId = isEditMode ? "precio_compra_bruto_editar" : "precio_compra_bruto";

    if ($("#" + impuesto1Id).val() !=0 && $("#" + impuesto2Id).val() != 0) {
        let porcentaje = $("#" + impuesto1Id).text().match(/\(([^)]+)\)/);
        porcentaje = porcentaje[1].replace('%', '');
        let iva = porcentaje;
    
        let porcentaje2 = $("#" + impuesto2Id +" option:selected").text().match(/\(([^)]+)\)/);
        porcentaje2 = porcentaje2[1].replace('%', '');
        let imp2 = porcentaje2;
        
        let valor_imp1 = parseInt($("#" + precioCompraNetoId).val() * iva) / 100;
        let valor_imp2 = parseInt($("#" + precioCompraNetoId).val() * imp2) / 100;

        let calculo2 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_imp1 + valor_imp2);
        $("#" + precioCompraBrutoId).val(calculo2);
    }else if($("#" + impuesto1Id).val() !=0 && !$("#" + impuesto2Id).val() == 0){
        let porcentaje = $("#" + impuesto1Id).text().match(/\(([^)]+)\)/);
        porcentaje = porcentaje[1].replace('%', '');
        let iva = porcentaje;

        valor_iva = parseInt($("#" + precioCompraNetoId).val() * iva) / 100;

        var calculo1 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_iva);
        $("#" + precioCompraBrutoId).val(calculo1);
    }
}

function calcularPrecioVenta() {

    const isEditMode = $("#precio_compra_bruto_editar").val();

    const pcb = isEditMode ? "precio_compra_bruto_editar" : "precio_compra_bruto";
    const mar = isEditMode ? "margen_editar" : "margen";
    const pv = isEditMode ? "precio_venta_editar" : "precio_venta";

    var precioCompraBruto = parseFloat($("#" + pcb).val());
    var margen = parseFloat($("#" + mar).val());

    if (isNaN(precioCompraBruto) || isNaN(margen)) {
        toastr.error("Por favor, ingrese un número válido para el precio de compra bruto y el margen.");
        return;
    }

    var precioVentaPublico = Math.round(precioCompraBruto + (precioCompraBruto * (margen / 100)));
    $("#" + pv).val(precioVentaPublico);
}

function calcularMargen() {

    const isEditMode = $("#precio_compra_bruto_editar").val();

    const pcb = isEditMode ? "precio_compra_bruto_editar" : "precio_compra_bruto";
    const mar = isEditMode ? "margen_editar" : "margen";
    const pv = isEditMode ? "precio_venta_editar" : "precio_venta";

    var precioCompraBruto = parseFloat($("#" + pcb).val());
    var precioVentaPublico = parseFloat($("#" + pv).val());

    if (isNaN(precioCompraBruto) || isNaN(precioVentaPublico)) {
        toastr.error("Por favor, ingrese un número válido para el precio de compra bruto y el precio de venta público.");
        return;
    }

    var margen = ((precioVentaPublico - precioCompraBruto) / precioCompraBruto) * 100;
    margen = Math.round(margen);

    $("#" + mar).val(margen);
}

function actualizarContadorEtiquetas() {
    var count = window.uuidsSeleccionados ? window.uuidsSeleccionados.size : 0;
    $('#contadorSeleccionados').text('(' + count + ')');
    $('#btnGenerarEtiquetas').prop('disabled', count === 0);
}