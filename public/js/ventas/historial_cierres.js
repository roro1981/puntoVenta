$(document).ready(function() {
    cargarCierres();
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
        order: [[3, 'desc']], // Ordenar por fecha de cierre descendente
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
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                    orthogonal: 'export'
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-primary',
                title: 'Historial de Cierres de Caja al ' + fechaFormateada,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
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
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
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
