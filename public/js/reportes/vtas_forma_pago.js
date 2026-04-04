$(document).ready(function () {

    // ---------------------------------------------------------------
    // Paleta de colores por forma de pago
    // ---------------------------------------------------------------
    var COLORES = {
        'EFECTIVO':         'rgba(39, 174, 96,  0.85)',
        'DEBITO':           'rgba(41, 128, 185, 0.85)',
        'TARJETA_DEBITO':   'rgba(41, 128, 185, 0.85)',
        'CREDITO':          'rgba(230, 126, 34, 0.85)',
        'TARJETA_CREDITO':  'rgba(230, 126, 34, 0.85)',
        'TRANSFERENCIA':    'rgba(142, 68, 173, 0.85)',
        'CHEQUE':           'rgba(22, 160, 133, 0.85)',
    };
    var COLORES_FALLBACK = [
        'rgba(52, 152, 219, 0.85)',
        'rgba(231, 76,  60, 0.85)',
        'rgba(241, 196, 15, 0.85)',
        'rgba(26,  188, 156, 0.85)',
        'rgba(155, 89,  182, 0.85)',
    ];

    function getColor(key, idx) {
        var k = (key || '').toUpperCase().replace(/\s+/g, '_');
        return COLORES[k] || COLORES_FALLBACK[idx % COLORES_FALLBACK.length];
    }

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
    $('#fp_desde').datepicker(dpOpts);
    $('#fp_hasta').datepicker(dpOpts);

    // ---------------------------------------------------------------
    // Accesos rápidos
    // ---------------------------------------------------------------
    $('.fp-atajo').on('click', function () {
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

        $('#fp_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
        $('#fp_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));

        $('.fp-atajo').removeClass('active');
        $(this).addClass('active');

        $('#btn_fp_generar').trigger('click');
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
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ---------------------------------------------------------------
    // Estado de gráficos y últimos parámetros
    // ---------------------------------------------------------------
    var donaChart      = null;
    var tendenciaChart = null;
    var ultimoDesde    = null;
    var ultimoHasta    = null;

    // ---------------------------------------------------------------
    // GENERAR
    // ---------------------------------------------------------------
    $('#btn_fp_generar').on('click', function () {
        var desdeRaw = $('#fp_desde').val();
        var hastaRaw = $('#fp_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        ultimoDesde = parseFecha(desdeRaw);
        ultimoHasta = parseFecha(hastaRaw);

        $('#fp_resultado').hide();
        $('#fp_spinner').show();
        $('#btn_fp_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/vtas_forma_pago/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta },
            success: function (data) {
                $('#fp_spinner').hide();
                if (!data.totalTickets || data.totalTickets === 0) {
                    $('#fp_resultado').hide();
                    toastr.info('No hay ventas registradas en el periodo seleccionado.');
                    return;
                }
                renderResultado(data);
                $('#fp_resultado').fadeIn(200);
                $('#btn_fp_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#fp_spinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    });

    // ---------------------------------------------------------------
    // EXPORTAR
    // ---------------------------------------------------------------
    $('#btn_fp_exportar').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        window.location.href = '/reportes/vtas_forma_pago/exportar?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    // ---------------------------------------------------------------
    // RENDER
    // ---------------------------------------------------------------
    function renderResultado(data) {
        // cards
        $('#fp_total_ventas').text(clp(data.totalVentas));
        $('#fp_total_tickets').text(Number(data.totalTickets).toLocaleString('es-CL'));
        $('#fp_forma_dominante').text(escHtml(data.formaDominante));
        $('#fp_ticket_promedio').text(clp(data.ticketPromedio));

        // tabla
        var $tbody = $('#fp_tabla tbody').empty();
        if (data.formasPago && data.formasPago.length > 0) {
            data.formasPago.forEach(function (fp, i) {
                var color = getColor(fp.label, i);
                var cant  = Number(fp.transacciones).toLocaleString('es-CL');
                $tbody.append(
                    '<tr>' +
                        '<td><span class="fp-badge" style="background:' + color + ';"></span>' + escHtml(fp.label) + '</td>' +
                        '<td class="text-right">' + cant + '</td>' +
                        '<td class="text-right">' + clp(fp.monto) + '</td>' +
                        '<td class="text-right">' +
                            '<div class="fp-bar-mini">' +
                                '<div class="fp-bar-mini-track">' +
                                    '<div class="fp-bar-mini-fill" style="width:' + Math.min(100, fp.porcentaje) + '%;background:' + color + ';"></div>' +
                                '</div>' +
                                '<span style="min-width:36px;font-size:12px;">' + fp.porcentaje + '%</span>' +
                            '</div>' +
                        '</td>' +
                        '<td class="text-right">' + clp(fp.promedio) + '</td>' +
                    '</tr>'
                );
            });
        } else {
            $tbody.append('<tr><td colspan="5" class="text-center fp-empty">Sin datos en el periodo.</td></tr>');
        }

        renderDona(data);
        renderTendencia(data);
    }

    function renderDona(data) {
        var ctx = document.getElementById('fpDonaChart');
        if (!ctx) return;
        if (donaChart) { donaChart.destroy(); donaChart = null; }

        var labels = data.formasPago.map(function (fp) { return fp.label; });
        var montos  = data.formasPago.map(function (fp) { return fp.monto; });
        var colors  = data.formasPago.map(function (fp, i) { return getColor(fp.label, i); });

        donaChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: montos,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { fontSize: 11, padding: 10 } },
                tooltips: {
                    callbacks: {
                        label: function (item, chartData) {
                            var label  = chartData.labels[item.index] || '';
                            var value  = chartData.datasets[0].data[item.index];
                            var total  = chartData.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            var pct    = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                            return ' ' + label + ': ' + clp(value) + ' (' + pct + '%)';
                        }
                    }
                },
                cutoutPercentage: 60,
            }
        });
    }

    function renderTendencia(data) {
        var ctx = document.getElementById('fpTendenciaChart');
        if (!ctx) return;
        if (tendenciaChart) { tendenciaChart.destroy(); tendenciaChart = null; }

        if (!data.tendencia || data.tendencia.length === 0) return;

        var labels   = data.tendencia.map(function (r) { return r.fecha; });
        var datasets = data.formasKeys.map(function (key, i) {
            var label = data.formasLabels[i] || key;
            var color = getColor(key, i);
            return {
                label: label,
                data: data.tendencia.map(function (r) { return r[key] || 0; }),
                backgroundColor: color,
                borderColor: color,
                borderWidth: 1,
                stack: 'stack',
            };
        });

        tendenciaChart = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: true, position: 'top', labels: { fontSize: 11 } },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (item, chartData) {
                            var ds = chartData.datasets[item.datasetIndex];
                            return ' ' + ds.label + ': ' + clp(item.yLabel);
                        }
                    }
                },
                scales: {
                    xAxes: [{ stacked: true, gridLines: { display: false } }],
                    yAxes: [{
                        stacked: true,
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) { return '$' + Number(v).toLocaleString('es-CL'); }
                        },
                        gridLines: { color: 'rgba(0,0,0,0.05)' },
                    }]
                }
            }
        });
    }

    // Activar "Este mes" por defecto al cargar
    $('[data-rango="mes"]').trigger('click');
});
