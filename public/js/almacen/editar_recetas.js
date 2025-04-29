$(document).ready(function () {
    $('#cant_insumo').off('keydown.enterHandler').on('keydown.enterHandler', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            agregarProducto();
        }
    });
    $('#insumo').on('keyup', function () {
        let query = $(this).val().trim();

        if (query.length >= 2) {
            $.ajax({
                url: "/almacen/searchInsumos",
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
                    toastr.error(response.data);
                    return;
                }

                let cod = response.data.codigo;
                let desc = response.data.descripcion;
                let precioUnit = parseFloat(response.data.precio_unit);
                let unidad = response.data.unidad_medida;

                let filaExistente = $('#tabla_ingredientes tbody tr[data-codigo="' + cod + '"]');
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
                            <td>${unidad}</td>
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
                    $('#tabla_ingredientes tbody').append(nuevaFila);
                }

                $('#insumo').val('');
                $('#cant_insumo').val('');
                $('#insumo').focus();
                recalcularCosto();

                const margenVal = parseFloat($('#margen').val());
                const precioVentaVal = parseFloat($('#precio_venta').val());

                if (!isNaN(precioVentaVal)) {
                    calcularPrecioVentaPorMargen();
                } else if (!isNaN(margenVal)) {
                    calcularMargenPorPrecioVenta();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
                toastr.error('Ocurrió un error al buscar el producto.');
            }
        });
    }
    function recalcularCosto() {
        let total = 0;
        $('#tabla_ingredientes tbody tr').each(function () {
            let tot = $(this).find('.td-total').text().trim();
            let tota = parseFloat(tot) || 0;
            total += tota;
        });

        $('#precio_costo').val(total.toFixed(1));
        return total;
    }

    function calcularPrecioVentaPorMargen() {
        let costo = parseFloat($('#precio_costo').val()) || 0;
        let margen = parseFloat($('#margen').val()) || 0;
        let precioVenta = Math.ceil(costo * (1 + (margen / 100)));
        $('#precio_venta').val(precioVenta);
    }

    function calcularMargenPorPrecioVenta() {
        let costo = parseFloat($('#precio_costo').val()) || 0;
        let pv = parseFloat($('#precio_venta').val()) || 0;
        if (costo > 0) {
            let margenCalculado = ((pv - costo) / costo) * 100;
            $('#margen').val(margenCalculado.toFixed(1));
        }
    }

    $('#tabla_ingredientes').on('click', '.btnEliminar', function () {
        $(this).closest('tr').remove();
        recalcularCosto();
        calcularPrecioVentaPorMargen();
    });

    $('#margen').on('change keyup', calcularPrecioVentaPorMargen);
    $('#precio_venta').on('change keyup', calcularMargenPorPrecioVenta);

    recalcularCosto();

    $('#volver').on('click', function (e) {
        e.preventDefault();
        $('#contenido').load("/almacen/recetas");
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

    $(document).off('click', '#act_receta').on('click', '#act_receta', function () {
        let codigo = $('#codigo').val().trim();
        let nombre = $('#nombre').val().trim();
        let precioVenta = $('#precio_venta').val().trim();
        let precioCosto = $('#precio_costo').val().trim();
        let categoria = $('#categoria_id').val();
        let descripcion = $('#descripcion').val().trim();
        let foto = $('#foto_receta').val().trim();

        if (!nombre || !precioVenta || isNaN(parseFloat(precioVenta)) || categoria == 0) {
            toastr.error('Debe ingresar los campos obligatorios');
            return;
        }

        const $filas = $('#tabla_ingredientes tbody tr');
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
                cantidad: cantidad,
            });
        });
        let uuid = $("#uuid_receta").val();
        $.ajax({
            url: '/almacen/recetas/' + uuid + '/update',
            type: 'PUT',
            data: JSON.stringify(dataReceta),
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                if (response.status == 200) {
                    toastr.success(response.message);
                    $('#contenido').load('/almacen/recetas');
                } else {
                    toastr.error('Error al modificar la receta: ' + (response.message));
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr, status, error);
                toastr.error('Error al guardar la receta.');
            }
        });
    });

    calcularMargenPorPrecioVenta();
});