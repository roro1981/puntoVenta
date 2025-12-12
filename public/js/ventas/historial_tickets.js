$(document).ready(function() {
    // Establecer fecha por defecto (última semana)
    var hoy = new Date();
    var hace7Dias = new Date();
    hace7Dias.setDate(hoy.getDate() - 7);
    
    $('#fecha_desde').val(formatearFecha(hace7Dias));
    $('#fecha_hasta').val(formatearFecha(hoy));
    
    cargarTickets();
});

function formatearFecha(fecha) {
    var año = fecha.getFullYear();
    var mes = String(fecha.getMonth() + 1).padStart(2, '0');
    var dia = String(fecha.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}

function cargarTickets() {
    var fechaDesde = $('#fecha_desde').val();
    var fechaHasta = $('#fecha_hasta').val();
    var fechaActual = new Date();
    var dia = fechaActual.getDate();
    var mes = fechaActual.getMonth() + 1;
    var ano = fechaActual.getFullYear();
    var fechaFormateada = `${dia.toString().padStart(2, '0')}-${mes.toString().padStart(2, '0')}-${ano}`;

    // Destruir tabla si existe
    if ($.fn.DataTable.isDataTable('#tabla_tickets')) {
        $('#tabla_tickets').DataTable().destroy();
    }

    $('#tabla_tickets').DataTable({
        responsive: true,
        destroy: true,
        ajax: {
            url: '/ventas/obtener-tickets',
            type: 'GET',
            data: {
                fecha_desde: fechaDesde,
                fecha_hasta: fechaHasta
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: 'fecha' },
            { data: 'vendedor' },
            { 
                data: null,
                render: function(data, type, row) {
                    if (type === 'export') {
                        return parseInt(row.total_raw);
                    }
                    return row.total;
                }
            },
            { data: 'forma_pago' },
            { 
                data: 'estado',
                render: function(data, type, row) {
                    if (type === 'export') {
                        return data === 'completada' ? 'Completada' : (data === 'parcialmente_anulada' ? 'Parcialmente Anulada' : 'Anulada');
                    }
                    var badge = '';
                    if (data === 'completada') {
                        badge = '<span class="badge badge-success">Completada</span>';
                    } else if (data === 'parcialmente_anulada') {
                        badge = '<span class="badge badge-warning">Parcial. Anulada</span>';
                    } else if (data === 'anulada') {
                        badge = '<span class="badge badge-danger">Anulada</span>';
                    }
                    return badge;
                }
            },
            { data: 'actions' }
        ],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        pageLength: 10,
        searching: true,
        order: [[1, 'desc']], // Ordenar por fecha descendente
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar a Excel',
                className: 'btn btn-success',
                title: 'Historial de Tickets al ' + fechaFormateada,
                filename: 'Historial_Tickets_' + fechaFormateada,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5],
                    orthogonal: 'export'
                }
            },
            {
                extend: 'print',
                text: 'Imprimir',
                className: 'btn btn-primary',
                title: 'Historial de Tickets al ' + fechaFormateada,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
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
                title: 'Historial de Tickets al ' + fechaFormateada,
                filename: 'Historial_Tickets_' + fechaFormateada,
                orientation: 'portrait',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                customize: function(doc) {
                    doc.pageMargins = [20, 20, 20, 20];
                    doc.pageSize = 'A4';
                    doc.pageOrientation = 'portrait';
                }
            }
        ]
    });
}

// Manejador para ver ticket PDF en modal
$(document).on('click', '.ver-ticket', function(e) {
    e.preventDefault();
    var ventaId = $(this).data('venta-id');
    
    // Cargar PDF en iframe
    $('#ticketFrame').attr('src', '/ventas/ticket-pdf/' + ventaId);
    $('#modalTicket').modal('show');
});

// Manejador para anular ticket
$(document).on('click', '.anular-ticket', function(e) {
    e.preventDefault();
    var ventaId = $(this).data('venta-id');
    
    // Mostrar modal
    $('#modalAnularTicket').modal('show');
    $('#detalleTicketAnular').html(`
        <div class="text-center">
            <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
            <p style="margin-top: 15px;">Cargando información...</p>
        </div>
    `);
    
    // Guardar ID de venta en el modal
    $('#modalAnularTicket').data('venta-id', ventaId);
    
    // Cargar detalles del ticket
    $.ajax({
        url: '/ventas/detalle-ticket/' + ventaId,
        type: 'GET',
        success: function(response) {
            mostrarDetalleAnulacion(response);
        },
        error: function(xhr) {
            var errorMsg = 'Error al cargar el detalle';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            $('#detalleTicketAnular').html(`
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> ${errorMsg}
                </div>
            `);
        }
    });
});

