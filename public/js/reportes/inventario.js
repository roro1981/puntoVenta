$(document).ready(function () {

    // ---------------------------------------------------------------
    // Estado global
    // ---------------------------------------------------------------
    var allData     = null;
    var invChart    = null;
    var invTable    = null;

    // ---------------------------------------------------------------
    // Paleta colores categorías
    // ---------------------------------------------------------------
    var PALETA = [
        'rgba(52,152,219,0.85)', 'rgba(46,204,113,0.85)', 'rgba(230,126,34,0.85)',
        'rgba(155,89,182,0.85)', 'rgba(231,76,60,0.85)',  'rgba(22,160,133,0.85)',
        'rgba(241,196,15,0.85)', 'rgba(52,73,94,0.85)',   'rgba(189,84,148,0.85)',
        'rgba(26,188,156,0.85)',
    ];

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    function clp(n) {
        return '$' + Number(n).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    function numFmt(n, dec) {
        dec = dec || 0;
        return Number(n).toLocaleString('es-CL', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }
    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function estadoHtml(e) {
        var map = {
            'Normal':     'inv-estado-normal',
            'Crítico':    'inv-estado-critico',
            'Agotado':    'inv-estado-agotado',
            'Sobrestock': 'inv-estado-sobrestock',
        };
        return '<span class="inv-estado ' + (map[e] || '') + '">' + esc(e) + '</span>';
    }

    function stockBarHtml(stock, stockMin, estado) {
        var max   = Math.max(stock, stockMin * 2, 1);
        var pct   = Math.min(100, (stock / max) * 100);
        var color = estado === 'Normal'  ? '#27ae60' :
                    estado === 'Crítico' ? '#f39c12' :
                    estado === 'Agotado' ? '#e74c3c' : '#3498db';
        return '<div class="inv-stock-bar">' +
            '<div class="inv-stock-track">' +
                '<div class="inv-stock-fill" style="width:' + pct + '%;background:' + color + ';"></div>' +
            '</div>' +
            '<span style="font-size:11px;min-width:38px;">' + numFmt(stock, 1) + '</span>' +
        '</div>';
    }

    // ---------------------------------------------------------------
    // CARGAR DATOS (auto al inicio)
    // ---------------------------------------------------------------
    function cargarDatos() {
        $('#inv_loader').show();
        $('#inv_resultado').hide();
        $('#btn_inv_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/inventario/data',
            method: 'GET',
            success: function (data) {
                $('#inv_loader').hide();
                if (!data.productos || data.productos.length === 0) {
                    toastr.info('No hay productos activos en el inventario.');
                    return;
                }
                allData = data;
                poblarFiltros(data.categorias);
                renderCards(data);
                renderTabla(data.productos);
                renderAlertas(data.productos);
                renderGrafico(data.distCategorias);
                renderHallazgos(data.hallazgos);
                $('#inv_resultado').fadeIn(200);
                $('#btn_inv_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#inv_loader').hide();
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message : 'Error al obtener datos.';
                toastr.error(msg);
            }
        });
    }

    // ---------------------------------------------------------------
    // Poblar select categorías
    // ---------------------------------------------------------------
    function poblarFiltros(categorias) {
        var $sel = $('#inv_filter_cat');
        $sel.find('option:not(:first)').remove();
        (categorias || []).forEach(function (cat) {
            $sel.append('<option value="' + esc(cat) + '">' + esc(cat) + '</option>');
        });
    }

    // ---------------------------------------------------------------
    // Filtrar y re-renderizar tabla
    // ---------------------------------------------------------------
    function aplicarFiltros() {
        if (!allData) return;
        var cat    = $('#inv_filter_cat').val();
        var estado = $('#inv_filter_estado').val();

        var filtrados = allData.productos.filter(function (p) {
            if (cat    && p.categoria !== cat)  return false;
            if (estado && p.estado    !== estado) return false;
            return true;
        });
        renderTabla(filtrados);
    }

    // ---------------------------------------------------------------
    // Cards KPI
    // ---------------------------------------------------------------
    function renderCards(data) {
        $('#inv_card_valor').text(clp(data.valorTotal));
        $('#inv_card_productos').text(numFmt(data.totalProductos));
        $('#inv_card_agotados').text(numFmt(data.agotados));
        $('#inv_card_criticos').text(numFmt(data.criticos));
    }

    // ---------------------------------------------------------------
    // Tabla principal
    // ---------------------------------------------------------------
    function renderTabla(productos) {
        if (invTable) { invTable.destroy(); invTable = null; }
        var $tbody = $('#inv_tabla tbody').empty();

        if (!productos || productos.length === 0) {
            $tbody.append('<tr><td colspan="9" class="text-center inv-empty-msg">Sin productos para los filtros seleccionados.</td></tr>');
            return;
        }

        productos.forEach(function (p) {
            var dias = p.dias_cobertura !== null
                ? numFmt(p.dias_cobertura) + ' días'
                : '<span style="color:#aaa;">Sin rotación</span>';

            $tbody.append(
                '<tr>' +
                    '<td>' + esc(p.codigo) + '</td>' +
                    '<td>' + esc(p.nombre) + '</td>' +
                    '<td>' + esc(p.categoria) + '</td>' +
                    '<td>' + stockBarHtml(p.stock, p.stock_minimo, p.estado) + '</td>' +
                    '<td class="text-right" style="font-size:11px;color:#888;">' + numFmt(p.stock_minimo, 1) + '</td>' +
                    '<td class="text-right">' + clp(p.valor_inventario) + '</td>' +
                    '<td class="text-right" style="font-size:11px;">' + numFmt(p.ventas_30d, 1) + '</td>' +
                    '<td class="text-right" style="font-size:11px;">' + dias + '</td>' +
                    '<td class="text-center">' + estadoHtml(p.estado) + '</td>' +
                '</tr>'
            );
        });

        invTable = $('#inv_tabla').DataTable({
            language: { url: '/vendor/datatables/Spanish.json', emptyTable: 'Sin productos.' },
            paging:   true,
            ordering: true,
            info:     true,
            pageLength: 25,
            dom: '<"inv-dt-top"lf>rt<"inv-dt-bottom"ip>',
            destroy: true,
            order: [[5, 'desc']], // ordenar por valor inventario desc
        });
    }

    // ---------------------------------------------------------------
    // Alertas (agotados + críticos)
    // ---------------------------------------------------------------
    function renderAlertas(productos) {
        var agotados = productos.filter(function (p) { return p.estado === 'Agotado'; });
        var criticos = productos.filter(function (p) { return p.estado === 'Crítico'; });

        var $pAgotados = $('#inv_alertas_agotados').empty();
        if (agotados.length === 0) {
            $pAgotados.html('<p class="inv-empty-msg">Sin productos agotados.</p>');
        } else {
            agotados.forEach(function (p) {
                $pAgotados.append(
                    '<div class="inv-alerta-item">' +
                        '<span class="inv-alerta-dot inv-dot-agotado"></span>' +
                        '<span class="inv-alerta-nombre">' + esc(p.nombre) + '</span>' +
                        '<span class="inv-alerta-stock">' + esc(p.categoria) + '</span>' +
                    '</div>'
                );
            });
        }

        var $pCriticos = $('#inv_alertas_criticos').empty();
        if (criticos.length === 0) {
            $pCriticos.html('<p class="inv-empty-msg">Sin productos en stock crítico.</p>');
        } else {
            criticos.forEach(function (p) {
                $pCriticos.append(
                    '<div class="inv-alerta-item">' +
                        '<span class="inv-alerta-dot inv-dot-critico"></span>' +
                        '<span class="inv-alerta-nombre">' + esc(p.nombre) + '</span>' +
                        '<span class="inv-alerta-stock">Stock: ' + numFmt(p.stock, 1) + ' / Mín: ' + numFmt(p.stock_minimo, 1) + '</span>' +
                    '</div>'
                );
            });
        }
    }

    // ---------------------------------------------------------------
    // Gráfico valor por categoría (barras horizontales)
    // ---------------------------------------------------------------
    function renderGrafico(distCat) {
        var ctx = document.getElementById('invCatChart');
        if (!ctx) return;
        if (invChart) { invChart.destroy(); invChart = null; }
        if (!distCat || distCat.length === 0) return;

        var top = distCat.slice(0, 10);
        var labels = top.map(function (d) { return d.categoria; });
        var valores = top.map(function (d) { return d.valor; });
        var colors  = top.map(function (d, i) { return PALETA[i % PALETA.length]; });

        invChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Valor inventario',
                    data: valores,
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
                            callback: function (v) { return clp(v); },
                            fontSize: 10,
                        }
                    }],
                    yAxes: [{ ticks: { fontSize: 11 } }]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // Hallazgos
    // ---------------------------------------------------------------
    function renderHallazgos(hallazgos) {
        var $ul = $('#inv_hallazgos_list').empty();
        if (!hallazgos || hallazgos.length === 0) {
            $ul.append('<li style="color:#aaa;font-size:12px;">Sin hallazgos para este inventario.</li>');
            return;
        }
        var iconMap = { ok: 'fa-check', warning: 'fa-exclamation', info: 'fa-info', bad: 'fa-times' };
        hallazgos.forEach(function (h) {
            var icon = iconMap[h.tipo] || 'fa-info';
            $ul.append(
                '<li>' +
                    '<span class="inv-h-icon inv-h-' + esc(h.tipo) + '"><i class="fa ' + icon + '"></i></span>' +
                    '<span>' + esc(h.texto) + '</span>' +
                '</li>'
            );
        });
    }

    // ---------------------------------------------------------------
    // Eventos
    // ---------------------------------------------------------------
    $('#inv_filter_cat, #inv_filter_estado').on('change', function () {
        aplicarFiltros();
    });

    $('#btn_inv_exportar').on('click', function () {
        window.location.href = '/reportes/inventario/exportar';
    });

    // ---------------------------------------------------------------
    // Inicio
    // ---------------------------------------------------------------
    cargarDatos();
});
