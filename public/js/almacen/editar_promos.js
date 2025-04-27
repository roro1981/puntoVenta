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

    $('#cant_producto').off('keydown.enterHandler').on('keydown.enterHandler', function (e) {
        if (e.which === 13) {
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
                    console.error(error);
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
                    toastr.error(response.data);
                    return;
                }

                let cod = response.data.codigo;
                let desc = response.data.descripcion;
                let precioUnit = parseFloat(response.data.precio_unit);
                let unidad = response.data.unidad_medida;

                let filaExistente = $('#tabla_detalle tbody tr[data-codigo="' + cod + '"]');
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
                    $('#tabla_detalle tbody').append(nuevaFila);
                }
                $('#listaProductos').hide();
                $('#producto').val('');
                $('#cant_producto').val('');
                $('#producto').focus();
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
        $('#tabla_detalle tbody tr').each(function () {
            let tot = $(this).find('.td-total').text().trim();
            let tota = parseFloat(tot) || 0;
            total += tota;
        });

        $('#precio_costo').val(total);
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

    $('#tabla_detalle').on('click', '.btnEliminar', function () {
        $(this).closest('tr').remove();
        recalcularCosto();
        calcularPrecioVentaPorMargen();
    });

    $('#margen').on('change keyup', calcularPrecioVentaPorMargen);
    $('#precio_venta').on('change keyup', calcularMargenPorPrecioVenta);

    recalcularCosto();

    $('#volver').on('click', function (e) {
        e.preventDefault();
        console.log("Click en #volver detectado");
        $('#contenido').load("/almacen/promociones");
    });

    $(document).off('click', '#act_promo').on('click', '#act_promo', function () {
        let codigo = $('#codigo').val().trim();
        let nombre = $('#nombre').val().trim();
        let precioVenta = $('#precio_venta').val().trim();
        let precioCosto = $('#precio_costo').val().trim();
        let categoria = $('#categoria_id').val();

        let sinFechas = $('#sin_fecha').is(':checked');

        let fechaInicio = sinFechas ? null : $('#fecha_inicio').val();
        let fechaFin = sinFechas ? null : $('#fecha_termino').val();

        if (!nombre || !precioVenta || isNaN(parseFloat(precioVenta)) || categoria == 0) {
            toastr.error('Debe ingresar los campos obligatorios');
            return;
        }

        const $filas = $('#tabla_detalle tbody tr');
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
            productos: []
        };

        $filas.each(function () {
            let codProducto = $(this).data('codigo');
            let cantStr = $(this).find('td').eq(3).text().trim();
            let cantidad = parseFloat(cantStr);

            dataPromo.productos.push({
                codigo: codProducto,
                cantidad: cantidad,
            });
        });
        let uuid = $("#uuid_promo").val();
        $.ajax({
            url: '/almacen/promociones/' + uuid + '/update',
            type: 'PUT',
            data: JSON.stringify(dataPromo),
            headers: {
                'X-CSRF-TOKEN': $("#token").val()
            },
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (response) {
                if (response.status == 200) {
                    toastr.success(response.message);
                    $('#contenido').load('/almacen/promociones');
                } else {
                    toastr.error('Error al modificar la promoción: ' + (response.message));
                }
            },
            error: function (xhr, status, error) {
                console.error(xhr, status, error);
                toastr.error('Error al actualizar promoción.');
            }
        });
    });

    calcularMargenPorPrecioVenta();
});