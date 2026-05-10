$(document).ready(function () {
    var dpOpts = {
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
        firstDay: 1,
    };

    $('#cp_desde').datepicker(dpOpts);
    $('#cp_hasta').datepicker(dpOpts);

    var detalleTable = null;
    var ultimoDesde = null;
    var ultimoHasta = null;

    function fmtFecha(d) {
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        return dd + '/' + mm + '/' + d.getFullYear();
    }

    function aIso(fechaDdMmYyyy) {
        var p = fechaDdMmYyyy.split('/');
        return p[2] + '-' + p[1] + '-' + p[0];
    }

    function clp(n) {
        return '$' + Number(n || 0).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function cargarRangoInicial() {
        var hoy = new Date();
        $('#cp_desde').datepicker('setDate', hoy);
        $('#cp_hasta').datepicker('setDate', hoy);
    }

    function cargarSelectGarzones(items) {
        var $sel = $('#cp_select_garzon');
        if (!$sel.length) return;

        var actual = $sel.val() || '';
        $sel.empty().append('<option value="">-- Seleccione garzon --</option>');
        (items || []).forEach(function (g) {
            var opt = $('<option>').val(g.id).text(g.nombre);
            if (String(g.id) === String(actual)) {
                opt.prop('selected', true);
            }
            $sel.append(opt);
        });
    }

    function renderTabla(rows) {
        if (detalleTable) {
            detalleTable.destroy();
            detalleTable = null;
        }

        var $tbody = $('#cp_tabla_detalle tbody');
        $tbody.empty();

        (rows || []).forEach(function (r) {
            $tbody.append(
                '<tr>' +
                    '<td>' + (r.folio || '') + '</td>' +
                    '<td>' + (r.fecha || '') + '</td>' +
                    '<td>' + (r.garzon || '') + '</td>' +
                    '<td>' + (r.mesa || '') + '</td>' +
                    '<td class="text-right">' + clp(r.ventas) + '</td>' +
                    '<td class="text-right">' + clp(r.propina) + '</td>' +
                '</tr>'
            );
        });

        detalleTable = $('#cp_tabla_detalle').DataTable({
            responsive: true,
            destroy: true,
            searching: true,
            pageLength: 10,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            }
        });
    }

    function generar() {
        var desdeRaw = $('#cp_desde').val();
        var hastaRaw = $('#cp_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas.');
            return;
        }

        var esGarzon = !!window.cpEsGarzon;
        var garzonId = $('#cp_select_garzon').val() || '';

        if (!esGarzon && !garzonId) {
            toastr.warning('Selecciona un garzon para consultar.');
            return;
        }

        ultimoDesde = aIso(desdeRaw);
        ultimoHasta = aIso(hastaRaw);

        $('#cp_spinner').show();
        $('#cp_resultado').hide();

        $.ajax({
            url: '/ventas/control_propinas/data',
            method: 'GET',
            data: {
                desde: ultimoDesde,
                hasta: ultimoHasta,
                garzon_id: garzonId
            },
            success: function (data) {
                $('#cp_spinner').hide();

                if (!esGarzon) {
                    cargarSelectGarzones(data.garzones || []);
                    if (!garzonId) {
                        $('#cp_resultado').hide();
                        return;
                    }
                }

                $('#cp_total_propinas').text(clp(data.totalPropinas));
                $('#cp_total_ventas').text(clp(data.totalVentas));
                $('#cp_total_comandas').text(Number(data.totalComandas || 0).toLocaleString('es-CL'));
                $('#cp_tasa_propina').text((Number(data.tasaPropina || 0)).toFixed(2) + '%');
                $('#cp_titulo_detalle').text('Detalle de propinas - ' + (data.nombreGarzon || 'Sin nombre'));

                renderTabla(data.detalle || []);
                $('#cp_resultado').show();
            },
            error: function (xhr) {
                $('#cp_spinner').hide();
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Error al cargar el control de propinas.';
                toastr.error(msg);
            }
        });
    }

    $('.cp-atajo').on('click', function () {
        var rango = $(this).data('rango');
        var hoy = new Date();
        var desde = new Date(hoy);
        var hasta = new Date(hoy);

        if (rango === 'ayer') {
            desde.setDate(hoy.getDate() - 1);
            hasta.setDate(hoy.getDate() - 1);
        } else if (rango === 'semana') {
            desde.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
        } else if (rango === 'mes') {
            desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        }

        $('#cp_desde').datepicker('setDate', desde);
        $('#cp_hasta').datepicker('setDate', hasta);
        generar();
    });

    $('#btn_cp_generar').on('click', generar);

    $('#cp_select_garzon').on('change', function () {
        if (ultimoDesde && ultimoHasta) {
            generar();
        }
    });

    cargarRangoInicial();

    if (window.cpEsGarzon) {
        generar();
    } else {
        // Cargar garzones disponibles del periodo actual sin forzar selección.
        var desdeInit = aIso($('#cp_desde').val());
        var hastaInit = aIso($('#cp_hasta').val());
        $.ajax({
            url: '/ventas/control_propinas/data',
            method: 'GET',
            data: { desde: desdeInit, hasta: hastaInit },
            success: function (data) {
                cargarSelectGarzones(data.garzones || []);
            }
        });
    }
});
