var comandasPendientes = window.comandasPendientes || [];
var pagosMixtosComanda = window.pagosMixtosComanda || [];
var comandaSeleccionada = window.comandaSeleccionada || null;

window.comandasPendientes = comandasPendientes;
window.pagosMixtosComanda = pagosMixtosComanda;
window.comandaSeleccionada = comandaSeleccionada;

if (window.cerrarComandasRefreshInterval) {
    clearInterval(window.cerrarComandasRefreshInterval);
    window.cerrarComandasRefreshInterval = null;
}

function limpiarIntervaloCerrarComandas() {
    if (window.cerrarComandasRefreshInterval) {
        clearInterval(window.cerrarComandasRefreshInterval);
        window.cerrarComandasRefreshInterval = null;
    }
}

function iniciarIntervaloCerrarComandas() {
    limpiarIntervaloCerrarComandas();

    if (!$('#listaComandasPendientes').length) {
        return;
    }

    window.cerrarComandasRefreshInterval = setInterval(function () {
        if (!$('#listaComandasPendientes').length) {
            limpiarIntervaloCerrarComandas();
            return;
        }

        cargarComandasPendientes();
    }, 30000);
}

$(document).ready(function () {
    if (!$('#listaComandasPendientes').length) {
        limpiarIntervaloCerrarComandas();
        return;
    }

    if (typeof cajaAbierta !== 'undefined' && !cajaAbierta) {
        $('#modalAperturaCaja').modal('show');
    }

    cargarComandasPendientes();
    iniciarIntervaloCerrarComandas();

    $('#btnRefrescarComandasPendientes').on('click', function () {
        cargarComandasPendientes();
    });

    $('#btnVerDetalleCajaCerrarComandas').on('click', function () {
        $('#passwordCajaCerrarComandas').val('');
        $('#modalVerificarPasswordCajaCerrarComandas').modal('show');
    });

    $('#btnConfirmarPasswordCajaCerrarComandas').on('click', function () {
        const password = $('#passwordCajaCerrarComandas').val();

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
                $('#btnConfirmarPasswordCajaCerrarComandas').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verificando...');
            },
            success: function (response) {
                if (response.status === 'OK') {
                    $('#modalVerificarPasswordCajaCerrarComandas').modal('hide');
                    $('#modalDetalleCajaCerrarComandas').modal('show');
                    cargarDetalleCajaCerrarComandas();
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
                $('#btnConfirmarPasswordCajaCerrarComandas').prop('disabled', false).html('<i class="fa fa-unlock"></i> Verificar');
            }
        });
    });

    $('#formaPagoCerrarComanda').on('change', function () {
        const esMixto = $(this).val() === 'MIXTO';
        if (!esMixto) {
            pagosMixtosComanda = [];
            actualizarTablaPagoMixtoComanda();
        }
        $('#seccionPagoMixtoComanda').toggle(esMixto);
    });

    $('#btnAgregarPagoMixtoComanda').on('click', function () {
        agregarPagoMixtoComanda();
    });

    $('#cerrar_incluye_propina').on('change', function () {
        actualizarTotalesCierreComanda();
        actualizarTablaPagoMixtoComanda();
    });

    $('#cerrar_propina_porcentaje').on('input', function () {
        actualizarTotalesCierreComanda();
        actualizarTablaPagoMixtoComanda();
    });

    $('#btnConfirmarCerrarComanda').on('click', function () {
        confirmarCierreComanda();
    });

    $('#btnConfirmarAperturaCaja').on('click', function () {
        const montoInicial = parseFloat($('#montoInicialCaja').val());
        const observaciones = $('#observacionesApertura').val();

        if (isNaN(montoInicial) || montoInicial < 0) {
            toastr.error('Ingrese un monto inicial válido');
            return;
        }

        $.ajax({
            url: '/ventas/abrir-caja',
            method: 'POST',
            data: {
                _token: $('#token').val(),
                monto_inicial: montoInicial,
                observaciones: observaciones,
                tipo_caja_origen: 'RESTAURANT'
            },
            beforeSend: function () {
                $('#btnConfirmarAperturaCaja').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Abriendo caja...');
            },
            success: function (response) {
                if (response.status === 'OK') {
                    toastr.success(response.message || 'Caja abierta correctamente');
                    $('#modalAperturaCaja').modal('hide');
                } else {
                    toastr.error(response.message || 'Error al abrir caja');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Error al abrir caja';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                toastr.error(errorMsg);
            },
            complete: function () {
                $('#btnConfirmarAperturaCaja').prop('disabled', false).html('<i class="fa fa-check"></i> Abrir Caja');
            }
        });
    });
});

