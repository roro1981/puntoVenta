$(document).ready(function () {
    cargaUsuarios();

    $('#rut').on('input', function () {
        const $this = $(this);
        const raw = $this.val().replace(/[^0-9kK]/g, '').toUpperCase();
        const formatted = formatearRut(raw);
        $this.val(formatted);

        if (raw.length < 2) {
            $('#rutFeedback').addClass('d-none');
            return;
        }

        const esValido = validarRut(raw);
        $('#rutFeedback').toggleClass('d-none', esValido);
    });

    function formatearRut(rutClean) {
        const cuerpo = rutClean.slice(0, -1);
        const dv = rutClean.slice(-1);

        const cuerpoConPuntos = cuerpo
            .split('').reverse().join('')
            .replace(/(\d{3})(?=\d)/g, '$1.')
            .split('').reverse().join('');

        return cuerpoConPuntos + (cuerpo ? '-' : '') + dv;
    }

    function validarRut(rutClean) {
        const cuerpo = rutClean.slice(0, -1);
        let dv = rutClean.slice(-1);

        let suma = 0, multiplicador = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += multiplicador * +cuerpo[i];
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        const resto = suma % 11;
        const dvCalc = resto === 1 ? 'K' : resto === 0 ? '0' : String(11 - resto);

        return dv === dvCalc;
    }

    $('.campo-mail').on('input', function () {
        const $input = $(this);
        const esValido = this.checkValidity();
        $input.next('.feedback-mail').toggleClass('d-none', esValido);
    });

    $('.campo-url').on('input', function () {
        const esValido = this.checkValidity();            // usa pattern + type=url
        $(this).next('.feedback-url')
            .toggleClass('d-none', esValido);
    });

    function cargaUsuarios() {

        $('#tabla_proveedores').DataTable({
            responsive: true,
            destroy: true,
            "ajax": {
                "url": "/compras/proveedores_list",
                "type": "GET"
            },
            "columns": [
                { "data": "razon_social" },
                { "data": "giro" },
                { "data": "region-comuna" },
                { "data": "fec_creacion" },
                { "data": "fec_modificacion" },
                { "data": "actions" }
            ],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
            "pageLength": 10,
            "searching": true,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });
    }
    $('#region').on('change', function () {
        const regionId = $(this).val();
        const $comuna = $('#comuna');

        // Limpia el segundo select
        $comuna.empty().append('<option value="">Cargando...</option>');

        if (!regionId) {
            $comuna.html('<option value="">-- Seleccionar región primero --</option>');
            return;
        }

        $.ajax({
            url: "/compras/" + regionId + "/comunas",
            type: 'GET',
            dataType: 'json',

            success: function (data) {
                $comuna.empty().append('<option value="">-- Seleccionar --</option>');
                $.each(data, function (_, item) {
                    $comuna.append(
                        $('<option>', { value: item.id, text: item.nom_comuna })
                    );
                });
            },

            error: function () {
                $comuna.html('<option value="">Error al cargar comunas</option>');
            }
        });
    });
    $('#edit_region').on('change', function () {
        const regionId = $(this).val();
        const $comuna = $('#edit_comuna');
        const comunaSel = $(this).data('comuna-id');

        // Limpia el segundo select
        $comuna.empty().append('<option value="">Cargando...</option>');

        if (!regionId) {
            $comuna.html('<option value="">-- Seleccionar región primero --</option>');
            return;
        }

        $.ajax({
            url: "/compras/" + regionId + "/comunas",
            type: 'GET',
            dataType: 'json',

            success: function (data) {
                $comuna.empty().append('<option value="">-- Seleccionar --</option>');
                $.each(data, function (_, item) {
                    $comuna.append(
                        $('<option>', { value: item.id, text: item.nom_comuna })
                    );
                });
                if (comunaSel) {
                    $comuna.val(String(comunaSel));
                    $('#edit_region').data('comuna-id', null);
                }
            },

            error: function () {
                $comuna.html('<option value="">Error al cargar comunas</option>');
            }
        });
    });
    $('#createProveedorForm').submit(function (event) {
        event.preventDefault();
        if (!this.checkValidity()) {
            e.preventDefault();
            $(this).addClass('was-validated');
        }
        const fd = new FormData(this);

        let rutLimpio = $('#rut').val().replace(/\./g, '');

        if (!validarRut(rutLimpio.replace(/-/g, ''))) {
            toastr.error("Rut no válido");
            return false;
        }
        fd.set('rut', rutLimpio);

        $.ajax({
            type: 'POST',
            url: '/compras/createProveedor',
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (data) {
                toastr.success(data.message);
                $("#createProveedorModal").modal("hide");
                $('#contenido').load('/compras/proveedores');
            },
            error: function (xhr, status, error) {
                if (xhr.status === 400) {
                    toastr.warning(xhr.responseJSON.message, 'Validación', { timeOut: 7000 });
                } else {
                    toastr.error('Error al crear proveedor.');
                }
            }
        });
    });

    $('#editProveedorForm').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        const uuid = $('#edit_uuid').val();
        const fd = new FormData(this);

        $.ajax({
            url: '/compras/' + uuid + '/update',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },

            success: function () {
                $('#editProveedorModal').modal('hide');
                $('#contenido').load('/compras/proveedores');
                toastr.success('Proveedor actualizado');
            },
            error: function (xhr) {
                toastr.error('No se pudo actualizar');
            }
        });
    });
    $(document).on('click', '.editar', function (e) {
        e.preventDefault();
        var uuid = $(this).data('uuid');

        $.ajax({
            type: 'GET',
            url: '/compras/' + uuid + '/edit',
            success: function (data) {
                $('#edit_rut').val(data.rut);
                $('#edit_razon_social').val(data.razon_social);
                $('#edit_nombre_fantasia').val(data.nombre_fantasia);
                $('#edit_giro').val(data.giro);
                $('#edit_direccion').val(data.direccion);
                $('#edit_telefono').val(data.telefono);
                $('#edit_email').val(data.email);
                $('#edit_pagina_web').val(data.pagina_web);
                $('#edit_contacto_nombre').val(data.contacto_nombre);
                $('#edit_contacto_email').val(data.contacto_email);
                $('#edit_contacto_telefono').val(data.contacto_telefono);
                $('#edit_region')
                    .val(data.region_id)
                    .data('comuna-id', data.comuna_id)   // “equipaje”
                    .trigger('change');
                $('#edit_uuid').val(uuid);
                $('#editProveedorModal').modal('show');
            }
        });
    });
    $(document).on('click', '.eliminar', function (event) {
        event.preventDefault();
        var uuid = $(this).data('uuid');
        var nombreProv = $(this).data('nameprov');
        Swal.fire({
            title: "¿Estás seguro?",
            text: "¿Estás seguro de eliminar el proveedor " + nombreProv + "?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'DELETE',
                    url: '/compras/' + uuid + '/delete',
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function (data) {
                        toastr.success(data.message);
                        $('#contenido').load('/compras/proveedores');
                    },
                    error: function (xhr, status, error) {
                        toastr.error("Error " + xhr.responseJSON.error + "<br>" + xhr.responseJSON.message);
                    }
                })
            } else {
                toastr.error("Eliminación cancelada");
            }
        });

    });
});
