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
                    callback: function (v) { return clpFormatter(v); }
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
                            callback: function (v) { return clpFormatter(v); }
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
                legend: { display: true, position: 'top', labels: { fontSize: 12, boxWidth: 14 } },
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
                            callback: function (v) { return clpFormatter(v); }
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

});
