$(document).ready(function () {

    // ---------------------------------------------------------------
    // Datepicker (jquery-ui ya cargado en el layout)
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
    $('#vf_desde').datepicker(dpOpts);
    $('#vf_hasta').datepicker(dpOpts);

    // ---------------------------------------------------------------
    // Accesos rápidos de rango
    // ---------------------------------------------------------------
    $('.vf-atajo').on('click', function () {
        var rango = $(this).data('rango');
        var hoy   = new Date();

        function fmt(d) {
            var dd = String(d.getDate()).padStart(2, '0');
            var mm = String(d.getMonth() + 1).padStart(2, '0');
            var yy = d.getFullYear();
            return dd + '/' + mm + '/' + yy;
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
            var ini = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            desde = fmt(ini); hasta = fmt(hoy);
        } else if (rango === 'mes_anterior') {
            var iniMA = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
            var finMA = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
            desde = fmt(iniMA); hasta = fmt(finMA);
        }
        $('#vf_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
        $('#vf_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));

        // Activar botón activo
        $('.vf-atajo').removeClass('active');
        $(this).addClass('active');

        // Generar reporte de inmediato
        $('#btn_vf_generar').trigger('click');
    });

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    function clp(n) {
        return '$' + Number(n).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function parseFecha(str) {
        // dd/mm/yyyy → yyyy-mm-dd
        var p = str.split('/');
        return p[2] + '-' + p[1] + '-' + p[0];
    }

    // ---------------------------------------------------------------
    // Estado: gráfico y parámetros de última búsqueda
    // ---------------------------------------------------------------
    var tendenciaChart = null;
    var ultimoDesde = null, ultimoHasta = null;

    // ---------------------------------------------------------------
    // GENERAR
    // ---------------------------------------------------------------
    $('#btn_vf_generar').on('click', function () {
        var desdeRaw = $('#vf_desde').val();
        var hastaRaw = $('#vf_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        ultimoDesde = parseFecha(desdeRaw);
        ultimoHasta = parseFecha(hastaRaw);

        $('#vf_resultado').hide();
        $('#vf_spinner').show();
        $('#btn_vf_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/vtas_fecha/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta },
            success: function (data) {
                $('#vf_spinner').hide();
                if (!data.totalTickets || data.totalTickets === 0) {
                    $('#vf_resultado').hide();
                    $('#btn_vf_exportar').prop('disabled', true);
                    toastr.info('No hay ventas registradas en el periodo seleccionado.');
                    return;
                }
                renderResultado(data);
                $('#vf_resultado').fadeIn(200);
                $('#btn_vf_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#vf_spinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    });

    // ---------------------------------------------------------------
    // EXPORTAR
    // ---------------------------------------------------------------
    $('#btn_vf_exportar').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        window.location.href = '/reportes/vtas_fecha/exportar?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    // ---------------------------------------------------------------
    // RENDER
    // ---------------------------------------------------------------
    function renderResultado(data) {
        var esRestaurant = data.tipoNegocio === 'RESTAURANT';

        // label tickets/comandas
        $('#lbl_tickets').text(esRestaurant ? 'Comandas cerradas' : 'Tickets emitidos');

        // cards
        $('#vf_total_ventas').text(clp(data.totalVentas));
        $('#vf_total_tickets').text(Number(data.totalTickets).toLocaleString('es-CL'));
        $('#vf_ticket_promedio').text(clp(data.ticketPromedio));
        $('#vf_promedio_diario').text(clp(data.promedioDiario));
        $('#vf_dias_periodo').text('Periodo de ' + data.diasPeriodo + ' día(s)');

        // gráfico tendencia
        renderTendencia(data.tendencia, esRestaurant);

        // tooltips
        $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
    }

    function renderTendencia(tendencia, esRestaurant) {
        var labels   = tendencia.map(function (t) { return t.fecha; });
        var totales  = tendencia.map(function (t) { return t.total; });
        var tickets  = tendencia.map(function (t) { return t.tickets; });

        var ctx = document.getElementById('vfTendenciaChart');
        if (!ctx) return;

        if (tendenciaChart) {
            tendenciaChart.destroy();
            tendenciaChart = null;
        }

        tendenciaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Venta ($)',
                        data: totales,
                        backgroundColor: 'rgba(41, 128, 185, 0.55)',
                        borderColor: 'rgba(41, 128, 185, 0.9)',
                        borderWidth: 1,
                        yAxisID: 'y-ventas',
                        order: 2,
                    },
                    {
                        label: esRestaurant ? 'Comandas' : 'Tickets',
                        data: tickets,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(231, 76, 60, 0.9)',
                        backgroundColor: 'rgba(231, 76, 60, 0.9)',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                        yAxisID: 'y-tickets',
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
                            if (item.datasetIndex === 0) {
                                return ' ' + ds.label + ': $' + Number(item.yLabel).toLocaleString('es-CL');
                            }
                            return ' ' + ds.label + ': ' + item.yLabel;
                        }
                    }
                },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [
                        {
                            id: 'y-ventas',
                            position: 'left',
                            ticks: {
                                callback: function (v) {
                                    return '$' + Number(v).toLocaleString('es-CL');
                                },
                                beginAtZero: true,
                            },
                            gridLines: { color: 'rgba(0,0,0,0.05)' },
                        },
                        {
                            id: 'y-tickets',
                            position: 'right',
                            ticks: { beginAtZero: true },
                            gridLines: { display: false },
                        }
                    ]
                }
            }
        });
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Activar atajo "Este mes" por defecto al cargar
    $('[data-rango="mes"]').trigger('click');
});
