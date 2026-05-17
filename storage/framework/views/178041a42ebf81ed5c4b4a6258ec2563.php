<head>
    <link rel="stylesheet" href="/css/reportes/anulaciones_comandas.css" />
</head>

<input type="hidden" id="ac_tipo_negocio" value="<?php echo e($tipoNegocio); ?>" />

<div class="ac-filtro">
    <div class="ac-filtro-inner">
        <div class="ac-filtro-group">
            <label>Desde</label>
            <input type="text" id="ac_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="ac-filtro-group">
            <label>Hasta</label>
            <input type="text" id="ac_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
        </div>
        <div class="ac-filtro-actions">
            <button id="btn_ac_generar" class="btn btn-info">
                <i class="fa fa-search"></i> Generar
            </button>
            <button id="btn_ac_exportar" class="btn btn-success" disabled>
                <i class="fa fa-file-excel-o"></i> Exportar Excel
            </button>
        </div>
        <div class="ac-atajos">
            <button class="btn btn-default btn-xs ac-atajo" data-rango="hoy">Hoy</button>
            <button class="btn btn-default btn-xs ac-atajo" data-rango="semana">Esta semana</button>
            <button class="btn btn-default btn-xs ac-atajo" data-rango="mes">Este mes</button>
            <button class="btn btn-default btn-xs ac-atajo" data-rango="anio">Este año</button>
        </div>
    </div>
</div>

<div id="ac_loader" class="ac-loader" style="display:none;">
    <i class="fa fa-spinner fa-spin fa-2x"></i>
    <span>Analizando anulaciones...</span>
</div>

<div id="ac_resultado" style="display:none;">
    <div class="ac-cards">
        <div class="ac-card">
            <div class="ac-card-icon bg-blue"><i class="fa fa-ban"></i></div>
            <div class="ac-card-body">
                <div class="ac-card-label">Eventos de eliminación</div>
                <div class="ac-card-value" id="ac_kpi_eventos">—</div>
            </div>
        </div>
        <div class="ac-card">
            <div class="ac-card-icon bg-red"><i class="fa fa-cubes"></i></div>
            <div class="ac-card-body">
                <div class="ac-card-label">Unidades eliminadas</div>
                <div class="ac-card-value" id="ac_kpi_unidades">—</div>
                <div class="ac-card-note" id="ac_kpi_variacion_unidades"></div>
            </div>
        </div>
        <div class="ac-card">
            <div class="ac-card-icon bg-orange"><i class="fa fa-dollar"></i></div>
            <div class="ac-card-body">
                <div class="ac-card-label">Monto referencial</div>
                <div class="ac-card-value" id="ac_kpi_monto">—</div>
                <div class="ac-card-note" id="ac_kpi_variacion_monto"></div>
            </div>
        </div>
        <div class="ac-card">
            <div class="ac-card-icon bg-green"><i class="fa fa-cutlery"></i></div>
            <div class="ac-card-body">
                <div class="ac-card-label">Comandas afectadas</div>
                <div class="ac-card-value" id="ac_kpi_comandas">—</div>
                <div class="ac-card-note" id="ac_kpi_mesas"></div>
            </div>
        </div>
    </div>

    <div class="ac-row ac-row-2">
        <div class="ac-panel">
            <h4>Categorías con más productos eliminados</h4>
            <div class="ac-chart-wrap ac-chart-wrap-sm">
                <canvas id="acCategoriasChart"></canvas>
            </div>
        </div>
        <div class="ac-panel">
            <h4>Productos más eliminados</h4>
            <div class="ac-chart-wrap ac-chart-wrap-sm">
                <canvas id="acProductosChart"></canvas>
            </div>
        </div>
    </div>

    <div class="ac-panel ac-panel-full">
        <h4>Tendencia diaria de eliminaciones</h4>
        <div class="ac-chart-wrap ac-chart-wrap-lg">
            <canvas id="acTendenciaChart"></canvas>
        </div>
    </div>

    <div class="ac-row ac-row-2">
        <div class="ac-panel">
            <h4>Usuarios con más eliminaciones</h4>
            <div id="ac_usuarios_list" class="ac-lista-resumen"></div>
        </div>
        <div class="ac-panel">
            <h4>Motivos más frecuentes</h4>
            <div id="ac_motivos_list" class="ac-lista-resumen"></div>
        </div>
    </div>

    <div class="ac-panel ac-panel-full">
        <h4>Lectura rápida</h4>
        <ul id="ac_hallazgos" class="ac-hallazgos"></ul>
    </div>

    <div class="ac-panel ac-panel-full">
        <h4>Detalle de eliminaciones</h4>
        <div class="table-responsive">
            <table id="ac_tabla" class="table table-hover table-condensed ac-tabla">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Comanda</th>
                        <th>Mesa</th>
                        <th>Garzón</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio ref.</th>
                        <th class="text-right">Monto ref.</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="/js/reportes/anulaciones_comandas.js"></script><?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/reportes/anulaciones_comandas.blade.php ENDPATH**/ ?>