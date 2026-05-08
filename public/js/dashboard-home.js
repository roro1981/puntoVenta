/* =========================================================
   Dashboard Home — scripts exclusivos del panel principal
   ========================================================= */

/* ----- Configuración global de Toastr ----- */
toastr.options = {
    'closeButton': true,
    'debug': false,
    'newestOnTop': false,
    'progressBar': true,
    'positionClass': 'toast-top-center',
    'preventDuplicates': false,
    'onclick': null,
    'showDuration': '500',
    'hideDuration': '1000',
    'timeOut': '5000',
    'extendedTimeOut': '1000',
    'showEasing': 'swing',
    'hideEasing': 'linear',
    'showMethod': 'fadeIn',
    'hideMethod': 'fadeOut'
};

document.addEventListener('DOMContentLoaded', function () {

    /* ---- helpers ---- */
    function clpFormatter(value) {
        return '$' + Number(value).toLocaleString('es-CL');
    }

    /* Tick de eje abreviado en móvil para evitar que el label ancho achique el gráfico */
    var isMobile = window.innerWidth < 768;
    function clpTick(v) {
        if (!isMobile) return '$' + Number(v).toLocaleString('es-CL');
        var abs = Math.abs(v);
        if (abs >= 1000000) return '$' + (v / 1000000).toFixed(1).replace('.', ',') + 'M';
        if (abs >= 1000)    return '$' + Math.round(v / 1000) + 'K';
        return '$' + Math.round(v);
    }

    /* Ajustes globales Chart.js para móvil */
    if (typeof Chart !== 'undefined') {
        Chart.defaults.global.defaultFontSize       = isMobile ? 10 : 12;
        Chart.defaults.global.elements.point.radius = isMobile ?  2 :  3;
        Chart.defaults.global.elements.point.hoverRadius = isMobile ? 4 : 6;
    }
    var chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        tooltips: {
            callbacks: {
                label: function (item) {
                    return ' ' + clpFormatter(item.yLabel || 0);
                }
            }
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function (v) { return clpTick(v); }
                },
                gridLines: { color: 'rgba(132,146,166,0.16)' }
            }],
            xAxes: [{ gridLines: { display: false } }]
        }
    };

    /* ----- Tendencia 7 días (línea) ----- */
    var trendCanvas = document.getElementById('homeSalesTrendChart');
    if (trendCanvas && typeof Chart !== 'undefined') {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: window.homeTrendLabels || [],
                datasets: [{
                    label: 'Ventas',
                    data: window.homeTrendData || [],
                    backgroundColor: 'rgba(230,126,34,0.18)',
                    borderColor: '#e67e22',
                    borderWidth: 3,
                    pointBackgroundColor: '#1f5f8b',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    lineTension: 0.25
                }]
            },
            options: chartDefaults
        });
    }

    /* ----- Ventas por hora (barras) ----- */
    var hourlyCanvas = document.getElementById('homeHourlyChart');
    if (hourlyCanvas && typeof Chart !== 'undefined') {
        new Chart(hourlyCanvas, {
            type: 'bar',
            data: {
                labels: window.homeHourlyLabels || [],
                datasets: [{
                    label: 'Ventas',
                    data: window.homeHourlyData || [],
                    backgroundColor: 'rgba(230,126,34,0.72)',
                    borderColor: '#e67e22',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: chartDefaults
        });
    }

    /* ----- Promedio por día de semana (barras) ----- */
    var dowCanvas = document.getElementById('homeDayOfWeekChart');
    if (dowCanvas && typeof Chart !== 'undefined') {
        new Chart(dowCanvas, {
            type: 'bar',
            data: {
                labels: window.homeDayLabels || [],
                datasets: [{
                    label: 'Promedio',
                    data: window.homeDayData || [],
                    backgroundColor: 'rgba(15,124,144,0.72)',
                    borderColor: '#0f7c90',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: chartDefaults
        });
    }

    /* ----- Ventas por categoría (barras horizontales) ----- */
    var catCanvas = document.getElementById('homeCategoryChart');
    if (catCanvas && typeof Chart !== 'undefined') {
        var catColors = [
            'rgba(31,95,139,0.75)', 'rgba(230,126,34,0.75)', 'rgba(15,124,144,0.75)',
            'rgba(90,63,160,0.75)', 'rgba(25,135,84,0.75)',  'rgba(183,58,58,0.75)',
            'rgba(217,138,0,0.75)', 'rgba(55,90,110,0.75)'
        ];
        new Chart(catCanvas, {
            type: 'horizontalBar',
            data: {
                labels: window.homeCategoryLabels || [],
                datasets: [{
                    label: 'Ventas del mes',
                    data: window.homeCategoryData || [],
                    backgroundColor: (window.homeCategoryLabels || []).map(function (_, i) {
                        return catColors[i % catColors.length];
                    }),
                    borderWidth: 0,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (item) { return ' ' + clpFormatter(item.xLabel || 0); }
                    }
                },
                scales: {
                    xAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) { return clpTick(v); }
                        },
                        gridLines: { color: 'rgba(132,146,166,0.16)' }
                    }],
                    yAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }

    /* ----- Evolución 6 meses: ventas vs compras (líneas) ----- */
    var sixCanvas = document.getElementById('home6MonthsChart');
    if (sixCanvas && typeof Chart !== 'undefined') {
        new Chart(sixCanvas, {
            type: 'line',
            data: {
                labels: window.home6MonthsLabels || [],
                datasets: [
                    {
                        label: 'Ventas',
                        data: window.home6MonthsVentas || [],
                        backgroundColor: 'rgba(31,95,139,0.14)',
                        borderColor: '#1f5f8b',
                        borderWidth: 3,
                        pointBackgroundColor: '#1f5f8b',
                        pointRadius: 4,
                        fill: true,
                        lineTension: 0.25
                    },
                    {
                        label: 'Compras est.',
                        data: window.home6MonthsCompras || [],
                        backgroundColor: 'rgba(183,58,58,0.10)',
                        borderColor: '#b73a3a',
                        borderWidth: 2,
                        pointBackgroundColor: '#b73a3a',
                        pointRadius: 4,
                        fill: false,
                        lineTension: 0.25,
                        borderDash: [5, 4]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: true, position: 'top', labels: { fontSize: isMobile ? 10 : 12, boxWidth: 14 } },
                tooltips: {
                    mode: 'index',
                    callbacks: {
                        label: function (item, data) {
                            return ' ' + data.datasets[item.datasetIndex].label + ': ' + clpFormatter(item.yLabel || 0);
                        }
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function (v) { return clpTick(v); }
                        },
                        gridLines: { color: 'rgba(132,146,166,0.16)' }
                    }],
                    xAxes: [{ gridLines: { display: false } }]
                }
            }
        });
    }

    /* ----- Administrador: ventas por hora ----- */
    var adminHourlyCanvas = document.getElementById('homeHourlyChartAdmin');
    if (adminHourlyCanvas && typeof Chart !== 'undefined') {
        new Chart(adminHourlyCanvas, {
            type: 'bar',
            data: {
                labels: window.adminHourlyLabels || [],
                datasets: [{
                    label: 'Ventas',
                    data: window.adminHourlyData || [],
                    backgroundColor: 'rgba(230,126,34,0.72)',
                    borderColor: '#e67e22',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: chartDefaults
        });
    }

    /* ----- Administrador: tendencia 7 días ----- */
    var adminTrendCanvas = document.getElementById('homeSalesTrendChartAdmin');
    if (adminTrendCanvas && typeof Chart !== 'undefined') {
        new Chart(adminTrendCanvas, {
            type: 'line',
            data: {
                labels: window.adminTrendLabels || [],
                datasets: [{
                    label: 'Ventas',
                    data: window.adminTrendData || [],
                    backgroundColor: 'rgba(230,126,34,0.18)',
                    borderColor: '#e67e22',
                    borderWidth: 3,
                    pointBackgroundColor: '#1f5f8b',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    lineTension: 0.25
                }]
            },
            options: chartDefaults
        });
    }

    /* ----- Administrador: promedio por día de semana ----- */
    var adminDowCanvas = document.getElementById('homeDayOfWeekChartAdmin');
    if (adminDowCanvas && typeof Chart !== 'undefined') {
        new Chart(adminDowCanvas, {
            type: 'bar',
            data: {
                labels: window.adminDayLabels || [],
                datasets: [{
                    label: 'Promedio',
                    data: window.adminDayData || [],
                    backgroundColor: 'rgba(15,124,144,0.72)',
                    borderColor: '#0f7c90',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: chartDefaults
        });
    }

    /* inicializar tooltips de Bootstrap en los iconos de ayuda */
    $('[data-toggle="tooltip"]').tooltip({ container: 'body' });

    /* Forzar redibujado de gráficos tras animación del sidebar de AdminLTE (fix móvil) */
    setTimeout(function () { if (typeof $ !== 'undefined') $(window).trigger('resize'); }, 420);
    /* También al abrir/cerrar sidebar manualmente */
    $(document).on('click', '.sidebar-toggle', function () {
        setTimeout(function () { $(window).trigger('resize'); }, 350);
    });

});

/* =========================================================
   jQuery: lógica de compras desde modales de stock
   ========================================================= */
$(function () {

    /* =====================================================
       MODAL: Sin stock (outOfStockByCategory)
       ===================================================== */

    // Habilitar cantidad solo si el producto está marcado
    $(document).on('change', '.check-prod', function () {
        $(this).closest('tr').find('.input-cant').prop('disabled', !this.checked);
        $('#btnAgregarCompra').toggle($('.check-prod:checked').length > 0);
    });

    // Selección masiva por categoría
    $(document).on('change', '.check-all-cat', function () {
        var checked = this.checked;
        $(this).closest('table').find('.check-prod').each(function () {
            $(this).prop('checked', checked).trigger('change');
        });
    });

    // Abrir selector de tipo de documento
    $(document).on('click', '#btnAgregarCompra', function () {
        $('#modalTipoDoc').modal('show');
    });

    // Confirmar y cargar en módulo de compras
    $(document).on('click', '#confirmTipoDoc', function () {
        var tipo = $('input[name="tipo_doc"]:checked').val();
        var codigos = [];
        var cantidades = {};
        var valido = true;

        $('.check-prod:checked').each(function () {
            var item = JSON.parse($(this).val());
            var cantidad = parseInt($(this).closest('tr').find('.input-cant').val());
            if (!cantidad || cantidad < 1) {
                toastr.error('Debes ingresar una cantidad válida para todos los productos seleccionados');
                valido = false;
                return false;
            }
            codigos.push(item.codigo);
            cantidades[item.codigo] = cantidad;
        });

        if (!valido || codigos.length === 0) return;

        $.ajax({
            url: '/compras/productos-dashboard',
            type: 'GET',
            data: { codigos: codigos },
            dataType: 'json',
            success: function (data) {
                var productos = [];
                codigos.forEach(function (cod) {
                    if (data[cod]) {
                        productos.push({
                            codigo:        data[cod].codigo,
                            descripcion:   data[cod].descripcion,
                            precio_compra: data[cod].precio_compra,
                            imp1:          data[cod].imp1,
                            imp2:          data[cod].imp2,
                            cantidad:      cantidades[cod]
                        });
                    }
                });

                if (productos.length === 0) {
                    toastr.warning('No se encontraron datos de los productos seleccionados');
                    return;
                }

                sessionStorage.setItem('dashboardPedido', JSON.stringify({ tipo: tipo, productos: productos }));

                $('#modalTipoDoc').modal('hide');
                $('#modalDashboardSinStock').modal('hide');
                $('.check-prod').prop('checked', false).trigger('change');
                $('.input-cant').val('').prop('disabled', true);
                $('#btnAgregarCompra').hide();

                $('#contenido').load('/compras/ingresos');
            },
            error: function () {
                toastr.error('Error al obtener los datos de los productos');
            }
        });
    });

    /* =====================================================
       MODAL: Alerta de stock (stockAlerts)
       ===================================================== */

    // Habilitar cantidad solo si el producto está marcado
    $(document).on('change', '.check-prod-alerta', function () {
        $(this).closest('tr').find('.input-cant-alerta').prop('disabled', !this.checked);
        $('#btnAgregarCompraAlerta').toggle($('.check-prod-alerta:checked').length > 0);
    });

    // Selección masiva por categoría
    $(document).on('change', '.check-all-cat-alerta', function () {
        var checked = this.checked;
        $(this).closest('table').find('.check-prod-alerta').each(function () {
            $(this).prop('checked', checked).trigger('change');
        });
    });

    // Abrir selector de tipo de documento
    $(document).on('click', '#btnAgregarCompraAlerta', function () {
        $('#modalTipoDocAlertas').modal('show');
    });

    // Confirmar y cargar en módulo de compras
    $(document).on('click', '#confirmTipoDocAlertas', function () {
        var tipo = $('input[name="tipo_doc_alerta"]:checked').val();
        var codigos = [];
        var cantidades = {};
        var valido = true;

        $('.check-prod-alerta:checked').each(function () {
            var item = JSON.parse($(this).val());
            var cantidad = parseInt($(this).closest('tr').find('.input-cant-alerta').val());
            if (!cantidad || cantidad < 1) {
                toastr.error('Debes ingresar una cantidad válida para todos los productos seleccionados');
                valido = false;
                return false;
            }
            codigos.push(item.codigo);
            cantidades[item.codigo] = cantidad;
        });

        if (!valido || codigos.length === 0) return;

        $.ajax({
            url: '/compras/productos-dashboard',
            type: 'GET',
            data: { codigos: codigos },
            dataType: 'json',
            success: function (data) {
                var productos = [];
                codigos.forEach(function (cod) {
                    if (data[cod]) {
                        productos.push({
                            codigo:        data[cod].codigo,
                            descripcion:   data[cod].descripcion,
                            precio_compra: data[cod].precio_compra,
                            imp1:          data[cod].imp1,
                            imp2:          data[cod].imp2,
                            cantidad:      cantidades[cod]
                        });
                    }
                });

                if (productos.length === 0) {
                    toastr.warning('No se encontraron datos de los productos seleccionados');
                    return;
                }

                sessionStorage.setItem('dashboardPedido', JSON.stringify({ tipo: tipo, productos: productos }));

                $('#modalTipoDocAlertas').modal('hide');
                $('#modalDashboardStockAlertas').modal('hide');
                $('.check-prod-alerta').prop('checked', false).trigger('change');
                $('.input-cant-alerta').val('').prop('disabled', true);
                $('#btnAgregarCompraAlerta').hide();

                $('#contenido').load('/compras/ingresos');
            },
            error: function () {
                toastr.error('Error al obtener los datos de los productos');
            }
        });
    });

    // ── CONTROL INTERNO — filtros de período ──────────────────────────────────

    function ciFormatCLP(n) {
        return '$' + Math.round(Number(n)).toLocaleString('es-CL');
    }

    function ciFormatNum(n, dec) {
        return Number(n).toLocaleString('es-CL', {
            minimumFractionDigits: dec || 0,
            maximumFractionDigits: dec || 0
        });
    }

    function ciCalcularRango(rango) {
        var hoy = new Date();
        function isoFmt(d) { return d.toISOString().split('T')[0]; }
        var desde, hasta = isoFmt(hoy);
        if (rango === 'semana') {
            var lunes = new Date(hoy);
            lunes.setDate(hoy.getDate() - ((hoy.getDay() + 6) % 7));
            desde = isoFmt(lunes);
        } else if (rango === 'mes') {
            desde = isoFmt(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
        } else if (rango === 'trimestre') {
            desde = isoFmt(new Date(hoy.getFullYear(), hoy.getMonth() - 2, 1));
        } else if (rango === 'semestre') {
            desde = isoFmt(new Date(hoy.getFullYear(), hoy.getMonth() - 5, 1));
        } else if (rango === 'anio') {
            desde = isoFmt(new Date(hoy.getFullYear(), 0, 1));
        }
        return { desde: desde, hasta: hasta };
    }

    function ciRenderData(data) {
        var anu    = data.anulaciones;
        var mermas = data.mermas;

        // KPIs
        var hoyVal = anu.cantidadHoy || 0;
        $('#ci-kpi-anu-hoy').text(hoyVal).css('color', hoyVal > 0 ? '#c0392b' : '#27ae60');
        $('#ci-kpi-anu-total').text(anu.totalMes || 0);
        $('#ci-kpi-anu-monto').text(ciFormatCLP(anu.montoMes || 0));
        $('#ci-kpi-merma-costo').text(ciFormatCLP(mermas.costoMes || 0));
        $('#ci-kpi-merma-note').text((mermas.cantidadMes || 0) + ' registros de merma.');

        // Tabla anulaciones
        if (anu.porUsuario && anu.porUsuario.length > 0) {
            var html = '<table class="home-modal-table"><thead><tr>'
                + '<th style="text-align:left;">Usuario</th>'
                + '<th style="text-align:center;">Cantidad</th>'
                + '<th style="text-align:right;">Monto anulado</th>'
                + '</tr></thead><tbody>';
            anu.porUsuario.forEach(function (row) {
                var badgeStyle = row.cantidad >= 5
                    ? 'background:#ffe0e0;color:#c0392b'
                    : 'background:#fff8e1;color:#856404';
                html += '<tr>'
                    + '<td style="text-align:left;">' + row.usuario + '</td>'
                    + '<td style="text-align:center;"><span style="display:inline-block;' + badgeStyle + ';padding:2px 10px;border-radius:10px;font-size:12px;font-weight:600;">' + row.cantidad + '</span></td>'
                    + '<td style="text-align:right;color:#c0392b;font-weight:600;">' + ciFormatCLP(row.montoTotal) + '</td>'
                    + '</tr>';
            });
            html += '</tbody><tfoot><tr style="background:#fff0f0;">'
                + '<td style="text-align:left;"><strong>Total</strong></td>'
                + '<td style="text-align:center;"><strong>' + (anu.totalMes || 0) + '</strong></td>'
                + '<td style="text-align:right;color:#c0392b;"><strong>' + ciFormatCLP(anu.montoMes || 0) + '</strong></td>'
                + '</tr></tfoot></table>';
            $('#ci-container-anulaciones').html('<div class="table-responsive">' + html + '</div>');
        } else {
            $('#ci-container-anulaciones').html('<div class="home-empty" style="color:#27ae60;"><i class="fa fa-check-circle"></i> Sin anulaciones en este período.</div>');
        }

        // Tabla mermas
        if (mermas.porProducto && mermas.porProducto.length > 0) {
            var html2 = '<table class="home-modal-table"><thead><tr>'
                + '<th style="text-align:left;">Producto</th>'
                + '<th style="text-align:left;">Categoría</th>'
                + '<th style="text-align:right;">Cant.</th>'
                + '<th style="text-align:right;">Costo</th>'
                + '</tr></thead><tbody>';
            mermas.porProducto.forEach(function (row) {
                html2 += '<tr>'
                    + '<td style="text-align:left;">' + row.producto + '</td>'
                    + '<td style="text-align:left;font-size:11px;color:#6b7280;">' + row.categoria + '</td>'
                    + '<td style="text-align:right;">' + ciFormatNum(row.cantidadTotal, 1) + '</td>'
                    + '<td style="text-align:right;color:#8e44ad;font-weight:600;">' + ciFormatCLP(row.costoTotal) + '</td>'
                    + '</tr>';
            });
            html2 += '</tbody><tfoot><tr style="background:#f5eeff;">'
                + '<td colspan="3" style="text-align:left;"><strong>Costo total mermas</strong></td>'
                + '<td style="text-align:right;color:#8e44ad;"><strong>' + ciFormatCLP(mermas.costoMes || 0) + '</strong></td>'
                + '</tr></tfoot></table>';
            $('#ci-container-mermas').html('<div class="table-responsive">' + html2 + '</div>');
        } else {
            $('#ci-container-mermas').html('<div class="home-empty" style="color:#27ae60;"><i class="fa fa-check-circle"></i> Sin mermas en este período.</div>');
        }

        // ── Retiros de caja ──────────────────────────────────────────
        var retiros = data.retiros || { porUsuario: [], montoTotal: 0, cantidadTotal: 0 };
        $('#ci-kpi-retiros-total').text(ciFormatCLP(retiros.montoTotal || 0));
        $('#ci-kpi-retiros-cant').text(retiros.cantidadTotal || 0);
        if (retiros.porUsuario && retiros.porUsuario.length > 0) {
            var htmlR = '<table class="home-modal-table"><thead><tr>'
                + '<th style="text-align:left;">Cajero</th>'
                + '<th style="text-align:center;">Retiros</th>'
                + '<th style="text-align:right;">Monto total</th>'
                + '</tr></thead><tbody>';
            retiros.porUsuario.forEach(function (row) {
                var alerta = row.montoTotal > 500000;
                htmlR += '<tr' + (alerta ? ' style="background:#fff0e0;"' : '') + '>'
                    + '<td style="text-align:left;">' + row.usuario + (alerta ? ' <i class="fa fa-warning" style="color:#e67e22;" title="Monto alto"></i>' : '') + '</td>'
                    + '<td style="text-align:center;">' + row.cantidad + '</td>'
                    + '<td style="text-align:right;color:#e67e22;font-weight:600;">' + ciFormatCLP(row.montoTotal) + '</td>'
                    + '</tr>';
            });
            htmlR += '</tbody><tfoot><tr style="background:#fff5eb;">'
                + '<td style="text-align:left;"><strong>Total</strong></td>'
                + '<td style="text-align:center;"><strong>' + (retiros.cantidadTotal || 0) + '</strong></td>'
                + '<td style="text-align:right;color:#e67e22;"><strong>' + ciFormatCLP(retiros.montoTotal || 0) + '</strong></td>'
                + '</tr></tfoot></table>';
            $('#ci-container-retiros').html('<div class="table-responsive">' + htmlR + '</div>');
        } else {
            $('#ci-container-retiros').html('<div class="home-empty" style="color:#27ae60;"><i class="fa fa-check-circle"></i> Sin retiros en este período.</div>');
        }

        // ── Cierres con diferencia alta ──────────────────────────────
        var alertas = data.cierres_alerta || { cierres: [], totalAlertas: 0, umbral: 5000 };
        var umbralFmt = '$' + ciFormatNum(alertas.umbral || 5000, 0);
        $('#ci-alerta-umbral').text('> ' + umbralFmt);
        $('#ci-kpi-alertas-total').text(alertas.totalAlertas || 0)
            .css('color', (alertas.totalAlertas || 0) > 0 ? '#c0392b' : '#27ae60');
        if (alertas.cierres && alertas.cierres.length > 0) {
            var htmlA = '<table class="home-modal-table"><thead><tr>'
                + '<th style="text-align:left;">Cajero</th>'
                + '<th style="text-align:right;">Esperado</th>'
                + '<th style="text-align:right;">Declarado</th>'
                + '<th style="text-align:right;">Diferencia</th>'
                + '<th style="text-align:left;">Cierre</th>'
                + '</tr></thead><tbody>';
            alertas.cierres.forEach(function (row) {
                var dif = row.diferencia || 0;
                var difStyle = dif < 0 ? 'color:#c0392b;font-weight:600;' : 'color:#27ae60;font-weight:600;';
                htmlA += '<tr>'
                    + '<td style="text-align:left;">' + row.cajero + '</td>'
                    + '<td style="text-align:right;">' + ciFormatCLP(row.montoEsperado) + '</td>'
                    + '<td style="text-align:right;">' + ciFormatCLP(row.montoDeclarado) + '</td>'
                    + '<td style="text-align:right;' + difStyle + '">' + ciFormatCLP(dif) + '</td>'
                    + '<td style="text-align:left;font-size:11px;color:#6b7280;">' + row.fechaCierre + '</td>'
                    + '</tr>';
            });
            htmlA += '</tbody></table>';
            $('#ci-container-cierres-alerta').html('<div class="table-responsive">' + htmlA + '</div>');
        } else {
            $('#ci-container-cierres-alerta').html('<div class="home-empty" style="color:#27ae60;"><i class="fa fa-check-circle"></i> Sin diferencias significativas en este período.</div>');
        }
    }

    function cargarControlInterno(desde, hasta) {
        $('#ci-container-anulaciones').html('<div class="home-empty"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');
        $('#ci-container-mermas').html('<div class="home-empty"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');
        $('#ci-container-retiros').html('<div class="home-empty"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');
        $('#ci-container-cierres-alerta').html('<div class="home-empty"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>');
        $.ajax({
            url: '/dashboard/control-interno',
            type: 'GET',
            data: { desde: desde, hasta: hasta },
            success: function (data) {
                ciRenderData(data);
            },
            error: function () {
                toastr.error('Error al cargar datos de Control Interno');
                $('#ci-container-anulaciones').html('<div class="home-empty" style="color:#c0392b;">Error al cargar.</div>');
                $('#ci-container-mermas').html('<div class="home-empty" style="color:#c0392b;">Error al cargar.</div>');
                $('#ci-container-retiros').html('<div class="home-empty" style="color:#c0392b;">Error al cargar.</div>');
                $('#ci-container-cierres-alerta').html('<div class="home-empty" style="color:#c0392b;">Error al cargar.</div>');
            }
        });
    }

    var ciLabels = {
        semana: 'Esta semana', mes: 'Este mes',
        trimestre: 'Este trimestre', semestre: 'Este semestre', anio: 'Este año'
    };

    $(document).on('click', '.ci-periodo-btn', function () {
        var rango = $(this).data('rango');
        $('.ci-periodo-btn').removeClass('btn-primary active').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('btn-primary active');
        $('#ci-periodo-label').text(ciLabels[rango] || rango);
        var f = ciCalcularRango(rango);
        cargarControlInterno(f.desde, f.hasta);
    });

    // Disparar "Esta semana" por defecto al cargar el dashboard
    if ($('.ci-periodo-btn').length) {
        $('.ci-periodo-btn[data-rango="semana"]').trigger('click');
    }

});
