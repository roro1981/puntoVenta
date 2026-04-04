<head>
    <link rel="stylesheet" href="/css/reportes/vtas_vendedor.css" />
</head>

<input type="hidden" id="tipo_negocio" value="{{ $tipoNegocio }}" />

<!-- ===== FILTRO ===== -->
<div class="vv-filtro">
    <div class="vv-filtro-inner">
        <div class="vv-filtro-group">
            <label>Desde</label>
            <input type="text" id="vv_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vv-filtro-group">
            <label>Hasta</label>
            <input type="text" id="vv_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vv-filtro-group vv-filtro-group-wide">
            <label>Vendedor</label>
            <select id="vv_select_vendedor" class="form-control">
                <option value="">— Todos —</option>
            </select>
        </div>
        <div class="vv-filtro-actions">
            <button id="btn_vv_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_vv_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <!-- accesos rápidos -->
        <div class="vv-atajos">
            <button class="btn btn-default btn-xs vv-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs vv-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs vv-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs vv-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs vv-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== RESULTADO ===== -->
<div id="vv_resultado" style="display:none;">

    <!-- CARDS -->
    <div class="vv-cards">
        <div class="vv-card">
            <div class="vv-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="vv-card-body">
                <div class="vv-card-label">Total vendido</div>
                <div class="vv-card-value" id="vv_total_ventas">—</div>
            </div>
        </div>
        <div class="vv-card">
            <div class="vv-card-icon" style="background:#27ae60;"><i class="fa fa-ticket"></i></div>
            <div class="vv-card-body">
                <div class="vv-card-label">Transacciones</div>
                <div class="vv-card-value" id="vv_total_tickets">—</div>
            </div>
        </div>
        <div class="vv-card">
            <div class="vv-card-icon" style="background:#8e44ad;"><i class="fa fa-line-chart"></i></div>
            <div class="vv-card-body">
                <div class="vv-card-label">Ticket promedio</div>
                <div class="vv-card-value" id="vv_ticket_promedio">—</div>
            </div>
        </div>
        <div class="vv-card" id="vv_card_destacado">
            <div class="vv-card-icon" style="background:#e67e22;"><i class="fa fa-trophy"></i></div>
            <div class="vv-card-body">
                <div class="vv-card-label">Vendedor destacado</div>
                <div class="vv-card-value vv-card-value-sm" id="vv_destacado">—</div>
            </div>
        </div>
    </div>

    <!-- RANKING + GRÁFICO BARRAS (solo cuando "Todos") -->
    <div id="vv_seccion_ranking">
        <div class="vv-row-2">
            <div class="vv-panel vv-panel-wide">
                <h4>Ranking de vendedores</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-condensed vv-table" id="vv_tabla_ranking">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Vendedor</th>
                                <th class="text-right">Transacciones</th>
                                <th class="text-right">Total vendido</th>
                                <th class="text-right">% del total</th>
                                <th class="text-right">Promedio</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="vv-panel">
                <h4>Comparativa</h4>
                <div class="vv-chart-wrap-bar">
                    <canvas id="vvBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TENDENCIA DIARIA -->
    <div class="vv-panel vv-panel-full">
        <h4 id="vv_titulo_tendencia">Evolución diaria de ventas</h4>
        <div class="vv-chart-wrap">
            <canvas id="vvTendenciaChart"></canvas>
        </div>
    </div>

    <!-- DETALLE DATATABLES -->
    <div class="vv-panel vv-panel-full">
        <h4>Detalle de ventas</h4>
        <div class="table-responsive">
            <table class="table table-hover vv-table-dt" id="vv_tabla_detalle" style="width:100%">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Forma de pago</th>
                        <th class="text-right">Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===== SPINNER ===== -->
<div id="vv_spinner" style="display:none;text-align:center;padding:40px;">
    <i class="fa fa-spinner fa-spin fa-2x" style="color:#3c8dbc;"></i>
    <div style="margin-top:8px;color:#5f7381;">Cargando datos...</div>
</div>

<script src="/js/reportes/vtas_vendedor.js"></script>
