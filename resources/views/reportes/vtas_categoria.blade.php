<head>
    <link rel="stylesheet" href="/css/reportes/vtas_categoria.css" />
</head>

<input type="hidden" id="tipo_negocio" value="{{ $tipoNegocio }}" />

<!-- ===== FILTRO ===== -->
<div class="vc-filtro">
    <div class="vc-filtro-inner">
        <div class="vc-filtro-group">
            <label>Desde</label>
            <input type="text" id="vc_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vc-filtro-group">
            <label>Hasta</label>
            <input type="text" id="vc_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vc-filtro-actions">
            <button id="btn_vc_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_vc_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <!-- Accesos rápidos -->
        <div class="vc-atajos">
            <button class="btn btn-default btn-xs vc-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs vc-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs vc-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs vc-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs vc-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== LOADER ===== -->
<div id="vc_loader" style="display:none;" class="vc-loader-wrap">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <span>Procesando...</span>
</div>

<!-- ===== RESULTADO ===== -->
<div id="vc_resultado" style="display:none;">

    <!-- ── SECCIÓN 1: KPIs ── -->
    <div class="vc-section-title">
        <i class="fa fa-bar-chart"></i> Resumen ejecutivo
    </div>
    <div class="vc-cards">
        <div class="vc-card">
            <div class="vc-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="vc-card-body">
                <div class="vc-card-label">Total en ventas</div>
                <div class="vc-card-value" id="vc_card_total">—</div>
            </div>
        </div>
        <div class="vc-card">
            <div class="vc-card-icon" style="background:#27ae60;"><i class="fa fa-tags"></i></div>
            <div class="vc-card-body">
                <div class="vc-card-label">Categorías con ventas</div>
                <div class="vc-card-value" id="vc_card_categorias">—</div>
            </div>
        </div>
        <div class="vc-card">
            <div class="vc-card-icon" style="background:#f39c12;"><i class="fa fa-trophy"></i></div>
            <div class="vc-card-body">
                <div class="vc-card-label">Categoría líder</div>
                <div class="vc-card-value vc-card-value-sm" id="vc_card_lider">—</div>
                <div style="font-size:11px;color:#aaa;" id="vc_card_lider_pct"></div>
            </div>
        </div>
        <div class="vc-card vc-card-variacion" id="vc_card_variacion_wrap">
            <div class="vc-card-icon" style="background:#8e44ad;"><i class="fa fa-line-chart"></i></div>
            <div class="vc-card-body">
                <div class="vc-card-label">Variación total ingresos</div>
                <div class="vc-card-value" id="vc_card_variacion">—</div>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 2: TABLA + DONA ── -->
    <div class="vc-section-title">
        <i class="fa fa-sort-amount-desc"></i> Ranking por categoría
    </div>
    <div class="vc-row-2">
        <div class="vc-panel vc-panel-wide">
            <div class="table-responsive">
                <table class="table table-hover table-condensed vc-tabla" id="vc_tabla">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Categoría</th>
                            <th class="text-right">Unidades</th>
                            <th class="text-right">Ingresos</th>
                            <th title="Porcentaje sobre el total de ingresos del período">
                                Participación <i class="fa fa-info-circle vc-th-info"></i>
                            </th>
                            <th title="Variación respecto al período anterior de igual duración">
                                Var. % <i class="fa fa-info-circle vc-th-info"></i>
                            </th>
                            <th class="text-center" title="Cambio de posición en el ranking vs período anterior">
                                Rank <i class="fa fa-info-circle vc-th-info"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="vc-panel vc-panel-narrow">
            <h4>Distribución</h4>
            <div class="vc-dona-wrap">
                <canvas id="vcDonaChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ── SECCIÓN 3: TENDENCIA TOP 5 ── -->
    <div class="vc-section-title">
        <i class="fa fa-line-chart"></i> Evolución diaria — Top 5 categorías
    </div>
    <div class="vc-panel vc-chart-full">
        <div class="vc-chart-wrap">
            <canvas id="vcTendenciaChart"></canvas>
        </div>
    </div>

    <!-- ── SECCIÓN 4: HALLAZGOS ── -->
    <div class="vc-section-title">
        <i class="fa fa-lightbulb-o"></i> Hallazgos automáticos
    </div>
    <div class="vc-panel">
        <ul id="vc_hallazgos_list" class="vc-hallazgos-ul"></ul>
    </div>

</div><!-- fin #vc_resultado -->

<script src="/js/reportes/vtas_categoria.js"></script>
