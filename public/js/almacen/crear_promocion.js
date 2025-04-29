$(document).ready(function () {
    const hoy = new Date();

    function formatearFecha(fecha) {
        const year = fecha.getFullYear();
        const month = String(fecha.getMonth() + 1).padStart(2, '0');
        const day = String(fecha.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    const fechaInicioDefault = formatearFecha(hoy);
    const fechaFinDefault = formatearFecha(new Date(hoy.getFullYear(), hoy.getMonth() + 1, hoy.getDate()));

    $('#fecha_inicio').val(fechaInicioDefault);
    $('#fecha_termino').val(fechaFinDefault);

    $('#sin_fecha').on('change', function () {
        const isChecked = $(this).is(':checked');

        if (isChecked) {
            $('#fecha_inicio').val(null).prop('disabled', true);
            $('#fecha_termino').val(null).prop('disabled', true);
        } else {
            $('#fecha_inicio').val(fechaInicioDefault).prop('disabled', false);
            $('#fecha_termino').val(fechaFinDefault).prop('disabled', false);
        }
    });

    $('#cant_producto').on('keypress', function (e) {
        if (e.keyCode === 13) { // 13 = Enter
            e.preventDefault();
            agregarProducto();
        }
    });
    $('#producto').on('keyup', function () {
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

        $('#listaProductos').html(html).show();

        $('.fila-sugerencia').on('click', function () {
            const codigo = $(this).data('codigo');
            $('#producto').val(codigo);
            $("#cant_producto").focus();
            $('#listaProductos').hide();
        });
    }

    function agregarProducto() {
        let codigo = $('#producto').val().trim();
        let cantidad = $('#cant_producto').val().trim();

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
            url: '/almacen/findProducto',
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

                let filaExistente = $('#tabla_detalle_promo tbody tr[data-codigo="' + cod + '"]');
                if (filaExistente.length > 0) {
                    let tdCantidad = filaExistente.find('.td-cantidad');
                    let cantActual = parseFloat(tdCantidad.text());
                    let nuevaCantidad = cantActual + cantidad;
                    tdCantidad.text(nuevaCantidad);

                    let tdTotal = filaExistente.find('.td-total');
                    let nuevoTotal = precioUnit * nuevaCantidad;
                    tdTotal.text(nuevoTotal);
                } else {
                    let total = (precioUnit * cantidad);

                    let nuevaFila = `
                        <tr data-codigo="${cod}">
                            <td>${cod}</td>
                            <td>${desc}</td>
                            <td>${unidad_medida}</td>
                            <td class="td-cantidad">${cantidad.toFixed(2)}</td>
                            <td>${precioUnit}</td>
                            <td class="td-total">${total}</td>
                            <td>
                                <button class="btn btn-danger btn-sm btnEliminar">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#tabla_detalle_promo tbody').append(nuevaFila);
                }
                $('#tabla_detalle_promo').parent().scrollTop($('#tabla_detalle_promo').parent()[0].scrollHeight);
                $('#listaProductos').hide();
                $('#producto').val('');
                $('#cant_producto').val('');
                $('#producto').focus();
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

    $('#tabla_detalle_promo').on('click', '.btnEliminar', function () {
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

        $('#tabla_detalle_promo tbody tr').each(function () {
            let totalFila = parseFloat($(this).find('.td-total').text());
            if (!isNaN(totalFila)) {
                suma += totalFila;
            }
            contadorItems++;
        });

        $('#costo_promo').text(suma);
        $('#total_items').text("Items: " + contadorItems);
    }

    $('#cant_producto, #margen, #precio_venta').on('keypress', function (e) {
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
        const precioCostoVal = parseFloat($('#costo_promo').text());
        const margenVal = parseFloat($('#margen').val());

        if (!isNaN(precioCostoVal) && !isNaN(margenVal)) {
            const precioVenta = precioCostoVal * (1 + (margenVal / 100));
            $('#precio_venta').val(Math.ceil(precioVenta));
        }
    }

    function calcularMargen() {
        const precioCostoVal = parseFloat($('#costo_promo').text());
        const precioVentaVal = parseFloat($('#precio_venta').val());

        if (!isNaN(precioCostoVal) && !isNaN(precioVentaVal) && precioCostoVal !== 0) {
            const margen = ((precioVentaVal - precioCostoVal) / precioCostoVal) * 100;
            $('#margen').val(margen.toFixed(1));
        }
    }

    $('#btn_guardar_promo').on('click', function () {
        let codigo = $('#cod_promo').val().trim();
        let nombre = $('#nom_promo').val().trim();
        let precioVenta = $('#precio_venta').val().trim();
        let precioCosto = $('#costo_promo').text().trim();
        let categoria = $('#categoria').val().trim();

        let sinFechas = $('#sin_fecha').is(':checked');

        let fechaInicio = sinFechas ? null : $('#fecha_inicio').val();
        let fechaFin = sinFechas ? null : $('#fecha_termino').val();

        if (!codigo || !nombre || !precioVenta || isNaN(parseFloat(precioVenta)) || categoria == 0) {
            toastr.error('Debe ingresar los campos obligatorios');
            return;
        }

        const $filas = $('#tabla_detalle_promo tbody tr');
        if ($filas.length === 0) {
            toastr.error('Debes agregar al menos 1 producto.');
            return;
        }

        let dataPromo = {
            codigo: codigo,
            nombre: nombre,
            precio_venta: parseFloat(precioVenta),
            precio_costo: parseFloat(precioCosto),
            categoria: categoria,
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
            detallePromo: []
        };

        $filas.each(function () {
            let codProducto = $(this).data('codigo');
            let cantStr = $(this).find('td').eq(2).text().trim();
            let cantidad = parseFloat(cantStr);

            dataPromo.detallePromo.push({
                codigo: codProducto,
                cantidad: cantidad,
            });
        });

        $.ajax({
            url: '/almacen/crearPromocion',
            type: 'POST',
            data: JSON.stringify(dataPromo),
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                toastr.success(response.message);
                $('#contenido').load('/almacen/promociones_crear');
            },
            error: function (xhr, status, error) {
                if (xhr.status === 400) {
                    toastr.warning(xhr.responseJSON.message, 'Validación', { timeOut: 7000 });
                } else {
                    toastr.error('Error al guardar la promocion.');
                }
            }
        });
    });
});

