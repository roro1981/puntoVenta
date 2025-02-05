$(document).ready(function () {
    cargaProductos();
    $("#calcular-margen").click(calcularPrecioVenta);
    $("#calcular-margen-editar").click(calcularPrecioVenta);
    $("#precio_venta").on("input", calcularMargen);
    $("#precio_venta_editar").on("input", calcularMargen);

    $(".upload").on('click', function () {
        var formData = new FormData();

        const isEditMode = $("#image_editar").val();
        const image = isEditMode ? "image_editar" : "image";
        const foto = isEditMode ? "nom_foto_editar" : "nom_foto";

        var files = $('#' + image)[0].files[0];
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
                    $(".card-img-top").attr("src", "https://www.edelar.com.ar/static/theme/images/sin_imagen.jpg");
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
                    $(".card-img-top").attr("src", "https://www.edelar.com.ar/static/theme/images/sin_imagen.jpg");
                } else {
                    toastr.error('Ocurrió un error inesperado. Intenta nuevamente.');
                }
            }
        });
        return false;
    });

});
$(document).on('click', '.editar', function () {
    var productoId = $(this).data('prod');

    $.ajax({
        url: '/almacen/productos/' + productoId + '/editar',
        method: 'GET',
        success: function (response) {
            var precioCompraBruto = parseFloat(response.precio_compra_bruto) || 0;
            var precioVenta = parseFloat(response.precio_venta) || 0;
            var ganancia = precioVenta - precioCompraBruto;
            var margenGanancia = 0;
            if (precioCompraBruto > 0) {
                margenGanancia = (ganancia / precioCompraBruto) * 100;
            }
            $('#producto_id').val(response.id);
            $('#codigo_editar').val(response.codigo);
            $('#descripcion_editar').val(response.descripcion);
            $('#precio_compra_neto_editar').val(parseInt(response.precio_compra_neto));
            $('#impuesto_1_editar').val(response.impuesto1);
            $('#impuesto_2_editar').val(response.impuesto2);
            $('#precio_compra_bruto_editar').val(response.precio_compra_bruto);
            $('#margen_editar').val(Math.round(margenGanancia));
            $('#precio_venta_editar').val(response.precio_venta);
            $('#categoria_editar').val(response.categoria_id);
            $('#stock_minimo_editar').val(parseInt(response.stock_minimo));
            $('#tipo_editar').val(response.tipo);
            $('#imagen_editar').attr('src', response.imagen ? response.imagen : "https://www.edelar.com.ar/static/theme/images/sin_imagen.jpg");
            $('#nom_foto_editar').val(response.imagen);

            window.valoresOriginales = {
                descripcion: response.descripcion,
                precio_compra_neto: parseInt(response.precio_compra_neto),
                impuesto_1: response.impuesto1,
                impuesto_2: response.impuesto2,
                precio_compra_bruto: response.precio_compra_bruto,
                precio_venta: response.precio_venta,
                categoria: response.categoria_id,
                stock_minimo: parseInt(response.stock_minimo),
                tipo: response.tipo,
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
    var productoId = $('#producto_id').val();
    var datosProducto = {
        descripcion: $('#descripcion_editar').val(),
        precio_compra_neto: $('#precio_compra_neto_editar').val(),
        impuesto_1: $('#impuesto_1_editar').val(),
        impuesto_2: $('#impuesto_2_editar').val(),
        precio_compra_bruto: $('#precio_compra_bruto_editar').val(),
        precio_venta: $('#precio_venta_editar').val(),
        categoria: $('#categoria_editar').val(),
        stock_minimo: $('#stock_minimo_editar').val(),
        tipo: $('#tipo_editar').val(),
        nom_foto: $('#nom_foto_editar').val()
    };
    console.log(window.valoresOriginales);
    let hayCambios = false;

    for (let key in datosProducto) {
        console.log(datosProducto[key], window.valoresOriginales[key]);
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
        url: '/almacen/productos/' + productoId + '/actualizar',
        method: 'PUT',
        data: datosProducto,
        headers: {
            'X-CSRF-TOKEN': $("#token_editar").val()
        },
        success: function (response) {
            $('#modalEditarProducto').modal('hide');
            toastr.success(response.message);
            $('#contenido').load('/almacen/productos');
        },
        error: function (xhr, status, error) {
            if (xhr.status === 422) {
                let errorMessages = '';

                $.each(xhr.responseJSON.errors, function (key, messages) {
                    errorMessages += messages.join('<br>') + '<br>';
                });

                toastr.warning(errorMessages, 'Campos obligatorios', { timeOut: 7000 });
            } else {
                toastr.error('Error al modificar producto');
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
            { "data": "codigo" },
            { "data": "descripcion" },
            { "data": "precio_venta" },
            { "data": "categoria" },
            { "data": "imagen" },
            { "data": "fec_creacion" },
            { "data": "fec_modificacion" },
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
                title: 'Listado de productos al ' + fechaFormateada,
                filename: 'Listado de productos al ' + fechaFormateada,
                exportOptions: {
                    columns: ':visible:not(:eq(7),:eq(4))',
                }
            },
            {
                "extend": 'print',
                "text": 'Imprimir',
                className: 'btn btn-primary',
                exportOptions: {
                    columns: ':visible:not(:eq(7),:eq(4))'
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
                    columns: ':visible:not(:eq(7),:eq(4))'
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
    const impuestoUnido = $("#impuesto_1").val();
    const imp1 = impuestoUnido ? impuestoUnido.split("_") : ["", ""];
    const impuesto2Unido = $("#impuesto_2").val();
    const imp2 = impuesto2Unido ? impuesto2Unido.split("_") : ["", ""];
    formData.set('impuesto_1', imp1[1] || "");
    formData.set('impuesto_2', imp2[1] || "");
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
            $('#modalNuevoProducto').modal('hide');
            toastr.success(data.message);
            $('#contenido').load('/almacen/productos');
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
    const impuesto2Id = isEditMode ? "impuesto_2_editar" : "impuesto_2";
    const precioCompraBrutoId = isEditMode ? "precio_compra_bruto_editar" : "precio_compra_bruto";
    console.log(precioCompraBrutoId);
    if ($("#" + precioCompraNetoId).val() == 0 || $("#" + precioCompraNetoId).val() == "") {
        $("#" + precioCompraBrutoId).val(0);
    } else {
        if (valor == 0) {
            $("#" + impuesto2Id).val(0);
            $("#" + precioCompraBrutoId).val("");
            return false;
        }
        if (isEditMode) {
            valor_iva = parseInt($("#" + precioCompraNetoId).val() * valor) / 100;
        } else {
            var trozos = valor.split("_");
            valor_iva = parseInt($("#" + precioCompraNetoId).val() * trozos[1]) / 100;
        }
        var calculo1 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_iva);
        $("#" + precioCompraBrutoId).val(calculo1);
    }
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
            if (isEditMode) {
                let iva = $("#" + impuesto1Id).val();
                let valor_imp1 = parseInt($("#" + precioCompraNetoId).val() * iva) / 100;
                let valor_imp2 = (valor == 0) ? 0 : parseInt($("#" + precioCompraNetoId).val() * valor) / 100;
                let calculo2 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_imp1 + valor_imp2);
                $("#" + precioCompraBrutoId).val(calculo2);
            } else {
                let iva = $("#" + impuesto1Id).val();
                var trozos2 = iva.split("_");
                let valor_imp1 = parseInt($("#" + precioCompraNetoId).val() * trozos2[1]) / 100;
                var trozos = valor.split("_");
                let valor_imp2 = (valor == 0) ? 0 : parseInt($("#" + precioCompraNetoId).val() * trozos[1]) / 100;
                let calculo2 = Math.round(parseInt($("#" + precioCompraNetoId).val()) + valor_imp1 + valor_imp2);
                $("#" + precioCompraBrutoId).val(calculo2);
            }

        }
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