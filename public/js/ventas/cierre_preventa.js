$(document).ready(function () {
    let preventaActual = null;
    let pagosMixtos = [];

    if (typeof cajaAbierta !== 'undefined' && !cajaAbierta) {
        $('#modalAperturaCaja').modal('show');
    }

    function formatCurrency(amount) {
        return '$ ' + new Intl.NumberFormat('es-CL').format(Math.round(amount || 0));
    }

    function limpiarVista() {
        preventaActual = null;
        $('#preventa-id').val('');
        $('#preventa-code').val('');
        $('#preventaMeta').html('');
        $('#preventa-detalles-body').html('');
        $('#totalPreventa').text('$ 0');
        $('#forma-pago').val('');
        $('#preventaData').hide();
        $('#preventaEmptyState').show();
        $('#btnCerrarPreventa').prop('disabled', false).html('<i class="fa fa-check"></i> Generar Venta');
        setTimeout(function () {
            $('#preventa-code').focus();
        }, 150);
    }

    function renderPreventa(preventa) {
        preventaActual = preventa;
        $('#preventa-id').val(preventa.venta_id);

        const metaHtml = [
            '<div><strong>Preventa N:</strong> ' + preventa.numero_preventa + '</div>',
            '<div><strong>Fecha:</strong> ' + (preventa.fecha_preventa || '-') + '</div>'
        ].join('');
        $('#preventaMeta').html(metaHtml);

        const rows = (preventa.detalles || []).map(function (d) {
            const cantidad = (parseFloat(d.cantidad) || 0).toString().replace(/\.0+$/, '');
            return '<tr>' +
                '<td>' + (d.descripcion_producto || '-') + '</td>' +
                '<td class="text-center">' + cantidad + '</td>' +
                '<td class="text-right">' + formatCurrency(d.precio_unitario) + '</td>' +
                '<td class="text-center">' + (d.descuento_porcentaje || 0) + ' %</td>' +
                '<td class="text-right"><strong>' + formatCurrency(d.subtotal_linea) + '</strong></td>' +
                '</tr>';
        }).join('');

        $('#preventa-detalles-body').html(rows);
        $('#totalPreventa').text(formatCurrency(preventa.total));

        $('#preventaEmptyState').hide();
        $('#preventaData').show();
    }

    function buscarPreventa() {
        const codigo = $('#preventa-code').val().trim();
        if (!codigo) {
            toastr.warning('Ingrese un codigo de preventa');
            return;
        }

        $.ajax({
            url: '/ventas/preventa/buscar',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                codigo_preventa: codigo
            },
            beforeSend: function () {
                $('#btnBuscarPreventa').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
            },
            success: function (response) {
                if (response.status === 'OK' && response.preventa) {
                    renderPreventa(response.preventa);
                    toastr.success('Preventa cargada correctamente');
                } else {
                    toastr.error(response.message || 'No se pudo cargar la preventa');
                }
            },
            error: function (xhr) {
                let msg = 'No se pudo encontrar la preventa';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            },
            complete: function () {
                $('#btnBuscarPreventa').prop('disabled', false).html('<i class="fa fa-search"></i> Buscar');
            }
        });
    }

    $('#btnBuscarPreventa').on('click', function () {
        buscarPreventa();
    });

    $('#preventa-code').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarPreventa();
        }
    });

    $('#btnLimpiarModulo').on('click', function () {
        limpiarVista();
    });

    function actualizarTablaPagoMixto() {
        const total = preventaActual ? parseInt(preventaActual.total || 0) : 0;
        const sumaMontosActuales = pagosMixtos.reduce(function (sum, p) { return sum + p.monto; }, 0);
        const pendiente = total - sumaMontosActuales;

        let html = '';
        pagosMixtos.forEach(function (pago) {
            const formaLabel = {
                'EFECTIVO': 'Efectivo',
                'TARJETA_DEBITO': 'Tarjeta Debito',
                'TARJETA_CREDITO': 'Tarjeta Credito',
                'TRANSFERENCIA': 'Transferencia',
                'CHEQUE': 'Cheque'
            }[pago.forma] || pago.forma;

            html += '<tr>' +
                '<td><strong>' + formaLabel + '</strong></td>' +
                '<td style="text-align:right;font-weight:600;">' + formatCurrency(pago.monto) + '</td>' +
                '<td style="text-align:right;">' +
                '<button class="btn btn-sm btn-danger btn-eliminar-pago" data-id="' + pago.id + '"><i class="fa fa-trash"></i></button>' +
                '</td>' +
                '</tr>';
        });

        $('#tablaPagoMixto tbody').html(html);
        $('#pendientePagoMixto').text(formatCurrency(pendiente));
        $('#btnConfirmarPagoMixto').prop('disabled', pendiente > 0);

        const $pendiente = $('#pendientePagoMixto');
        if (pendiente === 0) {
            $pendiente.css('color', '#155724');
        } else {
            $pendiente.css('color', '#856404');
        }

        $('.btn-eliminar-pago').off('click').on('click', function () {
            const id = $(this).data('id');
            pagosMixtos = pagosMixtos.filter(function (p) { return p.id !== id; });
            actualizarTablaPagoMixto();
        });
    }

    $('#btnAgregarFormaPago').on('click', function () {
        const forma = $('#formaPagoMixtoSelect').val();
        const monto = parseInt($('#montoPagoMixto').val() || 0);

        if (!forma) {
            toastr.warning('Selecciona una forma de pago');
            return;
        }

        if (monto <= 0) {
            toastr.warning('Ingresa un monto valido');
            return;
        }

        const total = preventaActual ? parseInt(preventaActual.total || 0) : 0;
        const sumaMontosActuales = pagosMixtos.reduce(function (sum, p) { return sum + p.monto; }, 0);

        if (sumaMontosActuales + monto > total) {
            toastr.error('El monto total no puede exceder al total de la venta');
            return;
        }

        pagosMixtos.push({ forma: forma, monto: monto, id: Date.now() });
        actualizarTablaPagoMixto();
        $('#formaPagoMixtoSelect').val('');
        $('#montoPagoMixto').val('');
    });

    $('#btnConfirmarPagoMixto').on('click', function () {
        if (!preventaActual) {
            toastr.error('Debe cargar una preventa');
            return;
        }

        const total = parseInt(preventaActual.total || 0);
        const suma = pagosMixtos.reduce(function (sum, p) { return sum + p.monto; }, 0);

        if (suma !== total) {
            toastr.error('El monto total no coincide con la preventa');
            return;
        }

        $('#modalPagoMixto').modal('hide');
        procesarCierrePreventa('MIXTO', pagosMixtos);
    });

    $('#btnCerrarPreventa').on('click', function () {
        if (!preventaActual || !$('#preventa-id').val()) {
            toastr.warning('Primero debe cargar una preventa');
            return;
        }

        const formaPago = $('#forma-pago').val();
        if (!formaPago) {
            toastr.warning('Debe seleccionar una forma de pago');
            return;
        }

        if (formaPago === 'MIXTO') {
            pagosMixtos = [];
            const total = parseInt(preventaActual.total || 0);
            $('#totalPagoMixto').text(formatCurrency(total));
            $('#pendientePagoMixto').text(formatCurrency(total));
            $('#tablaPagoMixto tbody').html('');
            $('#montoPagoMixto').val('');
            $('#formaPagoMixtoSelect').val('');
            $('#modalPagoMixto').modal('show');
            return;
        }

        procesarCierrePreventa(formaPago, null);
    });

    function procesarCierrePreventa(formaPago, desglosePagos) {
        const ventaId = $('#preventa-id').val();
        if (!ventaId) {
            toastr.error('No hay preventa seleccionada');
            return;
        }

        const data = {
            _token: $('#token').val(),
            venta_id: ventaId,
            forma_pago: formaPago
        };

        if (formaPago === 'MIXTO' && desglosePagos) {
            data.formas_pago_desglose = desglosePagos;
        }

        $.ajax({
            url: '/ventas/preventa/cerrar',
            method: 'POST',
            data: data,
            beforeSend: function () {
                $('#btnCerrarPreventa').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
            },
            success: function (response) {
                if (response.status === 'OK') {
                    const mensajeVenta = response.numero_venta
                        ? (response.message || 'Venta registrada') + ' — N° ' + response.numero_venta
                        : (response.message || 'Venta registrada');
                    toastr.success(mensajeVenta);

                    if (response.venta_id) {
                        $('#ticketFrame').attr('src', '/ventas/ticket-pdf/' + response.venta_id);
                        $('#modalTicket').modal('show');
                    }

                    limpiarVista();
                } else {
                    toastr.error(response.message || 'Error al cerrar preventa');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Error al cerrar la preventa';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            },
            complete: function () {
                $('#btnCerrarPreventa').prop('disabled', false).html('<i class="fa fa-check"></i> Generar Venta');
            }
        });
    }

    $('#btnVerCaja').on('click', function () {
        $('#passwordCaja').val('');
        $('#modalVerificarPassword').modal('show');
    });

    $('#btnConfirmarPassword').on('click', function () {
        const password = $('#passwordCaja').val();

        if (!password) {
            toastr.error('Ingrese su contraseña');
            return;
        }

        $.ajax({
            url: '/ventas/verificar-password',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                password: password
            },
            beforeSend: function () {
                $('#btnConfirmarPassword').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
            },
            success: function (response) {
                if (response.status === 'OK') {
                    $('#modalVerificarPassword').modal('hide');
                    cargarInfoCaja();
                    $('#modalInfoCaja').modal('show');
                } else {
                    toastr.error(response.message || 'Contraseña incorrecta');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Error al verificar contraseña';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            },
            complete: function () {
                $('#btnConfirmarPassword').prop('disabled', false).html('<i class="fa fa-unlock"></i> Verificar');
            }
        });
    });

    function cargarInfoCaja() {
        $.ajax({
            url: '/ventas/info-caja',
            method: 'GET',
            beforeSend: function () {
                $('#caja-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando informacion...</p></div>');
            },
            success: function (response) {
                if (response.status !== 'OK') {
                    $('#caja-content').html('<div class="alert alert-warning">No se pudo cargar la informacion de caja</div>');
                    return;
                }

                const caja = response.caja;
                const html =
                    '<div class="row mb-2">' +
                        '<div class="col-md-6"><p><strong>Fecha Apertura:</strong> ' + caja.fecha_apertura + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Monto Inicial:</strong> ' + formatCurrency(caja.monto_inicial) + '</p></div>' +
                    '</div>' +
                    '<div class="row mb-2">' +
                        '<div class="col-md-6"><p><strong>Cantidad de Ventas:</strong> ' + caja.cantidad_ventas + '</p></div>' +
                        '<div class="col-md-6"><p><strong>Total Ventas:</strong> <span class="text-success">' + formatCurrency(caja.total_ventas) + '</span></p></div>' +
                    '</div>' +
                    '<table class="table table-bordered table-sm">' +
                        '<thead class="thead-light"><tr><th>Forma Pago</th><th class="text-right">Monto</th></tr></thead>' +
                        '<tbody>' +
                            '<tr><td>Efectivo</td><td class="text-right">' + formatCurrency(caja.desglose.efectivo) + '</td></tr>' +
                            '<tr><td>Tarjeta Debito</td><td class="text-right">' + formatCurrency(caja.desglose.tarjeta_debito) + '</td></tr>' +
                            '<tr><td>Tarjeta Credito</td><td class="text-right">' + formatCurrency(caja.desglose.tarjeta_credito) + '</td></tr>' +
                            '<tr><td>Transferencia</td><td class="text-right">' + formatCurrency(caja.desglose.transferencia) + '</td></tr>' +
                            '<tr><td>Cheque</td><td class="text-right">' + formatCurrency(caja.desglose.cheque) + '</td></tr>' +
                            '<tr class="table-success"><td><strong>TOTAL</strong></td><td class="text-right"><strong>' + formatCurrency(caja.total_ventas) + '</strong></td></tr>' +
                        '</tbody>' +
                    '</table>' +
                    (caja.retiros && caja.retiros.length > 0
                        ? '<table class="table table-bordered table-sm mt-2">' +
                              '<thead class="thead-warning"><tr><th>Hora</th><th>Motivo</th><th class="text-right">Monto</th></tr></thead>' +
                              '<tbody>' +
                              caja.retiros.map(function(r) {
                                  return '<tr><td>' + r.created_at + '</td><td>' + r.motivo + '</td><td class="text-right text-danger">-' + formatCurrency(r.monto) + '</td></tr>';
                              }).join('') +
                              '<tr class="table-danger"><td colspan="2"><strong>TOTAL RETIROS</strong></td><td class="text-right"><strong>-' + formatCurrency(caja.total_retiros) + '</strong></td></tr>' +
                              '</tbody></table>'
                        : '') +
                    '<div class="alert alert-success"><strong>Monto Esperado:</strong> ' + formatCurrency(caja.monto_esperado) + (caja.total_retiros > 0 ? ' <small class="text-muted">(incluye − Retiros ' + formatCurrency(caja.total_retiros) + ')</small>' : '') + '</div>' +
                    '<div class="d-flex" style="gap:8px;">' +
                        '<button class="btn btn-warning flex-fill" id="btnAbrirRetiroCaja"><i class="fa fa-minus-circle"></i> Registrar Retiro</button>' +
                        '<button class="btn btn-danger flex-fill" id="btnAbrirCierreCaja"><i class="fa fa-power-off"></i> Cerrar Caja</button>' +
                    '</div>';

                $('#caja-content').html(html);
            },
            error: function () {
                $('#caja-content').html('<div class="alert alert-danger">Error al cargar informacion de caja</div>');
            }
        });
    }

    // Abrir modal de retiro de caja
    $(document).on('click', '#btnAbrirRetiroCaja', function() {
        $('#retiroMonto').val('');
        $('#retiroMotivo').val('');
        $('#modalRetiroCaja').modal('show');
    });

    // Confirmar retiro de caja
    $('#btnConfirmarRetiroCaja').on('click', function() {
        const monto = parseFloat($('#retiroMonto').val());
        const motivo = $('#retiroMotivo').val().trim();
        const tipoCaja = $('#retiroCajaTipoCaja').val() || 'ALMACEN';

        if (!monto || monto < 1) {
            toastr.error('Ingrese un monto válido (mínimo $1)');
            return;
        }
        if (!motivo || motivo.length < 3) {
            toastr.error('Ingrese un motivo (mínimo 3 caracteres)');
            return;
        }

        $('#btnConfirmarRetiroCaja').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Registrando...');

        $.ajax({
            url: '/ventas/retiro-caja',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                monto: monto,
                motivo: motivo,
                tipo_caja: tipoCaja
            },
            success: function(response) {
                if (response.status === 'OK') {
                    $('#modalRetiroCaja').modal('hide');
                    toastr.success('Retiro registrado correctamente');
                    cargarInfoCaja();
                } else {
                    toastr.error(response.message || 'Error al registrar retiro');
                }
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error al registrar retiro';
                toastr.error(msg);
            },
            complete: function() {
                $('#btnConfirmarRetiroCaja').prop('disabled', false).html('<i class="fa fa-check"></i> Registrar Retiro');
            }
        });
    });

    $(document).on('click', '#btnAbrirCierreCaja', function () {
        $.ajax({
            url: '/ventas/info-caja',
            method: 'GET',
            success: function (response) {
                if (response.status === 'OK') {
                    const caja = response.caja;
                    $('#montoEsperadoCierre').text(formatCurrency(caja.monto_esperado));
                    $('#totalVentasCierre').text(formatCurrency(caja.total_ventas));
                    $('#montoFinalDeclarado').val('');
                    $('#diferenciaCierre').text('$ 0').removeClass('text-danger text-success').addClass('text-muted');
                    $('#observacionesCierre').val('');
                    $('#modalCierreCaja').modal('show');
                    $('#modalCierreCaja').data('monto-esperado', caja.monto_esperado);
                }
            }
        });
    });

    $('#montoFinalDeclarado').on('input', function () {
        const montoFinal = parseFloat($(this).val()) || 0;
        const montoEsperado = parseFloat($('#modalCierreCaja').data('monto-esperado')) || 0;
        const diferencia = montoFinal - montoEsperado;

        const $diferencia = $('#diferenciaCierre');
        $diferencia.text(formatCurrency(Math.abs(diferencia)));

        if (diferencia > 0) {
            $diferencia.removeClass('text-muted text-danger').addClass('text-success');
            $diferencia.prepend('+ ');
        } else if (diferencia < 0) {
            $diferencia.removeClass('text-muted text-success').addClass('text-danger');
            $diferencia.prepend('- ');
        } else {
            $diferencia.removeClass('text-danger text-success').addClass('text-muted');
        }
    });

    $('#btnConfirmarCierreCaja').on('click', function () {
        const montoFinal = parseFloat($('#montoFinalDeclarado').val());
        const observaciones = $('#observacionesCierre').val();

        if (isNaN(montoFinal) || montoFinal < 0) {
            toastr.error('Ingrese un monto final valido');
            return;
        }

        Swal.fire({
            title: '¿Cerrar Caja?',
            text: 'Esta accion no se puede deshacer. ¿Estas seguro?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Si, cerrar caja',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/ventas/cerrar-caja',
                method: 'POST',
                data: {
                    _token: $('#token').val(),
                    monto_final_declarado: montoFinal,
                    observaciones: observaciones
                },
                beforeSend: function () {
                    $('#btnConfirmarCierreCaja').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cerrando caja...');
                },
                success: function (response) {
                    if (response.status === 'OK') {
                        const cajaId = response.caja_id;
                        $('#modalCierreCaja').modal('hide');
                        $('#modalInfoCaja').modal('hide');
                        $('#ticketFrame').attr('src', '/ventas/cierre-caja-pdf/' + cajaId);
                        $('#modalTicket').modal('show');

                        $('#modalTicket').one('hidden.bs.modal', function () {
                            window.location.reload();
                        });
                    } else {
                        toastr.error(response.message || 'Error al cerrar caja');
                        $('#btnConfirmarCierreCaja').prop('disabled', false).html('<i class="fa fa-power-off"></i> Cerrar Caja');
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'Error al cerrar caja';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                    $('#btnConfirmarCierreCaja').prop('disabled', false).html('<i class="fa fa-power-off"></i> Cerrar Caja');
                }
            });
        });
    });

    $('#btnConfirmarAperturaCaja').on('click', function () {
        const montoInicial = parseFloat($('#montoInicialCaja').val());
        const observaciones = $('#observacionesApertura').val();

        if (isNaN(montoInicial) || montoInicial < 0) {
            toastr.error('Ingrese un monto inicial valido');
            return;
        }

        $.ajax({
            url: '/ventas/abrir-caja',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                monto_inicial: montoInicial,
                observaciones: observaciones,
                tipo_caja_origen: 'ALMACEN'
            },
            beforeSend: function () {
                $('#btnConfirmarAperturaCaja').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Abriendo caja...');
            },
            success: function (response) {
                if (response.status === 'OK') {
                    toastr.success(response.message);
                    $('#modalAperturaCaja').modal('hide');
                    setTimeout(function () {
                        $('#preventa-code').focus();
                    }, 300);
                } else {
                    toastr.error(response.message || 'Error al abrir caja');
                    $('#btnConfirmarAperturaCaja').prop('disabled', false).html('<i class="fa fa-check"></i> Abrir Caja');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Error al abrir caja';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
                $('#btnConfirmarAperturaCaja').prop('disabled', false).html('<i class="fa fa-check"></i> Abrir Caja');
            }
        });
    });

    limpiarVista();

    // === PREVENTAS PENDIENTES ===

    $('#btnVerPendientes').on('click', function () {
        $('#pendientesLoading').show();
        $('#pendientesEmpty').hide();
        $('#pendientesTableWrap').hide();
        $('#tablaPreventasPendientes tbody').html('');

        $('#modalPreventasPendientes').modal('show');

        $.ajax({
            url: '/ventas/preventas-pendientes',
            method: 'GET',
            success: function (response) {
                $('#pendientesLoading').hide();

                if (response.status !== 'OK' || !response.preventas || response.preventas.length === 0) {
                    $('#pendientesEmpty').show();
                    return;
                }

                const rows = response.preventas.map(function (p) {
                    return '<tr data-id="' + p.venta_id + '">' +
                        '<td><strong>' + p.numero_preventa + '</strong></td>' +
                        '<td>' + (p.fecha_preventa || '-') + '</td>' +
                        '<td class="text-right">' + formatCurrency(p.total) + '</td>' +
                        '<td class="text-center">' +
                            '<button class="btn btn-sm btn-outline-info btn-ver-productos mr-1" data-numero="' + p.numero_preventa + '" title="Ver Productos">' +
                                '<i class="fa fa-eye"></i> Productos' +
                            '</button>' +
                            '<button class="btn btn-sm btn-primary btn-cargar-preventa" data-numero="' + p.numero_preventa + '">' +
                                '<i class="fa fa-arrow-right"></i> Cargar' +
                            '</button>' +
                        '</td>' +
                    '</tr>';
                }).join('');

                $('#tablaPreventasPendientes tbody').html(rows);
                $('#pendientesTableWrap').show();
            },
            error: function () {
                $('#pendientesLoading').hide();
                $('#pendientesEmpty').text('Error al cargar las preventas pendientes.').show();
            }
        });
    });

    $(document).on('click', '.btn-cargar-preventa', function () {
        const numero = $(this).data('numero');
        $('#modalPreventasPendientes').modal('hide');
        $('#preventa-code').val(numero);
        buscarPreventa();
    });

    $(document).on('click', '.btn-ver-productos', function () {
        const $btn = $(this);
        const numero = $btn.data('numero');
        const $row = $btn.closest('tr');
        const detalleRowId = 'detalle-prev-' + numero;
        const $detalleExistente = $('#' + detalleRowId);

        if ($detalleExistente.length) {
            $detalleExistente.toggle();
            $btn.toggleClass('active');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/ventas/preventa/buscar',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                codigo_preventa: numero
            },
            success: function (response) {
                if ($('#' + detalleRowId).length) return;

                if (response.status !== 'OK' || !response.preventa) {
                    toastr.error('No se pudieron cargar los productos');
                    return;
                }

                const detalles = response.preventa.detalles || [];
                let productosHtml;

                if (detalles.length === 0) {
                    productosHtml = '<em class="text-muted">Sin productos</em>';
                } else {
                    const filas = detalles.map(function (d) {
                        const cantidad = (parseFloat(d.cantidad) || 0).toString().replace(/\.0+$/, '');
                        return '<tr>' +
                            '<td>' + (d.descripcion_producto || '-') + '</td>' +
                            '<td class="text-center">' + cantidad + '</td>' +
                            '<td class="text-right">' + formatCurrency(d.precio_unitario) + '</td>' +
                            '<td class="text-center">' + (d.descuento_porcentaje || 0) + ' %</td>' +
                            '<td class="text-right"><strong>' + formatCurrency(d.subtotal_linea) + '</strong></td>' +
                        '</tr>';
                    }).join('');

                    productosHtml =
                        '<table class="productos-sub-tabla">' +
                            '<thead><tr>' +
                                '<th>Producto</th>' +
                                '<th class="text-center">Cant.</th>' +
                                '<th class="text-right">P. Unit.</th>' +
                                '<th class="text-center">Desc.</th>' +
                                '<th class="text-right">Subtotal</th>' +
                            '</tr></thead>' +
                            '<tbody>' + filas + '</tbody>' +
                        '</table>';
                }

                const $detalleRow = $('<tr class="detalle-productos" id="' + detalleRowId + '">' +
                    '<td colspan="4">' + productosHtml + '</td>' +
                '</tr>');

                $row.after($detalleRow);
                $btn.addClass('active');
            },
            error: function () {
                toastr.error('Error al cargar los productos de la preventa');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-eye"></i> Productos');
            }
        });
    });
});
