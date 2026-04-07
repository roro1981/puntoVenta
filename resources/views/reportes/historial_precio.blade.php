<head>
    <link rel="stylesheet" href="/css/reportes/historial_precio.css" />
</head>

<input type="hidden" id="tipo_negocio" value="{{ $tipoNegocio }}" />

<!-- ===== TOOLBAR ===== -->
<div class="hp-toolbar">

    <div class="hp-toolbar-group">
        <label>Tipo</label>
        <select id="hp_tipo" class="form-control input-sm">
            <option value="PRODUCTO">Producto</option>
            @if($tipoNegocio === 'RESTAURANT')
            <option value="RECETA">Receta</option>
            @else
            <option value="PROMOCION">Promoción</option>
            @endif
        </select>
    </div>

    <div class="hp-toolbar-group hp-search-wrap">
        <label>Buscar</label>
        <input type="text" id="hp_buscar" class="form-control input-sm hp-buscar-input"
               placeholder="Código o nombre…" autocomplete="off" />
        <div id="hp_sugerencias" class="hp-sugerencias" style="display:none;"></div>
    </div>

    <div class="hp-toolbar-actions">
        <button id="btn_hp_exportar" class="btn btn-success" disabled>
            <i class="fa fa-file-excel-o"></i> Exportar Excel
        </button>
    </div>

</div>

<!-- ===== LOADER ===== -->
<div id="hp_loader" style="display:none;" class="hp-loader-wrap">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <span>Cargando historial…</span>
</div>

<!-- ===== NOMBRE ENTIDAD ===== -->
<div id="hp_nombre_entidad" class="hp-nombre-entidad" style="display:none;"></div>

<!-- ===== RESULTADO ===== -->
<div id="hp_resultado" style="display:none;">

    <!-- ── TABS ── -->
    <ul class="hp-tabs">
        <li class="hp-tab active" data-tab="precios">
            <i class="fa fa-line-chart"></i> Historial de precios
        </li>
        <li class="hp-tab" id="hp_tab_compras" data-tab="compras" style="display:none;">
            <i class="fa fa-shopping-cart"></i> Hist. compras
        </li>
    </ul>

    <!-- ── PANEL PRECIOS ── -->
    <div id="hp_pane_precios">

        <div class="hp-section-title">
            <i class="fa fa-table"></i> Historial de cambios &mdash; Precio de venta
        </div>
        <div id="hp_kpi_venta" class="hp-kpi-bar" style="display:none;"></div>
        <div class="hp-panel">
            <div class="table-responsive">
                <table id="hp_tabla_precios"
                       class="table table-hover table-condensed hp-tabla"
                       width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th class="text-right">Precio anterior</th>
                            <th class="text-right">Precio nuevo</th>
                            <th class="text-center">Variación</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="hp-section-title">
            <i class="fa fa-area-chart"></i> Evolución gráfica — Precio venta &amp; Precio compra neto
        </div>
        <div class="hp-panel">
            <div class="hp-chart-wrap">
                <canvas id="hpChart"></canvas>
            </div>
        </div>

    </div><!-- fin #hp_pane_precios -->

    <!-- ── PANEL COMPRAS ── -->
    <div id="hp_pane_compras" style="display:none;">

        {{-- Sección: cambios en precio de compra (historial_precios) --}}
        <div id="hp_pane_costos">
            <div class="hp-section-title">
                <i class="fa fa-tag"></i> Historial de cambios &mdash; Precio de compra neto
            </div>
            <div id="hp_kpi_costo" class="hp-kpi-bar" style="display:none;"></div>
            <div class="hp-panel">
                <div class="table-responsive">
                    <table id="hp_tabla_costos"
                           class="table table-hover table-condensed hp-tabla"
                           width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-right">Precio anterior</th>
                                <th class="text-right">Precio nuevo</th>
                                <th class="text-center">Variaci&oacute;n</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sección: facturas y boletas de compra (solo PRODUCTO) --}}
        <div id="hp_pane_docs_compra">
            <div class="hp-section-title">
                <i class="fa fa-shopping-basket"></i> Compras registradas (facturas / boletas)
            </div>
            <div id="hp_kpi_compras" class="hp-kpi-bar" style="display:none;"></div>
            <div class="hp-panel">
                <div class="table-responsive">
                    <table id="hp_tabla_compras"
                           class="table table-hover table-condensed hp-tabla"
                           width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center">Documento</th>
                                <th>Proveedor</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Precio unitario</th>
                                <th class="text-right">Descuento</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- fin #hp_pane_compras -->

</div><!-- fin #hp_resultado -->

<script src="/js/reportes/historial_precio.js"></script>
