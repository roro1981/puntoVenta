<head>
    <link rel="stylesheet" href="css/reportes/movimientos_productos.css" />
    <script type="text/javascript" src="js/reportes/movimientos_productos.js"></script>
 </head>

{{-- ── Toolbar ─────────────────────────────────────────────── --}}
<div class="mv-toolbar">

    <div class="mv-toolbar-group mv-search-wrap">
        <label>Producto</label>
        <input type="text" id="mv_buscar" class="form-control input-sm mv-buscar-input"
               placeholder="Código o nombre…" autocomplete="off" />
        <div id="mv_sugerencias" class="mv-sugerencias" style="display:none;"></div>
    </div>

    <div class="mv-toolbar-group">
        <label>Tipo movimiento</label>
        <select class="form-control input-sm" id="tip_movi">
            <option value="1">TODOS</option>
            <option value="2">VENTAS</option>
            <option value="3">ENTRADAS</option>
            <option value="4">SALIDAS</option>
            <option value="5">MERMAS</option>
            <option value="6">FACTURAS COMPRA</option>
            <option value="7">BOLETAS COMPRA</option>
            <option value="8">ANULACIONES</option>
        </select>
    </div>

    <div class="mv-toolbar-group">
        <label>Desde</label>
        <input id="fecha_desde" class="form-control input-sm" readonly placeholder="dd/mm/aaaa" />
    </div>

    <div class="mv-toolbar-group">
        <label>Hasta</label>
        <input id="fecha_hasta" class="form-control input-sm" readonly placeholder="dd/mm/aaaa" />
    </div>

    <div class="mv-toolbar-group mv-toolbar-btns">
        <button class="btn btn-info btn-sm" id="btn_ver">
            <i class="fa fa-search"></i> Generar
        </button>
        <button class="btn btn-success btn-sm" id="excel" disabled>
            <i class="fa fa-file-excel-o"></i> Exportar
        </button>
    </div>

    <div class="mv-toolbar-separador"></div>

    <div class="mv-toolbar-group mv-fechas-rapidas-wrap">
        <label>Período rápido</label>
        <div class="mv-fechas-rapidas">
            <button type="button" class="btn-fecha-rapida" data-periodo="semana">Última semana</button>
            <button type="button" class="btn-fecha-rapida" data-periodo="mes">Último mes</button>
            <button type="button" class="btn-fecha-rapida" data-periodo="3meses">Últimos 3 meses</button>
            <button type="button" class="btn-fecha-rapida" data-periodo="6meses">Últimos 6 meses</button>
            <button type="button" class="btn-fecha-rapida" data-periodo="anio">Último año</button>
        </div>
    </div>

</div>

{{-- ── Loader ───────────────────────────────────────────────── --}}
<div id="mv_loader" class="mv-loader-wrap" style="display:none;">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
</div>

{{-- ── Nombre producto ──────────────────────────────────────── --}}
<div id="mv_nombre_producto" class="mv-nombre-producto" style="display:none;"></div>

{{-- ── Resultado ────────────────────────────────────────────── --}}
<div id="mv_resultado" style="display:none;">

    {{-- KPI bar --}}
    <div class="mv-kpi-bar">
        <div class="mv-kpi-item">
            <span class="mv-kpi-label"><i class="fa fa-cubes"></i> Stock actual</span>
            <span class="mv-kpi-valor" id="mv_kpi_stock">—</span>
        </div>
        <div class="mv-kpi-item kpi-entrada">
            <span class="mv-kpi-label"><i class="fa fa-arrow-down"></i> Entradas período</span>
            <span class="mv-kpi-valor" id="mv_kpi_entradas">—</span>
        </div>
        <div class="mv-kpi-item kpi-salida">
            <span class="mv-kpi-label"><i class="fa fa-arrow-up"></i> Salidas período</span>
            <span class="mv-kpi-valor" id="mv_kpi_salidas">—</span>
        </div>
        <div class="mv-kpi-item" id="mv_kpi_variacion_wrap">
            <span class="mv-kpi-label"><i class="fa fa-exchange"></i> Variación neta</span>
            <span class="mv-kpi-valor" id="mv_kpi_variacion">—</span>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="mv-panel">
        <table id="tbl_movis" class="table table-hover mv-tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo movimiento</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Stock resultante</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>
