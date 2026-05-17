$(document).ready(function () {
    var dpOpts = {
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        maxDate: 0,
        monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
        firstDay: 1,
    };

    $('#ac_desde, #ac_hasta').datepicker(dpOpts);

    var tabla = null;
    var categoriasChart = null;
    var productosChart = null;
    var tendenciaChart = null;
    var ultimoDesde = null;
    var ultimoHasta = null;

    function fmtFecha(date) {
        return String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + date.getFullYear();
    }

    function parseFecha(str) {
        var p = str.split('/');
        return p[2] + '-' + p[1] + '-' + p[0];
    }

    function clp(n) {
        return '$' + Number(n || 0).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function fmtNum(n) {
        return Number(n || 0).toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 1 });
    }

    function esc(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setRango(rango) {
        var hoy = new Date();
        var desde = new Date(hoy);
        var hasta = new Date(hoy);

        if (rango === 'hoy') {
            // mismo día
        } else if (rango === 'semana') {
            desde.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
        } else if (rango === 'mes') {
            desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        } else if (rango === 'anio') {
            desde = new Date(hoy.getFullYear(), 0, 1);
        }

        $('#ac_desde').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', fmtFecha(desde)));
        $('#ac_hasta').datepicker('setDate', $.datepicker.parseDate('dd/mm/yy', fmtFecha(hasta)));
        $('.ac-atajo').removeClass('active');
        $('.ac-atajo[data-rango="' + rango + '"]').addClass('active');
    }

    function renderResumenLista(selector, rows, formatter) {
        var $wrap = $(selector).empty();
        if (!rows || rows.length === 0) {
            $wrap.html('<div class="ac-resumen-item"><span>Sin datos en el período.</span></div>');
            return;
        }

        rows.forEach(function (row, index) {
            $wrap.append(
                '<div class="ac-resumen-item">' +
                    '<div><strong>#' + (index + 1) + '</strong> ' + formatter.left(row) + '</div>' +
                    '<div>' + formatter.right(row) + '</div>' +
                '</div>'
            );
        });
    }

    function destroyChart(chart) {
        if (chart) {
            chart.destroy();
        }
        return null;
    }

    function renderCategoriasChart(categorias) {
        categoriasChart = destroyChart(categoriasChart);
        var ctx = document.getElementById('acCategoriasChart');
        if (!ctx || !categorias || !categorias.length) return;

        var top = categorias.slice(0, 8);
        categoriasChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: top.map(function (item) { return item.categoria; }),
                datasets: [{
                    data: top.map(function (item) { return item.unidades; }),
                    backgroundColor: ['#2563eb','#dc2626','#ea580c','#16a34a','#7c3aed','#0891b2','#ca8a04','#475569'],
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'bottom', labels: { fontSize: 11 } },
                tooltips: {
                    callbacks: {
                        label: function (item, data) {
                            var label = data.labels[item.index] || '';
                            var val = data.datasets[0].data[item.index] || 0;
                            return ' ' + label + ': ' + fmtNum(val) + ' unidades';
                        }
                    }
                },
                cutoutPercentage: 58,
            }
        });
    }

    function renderProductosChart(productos) {
        productosChart = destroyChart(productosChart);
        var ctx = document.getElementById('acProductosChart');
        if (!ctx || !productos || !productos.length) return;

        var top = productos.slice(0, 8);
        productosChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: top.map(function (item) { return item.producto; }),
                datasets: [{
                    label: 'Unidades eliminadas',
                    data: top.map(function (item) { return item.unidades; }),
                    backgroundColor: 'rgba(220, 38, 38, 0.75)',
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ ticks: { beginAtZero: true } }],
                    yAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }

    function renderTendenciaChart(tendencia) {
        tendenciaChart = destroyChart(tendenciaChart);
        var ctx = document.getElementById('acTendenciaChart');
        if (!ctx || !tendencia || !tendencia.fechas || !tendencia.fechas.length) return;

        tendenciaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: tendencia.fechas,
                datasets: [
                    {
                        label: 'Monto referencial',
                        data: tendencia.monto,
                        backgroundColor: 'rgba(37, 99, 235, 0.55)',
                        borderColor: 'rgba(37, 99, 235, 0.9)',
                        borderWidth: 1,
                        yAxisID: 'y-monto',
                    },
                    {
                        label: 'Unidades',
                        data: tendencia.unidades,
                        type: 'line',
                        fill: false,
                        borderColor: 'rgba(220, 38, 38, 1)',
                        backgroundColor: 'rgba(220, 38, 38, 1)',
                        borderWidth: 2,
                        pointRadius: tendencia.fechas.length <= 20 ? 4 : 2,
                        yAxisID: 'y-unidades',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (item, data) {
                            var ds = data.datasets[item.datasetIndex];
                            if (item.datasetIndex === 0) {
                                return ' ' + ds.label + ': ' + clp(item.yLabel);
                            }
                            return ' ' + ds.label + ': ' + fmtNum(item.yLabel);
                        }
                    }
                },
                scales: {
                    yAxes: [
                        {
                            id: 'y-monto',
                            position: 'left',
                            ticks: { beginAtZero: true, callback: function (v) { return clp(v); } }
                        },
                        {
                            id: 'y-unidades',
                            position: 'right',
                            ticks: { beginAtZero: true },
                            gridLines: { display: false }
                        }
                    ]
                }
            }
        });
    }

    function renderTabla(detalles) {
        if (tabla) {
            tabla.destroy();
            tabla = null;
        }

        var $tbody = $('#ac_tabla tbody').empty();
        if (!detalles || !detalles.length) {
            $tbody.append('<tr><td colspan="11" class="text-center">No hay eliminaciones para el período seleccionado.</td></tr>');
            return;
        }

        detalles.forEach(function (item) {
            $tbody.append(
                '<tr>' +
                    '<td>' + esc(item.fecha) + '</td>' +
                    '<td>' + esc(item.numeroComanda) + '</td>' +
                    '<td>' + esc(item.mesa) + '</td>' +
                    '<td>' + esc(item.garzon) + '</td>' +
                    '<td>' + esc(item.producto) + '</td>' +
                    '<td><span class="ac-badge-cat">' + esc(item.categoria) + '</span></td>' +
                    '<td class="text-right">' + fmtNum(item.cantidad) + '</td>' +
                    '<td class="text-right">' + clp(item.precioReferencia) + '</td>' +
                    '<td class="text-right">' + clp(item.montoReferencia) + '</td>' +
                    '<td>' + esc(item.usuario) + '</td>' +
                    '<td>' + esc(item.motivo) + '</td>' +
                '</tr>'
            );
        });

        tabla = $('#ac_tabla').DataTable({
            language: { url: '/vendor/datatables/Spanish.json' },
            order: [[0, 'desc']],
            pageLength: 25,
            destroy: true,
        });
    }

    function renderHallazgos(hallazgos) {
        var $list = $('#ac_hallazgos').empty();
        if (!hallazgos || !hallazgos.length) {
            $list.append('<li>Sin hallazgos para el período.</li>');
            return;
        }
        hallazgos.forEach(function (item) {
            $list.append('<li>' + esc(item) + '</li>');
        });
    }

    function renderResultado(data) {
        $('#ac_kpi_eventos').text(fmtNum(data.kpis.eventos));
        $('#ac_kpi_unidades').text(fmtNum(data.kpis.unidades));
        $('#ac_kpi_monto').text(clp(data.kpis.monto));
        $('#ac_kpi_comandas').text(fmtNum(data.kpis.comandas));
        $('#ac_kpi_mesas').text('Mesas afectadas: ' + fmtNum(data.kpis.mesas));

        $('#ac_kpi_variacion_unidades').text(data.kpis.variacionUnidades === null ? 'Sin base comparable previa' : ((data.kpis.variacionUnidades >= 0 ? '+' : '') + data.kpis.variacionUnidades + '% vs período anterior'));
        $('#ac_kpi_variacion_monto').text(data.kpis.variacionMonto === null ? 'Sin base comparable previa' : ((data.kpis.variacionMonto >= 0 ? '+' : '') + data.kpis.variacionMonto + '% vs período anterior'));

        renderCategoriasChart(data.categorias);
        renderProductosChart(data.productos);
        renderTendenciaChart(data.tendencia);
        renderHallazgos(data.hallazgos);
        renderTabla(data.detalles);

        renderResumenLista('#ac_usuarios_list', data.usuarios, {
            left: function (row) { return esc(row.usuario); },
            right: function (row) { return fmtNum(row.eventos) + ' eventos · ' + fmtNum(row.unidades) + ' uds'; }
        });
        renderResumenLista('#ac_motivos_list', data.motivos, {
            left: function (row) { return esc(row.motivo); },
            right: function (row) { return fmtNum(row.eventos) + ' eventos'; }
        });
    }

    $('#btn_ac_generar').on('click', function () {
        var desdeRaw = $('#ac_desde').val();
        var hastaRaw = $('#ac_hasta').val();
        if (!desdeRaw || !hastaRaw) {
            toastr.warning('Selecciona ambas fechas antes de generar.');
            return;
        }

        ultimoDesde = parseFecha(desdeRaw);
        ultimoHasta = parseFecha(hastaRaw);

        $('#ac_resultado').hide();
        $('#ac_loader').show();
        $('#btn_ac_exportar').prop('disabled', true);

        $.ajax({
            url: '/reportes/anulaciones_comandas/data',
            method: 'GET',
            data: { desde: ultimoDesde, hasta: ultimoHasta },
            success: function (data) {
                $('#ac_loader').hide();
                if (!data.kpis || !data.kpis.eventos) {
                    toastr.info('No hay eliminaciones registradas en el período seleccionado.');
                    return;
                }
                renderResultado(data);
                $('#ac_resultado').fadeIn(200);
                $('#btn_ac_exportar').prop('disabled', false);
            },
            error: function (xhr) {
                $('#ac_loader').hide();
                toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error al obtener el reporte.');
            }
        });
    });

    $('#btn_ac_exportar').on('click', function () {
        if (!ultimoDesde || !ultimoHasta) {
            return;
        }
        window.location.href = '/reportes/anulaciones_comandas/exportar?desde=' + ultimoDesde + '&hasta=' + ultimoHasta;
    });

    $('.ac-atajo').on('click', function () {
        setRango($(this).data('rango'));
        $('#btn_ac_generar').trigger('click');
    });

    setRango('hoy');
    $('#btn_ac_generar').trigger('click');
});