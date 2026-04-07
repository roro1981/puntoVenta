$(document).ready(function () {

    // ---------------------------------------------------------------
    // Estado global
    // ---------------------------------------------------------------
    var productoUuid  = '';
    var productoNombre = '';
    var mvTable       = null;
    var lastData      = null; // para exportar

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function num(n) {
        return Number(n).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // ---------------------------------------------------------------
    // Datepicker fechas
    // ---------------------------------------------------------------
    var dpOpts = {
        clearText: 'Limpiar', closeText: 'Cerrar', prevText: '&laquo;', nextText: '&raquo;',
        currentText: 'Hoy',
        monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
        dayNamesShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
        dateFormat: 'dd/mm/yy', firstDay: 1, isRTL: false,
    };
    $('#fecha_desde, #fecha_hasta').datepicker(dpOpts);

    // Fecha por defecto: último mes
    var hoy = new Date();
    var hace30 = new Date(); hace30.setDate(hoy.getDate() - 30);
    function pad(n) { return n < 10 ? '0' + n : n; }
    $('#fecha_desde').datepicker('setDate', hace30);
    $('#fecha_hasta').datepicker('setDate', hoy);

    // Marcar «Último mes» como activo por defecto
    $('[data-periodo="mes"]').addClass('activo');

    // ---------------------------------------------------------------
    // Etiquetas de período rápido
    // ---------------------------------------------------------------
    $(document).on('click', '.btn-fecha-rapida', function () {
        var periodo = $(this).data('periodo');
        var hoyR  = new Date();
        var desde = new Date();

        if (periodo === 'semana') desde.setDate(hoyR.getDate() - 7);
        else if (periodo === 'mes')    desde.setMonth(hoyR.getMonth() - 1);
        else if (periodo === '3meses') desde.setMonth(hoyR.getMonth() - 3);
        else if (periodo === '6meses') desde.setMonth(hoyR.getMonth() - 6);
        else if (periodo === 'anio')   desde.setFullYear(hoyR.getFullYear() - 1);

        $('#fecha_desde').datepicker('setDate', desde);
        $('#fecha_hasta').datepicker('setDate', hoyR);

        // Marcar activo
        $('.btn-fecha-rapida').removeClass('activo');
        $(this).addClass('activo');

        // Ejecutar reporte automáticamente si ya hay producto seleccionado
        if (productoUuid) {
            $('#btn_ver').trigger('click');
        }
    });

    // ---------------------------------------------------------------
    // Autocomplete producto
    // ---------------------------------------------------------------
    var searchTimer = null;
    $('#mv_buscar').on('keyup', function () {
        var q = $(this).val().trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { $('#mv_sugerencias').empty().hide(); return; }

        searchTimer = setTimeout(function () {
            $.ajax({
                url: '/reportes/mov_productos/search',
                method: 'GET',
                data: { q: q },
                success: function (data) { renderSugerencias(data); }
            });
        }, 280);
    });

    function renderSugerencias(items) {
        var $box = $('#mv_sugerencias').empty();
        if (!items || items.length === 0) { $box.hide(); return; }

        var html = '<table><thead><tr><th>Código</th><th>Nombre</th><th>Stock</th></tr></thead><tbody>';
        items.forEach(function (item) {
            html += '<tr class="mv-sug-row"'
                  + ' data-uuid="' + esc(item.uuid) + '"'
                  + ' data-nombre="' + esc(item.descripcion) + '">'
                  + '<td>' + esc(item.codigo) + '</td>'
                  + '<td>' + esc(item.descripcion) + '</td>'
                  + '<td class="text-right">' + num(item.stock) + '</td>'
                  + '</tr>';
        });
        html += '</tbody></table>';
        $box.html(html).show();

        $box.find('.mv-sug-row').on('click', function () {
            productoUuid   = $(this).data('uuid');
            productoNombre = $(this).data('nombre');
            $('#mv_buscar').val(productoNombre);
            $box.hide();
        });
    }

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.mv-search-wrap').length) {
            $('#mv_sugerencias').hide();
        }
    });

    // ---------------------------------------------------------------
    // Generar
    // ---------------------------------------------------------------
    $('#btn_ver').on('click', function () {
        if (!productoUuid) {
            toastr.warning('Seleccione un producto del listado de sugerencias.');
            return;
        }
        var desde = $('#fecha_desde').val();
        var hasta = $('#fecha_hasta').val();
        if (!desde || !hasta) {
            toastr.warning('Ingrese el rango de fechas.');
            return;
        }

        // Convertir dd/mm/yyyy → yyyy/mm/dd para el backend
        var f1 = desde.split('/'); var f2 = hasta.split('/');
        var desdeApi = f1[2] + '/' + f1[1] + '/' + f1[0];
        var hastaApi = f2[2] + '/' + f2[1] + '/' + f2[0];

        if (Date.parse(hastaApi) < Date.parse(desdeApi)) {
            toastr.warning('La fecha "Desde" no puede ser mayor que "Hasta".');
            return;
        }

        $('#mv_loader').show();
        $('#mv_resultado').hide();
        $('#excel').prop('disabled', true);

        $.ajax({
            url: '/reportes/trae_movimientos',
            method: 'GET',
            data: {
                idp:       productoUuid,
                tipo_mov:  $('#tip_movi').val(),
                fec_desde: desdeApi,
                fec_hasta: hastaApi,
            },
            success: function (data) {
                $('#mv_loader').hide();
                if (!data || !data.movimientos) {
                    toastr.error('Error al procesar la respuesta.');
                    return;
                }
                if (data.movimientos.length === 0) {
                    toastr.info('No hay movimientos para el período y filtro seleccionados.');
                    return;
                }
                lastData = { uuid: productoUuid, tipo: $('#tip_movi').val(), desde: desdeApi, hasta: hastaApi };
                renderResultado(data);
            },
            error: function () {
                $('#mv_loader').hide();
                toastr.error('Error al obtener los movimientos.');
            }
        });
    });

    // ---------------------------------------------------------------
    // Render resultado
    // ---------------------------------------------------------------
    function tipoBadge(tipo) {
        var map = {
            'CREACIÓN':       ['mv-badge-creacion',  'fa-plus-circle'],
            'VENTA':          ['mv-badge-venta',   'fa-shopping-cart'],
            'VENTA (RECETA)': ['mv-badge-venta',   'fa-shopping-cart'],
            'VENTA (PROMO)':  ['mv-badge-venta',   'fa-shopping-cart'],
            'ENTRADA':        ['mv-badge-entrada',  'fa-arrow-down'],
            'FACTURA COMPRA': ['mv-badge-entrada',  'fa-file-text'],
            'BOLETA COMPRA':  ['mv-badge-entrada',  'fa-file-text-o'],
            'SALIDA':         ['mv-badge-salida',   'fa-arrow-up'],
            'MERMA':          ['mv-badge-merma',    'fa-exclamation-triangle'],
            'ANULACIÓN':      ['mv-badge-anulacion','fa-undo'],
        };
        var cfg = map[tipo] || ['mv-badge-default', 'fa-circle'];
        return '<span class="mv-badge ' + cfg[0] + '"><i class="fa ' + cfg[1] + '"></i> ' + esc(tipo) + '</span>';
    }

    function renderResultado(data) {
        // KPIs
        $('#mv_nombre_producto')
            .html('<i class="fa fa-cube"></i> ' + esc(data.nombre) + ' <small>(' + esc(data.codigo) + ')</small>')
            .show();
        $('#mv_kpi_stock').text(num(data.stock_actual));
        $('#mv_kpi_entradas').text(num(data.total_entradas));
        $('#mv_kpi_salidas').text(num(data.total_salidas));

        var varNeta = data.variacion_neta;
        var varColor = varNeta >= 0 ? '#1a7a3c' : '#a93226';
        var varPrefix = varNeta >= 0 ? '+' : '';
        $('#mv_kpi_variacion').text(varPrefix + num(varNeta)).css('color', varColor);

        // Tabla
        if (mvTable) { mvTable.destroy(); mvTable = null; }
        var $tbody = $('#tbl_movis tbody').empty();

        data.movimientos.forEach(function (m) {
            var esEntrada = m.signo === '+';
            var esSalida  = m.signo === '-';
            var rowClass  = esEntrada ? 'mv-row-entrada' : (esSalida ? 'mv-row-salida' : '');
            var cantHtml  = '';
            if (esEntrada) cantHtml = '<span class="mv-qty mv-qty-plus">+' + num(m.cantidad) + '</span>';
            else if (esSalida) cantHtml = '<span class="mv-qty mv-qty-minus">-' + num(m.cantidad) + '</span>';
            else if (m.cantidad > 0) cantHtml = '<span class="mv-qty">' + num(m.cantidad) + '</span>';
            else cantHtml = '<span class="mv-qty" style="color:#aaa">&mdash;</span>';

            $tbody.append(
                '<tr class="' + rowClass + '">'
                + '<td>' + esc(m.fecha) + '</td>'
                + '<td>' + tipoBadge(m.tipo_mov) + '</td>'
                + '<td class="text-center">' + cantHtml + '</td>'
                + '<td class="text-right"><strong>' + num(m.stock) + '</strong></td>'
                + '<td>' + esc(m.obs) + '</td>'
                + '</tr>'
            );
        });

        mvTable = $('#tbl_movis').DataTable({
            language: { url: '/vendor/datatables/Spanish.json' },
            order: [[0, 'desc']],
            paging: true,
            pageLength: 25,
            info: true,
            destroy: true,
        });

        $('#mv_resultado').fadeIn(200);
        $('#excel').prop('disabled', false);
    }

    // ---------------------------------------------------------------
    // Exportar
    // ---------------------------------------------------------------
    $('#excel').on('click', function () {
        if (!lastData) return;
        var url = '/reportes/exportar-movimientos'
            + '?tipo_mov=' + lastData.tipo
            + '&idprod=' + lastData.uuid
            + '&desde=' + lastData.desde
            + '&hasta=' + lastData.hasta;
        window.open(url, '_blank');
    });

});