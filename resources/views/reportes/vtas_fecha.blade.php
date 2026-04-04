<head>
    <link rel="stylesheet" href="/css/reportes/vtas_fecha.css" />
</head>

<input type="hidden" id="tipo_negocio" value="{{ $tipoNegocio }}" />

<!-- ===== FILTRO ===== -->
<div class="vf-filtro">
    <div class="vf-filtro-inner">
        <div class="vf-filtro-group">
            <label>Desde</label>
            <input type="text" id="vf_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vf-filtro-group">
            <label>Hasta</label>
            <input type="text" id="vf_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="vf-filtro-actions">
            <button id="btn_vf_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_vf_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <!-- accesos rápidos -->
        <div class="vf-atajos">
            <button class="btn btn-default btn-xs vf-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs vf-atajo" data-rango="ayer">Ayer</button>
            <button class="btn btn-default btn-xs vf-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs vf-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs vf-atajo" data-rango="mes_anterior">Mes anterior</button>
        </div>
    </div>
</div>

<!-- ===== RESULTADO (oculto hasta generar) ===== -->
<div id="vf_resultado" style="display:none;">

    <!-- CARDS -->
    <div class="vf-cards">
        <div class="vf-card">
            <div class="vf-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="vf-card-body">
                <div class="vf-card-label">Total Ventas</div>
                <div class="vf-card-value" id="vf_total_ventas">—</div>
            </div>
        </div>
        <div class="vf-card">
            <div class="vf-card-icon" style="background:#27ae60;"><i class="fa fa-ticket"></i></div>
            <div class="vf-card-body">
                <div class="vf-card-label" id="lbl_tickets">Tickets emitidos</div>
                <div class="vf-card-value" id="vf_total_tickets">—</div>
            </div>
        </div>
        <div class="vf-card">
            <div class="vf-card-icon" style="background:#8e44ad;"><i class="fa fa-line-chart"></i></div>
            <div class="vf-card-body">
                <div class="vf-card-label">Ticket promedio</div>
                <div class="vf-card-value" id="vf_ticket_promedio">—</div>
            </div>
        </div>
        <div class="vf-card">
            <div class="vf-card-icon" style="background:#e67e22;"><i class="fa fa-calendar"></i></div>
            <div class="vf-card-body">
                <div class="vf-card-label">Promedio diario</div>
                <div class="vf-card-value" id="vf_promedio_diario">—</div>
                <div class="vf-card-note" id="vf_dias_periodo"></div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO TENDENCIA -->
    <div class="vf-panel vf-panel-full">
        <h4>
            Tendencia de ventas del periodo
            <i class="fa fa-question-circle" style="margin-left:6px;color:#9aabb6;cursor:pointer;font-size:13px;"
               data-toggle="tooltip" data-placement="top"
               title="Muestra el total vendido por día en el rango seleccionado. Las barras representan el monto y la línea el número de tickets/comandas."></i>
        </h4>
        <div class="vf-chart-wrap">
            <canvas id="vfTendenciaChart"></canvas>
        </div>
    </div>

</div>

<!-- ===== SPINNER ===== -->
<div id="vf_spinner" style="display:none;text-align:center;padding:40px;">
    <i class="fa fa-spinner fa-spin fa-2x" style="color:#3c8dbc;"></i>
    <div style="margin-top:8px;color:#5f7381;">Cargando datos...</div>
</div>

<script src="/js/reportes/vtas_fecha.js"></script>
