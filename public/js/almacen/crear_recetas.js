$(document).ready(function () {
    $('#cant_insumo').on('keypress', function (e) {
        if (e.keyCode === 13) { // 13 = Enter
            e.preventDefault();
            agregarProducto();
        }
    });
    $('#insumo').on('keyup', function () {
        let query = $(this).val().trim();

        if (query.length >= 2) {
            $.ajax({
                url: "/almacen/searchInsumos", // /products/search
                type: "GET",
                data: { q: query },
                success: function (data) {
                    mostrarSugerencias(data);
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            $('#listaResultados').empty();
        }
    });

    function mostrarSugerencias(productos) {
        let html = '';

        if (productos.length === 0) {
            html = '<p>Sin resultados</p>';
        } else {
            html += '<table style="width:100%;">';
            html += '<thead>';
            html += '<tr><th>Código</th><th>Descripción</th></tr>';
            html += '</thead>';
            html += '<tbody>';

            $.each(productos, function (index, producto) {
                html += '<tr class="fila-sugerencia" data-codigo="' + producto.codigo + '">';
                html += '<td>' + producto.codigo + '</td>';
                html += '<td>' + producto.descripcion + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
        }

        $('#listaResultados').html(html).show();

        $('.fila-sugerencia').on('click', function () {
            const codigo = $(this).data('codigo');
            $('#insumo').val(codigo);
            $("#cant_insumo").focus();
            $('#listaResultados').hide();
        });
    }

    function agregarProducto() {
        let codigo = $('#insumo').val().trim();
        let cantidad = $('#cant_insumo').val().trim();

        if (cantidad === '') {
            cantidad = '1';
        }
        cantidad = parseFloat(cantidad);

        if (isNaN(cantidad) || cantidad <= 0) {
            toastr.error('La cantidad debe ser un número mayor a 0.');
            return;
        }

        if (!codigo) {
            toastr.error('Por favor, ingresa el código del producto.');
            return;
        }

        $.ajax({
            url: '/almacen/findInsumo',
            type: 'GET',
            data: { codigo: codigo },
            success: function (response) {
                if (response.status != 200) {
                    toastr.error(response.mensaje);
                    return;
                }

                let cod = response.data.codigo;
                let desc = response.data.descripcion;
                let precioUnit = parseFloat(response.data.precio_unit);
                let unidad_medida = response.data.unidad_medida;

                let filaExistente = $('#tabla_recetas tbody tr[data-codigo="' + cod + '"]');
                if (filaExistente.length > 0) {
                    let tdCantidad = filaExistente.find('.td-cantidad');
                    let cantActual = parseFloat(tdCantidad.text());
                    let nuevaCantidad = cantActual + cantidad;
                    tdCantidad.text(nuevaCantidad.toFixed(2));

                    let tdTotal = filaExistente.find('.td-total');
                    let nuevoTotal = precioUnit * nuevaCantidad;
                    tdTotal.text(nuevoTotal.toFixed(2));
                } else {
                    let total = (precioUnit * cantidad).toFixed(2);

                    let nuevaFila = `
                        <tr data-codigo="${cod}">
                            <td>${cod}</td>
                            <td>${desc}</td>
                            <td>${unidad_medida}</td>
                            <td class="td-cantidad">${cantidad.toFixed(2)}</td>
                            <td>${precioUnit.toFixed(2)}</td>
                            <td class="td-total">${total}</td>
                            <td>
                                <button class="btn btn-danger btn-sm btnEliminar">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#tabla_recetas tbody').append(nuevaFila);
                }
                $('#tabla_recetas').parent().scrollTop($('#tabla_recetas').parent()[0].scrollHeight);
                $('#listaResultados').hide();
                $('#insumo').val('');
                $('#cant_insumo').val('');
                $('#insumo').focus();
                recalcularTotal();

                const margenVal = parseFloat($('#margen').val());
                const precioVentaVal = parseFloat($('#precio_venta').val());

                if (!isNaN(margenVal)) {
                    calcularPrecioVenta();
                } else if (!isNaN(precioVentaVal)) {
                    calcularMargen();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
                toastr.error('Ocurrió un error al buscar el producto.');
            }
        });
    }

    $('#tabla_recetas').on('click', '.btnEliminar', function () {
        $(this).closest('tr').remove();
        recalcularTotal();

        const margenVal = parseFloat($('#margen').val());
        const precioVentaVal = parseFloat($('#precio_venta').val());

        if (!isNaN(margenVal)) {
            calcularPrecioVenta();
        } else if (!isNaN(precioVentaVal)) {
            calcularMargen();
        }
    });

    function recalcularTotal() {
        let suma = 0;
        let contadorItems = 0;

        $('#tabla_recetas tbody tr').each(function () {
            let totalFila = parseFloat($(this).find('.td-total').text());
            if (!isNaN(totalFila)) {
                suma += totalFila;
            }
            contadorItems++;
        });

        $('#costo_receta').text(suma.toFixed(2));
        $('#total_items').text("Items: " + contadorItems);
    }

    $('#cant_insumo, #margen, #precio_venta').on('keypress', function (e) {
        const key = e.key;
        if (!/[0-9.]/.test(key) && e.keyCode !== 8) {
            e.preventDefault();
        }
    });

    $('#margen').on('change keyup', function () {
        calcularPrecioVenta();
    });

    $('#precio_venta').on('change keyup', function () {
        calcularMargen();
    });

    function calcularPrecioVenta() {
        const precioCostoVal = parseFloat($('#costo_receta').text());
        const margenVal = parseFloat($('#margen').val());

        if (!isNaN(precioCostoVal) && !isNaN(margenVal)) {
            const precioVenta = precioCostoVal * (1 + (margenVal / 100));
            $('#precio_venta').val(Math.ceil(precioVenta));
        }
    }

    function calcularMargen() {
        const precioCostoVal = parseFloat($('#costo_receta').text());
        const precioVentaVal = parseFloat($('#precio_venta').val());

        if (!isNaN(precioCostoVal) && !isNaN(precioVentaVal) && precioCostoVal !== 0) {
            const margen = ((precioVentaVal - precioCostoVal) / precioCostoVal) * 100;
            $('#margen').val(margen.toFixed(1));
        }
    }

    $('#btn_guardar_rec').on('click', function () {
        let codigo = $('#cod_receta').val().trim();
        let nombre = $('#nom_receta').val().trim();
        let precioVenta = $('#precio_venta').val().trim();
        let precioCosto = $('#costo_receta').text().trim();
        let categoria = $('#categoria').val().trim();
        let descripcion = $('#desc_receta').val().trim();
        let foto = $('#foto_receta').val().trim();

        if (!codigo || !nombre || !precioVenta || isNaN(parseFloat(precioVenta)) || categoria == 0) {
            toastr.error('Debe ingresar los campos obligatorios');
            return;
        }

        const $filas = $('#tabla_recetas tbody tr');
        if ($filas.length === 0) {
            toastr.error('Debes agregar al menos 1 ingrediente.');
            return;
        }

        let dataReceta = {
            codigo: codigo,
            nombre: nombre,
            precio_venta: parseFloat(precioVenta),
            precio_costo: parseFloat(precioCosto),
            categoria: categoria,
            descripcion: descripcion, // opcional
            foto: foto,               // opcional
            ingredientes: []
        };

        $filas.each(function () {
            let codProducto = $(this).data('codigo');
            let cantStr = $(this).find('td').eq(3).text().trim();
            let cantidad = parseFloat(cantStr);

            dataReceta.ingredientes.push({
                codigo: codProducto,
                cantidad: parseFloat(cantidad),
            });
        });

        $.ajax({
            url: '/almacen/crearReceta',
            type: 'POST',
            data: JSON.stringify(dataReceta),
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                toastr.success(response.message);
                $('#contenido').load('/almacen/recetas_crear');
            },
            error: function (xhr, status, error) {
                if (xhr.status === 400) {
                    toastr.warning(xhr.responseJSON.message, 'Validación', { timeOut: 7000 });
                } else {
                    toastr.error('Error al guardar la receta.');
                }
            }
        });
    });
});

$(".upload").on('click', function () {
    var formData = new FormData();
    var files = $('#image')[0].files[0];
    formData.append('file', files);
    formData.append('_token', $('#token').val());
    $.ajax({
        url: '/almacen/upload-foto-receta',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response != 0) {
                $(".card-img-top").attr("src", response);
                var nombre_foto = $.trim(response.replace(/^.*\/\/[^\/]+/, ''));
                $("#foto_receta").val(nombre_foto);
            } else {
                $("#foto_receta").val("");
                toastr.error('Formato de imagen incorrecto.');
                $('#foto_receta').val(null);
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
