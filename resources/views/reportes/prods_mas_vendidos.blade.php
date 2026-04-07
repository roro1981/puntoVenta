<head>
    <link rel="stylesheet" href="/css/reportes/prods_mas_vendidos.css" />
</head>

<!-- ===== FILTRO ===== -->
<div class="pmv-filtro">
    <div class="pmv-filtro-inner">
        <div class="pmv-filtro-group">
            <label>Desde</label>
            <input type="text" id="pmv_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="pmv-filtro-group">
            <label>Hasta</label>
            <input type="text" id="pmv_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="pmv-filtro-group pmv-filtro-group-select">
            <label>Categoría</label>
            <select id="pmv_select_categoria" class="form-control">
                <option value="">Todas las categorías</option>
            </select>
        </div>
        <div class="pmv-filtro-actions">
            <button id="btn_pmv_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_pmv_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <!-- accesos rápidos -->
        <div class="pmv-atajos">
            <button class="btn btn-default btn-xs pmv-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs pmv-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs pmv-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs pmv-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs pmv-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== LOADER ===== -->
<div id="pmv_loader" style="display:none;" class="pmv-loader-wrap">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <span>Procesando...</span>
</div>

<!-- ===== RESULTADO ===== -->
<div id="pmv_resultado" style="display:none;">

    <!-- ── SECCIÓN 1: KPIs ── -->
    <div class="pmv-section-title">
        <i class="fa fa-bar-chart"></i> Resumen Ejecutivo
    </div>
    <div class="pmv-cards">
        <div class="pmv-card pmv-card-blue">
            <div class="pmv-card-icon"><i class="fa fa-cubes"></i></div>
            <div class="pmv-card-body">
                <div class="pmv-card-val" id="pmv_card_unidades">—</div>
                <div class="pmv-card-lbl">Unidades vendidas</div>
            </div>
        </div>
        <div class="pmv-card pmv-card-teal">
            <div class="pmv-card-icon"><i class="fa fa-list-ol"></i></div>
            <div class="pmv-card-body">
                <div class="pmv-card-val" id="pmv_card_productos">—</div>
                <div class="pmv-card-lbl">Productos distintos</div>
            </div>
        </div>
        <div class="pmv-card pmv-card-gold">
            <div class="pmv-card-icon"><i class="fa fa-trophy"></i></div>
            <div class="pmv-card-body">
                <div class="pmv-card-val pmv-card-val-sm" id="pmv_card_lider">—</div>
                <div class="pmv-card-lbl">Producto líder</div>
            </div>
        </div>
        <div class="pmv-card pmv-card-purple">
            <div class="pmv-card-icon"><i class="fa fa-pie-chart"></i></div>
            <div class="pmv-card-body">
                <div class="pmv-card-val" id="pmv_card_top10">—</div>
                <div class="pmv-card-lbl">Concentración Top 10</div>
            </div>
        </div>
        <div class="pmv-card pmv-card-variacion" id="pmv_card_variacion_wrap">
            <div class="pmv-card-icon"><i class="fa fa-line-chart"></i></div>
            <div class="pmv-card-body">
                <div class="pmv-card-val" id="pmv_card_variacion">—</div>
                <div class="pmv-card-lbl">Variación vs período anterior</div>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 2: RANKING ── -->
    <div class="pmv-section-title">
        <i class="fa fa-sort-amount-desc"></i> Ranking de productos
    </div>
    <div class="pmv-panel">
        <div class="table-responsive">
            <table id="pmv_tabla_ranking" class="table table-striped table-bordered pmv-tabla" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Categoría</th>
                        <th class="text-right">Unidades</th>
                        <th class="text-right" title="Porcentaje que representa este producto sobre el total de unidades vendidas en el período">Part. % <i class="fa fa-info-circle pmv-th-info"></i></th>
                        <th class="text-right" title="Variación de unidades vendidas respecto al período anterior de igual duración (ej: si eliges marzo, compara con febrero)">Var. % <i class="fa fa-info-circle pmv-th-info"></i></th>
                        <th class="text-center" title="Cambio de posición en el ranking comparado con el período anterior. ▲ subió puestos, ▼ bajó puestos, ★ Nuevo = no figuraba antes">Cambio rank. <i class="fa fa-info-circle pmv-th-info"></i></th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Días cob.</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- ── SECCIÓN 3: GRÁFICOS ── -->
    <div class="pmv-section-title">
        <i class="fa fa-bar-chart"></i> Análisis gráfico
    </div>
    <div class="pmv-row-charts">
        <div class="pmv-panel pmv-chart-bar-wrap">
            <div class="pmv-panel-header">Top 10 — Unidades vendidas</div>
            <div class="pmv-canvas-wrap" id="pmvBarWrap">
                <canvas id="pmvBarChart"></canvas>
            </div>
        </div>
        <div class="pmv-panel pmv-chart-donut-wrap">
            <div class="pmv-panel-header">Distribución Top 10 vs Resto</div>
            <div class="pmv-canvas-wrap" id="pmvDonutWrap">
                <canvas id="pmvDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 4: ANÁLISIS OPERATIVO ── -->
    <div class="pmv-section-title">
        <i class="fa fa-exclamation-triangle"></i> Análisis operativo
    </div>
    <div class="pmv-row-operativo">
        <!-- Stock crítico -->
        <div class="pmv-panel pmv-panel-critico">
            <div class="pmv-panel-header">
                <i class="fa fa-warning"></i> Stock en riesgo (Top 20)
                <i class="fa fa-info-circle pmv-th-info"
                   title="Muestra los productos del Top 20 en ventas que podrían quedarse sin stock. Crítico = menos de 3 días de cobertura o stock en 0. Riesgo = entre 3 y 7 días. Los días de cobertura se calculan como: (stock actual × días del período) ÷ unidades vendidas."></i>
            </div>
            <div id="pmv_stock_critico_body">
                <p class="pmv-empty-msg">Sin alertas de stock.</p>
            </div>
        </div>
        <!-- Movimientos del top -->
        <div class="pmv-panel pmv-panel-movimientos">
            <div class="pmv-panel-header">
                <i class="fa fa-exchange"></i> Movimientos en el Top 10
            </div>
            <div id="pmv_nuevos_top">
                <div class="pmv-mov-subtitulo"><i class="fa fa-arrow-circle-up pmv-icon-up"></i> Entraron al Top 10</div>
                <div id="pmv_nuevos_list" class="pmv-mov-list">—</div>
            </div>
            <div id="pmv_salidos_top" style="margin-top:12px;">
                <div class="pmv-mov-subtitulo"><i class="fa fa-arrow-circle-down pmv-icon-down"></i> Salieron del Top 10</div>
                <div id="pmv_salidos_list" class="pmv-mov-list">—</div>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 6: HALLAZGOS ── -->
    <div id="pmv_hallazgos_wrap" class="pmv-section-title" style="margin-top:18px;">
        <i class="fa fa-lightbulb-o"></i> Hallazgos automáticos
    </div>
    <div class="pmv-panel pmv-panel-hallazgos">
        <ul id="pmv_hallazgos_list" class="pmv-hallazgos-ul"></ul>
    </div>

</div><!-- fin #pmv_resultado -->

<script>var PMV_TIPO_NEGOCIO = "{{ $tipoNegocio }}";</script>
<script src="/js/reportes/prods_mas_vendidos.js"></script>
