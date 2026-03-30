/* =========================================================
   Garzones — CRUD
   ========================================================= */

$(document).ready(function () {

    cargarGarzones();

    /* ----- Abrir modal nuevo ----- */
    $('#nuevo_garzon').on('click', function () {
        resetForm();
        $('#modalGarzonLabel').text('Nuevo Garzón');
        $('#garzon-estado-group').hide();
    });

    /* ----- Guardar (crear o actualizar) ----- */
    $('#guardar_garzon').on('click', function () {
        var id       = $('#garzon_id').val();
        var nombre   = $('#garzon_nombre').val().trim();
        var apellido = $('#garzon_apellido').val().trim();
        var rut      = $('#garzon_rut').val().trim();
        var telefono = $('#garzon_telefono').val().trim();
        var email    = $('#garzon_email').val().trim();
        var estado   = $('#garzon_estado').val();

        if (!nombre || !apellido || !rut) {
            toastr.warning('Nombre, apellido y RUT son obligatorios');
            return;
        }

        var url    = id ? '/restaurant/garzones/actualizar/' + id : '/restaurant/garzones/crear';
        var method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: {
                _token:   $('#token').val(),
                nombre:   nombre,
                apellido: apellido,
                rut:      rut,
                telefono: telefono,
                email:    email,
                estado:   estado
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#modalGarzon').modal('hide');
                    cargarGarzones();
                } else {
                    toastr.error(response.message || 'Error al guardar');
                }
            },
            error: function (xhr) {
                var msg = 'Error al guardar el garzón';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        var errores = Object.values(xhr.responseJSON.errors).flat();
                        msg = errores.join('<br>');
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                toastr.error(msg);
            }
        });
    });

    /* ----- Helpers ----- */
    function resetForm() {
        $('#garzon_id').val('');
        $('#garzonForm')[0].reset();
        $('#garzon_estado').val('Activo');
    }

    function cargarGarzones() {
        $.ajax({
            url: '/restaurant/garzones/obtener',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.success) {
                    toastr.error('Error al cargar los garzones');
                    return;
                }

                if ($.fn.DataTable.isDataTable('#tablaGarzones')) {
                    $('#tablaGarzones').DataTable().destroy();
                    $('#tablaGarzones').find('tbody').empty();
                }

                var html = '';
                if (response.garzones.length === 0) {
                    html = '<tr><td colspan="8" class="text-center text-muted">No hay garzones registrados</td></tr>';
                } else {
                    $.each(response.garzones, function (i, g) {
                        var badgeClass = g.estado === 'Activo' ? 'success' : 'default';
                        html += '<tr>';
                        html += '<td>' + (i + 1) + '</td>';
                        html += '<td>' + $('<span>').text(g.nombre).html() + '</td>';
                        html += '<td>' + $('<span>').text(g.apellido).html() + '</td>';
                        html += '<td>' + $('<span>').text(g.rut).html() + '</td>';
                        html += '<td>' + (g.telefono ? $('<span>').text(g.telefono).html() : '<span class="text-muted">-</span>') + '</td>';
                        html += '<td>' + (g.email ? $('<span>').text(g.email).html() : '<span class="text-muted">-</span>') + '</td>';
                        html += '<td><span class="label label-' + badgeClass + '">' + g.estado + '</span></td>';
                        html += '<td class="garzon-acciones">';
                        html += '<button class="btn btn-xs btn-warning btn-editar-garzon"'
                              + ' data-id="' + g.id + '"'
                              + ' data-nombre="' + $('<span>').text(g.nombre).html() + '"'
                              + ' data-apellido="' + $('<span>').text(g.apellido).html() + '"'
                              + ' data-rut="' + $('<span>').text(g.rut).html() + '"'
                              + ' data-telefono="' + $('<span>').text(g.telefono || '').html() + '"'
                              + ' data-email="' + $('<span>').text(g.email || '').html() + '"'
                              + ' data-estado="' + g.estado + '">'
                              + '<i class="fa fa-pencil"></i> Editar</button> ';
                        html += '<button class="btn btn-xs btn-danger btn-eliminar-garzon"'
                              + ' data-id="' + g.id + '"'
                              + ' data-nombre="' + $('<span>').text(g.nombre + ' ' + g.apellido).html() + '">'
                              + '<i class="fa fa-trash"></i> Eliminar</button>';
                        html += '</td>';
                        html += '</tr>';
                    });
                }

                $('#garzones-tbody').html(html);

                $('#tablaGarzones').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    order: [[1, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: [7] }
                    ]
                });

                /* Eventos de fila — se vinculan directo a los elementos recién creados */
                $('#garzones-tbody').find('.btn-editar-garzon').on('click', function () {
                    resetForm();
                    $('#garzon_id').val($(this).data('id'));
                    $('#garzon_nombre').val($(this).data('nombre'));
                    $('#garzon_apellido').val($(this).data('apellido'));
                    $('#garzon_rut').val($(this).data('rut'));
                    $('#garzon_telefono').val($(this).data('telefono'));
                    $('#garzon_email').val($(this).data('email'));
                    $('#garzon_estado').val($(this).data('estado'));
                    $('#garzon-estado-group').show();
                    $('#modalGarzonLabel').text('Editar Garzón');
                    $('#modalGarzon').modal('show');
                });

                $('#garzones-tbody').find('.btn-eliminar-garzon').on('click', function () {
                    var id     = $(this).data('id');
                    var nombre = $(this).data('nombre');

                    Swal.fire({
                        title: '¿Eliminar garzón?',
                        html: 'Se eliminará a <strong>' + nombre + '</strong>.<br>Si tiene comandas asociadas no se podrá eliminar.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then(function (result) {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: '/restaurant/garzones/eliminar/' + id,
                            type: 'DELETE',
                            data: { _token: $('#token').val() },
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    cargarGarzones();
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function (xhr) {
                                var msg = 'Error al eliminar el garzón';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                toastr.error(msg);
                            }
                        });
                    });
                });
            },
            error: function () {
                toastr.error('Error al conectar con el servidor');
            }
        });
    }

});
