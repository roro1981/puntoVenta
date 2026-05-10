<div class="cp-wrap">
    <link rel="stylesheet" href="/css/reportes/vtas_garzon.css" />

    <div class="vg-filtro">
        <div class="vg-filtro-inner">
            <div class="vg-filtro-group">
                <label>Desde</label>
                <input type="text" id="cp_desde" class="form-control" placeholder="dd/mm/aaaa" readonly />
            </div>
            <div class="vg-filtro-group">
                <label>Hasta</label>
                <input type="text" id="cp_hasta" class="form-control" placeholder="dd/mm/aaaa" readonly />
            </div>

            <?php if(!$esGarzon): ?>
            <div class="vg-filtro-group vg-filtro-group-wide">
                <label>Garzon</label>
                <select id="cp_select_garzon" class="form-control">
                    <option value="">-- Seleccione garzon --</option>
                </select>
            </div>
            <?php else: ?>
            <div class="vg-filtro-group vg-filtro-group-wide">
                <label>Vista</label>
                <input type="text" class="form-control" value="Mis propinas y rendimiento" readonly />
            </div>
            <?php endif; ?>

            <div class="vg-filtro-actions">
                <button id="btn_cp_generar" class="btn btn-info">
                    <i class="fa fa-search"></i> Generar
                </button>
            </div>

            <div class="vg-atajos">
                <button class="btn btn-default btn-xs cp-atajo" data-rango="hoy">Hoy</button>
                <button class="btn btn-default btn-xs cp-atajo" data-rango="ayer">Ayer</button>
                <button class="btn btn-default btn-xs cp-atajo" data-rango="semana">Esta semana</button>
                <button class="btn btn-default btn-xs cp-atajo" data-rango="mes">Este mes</button>
            </div>
        </div>
    </div>

    <div id="cp_spinner" style="display:none;text-align:center;padding:40px;">
        <i class="fa fa-spinner fa-spin fa-2x" style="color:#3c8dbc;"></i>
        <div style="margin-top:8px;color:#5f7381;">Cargando informacion...</div>
    </div>

    <div id="cp_resultado" style="display:none;">
        <div class="vg-cards">
            <div class="vg-card">
                <div class="vg-card-icon" style="background:#16a085;"><i class="fa fa-hand-o-up"></i></div>
                <div class="vg-card-body">
                    <div class="vg-card-label">Total propinas</div>
                    <div class="vg-card-value" id="cp_total_propinas">$0</div>
                </div>
            </div>
            <div class="vg-card">
                <div class="vg-card-icon" style="background:#2980b9;"><i class="fa fa-dollar"></i></div>
                <div class="vg-card-body">
                    <div class="vg-card-label">Total ventas</div>
                    <div class="vg-card-value" id="cp_total_ventas">$0</div>
                </div>
            </div>
            <div class="vg-card">
                <div class="vg-card-icon" style="background:#27ae60;"><i class="fa fa-list-alt"></i></div>
                <div class="vg-card-body">
                    <div class="vg-card-label">Comandas cerradas</div>
                    <div class="vg-card-value" id="cp_total_comandas">0</div>
                </div>
            </div>
            <div class="vg-card">
                <div class="vg-card-icon" style="background:#e67e22;"><i class="fa fa-line-chart"></i></div>
                <div class="vg-card-body">
                    <div class="vg-card-label">Propina sobre ventas</div>
                    <div class="vg-card-value" id="cp_tasa_propina">0%</div>
                </div>
            </div>
        </div>

        <div class="vg-panel vg-panel-full">
            <h4 id="cp_titulo_detalle">Detalle de propinas</h4>
            <div class="table-responsive">
                <table class="table table-hover vg-table-dt" id="cp_tabla_detalle" style="width:100%">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha cierre</th>
                            <th>Garzon</th>
                            <th>Mesa</th>
                            <th class="text-right">Venta</th>
                            <th class="text-right">Propina</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    window.cpEsGarzon = <?php echo json_encode($esGarzon, 15, 512) ?>;
</script>
<script src="/js/ventas/control_propinas.js"></script>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/ventas/control_propinas.blade.php ENDPATH**/ ?>