<head>
    <link rel="stylesheet" href="/css/reportes/vtas_garzon.css" />
</head>

<!-- ===== FILTRO ===== -->
<div class="vg-filtro">
    <div class="vg-filtro-inner">
        <div class="vg-filtro-group">
            <label>Desde</label>
            <input type="text" id="vg_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vg-filtro-group">
            <label>Hasta</label>
            <input type="text" id="vg_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vg-filtro-group vg-filtro-group-wide">
            <label>Garzón</label>
            <select id="vg_select_garzon" class="form-control">
                <option value="">— Todos —</option>
            </select>
        </div>
        <div class="vg-filtro-actions">
            <button id="btn_vg_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_vg_exportar_ventas" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Ventas
            </button>
            <button id="btn_vg_exportar_propinas" class="btn btn-warning" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Propinas
            </button>
        </div>
        <!-- accesos rápidos -->
        <div class="vg-atajos">
            <button class="btn btn-default btn-xs vg-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs vg-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs vg-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs vg-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs vg-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== RESULTADO ===== -->
<div id="vg_resultado" style="display:none;">

    <!-- CARDS -->
    <div class="vg-cards">
        <div class="vg-card">
            <div class="vg-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="vg-card-body">
                <div class="vg-card-label">Total vendido</div>
                <div class="vg-card-value" id="vg_total_ventas">—</div>
            </div>
        </div>
        <div class="vg-card">
            <div class="vg-card-icon" style="background:#27ae60;"><i class="fa fa-list-alt"></i></div>
            <div class="vg-card-body">
                <div class="vg-card-label">Comandas cerradas</div>
                <div class="vg-card-value" id="vg_total_comandas">—</div>
            </div>
        </div>
        <div class="vg-card">
            <div class="vg-card-icon" style="background:#16a085;"><i class="fa fa-hand-o-up"></i></div>
            <div class="vg-card-body">
                <div class="vg-card-label">Total propinas</div>
                <div class="vg-card-value" id="vg_total_propinas">—</div>
            </div>
        </div>
        <div class="vg-card">
            <div class="vg-card-icon" style="background:#e67e22;"><i class="fa fa-trophy"></i></div>
            <div class="vg-card-body">
                <div class="vg-card-label">Garzón destacado</div>
                <div class="vg-card-value vg-card-value-sm" id="vg_destacado">—</div>
            </div>
        </div>
    </div>

    <!-- RANKING + GRÁFICO BARRAS -->
    <div class="vg-row-2">
        <div class="vg-panel vg-panel-wide">
            <h4>Ranking de garzones</h4>
            <div class="table-responsive">
                <table class="table table-hover table-condensed vg-table" id="vg_tabla_ranking">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Garzón</th>
                            <th class="text-right">Comandas</th>
                            <th class="text-right">Mesas</th>
                            <th class="text-right">Comensales</th>
                            <th class="text-right">Total vendido</th>
                            <th class="text-right">% del total</th>
                            <th class="text-right">Propinas</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="vg-panel">
            <h4>Comparativa</h4>
            <div class="vg-chart-wrap-bar">
                <canvas id="vgBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- TENDENCIA DIARIA -->
    <div class="vg-panel vg-panel-full">
        <h4>Evolución diaria de ventas</h4>
        <div class="vg-chart-wrap">
            <canvas id="vgTendenciaChart"></canvas>
        </div>
    </div>

    <!-- PROPINAS POR GARZÓN -->
    <div class="vg-panel vg-panel-full" id="vg_seccion_propinas">
        <h4><i class="fa fa-hand-o-up" style="color:#16a085;margin-right:6px;"></i>Propinas por garzón</h4>
        <div class="table-responsive">
            <table class="table table-hover table-condensed vg-table" id="vg_tabla_propinas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Garzón</th>
                        <th class="text-right">Comandas con propina</th>
                        <th class="text-right">Total propinas</th>
                        <th class="text-right">Propina promedio</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <p id="vg_sin_propinas" class="vg-empty" style="display:none;">
            No hay comandas con propina en el periodo seleccionado.
        </p>
    </div>

    <!-- DETALLE DATATABLES -->
    <div class="vg-panel vg-panel-full">
        <h4>Detalle de comandas</h4>
        <div class="table-responsive">
            <table class="table table-hover vg-table-dt" id="vg_tabla_detalle" style="width:100%">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha cierre</th>
                        <th>Garzón</th>
                        <th>Mesa</th>
                        <th class="text-right">Comensales</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Propina</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===== SPINNER ===== -->
<div id="vg_spinner" style="display:none;text-align:center;padding:40px;">
    <i class="fa fa-spinner fa-spin fa-2x" style="color:#3c8dbc;"></i>
    <div style="margin-top:8px;color:#5f7381;">Cargando datos...</div>
</div>

<script src="/js/reportes/vtas_garzon.js"></script>
