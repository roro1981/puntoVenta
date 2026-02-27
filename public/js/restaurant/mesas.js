$(document).ready(function() {
    cargarMesas();
    
    // Evento para abrir modal de nueva mesa
    $('#nueva_mesa').on('click', function() {
        $('#modalMesaLabel').text('Nueva Mesa');
        $('#mesa_id').val('');
        $('#mesaForm')[0].reset();
        $('#estado-group').hide();
    });
    
    // Evento para guardar mesa
    $('#guardar_mesa').on('click', function() {
        const mesaId = $('#mesa_id').val();
        const nombre = $('#nombre_mesa').val();
        const capacidad = $('#capacidad_mesa').val();
        const activa = $('#activa_mesa').val();
        
        if (!nombre || !capacidad) {
            Swal.fire('Error', 'Por favor complete todos los campos requeridos', 'error');
            return;
        }
        
        if (mesaId) {
            actualizarMesa(mesaId, nombre, capacidad, activa);
        } else {
            crearMesa(nombre, capacidad);
        }
    });
});

function cargarMesas() {
    $.ajax({
        url: '/restaurant/mesas/obtener',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderizarMesas(response.mesas);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar mesas:', xhr);
            Swal.fire('Error', 'No se pudieron cargar las mesas', 'error');
        }
    });
}

function renderizarMesas(mesas) {
    const container = $('#mesas-container');
    container.empty();
    
    mesas.forEach(function(mesa) {
        const mesaHtml = `
            <div class="mesa-card ${!mesa.activa ? 'inactiva' : ''}" data-id="${mesa.id}">
                <div class="mesa-header">
                    <h4>${mesa.nombre}</h4>
                    <span class="badge ${mesa.activa ? 'badge-success' : 'badge-secondary'}">
                        ${mesa.activa ? 'Activa' : 'Inactiva'}
                    </span>
                </div>
                <div class="mesa-body">
                    <p><i class="fa fa-users"></i> Capacidad: ${mesa.capacidad} personas</p>
                </div>
                <div class="mesa-footer">
                    <button class="btn btn-sm btn-primary editar-mesa" data-id="${mesa.id}">
                        <i class="fa fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-danger eliminar-mesa" data-id="${mesa.id}" data-nombre="${mesa.nombre}">
                        <i class="fa fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `;
        container.append(mesaHtml);
    });
    
    // Eventos para editar y eliminar
    $('.editar-mesa').on('click', function() {
        const mesaId = $(this).data('id');
        editarMesa(mesaId);
    });
    
    $('.eliminar-mesa').on('click', function() {
        const mesaId = $(this).data('id');
        const mesaNombre = $(this).data('nombre');
        eliminarMesa(mesaId, mesaNombre);
    });
}

function crearMesa(nombre, capacidad) {
    $.ajax({
        url: '/restaurant/mesas/crear',
        type: 'POST',
        data: {
            nombre: nombre,
            capacidad: capacidad,
            _token: $('#token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Éxito', response.message, 'success');
                $('#modalNuevaMesa').modal('hide');
                cargarMesas();
            }
        },
        error: function(xhr) {
            let mensaje = 'Error al crear la mesa';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }
    });
}

function editarMesa(mesaId) {
    $.ajax({
        url: '/restaurant/mesas/obtener',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const mesa = response.mesas.find(m => m.id === mesaId);
                if (mesa) {
                    $('#modalMesaLabel').text('Editar Mesa');
                    $('#mesa_id').val(mesa.id);
                    $('#nombre_mesa').val(mesa.nombre);
                    $('#capacidad_mesa').val(mesa.capacidad);
                    $('#activa_mesa').val(mesa.activa ? 1 : 0);
                    $('#estado-group').show();
                    $('#modalNuevaMesa').modal('show');
                }
            }
        }
    });
}

function actualizarMesa(mesaId, nombre, capacidad, activa) {
    $.ajax({
        url: '/restaurant/mesas/actualizar/' + mesaId,
        type: 'PUT',
        data: {
            nombre: nombre,
            capacidad: capacidad,
            activa: activa == 1 ? 1 : 0,
            _token: $('#token').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Éxito', response.message, 'success');
                $('#modalNuevaMesa').modal('hide');
                cargarMesas();
            }
        },
        error: function(xhr) {
            let mensaje = 'Error al actualizar la mesa';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire('Error', mensaje, 'error');
        }
    });
}

function eliminarMesa(mesaId, mesaNombre) {
    Swal.fire({
        title: '¿Está seguro?',
        text: `¿Desea eliminar la mesa "${mesaNombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/restaurant/mesas/eliminar/' + mesaId,
                type: 'DELETE',
                data: {
                    _token: $('#token').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success');
                        cargarMesas();
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error al eliminar la mesa';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', mensaje, 'error');
                }
            });
        }
    });
}
