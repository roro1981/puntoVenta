$(document).ready(function () {

    // ---------------------------------------------------------------
    // Paleta de colores para categorías
    // ---------------------------------------------------------------
    var PALETA = [
        'rgba(52,  152, 219, 0.85)',
        'rgba(46,  204, 113, 0.85)',
        'rgba(230, 126,  34, 0.85)',
        'rgba(155,  89, 182, 0.85)',
        'rgba(231,  76,  60, 0.85)',
        'rgba(22,  160, 133, 0.85)',
        'rgba(241, 196,  15, 0.85)',
        'rgba(52,   73,  94, 0.85)',
        'rgba(189,  84, 148, 0.85)',
        'rgba(26,  188, 156, 0.85)',
    ];

    function getColor(idx) {
        return PALETA[idx % PALETA.length];
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
    $('#vc_desde').datepicker(dpOpts);
    $('#vc_hasta').datepicker(dpOpts);

    // ---------------------------------------------------------------
    // Accesos rápidos de fecha
    // ---------------------------------------------------------------
    $('.vc-atajo').on('click', function () {
        var rango = $(this).data('rango');
        var hoy   = new Date();

        function fmt(d) {
            return String(d.getDate()).padStart(2, '0') + '/' +
                   String(d.getMonth() + 1).padStart(2, '0') + '/' +
                   d.getFullYear();
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

        $('#vc_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
        $('#vc_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));
        $('.vc-atajo').removeClass('active');
        $(this).addClass('active');
        $('#btn_vc_generar').trigger('click');
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
    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ---------------------------------------------------------------
    // Estado de gráficos
    // ---------------------------------------------------------------
    var donaChart     = null;
    var tendChart     = null;
    var ultimoDesde   = null;
    var ultimoHasta   = null;

    // ---------------------------------------------------------------
    // EXPORTAR
    // ---------------------------------------------------------------
    $('#btn_vc_exportar').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) return;
        window.location.href = '/reportes/cat_mas_vendidas/exportar?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    // ---------------------------------------------------------------
    // GENERAR
    // ---------------------------------------------------------------
    $('#btn_vc_generar').on('click', function () {
        var desdeRaw = $('#vc_desde').val();
        var hastaRaw = $('#vc_hasta').val();

        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        ultimoDesde = parseFecha(desdeRaw);
        ultimoHasta = parseFecha(hastaRaw);

        $('#vc_resultado').hide();
        $('#vc_loader').show();
        $('#btn_vc_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/cat_mas_vendidas/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta },
            success: function (data) {
                $('#vc_loader').hide();
                if (!data.totalMonto || data.totalMonto === 0) {
                    toastr.info('No hay ventas registradas en el periodo seleccionado.');
                    return;
                }
                renderResultado(data);
                $('#vc_resultado').fadeIn(200);
                $('#btn_vc_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#vc_loader').hide();
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    });

    // ---------------------------------------------------------------
    // RENDER PRINCIPAL
    // ---------------------------------------------------------------
    function renderResultado(data) {
        // -- KPIs ---------------------------------------------------
        $('#vc_card_total').text(clp(data.totalMonto));
        $('#vc_card_categorias').text(data.totalCategorias);

        var lider = data.lider;
        if (lider) {
            $('#vc_card_lider').text(esc(lider.categoria));
            $('#vc_card_lider_pct').text('(' + lider.participacion + '% del total)');
        } else {
            $('#vc_card_lider').text('—');
            $('#vc_card_lider_pct').text('');
        }

        // Variación total
        var $vcWrap = $('#vc_card_variacion_wrap');
        $vcWrap.removeClass('vc-var-up vc-var-down');
        if (data.variacionTotal !== null && data.variacionTotal !== undefined) {
            var v = data.variacionTotal;
            var prefijo = v >= 0 ? '+' : '';
            $('#vc_card_variacion').text(prefijo + v + '%');
            $vcWrap.addClass(v >= 0 ? 'vc-var-up' : 'vc-var-down');
        } else {
            $('#vc_card_variacion').text('—');
        }

        // -- Tabla --------------------------------------------------
        renderTabla(data.ranking, data.totalMonto);

        // -- Gráficos -----------------------------------------------
        renderDona(data.ranking);
        renderTendencia(data.tendencia);

        // -- Hallazgos ----------------------------------------------
        renderHallazgos(data.hallazgos);
    }

    // ---------------------------------------------------------------
    // Tabla ranking
    // ---------------------------------------------------------------
    function renderTabla(ranking, totalMonto) {
        var $tbody = $('#vc_tabla tbody').empty();
        if (!ranking || ranking.length === 0) {
            $tbody.append('<tr><td colspan="6" class="text-center vc-empty-msg">Sin datos en el periodo.</td></tr>');
            return;
        }
        ranking.forEach(function (r, i) {
            var color = getColor(i);

            // Participación con barra mini
            var barHtml =
                '<div class="vc-bar-mini">' +
                    '<div class="vc-bar-mini-track">' +
                        '<div class="vc-bar-mini-fill" style="width:' + Math.min(100, r.participacion) + '%;background:' + color + ';"></div>' +
                    '</div>' +
                    '<span style="min-width:36px;font-size:11px;">' + r.participacion + '%</span>' +
                '</div>';

            // Variación
            var varHtml;
            if (r.esNueva) {
                varHtml = '<span class="vc-badge-new">Nueva</span>';
            } else if (r.variacion === null || r.variacion === undefined) {
                varHtml = '<span class="vc-var vc-var-neu">—</span>';
            } else {
                var cls = r.variacion > 0 ? 'vc-var-up' : (r.variacion < 0 ? 'vc-var-down' : 'vc-var-neu');
                var pref = r.variacion > 0 ? '+' : '';
                varHtml = '<span class="vc-var ' + cls + '">' + pref + r.variacion + '%</span>';
            }

            // Cambio de rank
            var rankChangeHtml;
            if (r.esNueva) {
                rankChangeHtml = '<span class="vc-rank-new"><i class="fa fa-star"></i> Nueva</span>';
            } else if (r.cambioRank === null || r.cambioRank === undefined) {
                rankChangeHtml = '<span style="color:#aaa;">—</span>';
            } else if (r.cambioRank > 0) {
                rankChangeHtml = '<span class="vc-rank-up"><i class="fa fa-arrow-up"></i> ' + r.cambioRank + '</span>';
            } else if (r.cambioRank < 0) {
                rankChangeHtml = '<span class="vc-rank-down"><i class="fa fa-arrow-down"></i> ' + Math.abs(r.cambioRank) + '</span>';
            } else {
                rankChangeHtml = '<span style="color:#aaa;"><i class="fa fa-minus"></i></span>';
            }

            var rankClass = r.rank === 1 ? 'vc-rank-1' : (r.rank === 2 ? 'vc-rank-2' : (r.rank === 3 ? 'vc-rank-3' : ''));

            $tbody.append(
                '<tr>' +
                    '<td class="text-center"><span class="vc-rank-num ' + rankClass + '">' + r.rank + '</span></td>' +
                    '<td>' + esc(r.categoria) + '</td>' +
                    '<td class="text-right">' + Number(r.unidades).toLocaleString('es-CL', {minimumFractionDigits: 0, maximumFractionDigits: 1}) + '</td>' +
                    '<td class="text-right">' + clp(r.monto) + '</td>' +
                    '<td>' + barHtml + '</td>' +
                    '<td>' + varHtml + '</td>' +
                    '<td class="text-center">' + rankChangeHtml + '</td>' +
                '</tr>'
            );
        });
    }

    // ---------------------------------------------------------------
    // Gráfico dona (distribución)
    // ---------------------------------------------------------------
    function renderDona(ranking) {
        var ctx = document.getElementById('vcDonaChart');
        if (!ctx) return;
        if (donaChart) { donaChart.destroy(); donaChart = null; }
        if (!ranking || ranking.length === 0) return;

        // Mostrar top 9 + "Otras"
        var datos = ranking.slice();
        var topN  = datos.slice(0, 9);
        var otras = datos.slice(9);
        if (otras.length > 0) {
            var sumaOtras = otras.reduce(function (s, r) { return s + r.monto; }, 0);
            topN.push({ categoria: 'Otras (' + otras.length + ')', monto: sumaOtras });
        }

        var labels = topN.map(function (r) { return r.categoria; });
        var montos = topN.map(function (r) { return r.monto; });
        var colors = topN.map(function (r, i) { return getColor(i); });

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
                            var label = chartData.labels[item.index] || '';
                            var val   = chartData.datasets[0].data[item.index];
                            var total = chartData.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            var pct   = total > 0 ? ((val / total) * 100).toFixed(1) : '0.0';
                            return ' ' + label + ': ' + clp(val) + ' (' + pct + '%)';
                        }
                    }
                },
                cutoutPercentage: 60,
            }
        });
    }

    // ---------------------------------------------------------------
    // Gráfico tendencia (línea - ranking top 5 por día)
    // ---------------------------------------------------------------
    function renderTendencia(tendencia) {
        var ctx = document.getElementById('vcTendenciaChart');
        if (!ctx) return;
        if (tendChart) { tendChart.destroy(); tendChart = null; }
        if (!tendencia || !tendencia.fechas || tendencia.fechas.length === 0) return;

        var datasets = (tendencia.series || []).map(function (s, i) {
            var color = getColor(i);
            return {
                label: s.nombre,
                data: s.data,
                borderColor: color,
                backgroundColor: color.replace('0.85)', '0.12)'),
                pointRadius: tendencia.fechas.length <= 14 ? 4 : 2,
                borderWidth: 2,
                fill: false,
            };
        });

        tendChart = new Chart(ctx, {
            type: 'line',
            data: { labels: tendencia.fechas, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'top', labels: { fontSize: 11 } },
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
                    xAxes: [{ gridLines: { display: false }, ticks: { fontSize: 10 } }],
                    yAxes: [{
                        beginAtZero: true,
                        ticks: {
                            callback: function (v) { return '$' + Number(v).toLocaleString('es-CL'); },
                            fontSize: 10,
                        },
                        gridLines: { color: 'rgba(0,0,0,0.05)' },
                    }]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // Hallazgos gerenciales
    // ---------------------------------------------------------------
    function renderHallazgos(hallazgos) {
        var $ul = $('#vc_hallazgos_list').empty();
        if (!hallazgos || hallazgos.length === 0) {
            $ul.append('<li style="color:#aaa;font-size:12px;">Sin hallazgos para este período.</li>');
            return;
        }
        hallazgos.forEach(function (h) {
            var iconMap = { ok: 'fa-check', warning: 'fa-exclamation', info: 'fa-info', bad: 'fa-times' };
            var icon = iconMap[h.tipo] || 'fa-info';
            $ul.append(
                '<li>' +
                    '<span class="vc-hallazgo-icon vc-h-' + esc(h.tipo) + '"><i class="fa ' + icon + '"></i></span>' +
                    '<span>' + esc(h.texto) + '</span>' +
                '</li>'
            );
        });
    }

    // ---------------------------------------------------------------
    // Cargar "Este mes" al iniciar
    // ---------------------------------------------------------------
    $('[data-rango="mes"]').trigger('click');
});