function mostrarDetalleAnulacion(data) {
    var estadoVenta = data.venta.estado;
    var ticketTotalmenteAnulado = (estadoVenta === 'anulada');
    var hayProductosAnulables = data.detalles.some(d => !d.anulado);
    
    var html = `
        <div class="info-ticket mb-3">
            <h5>Información del Ticket</h5>
            <table class="table table-sm">
                <tr>
                    <td><strong>Ticket Nº:</strong></td>
                    <td>${String(data.venta.id).padStart(4, '0')}</td>
                    <td><strong>Fecha:</strong></td>
                    <td>${data.venta.fecha}</td>
                </tr>
                <tr>
                    <td><strong>Vendedor:</strong></td>
                    <td>${data.venta.vendedor}</td>
                    <td><strong>Total Actual:</strong></td>
                    <td><strong>$${Number(data.venta.total).toLocaleString('es-CL')}</strong></td>
                </tr>
                <tr>
                    <td><strong>Estado:</strong></td>
                    <td colspan="3">`;
    
    if (estadoVenta === 'completada') {
        html += '<span class="badge badge-success">Completada</span>';
    } else if (estadoVenta === 'parcialmente_anulada') {
        html += '<span class="badge badge-warning">Parcialmente Anulada</span>';
    } else {
        html += '<span class="badge badge-danger">Totalmente Anulada</span>';
    }
    
    html += `</td>
                </tr>
            </table>
        </div>`;
    
    if (ticketTotalmenteAnulado) {
        html += `
            <div class="alert alert-info">
                <h5><i class="fa fa-info-circle"></i> Ticket Totalmente Anulado</h5>
                <p>Este ticket ha sido completamente anulado. No se pueden realizar más acciones sobre él.</p>
            </div>
        `;
    }
    
    html += `
        <h5>Productos del Ticket</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>`;
    
    if (!ticketTotalmenteAnulado && hayProductosAnulables) {
        html += `<th width="50px">
                            <input type="checkbox" id="selectAll" title="Seleccionar todos">
                        </th>`;
    }
    
    html += `
                        <th>Producto</th>
                        <th width="100px">Cantidad</th>
                        <th width="120px">Precio Unit.</th>
                        <th width="120px">Subtotal</th>
                        <th width="100px">Tipo</th>
                        <th width="120px">Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.detalles.forEach(function(detalle) {
        var tipoLabel = '';
        var tipoColor = '';
        
        switch(detalle.tipo) {
            case 'simple':
                tipoLabel = 'Simple';
                tipoColor = 'badge-info';
                break;
            case 'receta':
                tipoLabel = 'Receta';
                tipoColor = 'badge-warning';
                break;
            case 'promocion':
                tipoLabel = 'Promoción';
                tipoColor = 'badge-success';
                break;
            default:
                tipoLabel = detalle.tipo;
                tipoColor = 'badge-secondary';
        }
        
        var estadoProducto = '';
        var claseFilaAnulada = detalle.anulado ? 'table-danger' : '';
        
        if (detalle.anulado) {
            estadoProducto = `
                <span class="badge badge-danger">Anulado</span><br>
                <small><strong>Por:</strong> ${detalle.usuario_anulacion || 'N/A'}</small><br>
                <small><strong>Fecha:</strong> ${detalle.fecha_anulacion || 'N/A'}</small>
            `;
        } else {
            estadoProducto = '<span class="badge badge-success">Activo</span>';
        }
        
        html += `
            <tr class="${claseFilaAnulada}">`;
        
        if (!ticketTotalmenteAnulado && hayProductosAnulables) {
            if (detalle.anulado) {
                html += `<td class="text-center">
                            <i class="fa fa-ban text-muted" title="Ya anulado"></i>
                        </td>`;
            } else {
                html += `<td class="text-center">
                            <input type="checkbox" class="detalle-checkbox" value="${detalle.id}">
                        </td>`;
            }
        }
        
        html += `
                <td>${detalle.descripcion}</td>
                <td class="text-center">${detalle.cantidad}</td>
                <td class="text-right">$${Number(detalle.precio_unitario).toLocaleString('es-CL')}</td>
                <td class="text-right"><strong>$${Number(detalle.subtotal).toLocaleString('es-CL')}</strong></td>
                <td class="text-center">
                    <span class="badge ${tipoColor}">${tipoLabel}</span>
                </td>
                <td class="text-center">
                    ${estadoProducto}
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    if (!ticketTotalmenteAnulado && hayProductosAnulables) {
        html += `
            <p class="text-muted small">
                <i class="fa fa-info-circle"></i> 
                Puedes seleccionar productos específicos para anular parcialmente el ticket, 
                o usar el botón "Anular Todo el Ticket" para anular todos los productos restantes.
            </p>
        `;
    }
    
    $('#detalleTicketAnular').html(html);
    
    // Mostrar/ocultar botones según el estado
    if (ticketTotalmenteAnulado) {
        $('#btnAnularCompleto').hide();
        $('#btnAnularSeleccionados').hide();
    } else if (hayProductosAnulables) {
        $('#btnAnularCompleto').show();
        $('#btnAnularSeleccionados').show();
        
        // Manejar selección de todos
        $('#selectAll').on('change', function() {
            $('.detalle-checkbox').prop('checked', $(this).is(':checked'));
        });
        
        // Actualizar select all cuando cambian los checkboxes individuales
        $(document).on('change', '.detalle-checkbox', function() {
            var total = $('.detalle-checkbox').length;
            var checked = $('.detalle-checkbox:checked').length;
            $('#selectAll').prop('checked', total === checked);
        });
    } else {
        $('#btnAnularCompleto').hide();
        $('#btnAnularSeleccionados').hide();
    }
}

