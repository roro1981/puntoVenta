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
    $('#vv_desde').datepicker(dpOpts);
    $('#vv_hasta').datepicker(dpOpts);

    // ---------------------------------------------------------------
    // Accesos rápidos
    // ---------------------------------------------------------------
    $('.vv-atajo').on('click', function () {
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

        $('#vv_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
        $('#vv_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));

        $('.vv-atajo').removeClass('active');
        $(this).addClass('active');

        // Generar de inmediato y resetear el select a "Todos"
        $('#vv_select_vendedor').val('');
        $('#btn_vv_generar').trigger('click');
    });

    // ---------------------------------------------------------------
    // Al cambiar el select de vendedor → regenerar
    // ---------------------------------------------------------------
    $('#vv_select_vendedor').on('change', function () {
        if (ultimoDesde && ultimoHasta) {
            $('#btn_vv_generar').trigger('click');
        }
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
    // Estado
    // ---------------------------------------------------------------
    var barChart       = null;
    var tendenciaChart = null;
    var detalleTable   = null;
    var ultimoDesde    = null;
    var ultimoHasta    = null;
    var vendedoresCargados = false;

    // ---------------------------------------------------------------
    // GENERAR
    // ---------------------------------------------------------------
    $('#btn_vv_generar').on('click', function () {
        var desdeRaw = $('#vv_desde').val();
        var hastaRaw = $('#vv_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        var nuevoDesde = parseFecha(desdeRaw);
        var nuevoHasta = parseFecha(hastaRaw);
        var fechasCambiaron = (nuevoDesde !== ultimoDesde || nuevoHasta !== ultimoHasta);
        var vendedorId = $('#vv_select_vendedor').val() || '';

        // Si las fechas cambiaron, limpiar el select y forzar "Todos"
        if (fechasCambiaron) {
            vendedoresCargados = false;
            $('#vv_select_vendedor').val('');
            vendedorId = '';
        }

        ultimoDesde = nuevoDesde;
        ultimoHasta = nuevoHasta;

        $('#vv_resultado').hide();
        $('#vv_spinner').show();
        $('#btn_vv_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/vtas_vendedor/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta, vendedor_id: vendedorId },
            success: function (data) {
                $('#vv_spinner').hide();

                if (!data.totalTickets || data.totalTickets === 0) {
                    $('#vv_resultado').hide();
                    toastr.info('No hay ventas registradas en el periodo seleccionado.');
                    return;
                }

                // Actualizar select de vendedores solo cuando cambian las fechas
                if (!vendedoresCargados || fechasCambiaron) {
                    actualizarSelectVendedores(data.vendedores, vendedorId);
                    vendedoresCargados = true;
                }

                renderResultado(data, vendedorId);
                $('#vv_resultado').fadeIn(200);
                $('#btn_vv_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#vv_spinner').hide();
                var msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    });

    // ---------------------------------------------------------------
    // EXPORTAR
    // ---------------------------------------------------------------
    $('#btn_vv_exportar').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        var vendedorId = $('#vv_select_vendedor').val() || '';
        var url = '/reportes/vtas_vendedor/exportar?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
        if (vendedorId) url += '&vendedor_id=' + vendedorId;
        window.location.href = url;
    });

    // ---------------------------------------------------------------
    // Actualizar select de vendedores
    // ---------------------------------------------------------------
    function actualizarSelectVendedores(vendedores, selectedId) {
        var $sel = $('#vv_select_vendedor').off('change');
        var prev = selectedId || $sel.val() || '';
        $sel.empty().append('<option value="">— Todos —</option>');
        (vendedores || []).forEach(function (v) {
            var opt = $('<option>').val(v.id).text(v.nombre);
            if (String(v.id) === String(prev)) opt.prop('selected', true);
            $sel.append(opt);
        });
        $sel.on('change', function () {
            if (ultimoDesde && ultimoHasta) $('#btn_vv_generar').trigger('click');
        });
    }

    // ---------------------------------------------------------------
    // RENDER PRINCIPAL
    // ---------------------------------------------------------------
    function renderResultado(data, vendedorId) {
        var modoTodos = !vendedorId;

        // Cards
        $('#vv_total_ventas').text(clp(data.totalVentas));
        $('#vv_total_tickets').text(Number(data.totalTickets).toLocaleString('es-CL'));
        $('#vv_ticket_promedio').text(clp(data.ticketPromedio));
        $('#vv_destacado').text(escHtml(data.vendedorDestacado));

        // Card destacado: mostrar u ocultar según modo
        $('#vv_card_destacado').toggle(modoTodos);

        // Sección ranking
        if (modoTodos) {
            $('#vv_seccion_ranking').show();
            renderRanking(data.ranking);
            renderBarChart(data.ranking);
        } else {
            $('#vv_seccion_ranking').hide();
            if (barChart) { barChart.destroy(); barChart = null; }
        }

        // Título tendencia
        if (modoTodos) {
            $('#vv_titulo_tendencia').text('Evolución diaria — Todos los vendedores');
        } else {
            $('#vv_titulo_tendencia').text('Evolución diaria — ' + escHtml(data.vendedorDestacado));
        }

        renderTendencia(data.tendencia);
        renderDetalle(data.detalle);
    }

    // ---------------------------------------------------------------
    // RANKING
    // ---------------------------------------------------------------
    function renderRanking(ranking) {
        var $tbody = $('#vv_tabla_ranking tbody').empty();
        if (!ranking || ranking.length === 0) {
            $tbody.append('<tr><td colspan="6" class="text-center vv-empty">Sin datos.</td></tr>');
            return;
        }
        ranking.forEach(function (r, i) {
            var numClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : ''));
            var pct = r.porcentaje;
            $tbody.append(
                '<tr>' +
                    '<td><span class="vv-rank-num ' + numClass + '">' + (i + 1) + '</span></td>' +
                    '<td>' + escHtml(r.nombre) + '</td>' +
                    '<td class="text-right">' + Number(r.transacciones).toLocaleString('es-CL') + '</td>' +
                    '<td class="text-right">' + clp(r.total) + '</td>' +
                    '<td class="text-right">' +
                        '<div class="vv-bar-mini">' +
                            '<div class="vv-bar-mini-track">' +
                                '<div class="vv-bar-mini-fill" style="width:' + Math.min(100, pct) + '%;"></div>' +
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
    // GRÁFICO BARRAS HORIZONTALES — comparativa
    // ---------------------------------------------------------------
    function renderBarChart(ranking) {
        var ctx = document.getElementById('vvBarChart');
        if (!ctx) return;
        if (barChart) { barChart.destroy(); barChart = null; }
        if (!ranking || ranking.length === 0) return;

        // Ajustar altura según cantidad de vendedores
        var alturaMin = Math.max(220, ranking.length * 40);
        ctx.parentElement.style.height = alturaMin + 'px';

        var labels  = ranking.map(function (r) { return r.nombre; });
        var totales = ranking.map(function (r) { return r.total; });
        var colors  = ranking.map(function (_, i) {
            var palette = ['rgba(41,128,185,0.8)','rgba(39,174,96,0.8)','rgba(142,68,173,0.8)',
                           'rgba(230,126,34,0.8)','rgba(22,160,133,0.8)','rgba(231,76,60,0.8)'];
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
        var ctx = document.getElementById('vvTendenciaChart');
        if (!ctx) return;
        if (tendenciaChart) { tendenciaChart.destroy(); tendenciaChart = null; }
        if (!tendencia || tendencia.length === 0) return;

        var labels  = tendencia.map(function (t) { return t.fecha; });
        var totales = tendencia.map(function (t) { return t.total; });
        var tickets = tendencia.map(function (t) { return t.tickets; });

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
                        order: 2,
                    },
                    {
                        label: 'Transacciones',
                        data: tickets,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(231,76,60,0.9)',
                        backgroundColor: 'rgba(231,76,60,0.9)',
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
                            id: 'y-tickets', position: 'right',
                            ticks: { beginAtZero: true },
                            gridLines: { display: false },
                        }
                    ]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // DATATABLES — detalle de ventas
    // ---------------------------------------------------------------
    function renderDetalle(detalle) {
        if ($.fn.DataTable.isDataTable('#vv_tabla_detalle')) {
            $('#vv_tabla_detalle').DataTable().destroy();
            $('#vv_tabla_detalle tbody').empty();
        }

        detalleTable = $('#vv_tabla_detalle').DataTable({
            data: detalle || [],
            columns: [
                { data: 'folio', width: '70px' },
                { data: 'fecha' },
                { data: 'vendedor' },
                { data: 'forma_pago' },
                {
                    data: 'total',
                    className: 'text-right',
                    render: function (val, type) {
                        if (type === 'export') return val;
                        return '$' + Number(val).toLocaleString('es-CL', { minimumFractionDigits: 0 });
                    }
                },
                {
                    data: 'estado',
                    render: function (val, type) {
                        if (type === 'export') return val;
                        if (val === 'completada') {
                            return '<span class="vv-badge vv-badge-ok">Completada</span>';
                        } else if (val === 'parcialmente_anulada') {
                            return '<span class="vv-badge vv-badge-parcial">Parcial. Anulada</span>';
                        }
                        return '<span class="label label-danger">' + escHtml(val) + '</span>';
                    }
                }
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            order: [[1, 'desc']],
            searching: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            dom: 'lBfrtip',
            buttons: [],   // El export se maneja por botón propio
        });
    }

    // Activar "Este mes" por defecto al cargar
    $('[data-rango="mes"]').trigger('click');
});
