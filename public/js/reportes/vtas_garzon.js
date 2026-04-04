$(document).ready(function () {

    // ---------------------------------------------------------------
    // Datepicker
    // ---------------------------------------------------------------
    var dpOpts = {
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
                          'Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
        firstDay: 1,
    };
    $('#vg_desde').datepicker(dpOpts);
    $('#vg_hasta').datepicker(dpOpts);

    // ---------------------------------------------------------------
    // Accesos rápidos
    // ---------------------------------------------------------------
    $('.vg-atajo').on('click', function () {
        var rango = $(this).data('rango');
        var hoy   = new Date();

        function fmt(d) {
            var dd = String(d.getDate()).padStart(2, '0');
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            return dd + '/' + mm + '/' + d.getFullYear();
        }

        var desde, hasta;
        if (rango === 'hoy') {
            desde = hasta = fmt(hoy);
        } else if (rango === 'ayer') {
            var ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
            desde = hasta = fmt(ayer);
        } else if (rango === 'semana') {
            var lunes = new Date(hoy);
            lunes.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
            desde = fmt(lunes); hasta = fmt(hoy);
        } else if (rango === 'mes') {
            desde = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            hasta = fmt(hoy);
        } else if (rango === 'mes_anterior') {
            desde = fmt(new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1));
            hasta = fmt(new Date(hoy.getFullYear(), hoy.getMonth(), 0));
        }

        $('#vg_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
        $('#vg_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));

        $('.vg-atajo').removeClass('active');
        $(this).addClass('active');

        $('#btn_vg_generar').trigger('click');
    });

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    function clp(n) {
        return '$' + Number(n).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    function parseFecha(str) {
        var p = str.split('/');
        return p[2] + '-' + p[1] + '-' + p[0];
    }
    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ---------------------------------------------------------------
    // Al cambiar el select de garzón → regenerar
    // ---------------------------------------------------------------
    $('#vg_select_garzon').on('change', function () {
        if (ultimoDesde && ultimoHasta) {
            $('#btn_vg_generar').trigger('click');
        }
    });

    // ---------------------------------------------------------------
    // Estado
    // ---------------------------------------------------------------
    var barChart          = null;
    var tendenciaChart    = null;
    var detalleTable      = null;
    var ultimoDesde       = null;
    var ultimoHasta       = null;
    var garzonesCargados  = false;

    // ---------------------------------------------------------------
    // GENERAR
    // ---------------------------------------------------------------
    $('#btn_vg_generar').on('click', function () {
        var desdeRaw = $('#vg_desde').val();
        var hastaRaw = $('#vg_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        var nuevoDesde = parseFecha(desdeRaw);
        var nuevoHasta = parseFecha(hastaRaw);
        var fechasCambiaron = (nuevoDesde !== ultimoDesde || nuevoHasta !== ultimoHasta);
        var garzonId = $('#vg_select_garzon').val() || '';

        if (fechasCambiaron) {
            garzonesCargados = false;
            $('#vg_select_garzon').val('');
            garzonId = '';
        }

        ultimoDesde = nuevoDesde;
        ultimoHasta = nuevoHasta;

        $('#vg_resultado').hide();
        $('#vg_spinner').show();
        $('#btn_vg_exportar_ventas').prop('disabled', true);
        $('#btn_vg_exportar_propinas').prop('disabled', true);

        $.ajax({
            url: '/reportes/vtas_garzon/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta, garzon_id: garzonId },
            success: function (data) {
                $('#vg_spinner').hide();

                if (!data.totalComandas || data.totalComandas === 0) {
                    $('#vg_resultado').hide();
                    toastr.info('No hay comandas cerradas en el periodo seleccionado.');
                    return;
                }

                if (!garzonesCargados || fechasCambiaron) {
                    actualizarSelectGarzones(data.garzones, garzonId);
                    garzonesCargados = true;
                }

                renderResultado(data, garzonId);
                $('#vg_resultado').fadeIn(200);
                $('#btn_vg_exportar_ventas').prop('disabled', false);
                if (data.totalPropinas > 0) {
                    $('#btn_vg_exportar_propinas').prop('disabled', false);
                }
            },
            error: function (xhr) {
                $('#vg_spinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    });

    // ---------------------------------------------------------------
    // EXPORTAR VENTAS
    // ---------------------------------------------------------------
    $('#btn_vg_exportar_ventas').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        window.location.href = '/reportes/vtas_garzon/exportar_ventas?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    // ---------------------------------------------------------------
    // EXPORTAR PROPINAS
    // ---------------------------------------------------------------
    $('#btn_vg_exportar_propinas').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        window.location.href = '/reportes/vtas_garzon/exportar_propinas?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    // ---------------------------------------------------------------
    // Actualizar select de garzones
    // ---------------------------------------------------------------
    function actualizarSelectGarzones(garzones, selectedId) {
        var $sel = $('#vg_select_garzon').off('change');
        var prev = selectedId || $sel.val() || '';
        $sel.empty().append('<option value="">— Todos —</option>');
        (garzones || []).forEach(function (g) {
            var opt = $('<option>').val(g.id).text(g.nombre);
            if (String(g.id) === String(prev)) opt.prop('selected', true);
            $sel.append(opt);
        });
        $sel.on('change', function () {
            if (ultimoDesde && ultimoHasta) $('#btn_vg_generar').trigger('click');
        });
    }

    // ---------------------------------------------------------------
    // RENDER PRINCIPAL
    // ---------------------------------------------------------------
    function renderResultado(data, garzonId) {
        var modoTodos = !garzonId;

        // Cards
        $('#vg_total_ventas').text(clp(data.totalVentas));
        $('#vg_total_comandas').text(Number(data.totalComandas).toLocaleString('es-CL'));
        $('#vg_total_propinas').text(clp(data.totalPropinas));
        $('#vg_destacado').text(escHtml(data.garzonDestacado));

        if (modoTodos) {
            renderRanking(data.ranking);
            renderBarChart(data.ranking);
        } else {
            $('#vg_tabla_ranking tbody').empty();
            if (barChart) { barChart.destroy(); barChart = null; }
        }

        renderTendencia(data.tendencia);
        renderPropinas(data.propinas);
        renderDetalle(data.detalle);
    }

    // ---------------------------------------------------------------
    // RANKING
    // ---------------------------------------------------------------
    function renderRanking(ranking) {
        var $tbody = $('#vg_tabla_ranking tbody').empty();
        if (!ranking || ranking.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center vg-empty">Sin datos.</td></tr>');
            return;
        }
        ranking.forEach(function (r, i) {
            var numClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : ''));
            var pct = r.porcentaje;
            $tbody.append(
                '<tr>' +
                    '<td><span class="vg-rank-num ' + numClass + '">' + (i + 1) + '</span></td>' +
                    '<td>' + escHtml(r.nombre) + '</td>' +
                    '<td class="text-right">' + Number(r.comandas).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right">' + Number(r.mesas).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right">' + Number(r.comensales).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right">' + clp(r.total) + '</td>' +
                    '<td class="text-right">' +
                        '<div class="vg-bar-mini">' +
                            '<div class="vg-bar-mini-track">' +
                                '<div class="vg-bar-mini-fill" style="width:' + Math.min(100, pct) + '%;"></div>' +
                            '</div>' +
                            '<span style="min-width:36px;font-size:12px;">' + pct + '%</span>' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-right">' + clp(r.propina) + '</td>' +
                '</tr>'
            );
        });
    }

    // ---------------------------------------------------------------
    // GRÁFICO BARRAS HORIZONTALES — comparativa
    // ---------------------------------------------------------------
    function renderBarChart(ranking) {
        var ctx = document.getElementById('vgBarChart');
        if (!ctx) return;
        if (barChart) { barChart.destroy(); barChart = null; }
        if (!ranking || ranking.length === 0) return;

        var alturaMin = Math.max(220, ranking.length * 40);
        ctx.parentElement.style.height = alturaMin + 'px';

        var labels  = ranking.map(function (r) { return r.nombre; });
        var totales = ranking.map(function (r) { return r.total; });
        var colors  = ranking.map(function (_, i) {
            var palette = ['rgba(22,160,133,0.8)','rgba(41,128,185,0.8)','rgba(142,68,173,0.8)',
                           'rgba(230,126,34,0.8)','rgba(39,174,96,0.8)','rgba(231,76,60,0.8)'];
            return palette[i % palette.length];
        });

        barChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total vendido',
                    data: totales,
                    backgroundColor: colors,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (item) { return ' ' + clp(item.xLabel); }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) { return '$' + Number(v).toLocaleString('es-CL'); }
                        },
                        gridLines: { color: 'rgba(0,0,0,0.05)' },
                    }],
                    yAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // GRÁFICO TENDENCIA DIARIA
    // ---------------------------------------------------------------
    function renderTendencia(tendencia) {
        var ctx = document.getElementById('vgTendenciaChart');
        if (!ctx) return;
        if (tendenciaChart) { tendenciaChart.destroy(); tendenciaChart = null; }
        if (!tendencia || tendencia.length === 0) return;

        var labels   = tendencia.map(function (t) { return t.fecha; });
        var totales  = tendencia.map(function (t) { return t.total; });
        var comandas = tendencia.map(function (t) { return t.comandas; });

        tendenciaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Venta ($)',
                        data: totales,
                        backgroundColor: 'rgba(22,160,133,0.55)',
                        borderColor: 'rgba(22,160,133,0.9)',
                        borderWidth: 1,
                        yAxisID: 'y-ventas',
                        order: 2,
                    },
                    {
                        label: 'Comandas',
                        data: comandas,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(230,126,34,0.9)',
                        backgroundColor: 'rgba(230,126,34,0.9)',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        yAxisID: 'y-comandas',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: true, position: 'top' },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (item, chartData) {
                            var ds = chartData.datasets[item.datasetIndex];
                            if (item.datasetIndex === 0) return ' ' + ds.label + ': ' + clp(item.yLabel);
                            return ' ' + ds.label + ': ' + item.yLabel;
                        }
                    }
                },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [
                        {
                            id: 'y-ventas', position: 'left',
                            ticks: { callback: function (v) { return '$' + Number(v).toLocaleString('es-CL'); }, beginAtZero: true },
                            gridLines: { color: 'rgba(0,0,0,0.05)' },
                        },
                        {
                            id: 'y-comandas', position: 'right',
                            ticks: { beginAtZero: true },
                            gridLines: { display: false },
                        }
                    ]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // PROPINAS POR GARZÓN
    // ---------------------------------------------------------------
    function renderPropinas(propinas) {
        var $tbody      = $('#vg_tabla_propinas tbody').empty();
        var $sinPropinas = $('#vg_sin_propinas');

        if (!propinas || propinas.length === 0) {
            $tbody.closest('table').hide();
            $sinPropinas.show();
            return;
        }

        $tbody.closest('table').show();
        $sinPropinas.hide();

        var totalPropinas = propinas.reduce(function (acc, r) { return acc + r.propina; }, 0) || 1;

        propinas.forEach(function (r, i) {
            var pct = Math.round((r.propina / totalPropinas) * 100 * 10) / 10;
            var numClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : ''));
            $tbody.append(
                '<tr>' +
                    '<td><span class="vg-rank-num ' + numClass + '">' + (i + 1) + '</span></td>' +
                    '<td>' + escHtml(r.nombre) + '</td>' +
                    '<td class="text-right">' + Number(r.comandas).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right"><strong>' + clp(r.propina) + '</strong></td>' +
                    '<td class="text-right">' + clp(r.promedio) + '</td>' +
                '</tr>'
            );
        });
    }

    // ---------------------------------------------------------------
    // DATATABLES — detalle de comandas
    // ---------------------------------------------------------------
    function renderDetalle(detalle) {
        if ($.fn.DataTable.isDataTable('#vg_tabla_detalle')) {
            $('#vg_tabla_detalle').DataTable().destroy();
            $('#vg_tabla_detalle tbody').empty();
        }

        detalleTable = $('#vg_tabla_detalle').DataTable({
            data: detalle || [],
            columns: [
                { data: 'folio', width: '80px' },
                { data: 'fecha' },
                { data: 'garzon' },
                { data: 'mesa', width: '80px' },
                {
                    data: 'comensales',
                    className: 'text-right',
                    width: '90px',
                },
                {
                    data: 'subtotal',
                    className: 'text-right',
                    render: function (val, type) {
                        if (type === 'export') return val;
                        return '$' + Number(val).toLocaleString('es-CL', { minimumFractionDigits: 0 });
                    }
                },
                {
                    data: 'propina',
                    className: 'text-right',
                    render: function (val, type) {
                        if (type === 'export') return val;
                        if (!val || val === 0) return '<span style="color:#bbb;">—</span>';
                        return '<span style="color:#16a085;font-weight:600;">' +
                            '$' + Number(val).toLocaleString('es-CL', { minimumFractionDigits: 0 }) +
                            '</span>';
                    }
                },
                {
                    data: 'total',
                    className: 'text-right',
                    render: function (val, type) {
                        if (type === 'export') return val;
                        return '<strong>$' + Number(val).toLocaleString('es-CL', { minimumFractionDigits: 0 }) + '</strong>';
                    }
                },
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            order: [[1, 'desc']],
            searching: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            dom: 'lBfrtip',
            buttons: [],
        });
    }

    // Activar "Este mes" por defecto al cargar
    $('[data-rango="mes"]').trigger('click');
});
