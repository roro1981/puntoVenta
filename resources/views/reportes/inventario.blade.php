<head>
    <link rel="stylesheet" href="/css/reportes/inventario.css" />
</head>

<input type="hidden" id="tipo_negocio" value="{{ $tipoNegocio }}" />

<!-- ===== TOOLBAR / FILTROS ===== -->
<div class="inv-toolbar">
    <div class="inv-toolbar-group">
        <label>Categoría</label>
        <select id="inv_filter_cat" class="form-control input-sm">
            <option value="">Todas las categorías</option>
        </select>
    </div>
    <div class="inv-toolbar-group">
        <label>Estado</label>
        <select id="inv_filter_estado" class="form-control input-sm">
            <option value="">Todos los estados</option>
            <option value="Normal">Normal</option>
            <option value="Crítico">Crítico</option>
            <option value="Agotado">Agotado</option>
            <option value="Sobrestock">Sobrestock</option>
        </select>
    </div>
    <div class="inv-toolbar-actions">
        <button id="btn_inv_exportar" class="btn btn-success" disabled>
            <i class="fa fa-file-excel-o"></i> Exportar Excel
        </button>
    </div>
</div>

<!-- ===== LOADER ===== -->
<div id="inv_loader" style="display:none;" class="inv-loader-wrap">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <span>Cargando inventario...</span>
</div>

<!-- ===== RESULTADO ===== -->
<div id="inv_resultado" style="display:none;">

    <!-- ── KPIs ── -->
    <div class="inv-section-title">
        <i class="fa fa-bar-chart"></i> Resumen ejecutivo
    </div>
    <div class="inv-cards">
        <div class="inv-card">
            <div class="inv-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
            <div class="inv-card-body">
                <div class="inv-card-label">Valor total inventario</div>
                <div class="inv-card-value" id="inv_card_valor">—</div>
            </div>
        </div>
        <div class="inv-card">
            <div class="inv-card-icon" style="background:#27ae60;"><i class="fa fa-cubes"></i></div>
            <div class="inv-card-body">
                <div class="inv-card-label">Productos activos</div>
                <div class="inv-card-value" id="inv_card_productos">—</div>
            </div>
        </div>
        <div class="inv-card">
            <div class="inv-card-icon" style="background:#e74c3c;"><i class="fa fa-ban"></i></div>
            <div class="inv-card-body">
                <div class="inv-card-label">Productos agotados</div>
                <div class="inv-card-value" id="inv_card_agotados">—</div>
            </div>
        </div>
        <div class="inv-card">
            <div class="inv-card-icon" style="background:#f39c12;"><i class="fa fa-warning"></i></div>
            <div class="inv-card-body">
                <div class="inv-card-label">Stock crítico</div>
                <div class="inv-card-value" id="inv_card_criticos">—</div>
            </div>
        </div>
    </div>

    <!-- ── TABLA COMPLETA ── -->
    <div class="inv-section-title">
        <i class="fa fa-table"></i> Detalle de productos
    </div>
    <div class="inv-panel">
        <div class="table-responsive">
            <table id="inv_tabla" class="table table-hover table-condensed inv-tabla" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th title="Stock actual vs stock mínimo">Stock actual</th>
                        <th class="text-right" title="Stock mínimo configurado">Mín. <i class="fa fa-info-circle inv-th-info"></i></th>
                        <th class="text-right">Valor inv.</th>
                        <th class="text-right" title="Unidades vendidas en los últimos 30 días">Vtas 30d <i class="fa fa-info-circle inv-th-info"></i></th>
                        <th class="text-right" title="Días estimados hasta agotar stock al ritmo actual de ventas">Cob. <i class="fa fa-info-circle inv-th-info"></i></th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- ── ALERTAS ── -->
    <div class="inv-section-title">
        <i class="fa fa-exclamation-triangle"></i> Alertas prioritarias
    </div>
    <div class="inv-row-alertas">
        <div class="inv-panel inv-panel-alerta">
            <h4><i class="fa fa-ban" style="color:#e74c3c;margin-right:5px;"></i> Productos agotados</h4>
            <div id="inv_alertas_agotados"></div>
        </div>
        <div class="inv-panel inv-panel-alerta">
            <h4><i class="fa fa-warning" style="color:#f39c12;margin-right:5px;"></i> Stock crítico (bajo el mínimo)</h4>
            <div id="inv_alertas_criticos"></div>
        </div>
    </div>

    <!-- ── GRÁFICO + HALLAZGOS ── -->
    <div class="inv-section-title">
        <i class="fa fa-pie-chart"></i> Valor por categoría &amp; Hallazgos
    </div>
    <div class="inv-row-bottom">
        <div class="inv-panel inv-panel-chart">
            <h4>Capital inmovilizado por categoría (Top 10)</h4>
            <div class="inv-chart-wrap">
                <canvas id="invCatChart"></canvas>
            </div>
        </div>
        <div class="inv-panel inv-panel-hallazgos">
            <h4><i class="fa fa-lightbulb-o"></i> Hallazgos automáticos</h4>
            <ul id="inv_hallazgos_list" class="inv-hallazgos-ul"></ul>
        </div>
    </div>

</div><!-- fin #inv_resultado -->

<script src="/js/reportes/inventario.js"></script>
