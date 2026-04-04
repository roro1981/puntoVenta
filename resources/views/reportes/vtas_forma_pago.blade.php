<head>
    <link rel="stylesheet" href="/css/reportes/vtas_forma_pago.css" />
</head>

<input type="hidden" id="tipo_negocio" value="{{ $tipoNegocio }}" />

<!-- ===== FILTRO ===== -->
<div class="fp-filtro">
    <div class="fp-filtro-inner">
        <div class="fp-filtro-group">
            <label>Desde</label>
            <input type="text" id="fp_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="fp-filtro-group">
            <label>Hasta</label>
            <input type="text" id="fp_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="fp-filtro-actions">
            <button id="btn_fp_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_fp_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <!-- accesos rápidos -->
        <div class="fp-atajos">
            <button class="btn btn-default btn-xs fp-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs fp-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs fp-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs fp-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs fp-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== RESULTADO (oculto hasta generar) ===== -->
<div id="fp_resultado" style="display:none;">

    <!-- CARDS -->
    <div class="fp-cards">
        <div class="fp-card">
            <div class="fp-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="fp-card-body">
                <div class="fp-card-label">Total recaudado</div>
                <div class="fp-card-value" id="fp_total_ventas">—</div>
            </div>
        </div>
        <div class="fp-card">
            <div class="fp-card-icon" style="background:#27ae60;"><i class="fa fa-ticket"></i></div>
            <div class="fp-card-body">
                <div class="fp-card-label" id="fp_lbl_tickets">Transacciones</div>
                <div class="fp-card-value" id="fp_total_tickets">—</div>
            </div>
        </div>
        <div class="fp-card">
            <div class="fp-card-icon" style="background:#e67e22;"><i class="fa fa-star"></i></div>
            <div class="fp-card-body">
                <div class="fp-card-label">Forma dominante</div>
                <div class="fp-card-value fp-card-value-sm" id="fp_forma_dominante">—</div>
            </div>
        </div>
        <div class="fp-card">
            <div class="fp-card-icon" style="background:#8e44ad;"><i class="fa fa-line-chart"></i></div>
            <div class="fp-card-body">
                <div class="fp-card-label">Ticket promedio</div>
                <div class="fp-card-value" id="fp_ticket_promedio">—</div>
            </div>
        </div>
    </div>

    <!-- TABLA + GRÁFICO DONA -->
    <div class="fp-row-2">
        <div class="fp-panel fp-panel-wide">
            <h4>Detalle por forma de pago</h4>
            <div class="table-responsive">
                <table class="table table-hover table-condensed fp-table" id="fp_tabla">
                    <thead>
                        <tr>
                            <th>Forma de pago</th>
                            <th class="text-right">Transacciones</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">% del total</th>
                            <th class="text-right">Promedio</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="fp-panel">
            <h4>Distribución</h4>
            <div class="fp-dona-wrap">
                <canvas id="fpDonaChart"></canvas>
            </div>
        </div>
    </div>

    <!-- GRÁFICO TENDENCIA DIARIA -->
    <div class="fp-panel fp-panel-full">
        <h4>Evolución diaria por forma de pago</h4>
        <div class="fp-chart-wrap">
            <canvas id="fpTendenciaChart"></canvas>
        </div>
    </div>

</div>

<!-- ===== SPINNER ===== -->
<div id="fp_spinner" style="display:none;text-align:center;padding:40px;">
    <i class="fa fa-spinner fa-spin fa-2x" style="color:#3c8dbc;"></i>
    <div style="margin-top:8px;color:#5f7381;">Cargando datos...</div>
</div>

<script src="/js/reportes/vtas_forma_pago.js"></script>
