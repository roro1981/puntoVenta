<head>
    <link rel="stylesheet" href="/css/reportes/vtas_mesa.css" />
</head>

<!-- ===== FILTRO ===== -->
<div class="vm-filtro">
    <div class="vm-filtro-inner">
        <div class="vm-filtro-group">
            <label>Desde</label>
            <input type="text" id="vm_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vm-filtro-group">
            <label>Hasta</label>
            <input type="text" id="vm_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vm-filtro-actions">
            <button id="btn_vm_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_vm_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <!-- accesos rápidos -->
        <div class="vm-atajos">
            <button class="btn btn-default btn-xs vm-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs vm-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs vm-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs vm-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs vm-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== RESULTADO ===== -->
<div id="vm_resultado" style="display:none;">

    <!-- CARDS -->
    <div class="vm-cards">
        <div class="vm-card">
            <div class="vm-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="vm-card-body">
                <div class="vm-card-label">Total vendido</div>
                <div class="vm-card-value" id="vm_total_ventas">—</div>
            </div>
        </div>
        <div class="vm-card">
            <div class="vm-card-icon" style="background:#27ae60;"><i class="fa fa-list-alt"></i></div>
            <div class="vm-card-body">
                <div class="vm-card-label">Comandas cerradas</div>
                <div class="vm-card-value" id="vm_total_comandas">—</div>
            </div>
        </div>
        <div class="vm-card">
            <div class="vm-card-icon" style="background:#8e44ad;"><i class="fa fa-users"></i></div>
            <div class="vm-card-body">
                <div class="vm-card-label">Total comensales</div>
                <div class="vm-card-value" id="vm_total_comensales">—</div>
            </div>
        </div>
        <div class="vm-card">
            <div class="vm-card-icon" style="background:#e67e22;"><i class="fa fa-line-chart"></i></div>
            <div class="vm-card-body">
                <div class="vm-card-label">Ticket promedio</div>
                <div class="vm-card-value" id="vm_ticket_promedio">—</div>
            </div>
        </div>
        <div class="vm-card vm-card-wide">
            <div class="vm-card-icon" style="background:#c0392b;"><i class="fa fa-trophy"></i></div>
            <div class="vm-card-body">
                <div class="vm-card-label">Mesa más activa</div>
                <div class="vm-card-value vm-card-value-sm" id="vm_mesa_destacada">—</div>
            </div>
        </div>
    </div>

    <!-- RANKING + DONUT -->
    <div class="vm-row-2">
        <!-- Tabla ranking -->
        <div class="vm-panel vm-panel-wide">
            <h4>Ranking de mesas</h4>
            <div class="table-responsive">
                <table class="table table-hover table-condensed vm-table" id="vm_tabla_ranking">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mesa</th>
                            <th class="text-right">Cap.</th>
                            <th class="text-right">Comandas</th>
                            <th class="text-right">Comensales</th>
                            <th class="text-right">Total vendido</th>
                            <th class="text-right">% del total</th>
                            <th class="text-right">Ticket prom.</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <!-- Donut distribución -->
        <div class="vm-panel">
            <h4>Distribución por mesa</h4>
            <div class="vm-chart-wrap-donut">
                <canvas id="vmDonutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- COMPARATIVA BARRAS + COMENSALES -->
    <div class="vm-row-2">
        <div class="vm-panel">
            <h4>Comparativa de ventas</h4>
            <div class="vm-chart-wrap-bar">
                <canvas id="vmBarChart"></canvas>
            </div>
        </div>
        <div class="vm-panel">
            <h4>Comensales por mesa</h4>
            <div class="vm-chart-wrap-bar">
                <canvas id="vmComensalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- TENDENCIA DIARIA -->
    <div class="vm-panel vm-panel-full">
        <h4>Evolución diaria</h4>
        <div class="vm-chart-wrap">
            <canvas id="vmTendenciaChart"></canvas>
        </div>
    </div>

    <!-- DETALLE DATATABLES -->
    <div class="vm-panel vm-panel-full">
        <h4>Detalle de comandas</h4>
        <div class="table-responsive">
            <table class="table table-hover vm-table-dt" id="vm_tabla_detalle" style="width:100%">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha cierre</th>
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
<div id="vm_spinner" style="display:none;text-align:center;padding:40px;">
    <i class="fa fa-spinner fa-spin fa-2x" style="color:#3c8dbc;"></i>
    <div style="margin-top:8px;color:#5f7381;">Cargando datos...</div>
</div>

<script src="/js/reportes/vtas_mesa.js"></script>
