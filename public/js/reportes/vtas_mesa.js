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
    $('#vm_desde').datepicker(dpOpts);
    $('#vm_hasta').datepicker(dpOpts);

    // ---------------------------------------------------------------
    // Accesos rápidos
    // ---------------------------------------------------------------
    $('.vm-atajo').on('click', function () {
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

        $('#vm_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
        $('#vm_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));

        $('.vm-atajo').removeClass('active');
        $(this).addClass('active');

        $('#btn_vm_generar').trigger('click');
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

    // Paleta de colores consistente para las mesas
    var PALETTE = [
        'rgba(41,128,185,0.8)',  'rgba(39,174,96,0.8)',  'rgba(142,68,173,0.8)',
        'rgba(230,126,34,0.8)', 'rgba(22,160,133,0.8)',  'rgba(231,76,60,0.8)',
        'rgba(241,196,15,0.8)', 'rgba(52,73,94,0.8)',    'rgba(26,188,156,0.8)',
    ];
    function colorFor(i) { return PALETTE[i % PALETTE.length]; }

    // ---------------------------------------------------------------
    // Estado
    // ---------------------------------------------------------------
    var donutChart     = null;
    var barChart       = null;
    var comensalesChart = null;
    var tendenciaChart = null;
    var detalleTable   = null;
    var ultimoDesde    = null;
    var ultimoHasta    = null;

    // ---------------------------------------------------------------
    // GENERAR
    // ---------------------------------------------------------------
    $('#btn_vm_generar').on('click', function () {
        var desdeRaw = $('#vm_desde').val();
        var hastaRaw = $('#vm_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        ultimoDesde = parseFecha(desdeRaw);
        ultimoHasta = parseFecha(hastaRaw);

        $('#vm_resultado').hide();
        $('#vm_spinner').show();
        $('#btn_vm_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/vtas_mesa/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta },
            success: function (data) {
                $('#vm_spinner').hide();

                if (!data.totalComandas || data.totalComandas === 0) {
                    $('#vm_resultado').hide();
                    toastr.info('No hay comandas cerradas en el periodo seleccionado.');
                    return;
                }

                renderResultado(data);
                $('#vm_resultado').fadeIn(200);
                $('#btn_vm_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#vm_spinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    });

    // ---------------------------------------------------------------
    // EXPORTAR
    // ---------------------------------------------------------------
    $('#btn_vm_exportar').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        window.location.href = '/reportes/vtas_mesa/exportar?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    // ---------------------------------------------------------------
    // RENDER PRINCIPAL
    // ---------------------------------------------------------------
    function renderResultado(data) {
        // Cards
        $('#vm_total_ventas').text(clp(data.totalVentas));
        $('#vm_total_comandas').text(Number(data.totalComandas).toLocaleString('es-CL'));
        $('#vm_total_comensales').text(Number(data.totalComensales).toLocaleString('es-CL'));
        $('#vm_ticket_promedio').text(clp(data.ticketPromedio));
        $('#vm_mesa_destacada').text(escHtml(data.mesaDestacada));

        renderRanking(data.ranking);
        renderDonut(data.donut);
        renderBarChart(data.ranking);
        renderComensalesChart(data.ranking);
        renderTendencia(data.tendencia);
        renderDetalle(data.detalle);
    }

    // ---------------------------------------------------------------
    // RANKING
    // ---------------------------------------------------------------
    function renderRanking(ranking) {
        var $tbody = $('#vm_tabla_ranking tbody').empty();
        if (!ranking || ranking.length === 0) {
            $tbody.append('<tr><td colspan="8" class="text-center vm-empty">Sin datos.</td></tr>');
            return;
        }
        ranking.forEach(function (r, i) {
            var numClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : ''));
            var pct = r.porcentaje;
            $tbody.append(
                '<tr>' +
                    '<td><span class="vm-rank-num ' + numClass + '">' + (i + 1) + '</span></td>' +
                    '<td><strong>' + escHtml(r.nombre) + '</strong></td>' +
                    '<td class="text-right">' + r.capacidad + '</td>' +
                    '<td class="text-right">' + Number(r.comandas).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right">' + Number(r.comensales).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right">' + clp(r.total) + '</td>' +
                    '<td class="text-right">' +
                        '<div class="vm-bar-mini">' +
                            '<div class="vm-bar-mini-track">' +
                                '<div class="vm-bar-mini-fill" style="width:' + Math.min(100, pct) + '%;"></div>' +
                            '</div>' +
                            '<span style="min-width:36px;font-size:12px;">' + pct + '%</span>' +
                        '</div>' +
                    '</td>' +
                    '<td class="text-right">' + clp(r.promedio) + '</td>' +
                '</tr>'
            );
        });
    }

    // ---------------------------------------------------------------
    // DONUT — distribución por mesa
    // ---------------------------------------------------------------
    function renderDonut(donut) {
        var ctx = document.getElementById('vmDonutChart');
        if (!ctx) return;
        if (donutChart) { donutChart.destroy(); donutChart = null; }
        if (!donut || donut.length === 0) return;

        var labels = donut.map(function (d) { return d.label; });
        var values = donut.map(function (d) { return d.valor; });
        var colors = donut.map(function (_, i) { return colorFor(i); });
        var total  = values.reduce(function (a, b) { return a + b; }, 0) || 1;

        donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 62,
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 12, fontSize: 11, padding: 8 }
                },
                tooltips: {
                    callbacks: {
                        label: function (item, chartData) {
                            var val = chartData.datasets[0].data[item.index];
                            var pct = Math.round((val / total) * 100 * 10) / 10;
                            return ' ' + chartData.labels[item.index] + ': ' + clp(val) + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // BARRAS HORIZONTALES — comparativa de ventas
    // ---------------------------------------------------------------
    function renderBarChart(ranking) {
        var ctx = document.getElementById('vmBarChart');
        if (!ctx) return;
        if (barChart) { barChart.destroy(); barChart = null; }
        if (!ranking || ranking.length === 0) return;

        var alturaMin = Math.max(240, ranking.length * 38);
        ctx.parentElement.style.height = alturaMin + 'px';

        var labels  = ranking.map(function (r) { return r.nombre; });
        var totales = ranking.map(function (r) { return r.total; });
        var colors  = ranking.map(function (_, i) { return colorFor(i); });

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
    // BARRAS HORIZONTALES — comensales por mesa
    // ---------------------------------------------------------------
    function renderComensalesChart(ranking) {
        var ctx = document.getElementById('vmComensalesChart');
        if (!ctx) return;
        if (comensalesChart) { comensalesChart.destroy(); comensalesChart = null; }
        if (!ranking || ranking.length === 0) return;

        var alturaMin = Math.max(240, ranking.length * 38);
        ctx.parentElement.style.height = alturaMin + 'px';

        var labels     = ranking.map(function (r) { return r.nombre; });
        var comensales = ranking.map(function (r) { return r.comensales; });
        var colors     = ranking.map(function (_, i) {
            return colorFor(i).replace('0.8)', '0.6)');
        });

        comensalesChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Comensales',
                    data: comensales,
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
                        label: function (item) { return ' ' + Number(item.xLabel).toLocaleString('es-CL') + ' comensales'; }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: { beginAtZero: true },
                        gridLines: { color: 'rgba(0,0,0,0.05)' },
                    }],
                    yAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // TENDENCIA DIARIA — ventas + comandas + comensales
    // ---------------------------------------------------------------
    function renderTendencia(tendencia) {
        var ctx = document.getElementById('vmTendenciaChart');
        if (!ctx) return;
        if (tendenciaChart) { tendenciaChart.destroy(); tendenciaChart = null; }
        if (!tendencia || tendencia.length === 0) return;

        var labels     = tendencia.map(function (t) { return t.fecha; });
        var totales    = tendencia.map(function (t) { return t.total; });
        var comandas   = tendencia.map(function (t) { return t.comandas; });
        var comensales = tendencia.map(function (t) { return t.comensales; });

        tendenciaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Venta ($)',
                        data: totales,
                        backgroundColor: 'rgba(41,128,185,0.55)',
                        borderColor: 'rgba(41,128,185,0.9)',
                        borderWidth: 1,
                        yAxisID: 'y-ventas',
                        order: 3,
                    },
                    {
                        label: 'Comandas',
                        data: comandas,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(231,76,60,0.9)',
                        backgroundColor: 'rgba(231,76,60,0.9)',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        yAxisID: 'y-cte',
                        order: 1,
                    },
                    {
                        label: 'Comensales',
                        data: comensales,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(142,68,173,0.9)',
                        backgroundColor: 'rgba(142,68,173,0.9)',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        borderDash: [5, 3],
                        yAxisID: 'y-cte',
                        order: 2,
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
                            return ' ' + ds.label + ': ' + Number(item.yLabel).toLocaleString('es-CL');
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
                            id: 'y-cte', position: 'right',
                            ticks: { beginAtZero: true },
                            gridLines: { display: false },
                        }
                    ]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // DATATABLES — detalle de comandas
    // ---------------------------------------------------------------
    function renderDetalle(detalle) {
        if ($.fn.DataTable.isDataTable('#vm_tabla_detalle')) {
            $('#vm_tabla_detalle').DataTable().destroy();
            $('#vm_tabla_detalle tbody').empty();
        }

        detalleTable = $('#vm_tabla_detalle').DataTable({
            data: detalle || [],
            columns: [
                { data: 'folio', width: '80px' },
                { data: 'fecha' },
                { data: 'mesa', width: '90px' },
                { data: 'comensales', className: 'text-right', width: '90px' },
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
                        return '<span style="color:#16a085;font-weight:600;">$' +
                            Number(val).toLocaleString('es-CL', { minimumFractionDigits: 0 }) + '</span>';
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
