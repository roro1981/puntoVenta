$(document).ready(function () {

    // ---------------------------------------------------------------
    // Estado global
    // ---------------------------------------------------------------
    var tipoNegocio    = ($('#tipo_negocio').val() || '').toUpperCase();
    var tipoEntidad    = 'PRODUCTO';
    var entidadId      = 0;
    var entidadNombre  = '';
    var hpChart        = null;
    var hpTable        = null;
    var hpCostosTable  = null;
    var hpComprasTable = null;
    var tabActiva      = 'precios'; // 'precios' | 'compras'

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    function clp(n) {
        if (n === null || n === undefined) return '—';
        return '$' + Number(n).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function fechaFmt(s) {
        if (!s) return '—';
        var d = new Date(s);
        return d.toLocaleDateString('es-CL') + ' ' + d.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
    }
    function fechaSolo(s) {
        if (!s) return '—';
        var d = new Date(s);
        return d.toLocaleDateString('es-CL');
    }

    function varHtml(variacion, anterior) {
        if (anterior === null) {
            return '<span class="hp-var hp-var-inicial">Precio inicial</span>';
        }
        if (variacion === null || variacion === undefined) return '—';
        if (variacion > 0)  return '<span class="hp-var hp-var-subida">▲ +' + variacion + '%</span>';
        if (variacion < 0)  return '<span class="hp-var hp-var-bajada">▼ ' + variacion + '%</span>';
        return '<span class="hp-var hp-var-igual">Sin cambio</span>';
    }

    function campoHtml(campo) {
        if (campo === 'precio_venta') {
            return '<span class="hp-campo">Precio venta</span>';
        }
        return '<span class="hp-campo hp-campo-compra">Precio compra</span>';
    }

    // ---------------------------------------------------------------
    // Tipo entidad selector
    // ---------------------------------------------------------------
    $('#hp_tipo').on('change', function () {
        tipoEntidad = $(this).val();
        entidadId   = 0;
        entidadNombre = '';
        $('#hp_buscar').val('');
        $('#hp_sugerencias').empty().hide();
        $('#hp_nombre_entidad').hide();
        $('#hp_resultado').hide();
        $('#btn_hp_exportar').prop('disabled', true);
        resetTablas();
    });

    // ---------------------------------------------------------------
    // Búsqueda de entidad con autocompletado
    // ---------------------------------------------------------------
    var searchTimer = null;
    $('#hp_buscar').on('keyup', function () {
        var q = $(this).val().trim();
        clearTimeout(searchTimer);
        if (q.length < 2) { $('#hp_sugerencias').empty().hide(); return; }

        searchTimer = setTimeout(function () {
            $.ajax({
                url: '/reportes/hist_precio_prod/search',
                method: 'GET',
                data: { tipo: tipoEntidad, q: q },
                success: function (data) {
                    renderSugerencias(data);
                }
            });
        }, 280);
    });

    function renderSugerencias(items) {
        var $box = $('#hp_sugerencias').empty();
        if (!items || items.length === 0) { $box.hide(); return; }

        var html = '<table><thead><tr><th>Código</th><th>Nombre</th></tr></thead><tbody>';
        items.forEach(function (item) {
            html += '<tr class="hp-sug-row" data-id="' + item.id + '" data-nombre="' + esc(item.descripcion || item.nombre) + '">'
                  + '<td>' + esc(item.codigo) + '</td>'
                  + '<td>' + esc(item.descripcion || item.nombre) + '</td>'
                  + '</tr>';
        });
        html += '</tbody></table>';
        $box.html(html).show();

        $box.find('.hp-sug-row').on('click', function () {
            entidadId     = parseInt($(this).data('id'));
            entidadNombre = $(this).data('nombre');
            $('#hp_buscar').val(entidadNombre);
            $box.hide();
            cargarDatos();
        });
    }

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.hp-search-wrap').length) {
            $('#hp_sugerencias').hide();
        }
    });

    // ---------------------------------------------------------------
    // Tabs
    // ---------------------------------------------------------------
    $(document).on('click', '.hp-tab', function () {
        $('.hp-tab').removeClass('active');
        $(this).addClass('active');
        tabActiva = $(this).data('tab');
        $('#hp_pane_precios').toggle(tabActiva === 'precios');
        $('#hp_pane_compras').toggle(tabActiva === 'compras');
    });

    // ---------------------------------------------------------------
    // Cargar datos
    // ---------------------------------------------------------------
    function cargarDatos() {
        if (entidadId <= 0) return;
        $('#hp_loader').show();
        $('#hp_resultado').hide();
        resetTablas();

        var req1 = $.ajax({ url: '/reportes/hist_precio_prod/data',    method: 'GET', data: { tipo: tipoEntidad, entidad_id: entidadId } });
        var req2 = tipoEntidad === 'PRODUCTO'
            ? $.ajax({ url: '/reportes/hist_precio_prod/compras', method: 'GET', data: { producto_id: entidadId } })
            : $.Deferred().resolve({ compras: [] }).promise();

        $.when(req1, req2).done(function (r1, r2) {
            $('#hp_loader').hide();
            var dataPrecios  = Array.isArray(r1) ? r1[0] : r1;
            var dataCompras  = Array.isArray(r2) ? r2[0] : r2;

            if (!dataPrecios.historial || dataPrecios.historial.length === 0) {
                toastr.info('No hay historial de precio para esta entidad.');
                return;
            }

            // Mostrar nombre
            var nombre = dataPrecios.nombre || entidadNombre;
            $('#hp_nombre_entidad').html('<i class="fa fa-tag"></i> ' + esc(nombre)).show();

            var historialVenta  = dataPrecios.historial.filter(function (r) { return r.campo === 'precio_venta'; });
            var historialCosto  = dataPrecios.historial.filter(function (r) { return r.campo === 'precio_compra_neto'; });

            renderTablaPrecios(historialVenta);
            renderGrafico(dataPrecios.historial);

            var hayCompras = tipoEntidad === 'PRODUCTO' && dataCompras.compras && dataCompras.compras.length > 0;
            var hayCostos  = historialCosto.length > 0;

            if (hayCompras || hayCostos) {
                if (hayCostos)  renderTablaCostos(historialCosto);
                if (hayCompras) renderTablaCompras(dataCompras.compras);
                $('#hp_tab_compras').show();
                $('#hp_pane_costos').toggle(hayCostos);
                $('#hp_pane_docs_compra').toggle(hayCompras);
            } else {
                $('#hp_tab_compras').hide();
            }

            $('#hp_resultado').fadeIn(200);
            $('#btn_hp_exportar').prop('disabled', false);
        }).fail(function () {
            $('#hp_loader').hide();
            toastr.error('Error al obtener el historial de precios.');
        });
    }

    // ---------------------------------------------------------------
    // Helpers KPI promedio
    // ---------------------------------------------------------------
    function calcPromedio(arr, campo) {
        var vals = arr
            .map(function (r) { return parseFloat(r[campo]); })
            .filter(function (v) { return !isNaN(v) && v > 0; });
        if (!vals.length) return null;
        return vals.reduce(function (a, b) { return a + b; }, 0) / vals.length;
    }

    function renderKpi($bar, items) {
        var html = items.map(function (item) {
            return '<span class="hp-kpi-item' + (item.extra || '') + '">' +
                   '<span class="hp-kpi-label"><i class="fa ' + item.icon + '"></i>' + esc(item.label) + '</span>' +
                   '<span class="hp-kpi-valor">' + clp(Math.round(item.valor)) + '</span>' +
                   '</span>';
        }).join('');
        $bar.html(html).show();
    }

    // ---------------------------------------------------------------
    // Tabla historial precio VENTA (pestaña precios)
    // ---------------------------------------------------------------
    function renderTablaPrecios(historial) {
        if (hpTable) { hpTable.destroy(); hpTable = null; }
        var $tbody = $('#hp_tabla_precios tbody').empty();

        if (!historial || historial.length === 0) {
            $tbody.append('<tr><td colspan="5" class="hp-empty-msg">Sin registros de precio de venta.</td></tr>');
            return;
        }

        historial.forEach(function (r) {
            $tbody.append(
                '<tr>' +
                '<td>' + fechaFmt(r.fecha_cambio) + '</td>' +
                '<td class="text-right">' + (r.precio_anterior !== null ? clp(r.precio_anterior) : '<span style="color:#aaa;">—</span>') + '</td>' +
                '<td class="text-right"><strong>' + clp(r.precio_nuevo) + '</strong></td>' +
                '<td class="text-center">' + varHtml(r.variacion, r.precio_anterior) + '</td>' +
                '<td>' + esc(r.usuario) + '</td>' +
                '</tr>'
            );
        });

        hpTable = $('#hp_tabla_precios').DataTable({
            language: { url: '/vendor/datatables/Spanish.json' },
            paging: true,
            ordering: false,
            info: true,
            pageLength: 25,
            destroy: true,
        });

        // KPI promedio venta
        var prom = calcPromedio(historial, 'precio_nuevo');
        if (prom !== null) {
            renderKpi($('#hp_kpi_venta'), [{ label: 'Precio promedio de venta', valor: prom, icon: 'fa-line-chart' }]);
        }
    }

    // ---------------------------------------------------------------
    // Tabla historial precio COMPRA NETO (pestaña compras)
    // ---------------------------------------------------------------
    function renderTablaCostos(historial) {
        if (hpCostosTable) { hpCostosTable.destroy(); hpCostosTable = null; }
        var $tbody = $('#hp_tabla_costos tbody').empty();

        if (!historial || historial.length === 0) {
            $tbody.append('<tr><td colspan="5" class="hp-empty-msg">Sin registros de precio de compra.</td></tr>');
            return;
        }

        historial.forEach(function (r) {
            $tbody.append(
                '<tr>' +
                '<td>' + fechaFmt(r.fecha_cambio) + '</td>' +
                '<td class="text-right">' + (r.precio_anterior !== null ? clp(r.precio_anterior) : '<span style="color:#aaa;">—</span>') + '</td>' +
                '<td class="text-right"><strong>' + clp(r.precio_nuevo) + '</strong></td>' +
                '<td class="text-center">' + varHtml(r.variacion, r.precio_anterior) + '</td>' +
                '<td>' + esc(r.usuario) + '</td>' +
                '</tr>'
            );
        });

        hpCostosTable = $('#hp_tabla_costos').DataTable({
            language: { url: '/vendor/datatables/Spanish.json' },
            paging: true,
            ordering: false,
            info: true,
            pageLength: 25,
            destroy: true,
        });

        // KPI promedio compra neto
        var prom = calcPromedio(historial, 'precio_nuevo');
        if (prom !== null) {
            renderKpi($('#hp_kpi_costo'), [{ label: 'Precio promedio de compra neto', valor: prom, icon: 'fa-shopping-cart', extra: ' kpi-compra' }]);
        }
    }

    // ---------------------------------------------------------------
    // Tabla historial de compras
    // ---------------------------------------------------------------
    function renderTablaCompras(compras) {
        if (hpComprasTable) { hpComprasTable.destroy(); hpComprasTable = null; }
        var $tbody = $('#hp_tabla_compras tbody').empty();

        if (!compras || compras.length === 0) {
            $tbody.append('<tr><td colspan="6" class="hp-empty-msg">Sin registros de compra.</td></tr>');
            return;
        }

        compras.forEach(function (r) {
            var badgeClass = r.tipo_doc === 'Factura' ? 'label-primary' : 'label-info';
            $tbody.append(
                '<tr>' +
                '<td>' + fechaSolo(r.fecha) + '</td>' +
                '<td><span class="label ' + badgeClass + '">' + esc(r.tipo_doc) + '</span> ' + esc(r.num_doc) + '</td>' +
                '<td>' + esc(r.proveedor) + '</td>' +
                '<td class="text-right">' + Number(r.cantidad).toLocaleString('es-CL', {minimumFractionDigits:1, maximumFractionDigits:1}) + '</td>' +
                '<td class="text-right"><strong>' + clp(r.precio_unitario) + '</strong></td>' +
                '<td class="text-right">' + (r.descuento ? r.descuento + '%' : '—') + '</td>' +
                '</tr>'
            );
        });

        hpComprasTable = $('#hp_tabla_compras').DataTable({
            language: { url: '/vendor/datatables/Spanish.json' },
            paging: true,
            ordering: false,
            info: true,
            pageLength: 25,
            destroy: true,
        });

        // KPI promedio compras reales
        var prom = calcPromedio(compras, 'precio_unitario');
        if (prom !== null) {
            renderKpi($('#hp_kpi_compras'), [{ label: 'Precio promedio de compra (docs.)', valor: prom, icon: 'fa-file-text-o', extra: ' kpi-compra' }]);
        }
    }

    // ---------------------------------------------------------------
    // Gráfico evolución precios (venta y compra)
    // ---------------------------------------------------------------
    function renderGrafico(historial) {
        var ctx = document.getElementById('hpChart');
        if (!ctx) return;
        if (hpChart) { hpChart.destroy(); hpChart = null; }

        var pvRaw = historial.filter(function (r) { return r.campo === 'precio_venta'; });
        var pcRaw = historial.filter(function (r) { return r.campo === 'precio_compra_neto'; });
        if (pvRaw.length === 0 && pcRaw.length === 0) return;

        // Etiqueta de fecha para un registro
        function toLabel(s) {
            var d = new Date(s);
            return d.toLocaleDateString('es-CL') + ' ' + d.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' });
        }

        // Unificar todas las fechas (historial ya llega ordenado por fecha_cambio)
        var allDates = [];
        var seen = {};
        pvRaw.concat(pcRaw).forEach(function (r) {
            var lbl = toLabel(r.fecha_cambio);
            if (!seen[lbl]) { seen[lbl] = true; allDates.push(lbl); }
        });

        // Forward-fill: para cada fecha del eje X retorna el precio vigente
        function makeSeries(data, allDates) {
            var map = {};
            data.forEach(function (r) { map[toLabel(r.fecha_cambio)] = r.precio_nuevo; });
            var last = null;
            return allDates.map(function (d) {
                if (map[d] !== undefined) last = map[d];
                return last;
            });
        }

        var datasets = [];
        if (pvRaw.length > 0) {
            datasets.push({
                label: 'Precio venta',
                data: makeSeries(pvRaw, allDates),
                borderColor: 'rgba(41,128,185,0.9)',
                backgroundColor: 'rgba(41,128,185,0.08)',
                pointBackgroundColor: 'rgba(41,128,185,1)',
                pointRadius: 5,
                fill: false,
                tension: 0.1,
            });
        }
        if (pcRaw.length > 0) {
            datasets.push({
                label: 'Precio compra neto',
                data: makeSeries(pcRaw, allDates),
                borderColor: 'rgba(192,57,43,0.9)',
                backgroundColor: 'rgba(192,57,43,0.08)',
                pointBackgroundColor: 'rgba(192,57,43,1)',
                pointRadius: 5,
                fill: false,
                tension: 0.1,
            });
        }

        hpChart = new Chart(ctx, {
            type: 'line',
            data: { labels: allDates, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: datasets.length > 1, labels: { fontSize: 11 } },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (item, data) {
                            return ' ' + data.datasets[item.datasetIndex].label + ': ' + clp(item.yLabel);
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false,
                            callback: function (v) { return clp(v); },
                            fontSize: 10,
                        }
                    }],
                    xAxes: [{ ticks: { fontSize: 9, maxRotation: 45 } }]
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // Reset
    // ---------------------------------------------------------------
    function resetTablas() {
        if (hpTable)        { hpTable.destroy();        hpTable = null; }
        if (hpCostosTable)  { hpCostosTable.destroy();  hpCostosTable = null; }
        if (hpComprasTable) { hpComprasTable.destroy(); hpComprasTable = null; }
        if (hpChart)        { hpChart.destroy();        hpChart = null; }
        $('#hp_tabla_precios tbody').empty();
        $('#hp_tabla_costos tbody').empty();
        $('#hp_tabla_compras tbody').empty();
        $('#hp_nombre_entidad').hide();
        $('#hp_kpi_venta, #hp_kpi_costo, #hp_kpi_compras').hide().empty();
    }

    // ---------------------------------------------------------------
    // Exportar
    // ---------------------------------------------------------------
    $('#btn_hp_exportar').on('click', function () {
        if (entidadId <= 0) return;
        window.location.href = '/reportes/hist_precio_prod/exportar?tipo=' + tipoEntidad + '&entidad_id=' + entidadId;
    });

    // ---------------------------------------------------------------
    // Ocultar tab compras si no es PRODUCTO
    // ---------------------------------------------------------------
    if (tipoNegocio !== 'RESTAURANT') {
        // En ALMACEN mostrar tab compras siempre disponible al abrir, solo se oculta si no hay datos
    }
});
