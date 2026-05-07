$(document).ready(function() {
    cargarCierres();
    initConsolidarHandlers();
});

function cargarCierres() {
    var fechaActual = new Date();
    var dia = fechaActual.getDate();
    var mes = fechaActual.getMonth() + 1;
    var ano = fechaActual.getFullYear();
    var fechaFormateada = `${dia.toString().padStart(2, '0')}-${mes.toString().padStart(2, '0')}-${ano}`;

    $('#tabla_cierres').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: '/ventas/obtener-cierres',
            type: 'GET',
            dataSrc: 'data'
        },
        columns: [
            {
                // Columna checkbox
                data: null,
                orderable: false,
                searchable: false,
                className: 'dt-body-center',
                render: function(data, type, row) {
                    return '<input type="checkbox" class="check-cierre" data-id="' + row.id.replace(/^0+/, '') + '">';
                }
            },
            { data: 'id' },
            { data: 'usuario' },
            { data: 'fecha_apertura' },
            { data: 'fecha_cierre' },
            { 
                data: null,
                render: function(data, type, row) {
                    if (type === 'export') {
                        return parseInt(row.monto_inicial_raw);
                    }
                    return row.monto_inicial;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    if (type === 'export') {
                        return parseInt(row.monto_ventas_raw);
                    }
                    return row.monto_ventas;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    if (type === 'export') {
                        return parseInt(row.monto_esperado_raw);
                    }
                    return row.monto_esperado;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    if (type === 'export') {
                        return parseInt(row.monto_declarado_raw);
                    }
                    return row.monto_declarado;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    if (type === 'export') {
                        return parseInt(row.diferencia_raw);
                    }
                    return row.diferencia;
                }
            },
            { data: 'actions' }
        ],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        pageLength: 10,
        searching: true,
        order: [[4, 'desc']], // Ordenar por fecha de cierre descendente (índice 4 con checkbox)
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                className: 'btn btn-success',
                title: 'Historial de Cierres de Caja al ' + fechaFormateada,
                filename: 'Historial_Cierres_' + fechaFormateada,
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9],
                    orthogonal: 'export'
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-primary',
                title: 'Historial de Cierres de Caja al ' + fechaFormateada,
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                },
                customize: function(win) {
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
                title: 'Historial de Cierres de Caja al ' + fechaFormateada,
                filename: 'Historial_Cierres_' + fechaFormateada,
                orientation: 'landscape',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9]
                },
                customize: function(doc) {
                    doc.pageMargins = [20, 20, 20, 20];
                    doc.pageSize = 'A4';
                    doc.pageOrientation = 'landscape';
                }
            }
        ]
    });
}

// Manejador para ver ticket PDF en modal
$(document).on('click', '.ver-ticket-pdf', function(e) {
    e.preventDefault();
    var cajaId = $(this).data('caja-id');
    
    // Cargar PDF en iframe
    $('#ticketCierreFrame').attr('src', '/ventas/cierre-caja-pdf/' + cajaId);
    $('#modalTicketCierre').modal('show');
});

