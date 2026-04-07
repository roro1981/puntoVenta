/**
 * Reporte: Productos más rentables
 * /js/reportes/prods_rentables.js
 */
(function ($) {
    'use strict';

    // ── estado global ──────────────────────────────────────────────────
    var barChart    = null;
    var donutChart  = null;
    var dataTable   = null;
    var lastData    = null;

    // ── helpers numéricos ──────────────────────────────────────────────
    function fmtCLP(n) {
        var v = parseFloat(n) || 0;
        return '$' + v.toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    function fmtPct(n, dec) {
        dec = (dec === undefined) ? 1 : dec;
        return (parseFloat(n) || 0).toFixed(dec) + '%';
    }
    function fmtNum(n) {
        return Number(parseInt(n) || 0).toLocaleString('es-CL');
    }

    // ── helpers fecha ──────────────────────────────────────────────────
    function pad2(n) { return n < 10 ? '0' + n : '' + n; }

    function parseFecha(str) {
        // Convierte dd/mm/yyyy → yyyy-mm-dd
        var p = str.split('/');
        return p[2] + '-' + p[1] + '-' + p[0];
    }

    function fechaHoy() {
        var d = new Date();
        return pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear();
    }

    function addDays(d, n) {
        var r = new Date(d.getTime());
        r.setDate(r.getDate() + n);
        return r;
    }

    function fmtDate(d) {
        return pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear();
    }

    function setAtajo(rango) {
        var hoy  = new Date();
        var desde, hasta;

        if (rango === 'hoy') {
            desde = hasta = hoy;
        } else if (rango === 'ayer') {
            desde = hasta = addDays(hoy, -1);
        } else if (rango === 'semana') {
            var dow = hoy.getDay(); // 0=dom
            dow = (dow === 0) ? 6 : dow - 1;
            desde = addDays(hoy, -dow);
            hasta = hoy;
        } else if (rango === 'mes') {
            desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            hasta = hoy;
        } else if (rango === 'mes_anterior') {
            var y = hoy.getFullYear(), m = hoy.getMonth();
            if (m === 0) { y--; m = 11; } else { m--; }
            desde = new Date(y, m, 1);
            var lastDay = new Date(y, m + 1, 0).getDate();
            hasta = new Date(y, m, lastDay);
        } else {
            return;
        }

        $('#pr_desde').datepicker('setDate', desde);
        $('#pr_hasta').datepicker('setDate', hasta);

        $('.pr-atajo').removeClass('active');
        $('.pr-atajo[data-rango="' + rango + '"]').addClass('active');
    }

    // ── semáforo ───────────────────────────────────────────────────────
    function semaforoBadge(semaforo, margen) {
        var label = fmtPct(margen);
        var cls   = 'pr-badge-' + (semaforo || 'bajo');
        return '<span class="pr-badge ' + cls + '">' + label + '</span>';
    }

    // ── variación ──────────────────────────────────────────────────────
    function varCell(v) {
        if (v === null || v === undefined || v === '') {
            return '<span class="pr-var-neu">Sin datos prev.</span>';
        }
        var n = parseFloat(v);
        if (n > 0)  return '<span class="pr-var-pos">▲ +' + n.toFixed(1) + '%</span>';
        if (n < 0)  return '<span class="pr-var-neg">▼ ' + n.toFixed(1) + '%</span>';
        return '<span class="pr-var-neu">= 0.0%</span>';
    }

    // ── cambio ranking ─────────────────────────────────────────────────
    function rankChange(item) {
        if (item.esNuevo) return '<span class="pr-rank-new">★ Nuevo</span>';
        var c = parseInt(item.cambioRanking) || 0;
        if (c > 0) return '<span class="pr-rank-up">▲ +' + c + '</span>';
        if (c < 0) return '<span class="pr-rank-down">▼ ' + c + '</span>';
        return '<span class="pr-rank-eq">= Sin cambio</span>';
    }

    // ── renderizar tabla DataTable ─────────────────────────────────────
    function renderTabla(ranking) {
        if (dataTable) {
            dataTable.destroy();
            dataTable = null;
        }
        $('#pr_tabla_ranking tbody').empty();

        if (!ranking || !ranking.length) {
            $('#pr_tabla_ranking tbody').append(
                '<tr><td colspan="11" class="text-center text-muted">Sin datos para el período seleccionado.</td></tr>'
            );
            return;
        }

        $.each(ranking, function (i, row) {
            var tr = '<tr>' +
                '<td class="text-center"><strong>' + row.rankingActual + '</strong></td>' +
                '<td>' + (row.nombre || '—') + '</td>' +
                '<td>' + (row.codigo || '—') + '</td>' +
                '<td>' + (row.categoria || '—') + '</td>' +
                '<td class="text-right">' + fmtNum(row.unidades) + '</td>' +
                '<td class="text-right">' + fmtCLP(row.ingresos) + '</td>' +
                '<td class="text-right">' + fmtCLP(row.costo) + '</td>' +
                '<td class="text-right"><strong>' + fmtCLP(row.utilidad) + '</strong></td>' +
                '<td class="text-right">' + semaforoBadge(row.semaforo, row.margen) + '</td>' +
                '<td class="text-right">' + varCell(row.varUtilidad) + '</td>' +
                '<td class="text-center">' + rankChange(row) + '</td>' +
                '</tr>';
            $('#pr_tabla_ranking tbody').append(tr);
        });

        dataTable = $('#pr_tabla_ranking').DataTable({
            language: {
                url: '/vendor/datatables/Spanish.json',
                emptyTable: 'Sin datos.'
            },
            paging:   true,
            ordering: false,
            info:     true,
            pageLength: 25,
            dom: '<"pr-dt-top"lf>rt<"pr-dt-bottom"ip>',
            destroy: true
        });
    }

    // ── gráfico de barras horizontal ───────────────────────────────────
    function renderBarChart(ranking) {
        if (barChart) { barChart.destroy(); barChart = null; }

        var top10 = (ranking || []).slice(0, 10);
        if (!top10.length) {
            $('#prBarWrap').html('<p class="pr-empty-msg">Sin datos.</p>');
            return;
        }

        var labels   = top10.map(function (r) { return r.nombre; });
        var utilidad = top10.map(function (r) { return parseFloat(r.utilidad) || 0; });
        var colors   = top10.map(function (r) {
            if (r.semaforo === 'excelente') return 'rgba(56,161,105,.85)';
            if (r.semaforo === 'bueno')     return 'rgba(52,144,220,.85)';
            if (r.semaforo === 'bajo')      return 'rgba(224,123,26,.85)';
            return 'rgba(227,52,47,.85)';
        });

        var h = Math.max(300, top10.length * 44 + 60);
        $('#prBarWrap').css('height', h + 'px');
        // Reinsertar canvas limpio
        $('#prBarWrap').html('<canvas id="prBarChart"></canvas>');

        var ctx = document.getElementById('prBarChart').getContext('2d');
        barChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Utilidad ($)',
                    data: utilidad,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (tip, data) {
                            var v = data.datasets[tip.datasetIndex].data[tip.index];
                            return ' ' + fmtCLP(v);
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) { return fmtCLP(v); }
                        }
                    }],
                    yAxes: [{ ticks: { fontSize: 11 } }]
                }
            }
        });
    }

    // ── gráfico donut por categoría ────────────────────────────────────
    function renderDonutChart(distCategorias) {
        if (donutChart) { donutChart.destroy(); donutChart = null; }

        if (!distCategorias || !distCategorias.length) {
            $('#prDonutWrap').html('<p class="pr-empty-msg">Sin datos.</p>');
            return;
        }

        var palette = [
            '#3490dc','#38a169','#805ad5','#d69e2e','#e07b1a',
            '#e3342f','#00bcd4','#9c27b0','#4caf50','#ff5722'
        ];

        var labels = distCategorias.map(function (d) { return d.categoria; });
        var vals   = distCategorias.map(function (d) { return parseFloat(d.utilidad) || 0; });
        var bgs    = labels.map(function (l, i) { return palette[i % palette.length]; });

        $('#prDonutWrap').css('height', '340px');
        $('#prDonutWrap').html('<canvas id="prDonutChart"></canvas>');

        var ctx = document.getElementById('prDonutChart').getContext('2d');
        donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: vals,
                    backgroundColor: bgs,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { fontSize: 11, boxWidth: 12 } },
                tooltips: {
                    callbacks: {
                        label: function (tip, data) {
                            var lbl = data.labels[tip.index];
                            var v   = data.datasets[0].data[tip.index];
                            var total = data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            var pct = total > 0 ? ((v / total) * 100).toFixed(1) : '0.0';
                            return ' ' + lbl + ': ' + fmtCLP(v) + ' (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    // ── estrellas (top 5 por margen%) ─────────────────────────────────
    function renderEstrellas(estrellas) {
        var $body = $('#pr_estrellas_body').empty();
        if (!estrellas || !estrellas.length) {
            $body.html('<p class="pr-empty-msg">Sin datos suficientes.</p>');
            return;
        }
        $.each(estrellas, function (i, e) {
            $body.append(
                '<div class="pr-estrella-item">' +
                    '<div class="pr-estrella-rank">' + (i + 1) + '</div>' +
                    '<div class="pr-estrella-nombre">' + (e.nombre || '—') + '</div>' +
                    '<div>' +
                        '<div class="pr-estrella-margen">' + fmtPct(e.margen) + '</div>' +
                        '<div class="pr-estrella-util">' + fmtCLP(e.utilidad) + '</div>' +
                    '</div>' +
                '</div>'
            );
        });
    }

    // ── alertas críticas ───────────────────────────────────────────────
    function renderAlertas(alertas) {
        var $body = $('#pr_alertas_body').empty();
        if (!alertas || !alertas.length) {
            $body.html('<p class="pr-empty-msg">Sin alertas de margen crítico.</p>');
            return;
        }
        $.each(alertas, function (i, a) {
            $body.append(
                '<div class="pr-alerta-item">' +
                    '<i class="fa fa-exclamation-triangle pr-alerta-icon"></i>' +
                    '<div class="pr-alerta-nombre">' + (a.nombre || '—') + '</div>' +
                    '<div>' +
                        '<div class="pr-alerta-margen">' + fmtPct(a.margen) + '</div>' +
                        '<div class="pr-alerta-util">' + fmtCLP(a.utilidad) + '</div>' +
                    '</div>' +
                '</div>'
            );
        });
    }

    // ── hallazgos gerenciales ──────────────────────────────────────────
    var HALLAZGO_ICONS = {
        ok:      '<i class="fa fa-check-circle pr-h-icon pr-h-ok"></i>',
        info:    '<i class="fa fa-info-circle pr-h-icon pr-h-info"></i>',
        warning: '<i class="fa fa-exclamation-circle pr-h-icon pr-h-warning"></i>',
        critico: '<i class="fa fa-times-circle pr-h-icon pr-h-critico"></i>'
    };

    function renderHallazgos(hallazgos) {
        var $ul = $('#pr_hallazgos_list').empty();
        if (!hallazgos || !hallazgos.length) {
            $ul.append('<li><i class="fa fa-info-circle pr-h-icon pr-h-info"></i> No se generaron hallazgos automáticos.</li>');
            return;
        }
        $.each(hallazgos, function (i, h) {
            var icon = HALLAZGO_ICONS[h.tipo] || HALLAZGO_ICONS.info;
            $ul.append('<li>' + icon + '<span>' + h.texto + '</span></li>');
        });
    }

    // ── KPI cards ──────────────────────────────────────────────────────
    function renderCards(d) {
        $('#pr_card_ingresos').text(fmtCLP(d.totalIngresos));
        $('#pr_card_costo').text(fmtCLP(d.totalCosto));
        $('#pr_card_utilidad').text(fmtCLP(d.totalUtilidad));

        var margen = parseFloat(d.margenGlobal) || 0;
        $('#pr_card_margen').text(fmtPct(margen));

        var liderNombre = d.liderNombre || '—';
        var liderUtil   = d.liderUtilidad ? fmtCLP(d.liderUtilidad) : '';
        var liderMgn    = d.liderMargen   ? fmtPct(d.liderMargen)   : '';
        var liderTxt    = liderNombre;
        if (liderUtil) liderTxt += '<br><small>' + liderUtil + ' · ' + liderMgn + '</small>';
        $('#pr_card_lider').html(liderTxt);
    }

    // ── cargar categorías ──────────────────────────────────────────────
    function cargarCategorias(categorias) {
        var $sel = $('#pr_select_categoria');
        $sel.find('option:not(:first)').remove();
        if (categorias && categorias.length) {
            $.each(categorias, function (i, cat) {
                $sel.append('<option value="' + cat.id + '">' + cat.nombre + '</option>');
            });
        }
    }

    // ── generar reporte ────────────────────────────────────────────────
    function generarReporte() {
        var desde = $('#pr_desde').val().trim();
        var hasta = $('#pr_hasta').val().trim();

        if (!desde || !hasta) {
            alert('Seleccione un rango de fechas.');
            return;
        }

        $('#pr_resultado').hide();
        $('#pr_loader').show();
        $('#btn_pr_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/prods_rentables/data',
            method: 'GET',
            data: {
                desde:      parseFecha(desde),
                hasta:      parseFecha(hasta),
                categoria:  $('#pr_select_categoria').val() || ''
            },
            success: function (d) {
                $('#pr_loader').hide();

                if (!d.ranking || d.ranking.length === 0) {
                    toastr.info('No hay registros en el período seleccionado.');
                    return;
                }

                lastData = d;

                renderCards(d);
                if (d.categorias && d.categorias.length) {
                    cargarCategorias(d.categorias);
                }
                renderTabla(d.ranking);
                renderBarChart(d.ranking);
                renderDonutChart(d.distCategorias);
                renderEstrellas(d.estrellas);
                renderAlertas(d.alertasCritico);
                renderHallazgos(d.hallazgos);

                $('#pr_resultado').fadeIn(200);
                $('#btn_pr_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#pr_loader').hide();
                var msg = 'Error al generar el reporte.';
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.message) msg = r.message;
                } catch (e) {}
                alert(msg);
            }
        });
    }

    // ── init ───────────────────────────────────────────────────────────
    $(function () {

        // Datepicker (jQuery UI)
        $('#pr_desde, #pr_hasta').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            yearRange: '-5:+0',
            maxDate: 0
        });

        // Confirmar que atajos limpian selección previa
        $('.pr-atajo').on('click', function () {
            setAtajo($(this).data('rango'));
            generarReporte();
        });

        // Generar
        $('#btn_pr_generar').on('click', function () {
            generarReporte();
        });

        // Exportar
        $('#btn_pr_exportar').on('click', function () {
            var desde = $('#pr_desde').val().trim();
            var hasta = $('#pr_hasta').val().trim();
            if (!desde || !hasta) { alert('Seleccione un rango de fechas.'); return; }
            var cat = $('#pr_select_categoria').val() || '';
            window.location.href = '/reportes/prods_rentables/exportar?desde=' +
                encodeURIComponent(parseFecha(desde)) + '&hasta=' + encodeURIComponent(parseFecha(hasta)) +
                '&categoria=' + encodeURIComponent(cat);
        });

        // Carga inicial: "Este mes" activo, auto-generar
        setAtajo('mes');
        generarReporte();
    });

}(jQuery));