// Anular ticket completo - usar delegación de eventos
$(document).on('click', '#btnAnularCompleto', function() {
    var ventaId = $('#modalAnularTicket').data('venta-id');
    
    // Cerrar modal antes de mostrar SweetAlert para evitar conflictos
    $('#modalAnularTicket').modal('hide');
    
    // Esperar a que el modal se cierre completamente
    setTimeout(function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se anulará TODO el ticket y se devolverá el stock completo',
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motivo de la anulación',
            inputPlaceholder: 'Ingresa el motivo de la anulación...',
            inputAttributes: {
                'aria-label': 'Motivo de anulación'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular todo',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar un motivo'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                anularTicket(ventaId, true, [], result.value);
            } else {
                // Si cancela, remover backdrop residual
                $('.swal2-container').remove();
                $('body').removeClass('swal2-shown swal2-height-auto');
            }
        });
    }, 400);
});

// Anular productos seleccionados - usar delegación de eventos
$(document).on('click', '#btnAnularSeleccionados', function() {
    var ventaId = $('#modalAnularTicket').data('venta-id');
    var detallesSeleccionados = [];
    
    // IMPORTANTE: Capturar seleccionados ANTES de cerrar el modal
    $('.detalle-checkbox:checked').each(function() {
        detallesSeleccionados.push(parseInt($(this).val()));
    });
    
    if (detallesSeleccionados.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección',
            text: 'Debes seleccionar al menos un producto para anular'
        });
        return;
    }
    
    // Ahora sí cerrar modal
    $('#modalAnularTicket').modal('hide');
    
    // Esperar a que el modal se cierre completamente
    setTimeout(function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se anularán ${detallesSeleccionados.length} producto(s) seleccionado(s)`,
            icon: 'warning',
            input: 'textarea',
            inputLabel: 'Motivo de la anulación',
            inputPlaceholder: 'Ingresa el motivo de la anulación...',
            inputAttributes: {
                'aria-label': 'Motivo de anulación'
            },
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular seleccionados',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar un motivo'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar con anular_completo en FALSE
                anularTicket(ventaId, false, detallesSeleccionados, result.value);
            } else {
                // Si cancela, remover backdrop residual
                $('.swal2-container').remove();
                $('body').removeClass('swal2-shown swal2-height-auto');
            }
        });
    }, 400);
});

function anularTicket(ventaId, anularCompleto, detalles, motivo) {
    var token = $('#token').val();
    
    $.ajax({
        url: '/ventas/anular-ticket/' + ventaId,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': token
        },
        data: {
            anular_completo: anularCompleto,
            detalles: detalles,
            motivo: motivo
        },
        success: function(response) {
            $('#modalAnularTicket').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Ticket anulado',
                text: response.mensaje + '. Monto anulado: $' + Number(response.monto_anulado).toLocaleString('es-CL'),
                confirmButtonColor: '#01338d'
            }).then(() => {
                // Recargar la tabla
                cargarTickets();
            });
        },
        error: function(xhr) {
            var errorMsg = 'Error al anular el ticket';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

// Manejador para filtrar
$('#btnFiltrar').on('click', function() {
    cargarTickets();
});

// Manejador para limpiar filtros
$('#btnLimpiar').on('click', function() {
    var hoy = new Date();
    var hace7Dias = new Date();
    hace7Dias.setDate(hoy.getDate() - 7);
    
    $('#fecha_desde').val(formatearFecha(hace7Dias));
    $('#fecha_hasta').val(formatearFecha(hoy));
    
    cargarTickets();
});
