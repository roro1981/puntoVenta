/* ============================================================
   PRODUCTOS MÁS VENDIDOS — prods_mas_vendidos.js
   ============================================================ */
(function () {
    'use strict';

    /* ── Paleta ── */
    var PALETTE = [
        '#3d9adb', '#2bbfa0', '#e9a826', '#9b59b6', '#e74c3c',
        '#1abc9c', '#f39c12', '#8e44ad', '#3498db', '#c0392b'
    ];

    /* ── Estado global ── */
    var pmvBarChart   = null;
    var pmvDonutChart = null;
    var pmvDataTable  = null;
    var ultimoDatos      = null;

    /* ── Datepicker (jQuery UI) ── */
    function initDatepickers() {
        var opts = {
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            maxDate: 0,
            monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                         'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun',
                              'Jul','Ago','Sep','Oct','Nov','Dic'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
            firstDay: 1
        };
        $('#pmv_desde').datepicker(opts);
        $('#pmv_hasta').datepicker(opts);
    }

    /* ── Atajos de fecha ── */
    function fmtDate(d) {
        var dd = String(d.getDate()).padStart(2, '0');
        var mm = String(d.getMonth() + 1).padStart(2, '0');
        return dd + '/' + mm + '/' + d.getFullYear();
    }

    function setAtajo(rango) {
        var hoy   = new Date();
        var desde, hasta;

        if (rango === 'hoy') {
            desde = hasta = fmtDate(hoy);
        } else if (rango === 'ayer') {
            var ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
            desde = hasta = fmtDate(ayer);
        } else if (rango === 'semana') {
            var lunes = new Date(hoy);
            lunes.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
            desde = fmtDate(lunes); hasta = fmtDate(hoy);
        } else if (rango === 'mes') {
            desde = fmtDate(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            hasta = fmtDate(hoy);
        } else if (rango === 'mes_anterior') {
            desde = fmtDate(new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1));
            hasta = fmtDate(new Date(hoy.getFullYear(), hoy.getMonth(), 0));
        }

        if (desde && hasta) {
            $('#pmv_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', desde));
            $('#pmv_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', hasta));
        }
    }

    /* ── Parsear fecha dd/mm/yyyy → yyyy-mm-dd ── */
    function parseFecha(str) {
        if (!str) return '';
        var p = str.split('/');
        return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : str;
    }

    /* ── Formatear número ── */
    function fmt(n, dec) {
        if (n === null || n === undefined || isNaN(n)) return '—';
        dec = dec !== undefined ? dec : 0;
        return parseFloat(n).toLocaleString('es-CL', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    /* ── Actualizar select categorías ── */
    function actualizarSelectCategorias(cats) {
        var sel = $('#pmv_select_categoria');
        var prev = sel.val();
        sel.empty().append('<option value="">Todas las categorías</option>');
        $.each(cats || [], function (_, c) {
            sel.append($('<option>').val(c.id).text(c.nombre));
        });
        if (prev) sel.val(prev);
    }

    /* ── Generar reporte ── */
    function generarReporte() {
        var desde = parseFecha($('#pmv_desde').val());
        var hasta = parseFecha($('#pmv_hasta').val());
        if (!desde || !hasta) {
            alert('Selecciona un rango de fechas.');
            return;
        }

        var categoriaId = $('#pmv_select_categoria').val() || '';

        $('#pmv_loader').show();
        $('#pmv_resultado').hide();
        $('#btn_pmv_exportar').prop('disabled', true);

        $.get('/reportes/prods_mas_vendidos/data', {
            desde: desde,
            hasta: hasta,
            categoria_id: categoriaId
        })
        .done(function (d) {
            if (!d.ranking || d.ranking.length === 0) {
                toastr.info('No hay registros en el período seleccionado.');
                return;
            }
            ultimoDatos = { desde: desde, hasta: hasta, categoriaId: categoriaId };
            actualizarSelectCategorias(d.categorias);
            renderResultado(d);
            $('#btn_pmv_exportar').prop('disabled', false);
        })
        .fail(function (xhr) {
            var msg = 'Error al consultar datos.';
            try { msg = xhr.responseJSON.message || msg; } catch (e) {}
            alert(msg);
        })
        .always(function () {
            $('#pmv_loader').hide();
        });
    }

    /* ── RENDER PRINCIPAL ── */
    function renderResultado(d) {
        renderCards(d);
        renderTabla(d.ranking || []);
        renderBarChart(d.ranking || []);
        renderDonutChart(d.ranking || []);
        renderStockCritico(d.stockCritico || []);
        renderMovimientos(d.nuevosEnTop || [], d.salieronDeTop || []);
        renderHallazgos(d.hallazgos || []);
        $('#pmv_resultado').show();
    }

    /* ── Cards ── */
    function renderCards(d) {
        $('#pmv_card_unidades').text(fmt(d.totalUnidades));
        $('#pmv_card_productos').text(fmt(d.totalProductos));

        var liderText = d.liderNombre !== '—'
            ? d.liderNombre + ' (' + fmt(d.liderUnidades) + ' uds.)'
            : '—';
        $('#pmv_card_lider').text(liderText);
        $('#pmv_card_top10').text(fmt(d.participacionTop10, 1) + '%');

        // Variación
        var $varCard = $('#pmv_card_variacion_wrap');
        $varCard.removeClass('pmv-var-pos pmv-var-neg');
        if (d.variacionTotal === null || d.variacionTotal === undefined) {
            $('#pmv_card_variacion').text('—');
        } else {
            var signo = d.variacionTotal >= 0 ? '+' : '';
            $('#pmv_card_variacion').text(signo + fmt(d.variacionTotal, 1) + '%');
            $varCard.addClass(d.variacionTotal >= 0 ? 'pmv-var-pos' : 'pmv-var-neg');
        }
    }

    /* ── DataTable ranking ── */
    function renderTabla(ranking) {
        if (pmvDataTable) {
            pmvDataTable.destroy();
            $('#pmv_tabla_ranking tbody').empty();
        }

        var tBodyData = ranking.map(function (r) {
            // Variación vs período anterior
            var varHtml;
            if (r.esNuevo) {
                // Sin ventas en el período anterior → no hay % de variación calculable
                varHtml = '<span class="pmv-badge pmv-badge-nuevo" title="No registró ventas en el período anterior">Sin datos prev.</span>';
            } else if (r.variacion === null || r.variacion === undefined) {
                varHtml = '<span class="pmv-rank-eq">—</span>';
            } else {
                var signo = r.variacion >= 0 ? '+' : '';
                var cls   = r.variacion > 0 ? 'pmv-rank-up' : (r.variacion < 0 ? 'pmv-rank-down' : 'pmv-rank-eq');
                varHtml = '<span class="' + cls + '">' + signo + fmt(r.variacion, 1) + '%</span>';
            }

            // Cambio de posición en el ranking
            var rankHtml;
            if (r.esNuevo) {
                // Entró al ranking en este período (no estaba antes)
                rankHtml = '<span class="pmv-badge pmv-badge-nuevo" title="No figuraba en el ranking del período anterior">★ Nuevo</span>';
            } else if (r.cambioRanking === null || r.cambioRanking === undefined) {
                rankHtml = '<span class="pmv-rank-eq">—</span>';
            } else if (r.cambioRanking > 0) {
                rankHtml = '<span class="pmv-rank-up">▲ ' + r.cambioRanking + '</span>';
            } else if (r.cambioRanking < 0) {
                rankHtml = '<span class="pmv-rank-down">▼ ' + Math.abs(r.cambioRanking) + '</span>';
            } else {
                rankHtml = '<span class="pmv-rank-eq">→ Igual</span>';
            }

            // Estado semáforo
            var estadoHtml;
            var estadoMap = {
                'ok':       ['pmv-badge-ok',      'OK'],
                'riesgo':   ['pmv-badge-riesgo',   'Riesgo'],
                'critico':  ['pmv-badge-critico',  'Crítico'],
                'no_aplica':['pmv-badge-na',        '—']
            };
            var em = estadoMap[r.estado] || estadoMap['no_aplica'];
            estadoHtml = '<span class="pmv-badge ' + em[0] + '">' + em[1] + '</span>';

            // Días cobertura
            var diasHtml = r.tieneStock && r.diasCobertura !== null
                ? fmt(r.diasCobertura, 1)
                : '—';

            // Stock
            var stockHtml = r.tieneStock && r.stock !== null
                ? fmt(r.stock)
                : '—';

            return [
                r.rankingActual,
                r.nombre,
                r.codigo || '—',
                r.categoria || '—',
                fmt(r.unidades),
                fmt(r.participacion, 1) + '%',
                varHtml,
                rankHtml,
                stockHtml,
                diasHtml,
                estadoHtml
            ];
        });

        pmvDataTable = $('#pmv_tabla_ranking').DataTable({
            data: tBodyData,
            columns: [
                { className: 'text-center' },
                {},
                {},
                {},
                { className: 'text-right' },
                { className: 'text-right' },
                { className: 'text-right' },
                { className: 'text-center' },
                { className: 'text-right' },
                { className: 'text-right' },
                { className: 'text-center' }
            ],
            pageLength: 20,
            order: [[0, 'asc']],
            language: {
                url: '/js/datatables/spanish.json'
            },
            responsive: true,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
        });
    }

    /* ── Bar chart (Top 10 horizontal) ── */
    function renderBarChart(ranking) {
        if (pmvBarChart) { pmvBarChart.destroy(); pmvBarChart = null; }

        var top10 = ranking.slice(0, 10);
        var labels = top10.map(function (r) {
            return r.nombre.length > 30 ? r.nombre.substring(0, 28) + '…' : r.nombre;
        });
        var values = top10.map(function (r) { return r.unidades; });
        var colors = top10.map(function (_, i) { return PALETTE[i % PALETTE.length]; });

        var ctx = document.getElementById('pmvBarChart');
        if (!ctx) return;

        // Altura dinámica: al menos 44px por barra + espacio para ejes
        var alturaWrap = Math.max(300, top10.length * 44 + 60);
        document.getElementById('pmvBarWrap').style.height = alturaWrap + 'px';

        pmvBarChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Unidades',
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) { return fmt(v); }
                        },
                        gridLines: { color: '#f0f0f0' }
                    }],
                    yAxes: [{
                        ticks: { fontSize: 11 },
                        gridLines: { display: false }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function (t) { return ' ' + fmt(t.xLabel) + ' uds.'; }
                    }
                }
            }
        });
    }

    /* ── Donut chart (Top 10 vs Resto) ── */
    function renderDonutChart(ranking) {
        if (pmvDonutChart) { pmvDonutChart.destroy(); pmvDonutChart = null; }

        var top10        = ranking.slice(0, 10);
        var totalTop10   = top10.reduce(function (s, r) { return s + r.unidades; }, 0);
        var totalGlobal  = ranking.reduce(function (s, r) { return s + r.unidades; }, 0);
        var resto        = totalGlobal - totalTop10;

        var labels = top10.map(function (r) {
            return r.nombre.length > 22 ? r.nombre.substring(0, 20) + '…' : r.nombre;
        });
        var values = top10.map(function (r) { return r.unidades; });
        var colors = top10.map(function (_, i) { return PALETTE[i % PALETTE.length]; });

        if (resto > 0) {
            labels.push('Resto');
            values.push(resto);
            colors.push('#cccccc');
        }

        var ctx = document.getElementById('pmvDonutChart');
        if (!ctx) return;

        document.getElementById('pmvDonutWrap').style.height = '340px';

        pmvDonutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 58,
                legend: {
                    position: 'bottom',
                    labels: { fontSize: 10, boxWidth: 12 }
                },
                tooltips: {
                    callbacks: {
                        label: function (t, data) {
                            var total = data.datasets[0].data.reduce(function (s, v) { return s + v; }, 0);
                            var val   = data.datasets[0].data[t.index];
                            var pct   = total > 0 ? (val / total * 100).toFixed(1) : '0.0';
                            return ' ' + data.labels[t.index] + ': ' + fmt(val) + ' uds. (' + pct + '%)';
                        }
                    }
                }
            }
        });
    }

    /* ── Stock crítico ── */
    function renderStockCritico(lista) {
        var $body = $('#pmv_stock_critico_body');
        $body.empty();

        if (!lista.length) {
            $body.html('<p class="pmv-empty-msg">Sin alertas de stock en el Top 20.</p>');
            return;
        }

        $.each(lista, function (_, r) {
            var em = r.estado === 'critico'
                ? ['pmv-badge-critico', 'Crítico']
                : ['pmv-badge-riesgo', 'Riesgo'];

            var diasText = r.diasCobertura !== null ? fmt(r.diasCobertura, 1) + ' días' : '—';
            var stockText = r.stock !== null ? fmt(r.stock) + ' uds.' : '—';

            $body.append(
                '<div class="pmv-critico-item">' +
                    '<span class="pmv-critico-nombre">' +
                        '<span class="pmv-badge pmv-badge-na" style="margin-right:5px;">#' + r.rankingActual + '</span>' +
                        escHtml(r.nombre) +
                    '</span>' +
                    '<span class="pmv-critico-meta">' +
                        '<small>Stock: ' + stockText + '</small>' +
                        '<small>Cob.: ' + diasText + '</small>' +
                        '<span class="pmv-badge ' + em[0] + '">' + em[1] + '</span>' +
                    '</span>' +
                '</div>'
            );
        });
    }

    /* ── Movimientos Top 10 ── */
    function renderMovimientos(nuevos, salidos) {
        var $nList = $('#pmv_nuevos_list');
        var $sList = $('#pmv_salidos_list');

        $nList.html(nuevos.length
            ? nuevos.map(function (n) {
                return '<span class="pmv-mov-tag">' + escHtml(n) + '</span>';
              }).join('')
            : '<span class="pmv-empty-msg">Sin cambios.</span>'
        );

        $sList.html(salidos.length
            ? salidos.map(function (n) {
                return '<span class="pmv-mov-tag pmv-mov-tag-out">' + escHtml(n) + '</span>';
              }).join('')
            : '<span class="pmv-empty-msg">Sin cambios.</span>'
        );
    }

    /* ── Hallazgos ── */
    function renderHallazgos(hallazgos) {
        var $ul = $('#pmv_hallazgos_list');
        $ul.empty();

        if (!hallazgos.length) {
            $ul.append('<li class="pmv-hallazgo-item pmv-hallazgo-info"><span class="pmv-hallazgo-icon fa fa-info-circle"></span><span>Sin hallazgos para mostrar.</span></li>');
            return;
        }

        var iconMap = {
            'info':    'fa fa-info-circle',
            'ok':      'fa fa-check-circle',
            'warning': 'fa fa-exclamation-triangle',
            'critico': 'fa fa-times-circle'
        };
        var clsMap = {
            'info':    'pmv-hallazgo-info',
            'ok':      'pmv-hallazgo-ok',
            'warning': 'pmv-hallazgo-warning',
            'critico': 'pmv-hallazgo-critico'
        };

        $.each(hallazgos, function (_, h) {
            var cls  = clsMap[h.tipo]  || 'pmv-hallazgo-info';
            var icon = iconMap[h.tipo] || 'fa fa-info-circle';
            $ul.append(
                '<li class="pmv-hallazgo-item ' + cls + '">' +
                    '<span class="pmv-hallazgo-icon ' + icon + '"></span>' +
                    '<span>' + escHtml(h.texto) + '</span>' +
                '</li>'
            );
        });
    }

    /* ── Exportar ── */
    function exportarExcel() {
        if (!ultimoDatos) return;
        var url = '/reportes/prods_mas_vendidos/exportar'
            + '?desde='        + encodeURIComponent(ultimoDatos.desde)
            + '&hasta='        + encodeURIComponent(ultimoDatos.hasta)
            + '&categoria_id=' + encodeURIComponent(ultimoDatos.categoriaId || '');
        window.location.href = url;
    }

    /* ── Utilidad: escapar HTML ── */
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ── INIT ── */
    $(function () {
        initDatepickers();

        // Cargar mes actual, marcar botón activo y generar reporte
        setAtajo('mes');
        $('.pmv-atajo[data-rango="mes"]').addClass('active');
        generarReporte();

        // Eventos atajos: setear fechas + marcar activo + generar
        $('.pmv-atajo').on('click', function () {
            setAtajo($(this).data('rango'));
            $('.pmv-atajo').removeClass('active');
            $(this).addClass('active');
            generarReporte();
        });

        $('#btn_pmv_generar').on('click', generarReporte);
        $('#btn_pmv_exportar').on('click', exportarExcel);

        // Regenerar si cambia categoría (solo si ya hay datos)
        $('#pmv_select_categoria').on('change', function () {
            if (ultimoDatos) generarReporte();
        });
    });

}());