function cargarDetalleCajaCerrarComandas() {
    $.ajax({
        url: '/ventas/cerrar_comandas/info-caja',
        method: 'GET',
        beforeSend: function () {
            $('#detalleCajaCerrarComandasContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Cargando información...</p></div>');
        },
        success: function (response) {
            if (response.status !== 'OK') {
                $('#detalleCajaCerrarComandasContent').html('<div class="alert alert-warning">No se pudo cargar la información de caja</div>');
                return;
            }

            const caja = response.caja;

            const html = `
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fa fa-info-circle"></i> Información de Caja</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Fecha Apertura:</strong> ${caja.fecha_apertura}</p>
                                <p><strong>Monto Inicial:</strong> ${formatoMoneda(caja.monto_inicial)}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Cantidad de Ventas:</strong> ${caja.cantidad_ventas}</p>
                                <p><strong>Total Ventas:</strong> <span class="text-success font-weight-bold">${formatoMoneda(caja.total_ventas)}</span></p>
                            </div>
                        </div>

                        ${caja.observaciones_apertura ? `
                        <div class="alert alert-info">
                            <strong>Observaciones de Apertura:</strong><br>
                            ${caja.observaciones_apertura}
                        </div>
                        ` : ''}

                        <h6><strong>Desglose por Forma de Pago</strong></h6>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Forma de Pago</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>💵 Efectivo</td>
                                    <td class="text-right">${formatoMoneda(caja.desglose.efectivo)}</td>
                                </tr>
                                <tr>
                                    <td>🏦 Tarjeta Débito</td>
                                    <td class="text-right">${formatoMoneda(caja.desglose.tarjeta_debito)}</td>
                                </tr>
                                <tr>
                                    <td>💳 Tarjeta Crédito</td>
                                    <td class="text-right">${formatoMoneda(caja.desglose.tarjeta_credito)}</td>
                                </tr>
                                <tr>
                                    <td>🔄 Transferencia</td>
                                    <td class="text-right">${formatoMoneda(caja.desglose.transferencia)}</td>
                                </tr>
                                <tr>
                                    <td>📋 Cheque</td>
                                    <td class="text-right">${formatoMoneda(caja.desglose.cheque)}</td>
                                </tr>
                                ${caja.desglose.mixto > 0 ? `
                                <tr>
                                    <td>🔀 Mixto</td>
                                    <td class="text-right">${formatoMoneda(caja.desglose.mixto)}</td>
                                </tr>
                                ` : ''}
                                <tr class="table-success font-weight-bold">
                                    <td>TOTAL</td>
                                    <td class="text-right">${formatoMoneda(caja.total_ventas)}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="alert alert-success mt-3" style="margin-bottom:0;">
                            <div class="d-flex justify-content-between align-items-center" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
                                <div>
                                    <h5 style="margin-top:0;"><strong>Monto Esperado en Caja:</strong> ${formatoMoneda(caja.monto_esperado)}</h5>
                                    <small>Monto Inicial (${formatoMoneda(caja.monto_inicial)}) + Total Ventas (${formatoMoneda(caja.total_ventas)})</small>
                                </div>
                                <button class="btn btn-danger btn-lg" id="btnAbrirCierreCajaCerrarComandas">
                                    <i class="fa fa-power-off"></i> Cerrar Caja
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#detalleCajaCerrarComandasContent').html(html);
        },
        error: function () {
            $('#detalleCajaCerrarComandasContent').html('<div class="alert alert-danger">Error al cargar información de caja</div>');
        }
    });
}

$(document).on('click', '#btnAbrirCierreCajaCerrarComandas', function () {
    $.ajax({
        url: '/ventas/cerrar_comandas/info-caja',
        method: 'GET',
        success: function (response) {
            if (response.status === 'OK') {
                const caja = response.caja;
                $('#montoEsperadoCierreCerrarComandas').text(formatoMoneda(caja.monto_esperado));
                $('#totalVentasCierreCerrarComandas').text(formatoMoneda(caja.total_ventas));
                $('#montoFinalDeclaradoCerrarComandas').val('');
                $('#diferenciaCierreCerrarComandas').text('$ 0').removeClass('text-danger text-success').addClass('text-muted');
                $('#observacionesCierreCerrarComandas').val('');
                $('#modalCierreCajaCerrarComandas').data('monto-esperado', caja.monto_esperado);
                $('#modalCierreCajaCerrarComandas').modal('show');
            }
        },
        error: function () {
            toastr.error('No se pudo cargar la información de caja para cierre');
        }
    });
});

$('#montoFinalDeclaradoCerrarComandas').on('input', function () {
    const montoFinal = parseFloat($(this).val()) || 0;
    const montoEsperado = parseFloat($('#modalCierreCajaCerrarComandas').data('monto-esperado')) || 0;
    const diferencia = montoFinal - montoEsperado;

    const $diferencia = $('#diferenciaCierreCerrarComandas');
    const diferenciaAbs = formatoMoneda(Math.abs(diferencia));

    if (diferencia > 0) {
        $diferencia.removeClass('text-muted text-danger').addClass('text-success').text('+ ' + diferenciaAbs);
    } else if (diferencia < 0) {
        $diferencia.removeClass('text-muted text-success').addClass('text-danger').text('- ' + diferenciaAbs);
    } else {
        $diferencia.removeClass('text-danger text-success').addClass('text-muted').text('$ 0');
    }
});

$('#btnConfirmarCierreCajaCerrarComandas').on('click', function () {
    const montoFinal = parseFloat($('#montoFinalDeclaradoCerrarComandas').val());
    const observaciones = $('#observacionesCierreCerrarComandas').val();

    if (isNaN(montoFinal) || montoFinal < 0) {
        toastr.error('Ingrese un monto final válido');
        return;
    }

    $.ajax({
        url: '/ventas/cerrar_comandas/cerrar-caja',
        method: 'POST',
        data: {
            _token: $('#token').val(),
            monto_final_declarado: montoFinal,
            observaciones: observaciones
        },
        beforeSend: function () {
            $('#btnConfirmarCierreCajaCerrarComandas').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cerrando caja...');
        },
        success: function (response) {
            if (response.status === 'OK') {
                toastr.success(response.message || 'Caja cerrada correctamente');
                $('#modalCierreCajaCerrarComandas').modal('hide');
                $('#modalDetalleCajaCerrarComandas').modal('hide');
                const diferencia = response.diferencia;
                const cajaId = response.caja_id;
                let mensajeCierre = response.message || 'Caja cerrada correctamente';
                
                if (Math.abs(diferencia) > 0) {
                    if (diferencia > 0) {
                        mensajeCierre += `\nSobrante: ${formatoMoneda(diferencia)}`;
                    } else {
                        mensajeCierre += `\nFaltante: ${formatoMoneda(Math.abs(diferencia))}`;
                    }
                }

                $('#modalTicketRest').off('hidden.bs.modal.cierreCajaRest').on('hidden.bs.modal.cierreCajaRest', function() {
                    $('#ticketFrameRest').attr('src', 'about:blank');
                    Swal.fire({
                        title: 'Caja Cerrada',
                        text: mensajeCierre,
                        type: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                });

                $('#ticketFrameRest').attr('src', `/ventas/cierre-caja-pdf/${cajaId}`);
                $('#modalTicketRest').modal('show');
            } else {
                toastr.error(response.message || 'Error al cerrar caja');
            }
        },
        error: function (xhr) {
            let mensaje = 'Error al cerrar caja';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            toastr.error(mensaje);
        },
        complete: function () {
            $('#btnConfirmarCierreCajaCerrarComandas').prop('disabled', false).html('<i class="fa fa-power-off"></i> Cerrar Caja');
        }
    });
});

function cargarComandasPendientes() {
    $.ajax({
        url: '/ventas/cerrar_comandas/pendientes',
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {
            $('#estadoCierreComandas').removeClass('alert-danger alert-success').addClass('alert-info').text('Cargando comandas pendientes de pago...');
        },
        success: function (response) {
            if (!response.success) {
                toastr.error(response.message || 'No se pudieron cargar las comandas pendientes');
                return;
            }

            comandasPendientes = response.comandas || [];
            renderizarComandasPendientes();
        },
        error: function (xhr) {
            let mensaje = 'Error al cargar comandas pendientes';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            $('#estadoCierreComandas').removeClass('alert-info').addClass('alert-danger').text(mensaje);
        }
    });
}

function renderizarComandasPendientes() {
    const container = $('#listaComandasPendientes');
    container.empty();

    if (comandasPendientes.length === 0) {
        $('#estadoCierreComandas').removeClass('alert-info alert-danger').addClass('alert-success').text('No hay mesas pendientes de pago.');
        return;
    }

    $('#estadoCierreComandas').removeClass('alert-danger alert-success').addClass('alert-info').text('Mesas pendientes de pago: ' + comandasPendientes.length);

    comandasPendientes.forEach(function (comanda) {
        const html = `
            <div class="col-md-4">
                <div class="card-comanda-pendiente">
                    <div class="card-header">
                        <i class="fa fa-chair"></i> ${comanda.mesa_nombre || 'Mesa'}
                        <span class="badge badge-warning pull-right">Pendiente pago</span>
                    </div>
                    <div class="card-body">
                        <p><strong>Comanda:</strong> ${comanda.numero_comanda}</p>
                        <p><strong>Garzón:</strong> ${comanda.garzon || 'Sin asignar'}</p>
                        <p><strong>Comensales:</strong> ${comanda.comensales || 0}</p>
                        <p><strong>Items:</strong> ${comanda.cantidad_items || 0}</p>
                        <p><strong>Abierta:</strong> ${comanda.fecha_apertura || '-'}</p>
                        <div class="clearfix" style="margin-top:10px;">
                            <span class="total-comanda pull-left">${formatoMoneda(comanda.total)}</span>
                            <button class="btn btn-success btn-sm btn-cerrar-comanda pull-right" data-comanda-id="${comanda.id}">
                                <i class="fa fa-cash-register"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.append(html);
    });

    $('.btn-cerrar-comanda').off('click').on('click', function () {
        const comandaId = $(this).data('comanda-id');
        abrirModalCerrarComanda(comandaId);
    });
}

function abrirModalCerrarComanda(comandaId) {
    comandaSeleccionada = comandasPendientes.find(c => c.id == comandaId) || null;

    if (!comandaSeleccionada) {
        toastr.error('No se encontró la comanda seleccionada');
        return;
    }

    pagosMixtosComanda = [];
    $('#formaPagoCerrarComanda').val('');
    $('#seccionPagoMixtoComanda').hide();
    $('#formaPagoMixtoComandaSelect').val('');
    $('#montoPagoMixtoComanda').val('');

    const aplicaPropina = comandaSeleccionada.incluye_propina === true || comandaSeleccionada.incluye_propina === 1;
    const porcentajeInicial = (typeof comandaSeleccionada.porcentaje_propina !== 'undefined' && comandaSeleccionada.porcentaje_propina !== null)
        ? parseFloat(comandaSeleccionada.porcentaje_propina)
        : (typeof porcentajePropinaGlobal !== 'undefined' ? parseFloat(porcentajePropinaGlobal) : 10);

    $('#cerrar_incluye_propina').prop('checked', aplicaPropina);
    $('#cerrar_propina_porcentaje').val(isNaN(porcentajeInicial) ? 10 : porcentajeInicial);

    $('#tituloComandaCerrar').text(comandaSeleccionada.numero_comanda);
    $('#modalMesaNombre').text(comandaSeleccionada.mesa_nombre || '-');
    $('#modalComensales').text(comandaSeleccionada.comensales || 0);

    actualizarTotalesCierreComanda();

    actualizarTablaPagoMixtoComanda();
    $('#modalCerrarComanda').modal('show');
}

function obtenerTotalesCierreComanda() {
    if (!comandaSeleccionada) {
        return {
            subtotal: 0,
            porcentajePropina: 0,
            incluyePropina: false,
            propina: 0,
            total: 0
        };
    }

    const subtotal = parseFloat(comandaSeleccionada.subtotal || 0);
    const incluyePropina = $('#cerrar_incluye_propina').is(':checked');
    const porcentajeInput = parseFloat($('#cerrar_propina_porcentaje').val() || 0);
    const porcentajePropina = Math.max(0, Math.min(100, isNaN(porcentajeInput) ? 0 : porcentajeInput));
    const propina = incluyePropina ? Math.round(subtotal * (porcentajePropina / 100)) : 0;
    const total = subtotal + propina;

    return {
        subtotal: subtotal,
        porcentajePropina: porcentajePropina,
        incluyePropina: incluyePropina,
        propina: propina,
        total: total
    };
}

function actualizarTotalesCierreComanda() {
    const totales = obtenerTotalesCierreComanda();
    $('#modalSubtotalComanda').text(formatoMoneda(totales.subtotal));
    $('#modalPropinaComanda').text(formatoMoneda(totales.propina));
    $('#modalTotalComanda').text(formatoMoneda(totales.total));
    $('#modalTotalComandaCalculado').text(formatoMoneda(totales.total));
    $('#totalPagoMixtoComanda').text(formatoMoneda(totales.total));
}

function agregarPagoMixtoComanda() {
    if (!comandaSeleccionada) {
        return;
    }

    const forma = $('#formaPagoMixtoComandaSelect').val();
    const monto = parseInt($('#montoPagoMixtoComanda').val() || 0);

    if (!forma) {
        toastr.warning('Selecciona una forma de pago');
        return;
    }

    if (monto <= 0) {
        toastr.warning('Ingresa un monto válido');
        return;
    }

    const total = Math.round(obtenerTotalesCierreComanda().total);
    const acumulado = pagosMixtosComanda.reduce((sum, p) => sum + p.monto, 0);

    if (acumulado + monto > total) {
        toastr.error('La suma excede el total de la comanda');
        return;
    }

    pagosMixtosComanda.push({
        id: Date.now(),
        forma: forma,
        monto: monto
    });

    $('#formaPagoMixtoComandaSelect').val('');
    $('#montoPagoMixtoComanda').val('');
    actualizarTablaPagoMixtoComanda();
}

function actualizarTablaPagoMixtoComanda() {
    const tbody = $('#tablaPagoMixtoComanda tbody');
    tbody.empty();

    const total = comandaSeleccionada ? Math.round(obtenerTotalesCierreComanda().total) : 0;

    pagosMixtosComanda.forEach(function (pago) {
        const fila = `
            <tr>
                <td>${etiquetaFormaPago(pago.forma)}</td>
                <td class="text-right">${formatoMoneda(pago.monto)}</td>
                <td class="text-right">
                    <button type="button" class="btn btn-danger btn-xs btn-eliminar-pago-mixto" data-id="${pago.id}">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(fila);
    });

    $('.btn-eliminar-pago-mixto').off('click').on('click', function () {
        const id = $(this).data('id');
        pagosMixtosComanda = pagosMixtosComanda.filter(p => p.id !== id);
        actualizarTablaPagoMixtoComanda();
    });

    const acumulado = pagosMixtosComanda.reduce((sum, p) => sum + p.monto, 0);
    const pendiente = total - acumulado;
    $('#pendientePagoMixtoComanda').text(formatoMoneda(pendiente));
}

function confirmarCierreComanda() {
    if (!comandaSeleccionada) {
        toastr.error('No hay comanda seleccionada');
        return;
    }

    const formaPago = $('#formaPagoCerrarComanda').val();
    if (!formaPago) {
        toastr.warning('Selecciona una forma de pago');
        return;
    }

    const payload = {
        _token: $('#token').val(),
        forma_pago: formaPago,
        incluye_propina: $('#cerrar_incluye_propina').is(':checked') ? 1 : 0,
        porcentaje_propina: parseFloat($('#cerrar_propina_porcentaje').val() || 0)
    };

    const totalCalculado = Math.round(obtenerTotalesCierreComanda().total);

    if (formaPago === 'MIXTO') {
        if (pagosMixtosComanda.length === 0) {
            toastr.warning('Agrega el desglose de pagos mixtos');
            return;
        }

        const acumulado = pagosMixtosComanda.reduce((sum, p) => sum + p.monto, 0);

        if (acumulado !== totalCalculado) {
            toastr.error('La suma del pago mixto debe coincidir con el total');
            return;
        }

        payload.formas_pago_desglose = pagosMixtosComanda.map(function (p) {
            return {
                forma: p.forma,
                monto: p.monto
            };
        });
    }

    $.ajax({
        url: '/ventas/cerrar_comandas/cerrar/' + comandaSeleccionada.id,
        type: 'POST',
        dataType: 'json',
        data: payload,
        beforeSend: function () {
            $('#btnConfirmarCerrarComanda').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cerrando...');
        },
        success: function (response) {
            if (!response.success) {
                toastr.error(response.message || 'No se pudo cerrar la comanda');
                return;
            }

            toastr.success(response.message || 'Comanda cerrada correctamente');
            $('#modalCerrarComanda').modal('hide');

            if (response.venta_id) {
                $('#tituloTicket').text('Ticket Pago Comanda');

                $('#modalTicketRest').off('hidden.bs.modal.cierreComandaRest').on('hidden.bs.modal.cierreComandaRest', function() {
                    $('#ticketFrameRest').attr('src', 'about:blank');
                    cargarComandasPendientes();
                });

                $('#ticketFrameRest').attr('src', `/restaurant/comandas/ticket-pago/${comandaSeleccionada.id}/${response.venta_id}`);
                $('#modalTicketRest').modal('show');
            } else {
                cargarComandasPendientes();
            }
        },
        error: function (xhr) {
            let mensaje = 'Error al cerrar la comanda';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            toastr.error(mensaje);
        },
        complete: function () {
            $('#btnConfirmarCerrarComanda').prop('disabled', false).html('<i class="fa fa-check"></i> Confirmar cierre');
        }
    });
}

function formatoMoneda(valor) {
    const numero = parseInt(valor || 0);
    return '$ ' + numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function etiquetaFormaPago(forma) {
    const labels = {
        EFECTIVO: '💵 Efectivo',
        TARJETA_DEBITO: '🏦 Tarjeta Débito',
        TARJETA_CREDITO: '💳 Tarjeta Crédito',
        TRANSFERENCIA: '🔄 Transferencia',
        CHEQUE: '📋 Cheque'
    };

    return labels[forma] || forma;
}
