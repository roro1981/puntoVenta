<head>
    <link rel="stylesheet" href="/css/reportes/prods_rentables.css" />
</head>

<!-- ===== FILTRO ===== -->
<div class="pr-filtro">
    <div class="pr-filtro-inner">
        <div class="pr-filtro-group">
            <label>Desde</label>
            <input type="text" id="pr_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="pr-filtro-group">
            <label>Hasta</label>
            <input type="text" id="pr_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="pr-filtro-group pr-filtro-group-select">
            <label>Categoría</label>
            <select id="pr_select_categoria" class="form-control">
                <option value="">Todas las categorías</option>
            </select>
        </div>
        <div class="pr-filtro-actions">
            <button id="btn_pr_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_pr_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div class="pr-atajos">
            <button class="btn btn-default btn-xs pr-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs pr-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs pr-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs pr-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs pr-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== LOADER ===== -->
<div id="pr_loader" style="display:none;" class="pr-loader-wrap">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <span>Procesando...</span>
</div>

<!-- ===== RESULTADO ===== -->
<div id="pr_resultado" style="display:none;">

    <!-- ── SECCIÓN 1: KPIs ── -->
    <div class="pr-section-title">
        <i class="fa fa-dollar"></i> Resumen de rentabilidad
    </div>
    <div class="pr-cards">
        <div class="pr-card pr-card-blue">
            <div class="pr-card-icon"><i class="fa fa-money"></i></div>
            <div class="pr-card-body">
                <div class="pr-card-val" id="pr_card_ingresos">—</div>
                <div class="pr-card-lbl">Ingresos totales</div>
            </div>
        </div>
        <div class="pr-card pr-card-red">
            <div class="pr-card-icon"><i class="fa fa-shopping-cart"></i></div>
            <div class="pr-card-body">
                <div class="pr-card-val" id="pr_card_costo">—</div>
                <div class="pr-card-lbl">Costo total</div>
            </div>
        </div>
        <div class="pr-card pr-card-green">
            <div class="pr-card-icon"><i class="fa fa-line-chart"></i></div>
            <div class="pr-card-body">
                <div class="pr-card-val" id="pr_card_utilidad">—</div>
                <div class="pr-card-lbl">Utilidad bruta</div>
            </div>
        </div>
        <div class="pr-card pr-card-purple">
            <div class="pr-card-icon"><i class="fa fa-pie-chart"></i></div>
            <div class="pr-card-body">
                <div class="pr-card-val" id="pr_card_margen">—</div>
                <div class="pr-card-lbl">Margen global</div>
            </div>
        </div>
        <div class="pr-card pr-card-gold">
            <div class="pr-card-icon"><i class="fa fa-trophy"></i></div>
            <div class="pr-card-body">
                <div class="pr-card-val pr-card-val-sm" id="pr_card_lider">—</div>
                <div class="pr-card-lbl">Mayor utilidad bruta</div>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 2: RANKING ── -->
    <div class="pr-section-title">
        <i class="fa fa-sort-amount-desc"></i> Ranking de rentabilidad
    </div>
    <div class="pr-panel">
        <div class="table-responsive">
            <table id="pr_tabla_ranking" class="table table-striped table-bordered pr-tabla" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>Categoría</th>
                        <th class="text-right">Unidades</th>
                        <th class="text-right">Ingresos</th>
                        <th class="text-right">Costo</th>
                        <th class="text-right">Utilidad</th>
                        <th class="text-right"
                            title="Porcentaje de ganancia neta sobre los ingresos: (Utilidad ÷ Ingresos) × 100. Excelente ≥40%, Bueno 20–39%, Bajo 5–19%, Crítico <5%">
                            Margen % <i class="fa fa-info-circle pr-th-info"></i>
                        </th>
                        <th class="text-right"
                            title="Variación de la utilidad en $ respecto al período anterior de igual duración">
                            Var. Util. % <i class="fa fa-info-circle pr-th-info"></i>
                        </th>
                        <th class="text-center"
                            title="Cambio de posición en el ranking vs el período anterior. ▲ subió, ▼ bajó, ★ Nuevo = no figuraba antes">
                            Cambio rank. <i class="fa fa-info-circle pr-th-info"></i>
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- ── SECCIÓN 3: GRÁFICOS ── -->
    <div class="pr-section-title">
        <i class="fa fa-bar-chart"></i> Análisis gráfico
    </div>
    <div class="pr-row-charts">
        <div class="pr-panel pr-chart-bar-wrap">
            <div class="pr-panel-header">Top 10 — Utilidad bruta ($)</div>
            <div class="pr-canvas-wrap" id="prBarWrap">
                <canvas id="prBarChart"></canvas>
            </div>
        </div>
        <div class="pr-panel pr-chart-donut-wrap">
            <div class="pr-panel-header">Utilidad por categoría</div>
            <div class="pr-canvas-wrap" id="prDonutWrap">
                <canvas id="prDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 4: ANÁLISIS OPERATIVO ── -->
    <div class="pr-section-title">
        <i class="fa fa-star"></i> Análisis operativo
    </div>
    <div class="pr-row-operativo">
        <!-- Estrellas: mayor margen % -->
        <div class="pr-panel pr-panel-estrellas">
            <div class="pr-panel-header">
                <i class="fa fa-star pr-icon-star"></i> Top 5 por margen %
                <i class="fa fa-info-circle pr-th-info"
                   title="Productos con mejor margen porcentual (mínimo 5 unidades vendidas). Son los más eficientes en rentabilidad relativa."></i>
            </div>
            <div id="pr_estrellas_body">
                <p class="pr-empty-msg">Sin datos.</p>
            </div>
        </div>
        <!-- Alertas: margen crítico -->
        <div class="pr-panel pr-panel-alertas">
            <div class="pr-panel-header">
                <i class="fa fa-exclamation-triangle pr-icon-alert"></i> Margen crítico
                <i class="fa fa-info-circle pr-th-info"
                   title="Productos con margen menor al 5%. Venden pero aportan muy poca ganancia. Revise el precio de venta o el costo de compra."></i>
            </div>
            <div id="pr_alertas_body">
                <p class="pr-empty-msg">Sin alertas.</p>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 5: HALLAZGOS ── -->
    <div class="pr-section-title" style="margin-top:18px;">
        <i class="fa fa-lightbulb-o"></i> Hallazgos automáticos
    </div>
    <div class="pr-panel pr-panel-hallazgos">
        <ul id="pr_hallazgos_list" class="pr-hallazgos-ul"></ul>
    </div>

</div><!-- fin #pr_resultado -->

<script>var PR_TIPO_NEGOCIO = "{{ $tipoNegocio }}";</script>
<script src="/js/reportes/prods_rentables.js"></script>