// Manejador para ver detalle del cierre
$(document).on('click', '.ver-detalle', function() {
    var cajaId = $(this).data('caja-id');
    
    // Mostrar modal con loader
    $('#modalDetalleCierre').modal('show');
    $('#detalleCierreContent').html(`
        <div class="text-center">
            <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
            <p style="margin-top: 15px;">Cargando información...</p>
        </div>
    `);
    
    // Obtener detalle
    $.ajax({
        url: '/ventas/detalle-cierre/' + cajaId,
        type: 'GET',
        success: function(response) {
            mostrarDetalleCierre(response);
        },
        error: function(xhr) {
            var errorMsg = 'Error al cargar el detalle';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#detalleCierreContent').html(`
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> ${errorMsg}
                </div>
            `);
        }
    });
});

function mostrarDetalleCierre(data) {
    var caja = data.caja;
    var desglose = data.desglose;
    
    var diferenciaClass = '';
    var diferenciaIcon = '';
    var diferenciaText = '';
    
    if (caja.diferencia > 0) {
        diferenciaClass = 'sobrante';
        diferenciaIcon = 'fa-arrow-up';
        diferenciaText = 'SOBRANTE: $' + formatNumber(caja.diferencia);
    } else if (caja.diferencia < 0) {
        diferenciaClass = 'faltante';
        diferenciaIcon = 'fa-arrow-down';
        diferenciaText = 'FALTANTE: $' + formatNumber(Math.abs(caja.diferencia));
    } else {
        diferenciaClass = 'exacto';
        diferenciaIcon = 'fa-check-circle';
        diferenciaText = 'CUADRE EXACTO';
    }
    
    var html = `
        <div class="detalle-cierre-container">
            <div class="row">
                <div class="col-md-6">
                    <div class="detalle-section">
                        <h5><i class="fa fa-info-circle"></i> Información General</h5>
                        <table class="info-table">
                            <tr>
                                <th>Nº Cierre:</th>
                                <td><strong>${caja.id}</strong></td>
                            </tr>
                            <tr>
                                <th>Usuario:</th>
                                <td>${caja.usuario}</td>
                            </tr>
                            <tr>
                                <th>Apertura:</th>
                                <td>${caja.fecha_apertura}</td>
                            </tr>
                            <tr>
                                <th>Cierre:</th>
                                <td>${caja.fecha_cierre}</td>
                            </tr>
                            <tr>
                                <th>Duración:</th>
                                <td>${caja.duracion}</td>
                            </tr>
                            <tr>
                                <th>Cantidad Ventas:</th>
                                <td><strong style="color: #01338d; font-size: 16px;">${caja.cantidad_ventas}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="detalle-section">
                        <h5><i class="fa fa-credit-card"></i> Desglose por Forma de Pago</h5>
                        <table class="desglose-table">
                            ${desglose.efectivo > 0 ? `<tr><td>💵 Efectivo</td><td>$${formatNumber(desglose.efectivo)}</td></tr>` : ''}
                            ${desglose.tarjeta_debito > 0 ? `<tr><td>🏦 Tarjeta Débito</td><td>$${formatNumber(desglose.tarjeta_debito)}</td></tr>` : ''}
                            ${desglose.tarjeta_credito > 0 ? `<tr><td>💳 Tarjeta Crédito</td><td>$${formatNumber(desglose.tarjeta_credito)}</td></tr>` : ''}
                            ${desglose.transferencia > 0 ? `<tr><td>🔄 Transferencia</td><td>$${formatNumber(desglose.transferencia)}</td></tr>` : ''}
                            ${desglose.cheque > 0 ? `<tr><td>📋 Cheque</td><td>$${formatNumber(desglose.cheque)}</td></tr>` : ''}
                            ${desglose.mixto > 0 ? `<tr><td>🔀 Mixto</td><td>$${formatNumber(desglose.mixto)}</td></tr>` : ''}
                            ${!desglose.efectivo && !desglose.tarjeta_debito && !desglose.tarjeta_credito && !desglose.transferencia && !desglose.cheque && !desglose.mixto ? '<tr><td colspan="2" style="text-align: center; color: #999;">Sin ventas registradas</td></tr>' : ''}
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="detalle-section">
                        <h5><i class="fa fa-calculator"></i> Resumen Financiero</h5>
                        <div class="resumen-financiero">
                            <table>
                                <tr>
                                    <th>Monto Inicial:</th>
                                    <td>$${formatNumber(caja.monto_inicial)}</td>
                                </tr>
                                <tr>
                                    <th>Total Ventas:</th>
                                    <td>$${formatNumber(caja.monto_ventas)}</td>
                                </tr>
                                ${caja.total_retiros > 0 ? `<tr style="color:#c0392b;">
                                    <th>Total Retiros:</th>
                                    <td>-$${formatNumber(caja.total_retiros)}</td>
                                </tr>` : ''}
                                <tr class="row-esperado">
                                    <th><strong>Monto Esperado:</strong></th>
                                    <td><strong>$${formatNumber(caja.monto_esperado)}</strong></td>
                                </tr>
                                <tr class="row-declarado">
                                    <th><strong>Monto Declarado:</strong></th>
                                    <td><strong>$${formatNumber(caja.monto_declarado)}</strong></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="alert-diferencia ${diferenciaClass}">
                            <i class="fa ${diferenciaIcon}"></i> ${diferenciaText}
                        </div>
                    </div>
                </div>
            </div>

            ${data.retiros && data.retiros.length > 0 ? `
            <div class="row">
                <div class="col-12">
                    <div class="detalle-section">
                        <h5><i class="fa fa-minus-circle text-danger"></i> Retiros de Caja</h5>
                        <div style="overflow-x:auto;">
                            <table class="table table-sm table-bordered" style="font-size:12px;">
                                <thead style="background-color:#fef9e7;">
                                    <tr><th>Fecha y Hora</th><th>Motivo</th><th style="text-align:right;">Monto</th></tr>
                                </thead>
                                <tbody>
                                    ${data.retiros.map(r => `<tr>
                                        <td>${r.created_at}</td>
                                        <td>${r.motivo}</td>
                                        <td style="text-align:right;color:#c0392b;font-weight:bold;">-$${formatNumber(r.monto)}</td>
                                    </tr>`).join('')}
                                    <tr style="background:#fdecea;">
                                        <td colspan="2"><strong>TOTAL RETIROS</strong></td>
                                        <td style="text-align:right;color:#c0392b;font-weight:bold;">-$${formatNumber(caja.total_retiros)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>` : ''}
            
            ${caja.observaciones ? `
            <div class="row">
                <div class="col-12">
                    <div class="observaciones-box">
                        <h5><i class="fa fa-comment"></i> Observaciones</h5>
                        <div class="observaciones-content">
                            ${caja.observaciones.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    $('#detalleCierreContent').html(html);
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// ============================================================
// Checkbox: seleccionar todos / individuales
// ============================================================
$(document).on('change', '#checkTodos', function() {
    var checked = $(this).prop('checked');
    $('#tabla_cierres tbody .check-cierre').prop('checked', checked);
    actualizarBotonConsolidar();
});

$(document).on('change', '.check-cierre', function() {
    var total = $('#tabla_cierres tbody .check-cierre').length;
    var seleccionados = $('#tabla_cierres tbody .check-cierre:checked').length;
    $('#checkTodos').prop('indeterminate', seleccionados > 0 && seleccionados < total);
    $('#checkTodos').prop('checked', seleccionados === total && total > 0);
    actualizarBotonConsolidar();
});

function actualizarBotonConsolidar() {
    var seleccionados = $('#tabla_cierres tbody .check-cierre:checked').length;
    $('#countSeleccionadas').text(seleccionados);
    $('#btnConsolidar').prop('disabled', seleccionados < 2);
    if (seleccionados > 0) {
        $('#btnDeseleccionarTodo').show();
    } else {
        $('#btnDeseleccionarTodo').hide();
    }
}

$(document).on('click', '#btnDeseleccionarTodo', function() {
    $('#tabla_cierres tbody .check-cierre').prop('checked', false);
    $('#checkTodos').prop('checked', false).prop('indeterminate', false);
    actualizarBotonConsolidar();
});

// ============================================================
// Consolidar cajas seleccionadas
// ============================================================
function initConsolidarHandlers() {
    $('#btnConsolidar').on('click', function() {
        var ids = [];
        $('#tabla_cierres tbody .check-cierre:checked').each(function() {
            ids.push(parseInt($(this).data('id')));
        });

        if (ids.length < 2) {
            alert('Selecciona al menos 2 cierres para consolidar.');
            return;
        }

        $('#modalConsolidar').data('ids', ids);
        $('#consolidadoContent').html(`
            <div class="text-center">
                <i class="fa fa-spinner fa-spin fa-3x text-warning"></i>
                <p style="margin-top: 15px;">Consolidando información...</p>
            </div>
        `);
        $('#consolidadoFooter').hide();
        $('#modalConsolidar').modal('show');

        var token = $('#token').val();
        $.ajax({
            url: '/ventas/consolidar-cajas',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ ids: ids }),
            headers: { 'X-CSRF-TOKEN': token },
            success: function(data) {
                mostrarConsolidado(data);
                $('#consolidadoFooter').show();
            },
            error: function(xhr) {
                var msg = 'Error al consolidar.';
                if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                $('#consolidadoContent').html(`<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ${msg}</div>`);
            }
        });
    });

    $('#btnImprimirConsolidado').on('click', function() {
        var ids = $('#modalConsolidar').data('ids');
        if (!ids || !ids.length) return;
        $('#ticketCierreFrame').attr('src', '/ventas/consolidar-cajas/imprimir?ids=' + ids.join(','));
        $('#modalConsolidar').modal('hide');
        $('#modalTicketCierre').modal('show');
    });

    $('#btnExcelConsolidado').on('click', function() {
        var ids = $('#modalConsolidar').data('ids');
        if (!ids || !ids.length) return;
        window.location.href = '/ventas/consolidar-cajas/excel?ids=' + ids.join(',');
    });
}

function mostrarConsolidado(data) {
    var diferenciaClass = '';
    var diferenciaIcon = '';
    var diferenciaText = '';

    if (data.diferencia > 0) {
        diferenciaClass = 'sobrante';
        diferenciaIcon = 'fa-arrow-up';
        diferenciaText = 'SOBRANTE: $' + formatNumber(data.diferencia);
    } else if (data.diferencia < 0) {
        diferenciaClass = 'faltante';
        diferenciaIcon = 'fa-arrow-down';
        diferenciaText = 'FALTANTE: $' + formatNumber(Math.abs(data.diferencia));
    } else {
        diferenciaClass = 'exacto';
        diferenciaIcon = 'fa-check-circle';
        diferenciaText = 'CUADRE EXACTO';
    }

    var desgloseLabels = {
        efectivo: '💵 Efectivo',
        tarjeta_debito: '🏦 Tarjeta Débito',
        tarjeta_credito: '💳 Tarjeta Crédito',
        transferencia: '🔄 Transferencia',
        cheque: '📋 Cheque',
        mixto: '🔀 Mixto'
    };

    var filasDesglose = '';
    Object.keys(desgloseLabels).forEach(function(key) {
        if (data.desglose[key] > 0) {
            filasDesglose += `<tr><td>${desgloseLabels[key]}</td><td style="text-align:right;font-weight:bold;">$${formatNumber(data.desglose[key])}</td></tr>`;
        }
    });
    if (!filasDesglose) {
        filasDesglose = '<tr><td colspan="2" style="text-align:center;color:#999;">Sin ventas registradas</td></tr>';
    }

    var filasCajas = '';
    data.cajas.forEach(function(c) {
        var difClass = c.diferencia > 0 ? 'text-success' : (c.diferencia < 0 ? 'text-danger' : 'text-info');
        var difText  = c.diferencia > 0 ? '+$' + formatNumber(c.diferencia) : (c.diferencia < 0 ? '-$' + formatNumber(Math.abs(c.diferencia)) : 'Exacto');
        filasCajas += `
            <tr>
                <td><strong>#${c.id}</strong></td>
                <td>${c.usuario}</td>
                <td>${c.fecha_apertura}</td>
                <td>${c.fecha_cierre}</td>
                <td style="text-align:right;">$${formatNumber(c.monto_ventas)}</td>
                <td style="text-align:right;">$${formatNumber(c.monto_declarado)}</td>
                <td style="text-align:right;" class="${difClass} font-weight-bold">${difText}</td>
            </tr>
        `;
    });

    var filasRetiros = '';
    if (data.retiros && data.retiros.length > 0) {
        data.retiros.forEach(function(r) {
            filasRetiros += `<tr>
                <td>${r.created_at}</td>
                <td>${r.motivo}</td>
                <td style="text-align:right;color:#c0392b;font-weight:bold;">-$${formatNumber(r.monto)}</td>
            </tr>`;
        });
        filasRetiros += `<tr style="background:#fdecea;">
            <td colspan="2"><strong>TOTAL RETIROS</strong></td>
            <td style="text-align:right;color:#c0392b;font-weight:bold;">-$${formatNumber(data.total_retiros)}</td>
        </tr>`;
    }

    var html = `
        <div class="detalle-cierre-container">
            <div class="row">
                <div class="col-md-5">
                    <div class="detalle-section">
                        <h5><i class="fa fa-info-circle"></i> Información General</h5>
                        <table class="info-table">
                            <tr><th>Turnos incluidos:</th><td><strong>${data.cajas.length}</strong></td></tr>
                            <tr><th>Período:</th><td>${data.fecha_desde} &mdash; ${data.fecha_hasta}</td></tr>
                            <tr><th>Cantidad de ventas:</th><td><strong style="color:#01338d;font-size:16px;">${data.cantidad_ventas}</strong></td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="detalle-section">
                        <h5><i class="fa fa-credit-card"></i> Desglose por Forma de Pago</h5>
                        <table class="desglose-table">${filasDesglose}</table>
                    </div>
                </div>
            </div>

            ${filasRetiros ? `
            <div class="row">
                <div class="col-12">
                    <div class="detalle-section">
                        <h5><i class="fa fa-minus-circle text-danger"></i> Retiros de Caja</h5>
                        <div style="overflow-x:auto;">
                            <table class="table table-sm table-bordered" style="font-size:12px;">
                                <thead style="background-color:#fef9e7;">
                                    <tr><th>Fecha y Hora</th><th>Motivo</th><th style="text-align:right;">Monto</th></tr>
                                </thead>
                                <tbody>${filasRetiros}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>` : ''}

            <div class="row">
                <div class="col-12">
                    <div class="detalle-section">
                        <h5><i class="fa fa-calculator"></i> Resumen Financiero</h5>
                        <div class="resumen-financiero">
                            <table>
                                <tr><th>Monto Inicial Total:</th><td>$${formatNumber(data.monto_inicial)}</td></tr>
                                <tr><th>Total Ventas:</th><td>$${formatNumber(data.total_ventas)}</td></tr>
                                ${data.total_retiros > 0 ? `<tr style="color:#c0392b;"><th>Total Retiros:</th><td>-$${formatNumber(data.total_retiros)}</td></tr>` : ''}
                                <tr class="row-esperado"><th><strong>Monto Esperado:</strong></th><td><strong>$${formatNumber(data.monto_esperado)}</strong></td></tr>
                                <tr class="row-declarado"><th><strong>Monto Declarado Total:</strong></th><td><strong>$${formatNumber(data.monto_declarado)}</strong></td></tr>
                            </table>
                        </div>
                        <div class="alert-diferencia ${diferenciaClass}">
                            <i class="fa ${diferenciaIcon}"></i> ${diferenciaText}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="detalle-section">
                        <h5><i class="fa fa-list"></i> Detalle por Turno</h5>
                        <div style="overflow-x:auto;">
                            <table class="table table-sm table-bordered" style="font-size:12px;">
                                <thead style="background-color:#f5f5f5;">
                                    <tr>
                                        <th>Nº</th><th>Usuario</th><th>Apertura</th><th>Cierre</th>
                                        <th style="text-align:right;">Ventas</th>
                                        <th style="text-align:right;">Declarado</th>
                                        <th style="text-align:right;">Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>${filasCajas}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#consolidadoContent').html(html);
}
