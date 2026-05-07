
<!DOCTYPE html>
<html>
     <!-- Meta tags -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

<!-- Title -->
<title>CONTROL-TOTAL | Principal</title>

<!-- Favicon -->
<link rel="shortcut icon" href="img/favicon.ico">

<!-- CSS Files -->
<!-- Bootstrap -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="css/font-awesome.css">
<!-- AdminLTE -->
<link rel="stylesheet" href="css/AdminLTE.min.css">
<link rel="stylesheet" href="css/_all-skins.min.css">
<!-- FullCalendar -->
<link href='js/fullcalendar-4.4.2/packages/core/main.css' rel='stylesheet'>
<link href='js/fullcalendar-4.4.2/packages/daygrid/main.css' rel='stylesheet'>
<link href='js/fullcalendar-4.4.2/packages/timegrid/main.css' rel='stylesheet'>
<link href='js/fullcalendar-4.4.2/packages/list/main.css' rel='stylesheet'>
<link rel="stylesheet" type="text/css" href="js/sb/shadowbox.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<!-- Bootstrap-select -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css" rel="stylesheet">
<!-- Alertify -->
<link rel="stylesheet" href="resources/alertify/themes/alertify.core.css">
<link rel="stylesheet" href="resources/alertify/themes/alertify.default.css">
<!-- jQuery UI -->
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.css">
<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="js/DataTables/datatables.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<!-- Stacktable -->
<link rel="stylesheet" type="text/css" href="css/stacktable.css">

<!-- JavaScript Files -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap -->
<script src="js/bootstrap.min.js"></script>
<!-- AdminLTE -->
<script src="js/app.min.js"></>
<!-- FullCalendar v4.4.2 -->
<script src='js/fullcalendar-4.4.2/packages/core/main.js'></script>
<script src='js/fullcalendar-4.4.2/packages/core/locales-all.js'></script>
<script src='js/fullcalendar-4.4.2/packages/interaction/main.js'></script>
<script src='js/fullcalendar-4.4.2/packages/daygrid/main.js'></script>
<script src='js/fullcalendar-4.4.2/packages/timegrid/main.js'></script>
<script src='js/fullcalendar-4.4.2/packages/list/main.js'></script>
<script src='js/fullcalendar-4.4.2/packages/google-calendar/main.js'></script>
<!-- FileSaver -->
<script type="text/javascript" src="js/excel_js/table_export/libs/FileSaver/FileSaver.min.js"></script>
<!-- TableExport -->
<script type="text/javascript" src="js/excel_js/table_export/tableExport.js"></script>
<script type="text/javascript" src="js/excel_js/table_export/libs/js-xlsx/xlsx.core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<!-- Table2Excel -->
<script src="js/excel_js/table2excel/src/jquery.table2excel.js"></script>
<!-- Shadowbox -->
<script type="text/javascript" src="js/sb/shadowbox.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.10/dist/sweetalert2.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Bootstrap-select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
<!-- jQuery Validate -->
<script src="js/jquery.validate.js" type="text/javascript"></script>
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="js/DataTables/datatables.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>	
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.min.js"></script>
<!-- Sortable.js -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<!-- jQuery UI -->
<script type="text/javascript" charset="utf8" src="js/jquery-ui/jquery-ui.js"></script>
<!-- Stacktable -->
<script type="text/javascript" charset="utf8" src="js/stacktable.js"></script>

<!-- Custom Scripts -->
<script src="<?php echo e(asset('js/dashboard.js')); ?>"></script>
<script type="text/javascript" src="js/js.js?<?php echo date("YmdHis")+1; ?>"></script>
<!-- Dashboard Home -->
<link rel="stylesheet" href="<?php echo e(asset('css/dashboard-home.css')); ?>">
<script src="<?php echo e(asset('js/dashboard-home.js')); ?>"></script>
<script src="<?php echo e(asset('js/dashboard-preventas.js')); ?>"></script>

  </head>
  <body class="hold-transition skin-blue sidebar-mini">
   
    <div class="wrapper">

      <header class="main-header">
        <a href="/dashboard" class="logo">
          <span class="logo-mini"><b>P</b>V</span>
          <span class="logo-lg"><b>Punto Venta</b></span>
        </a>

        <nav class="navbar navbar-static-top" role="navigation">
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Navegación</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <small class="bg-green usuario_activo"><i style="color:green;font-size:15px" class='fa fa fa-user'></i><?php if(auth()->guard()->check()): ?> Usuario activo: <?php echo e(Auth::user()->name); ?> <?php endif; ?></small>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header" style="height: auto">
                    
                    <p>
                      <?php echo e(Auth::user()->name_complete); ?>

                      <small><?php if(auth()->guard()->check()): ?> <?php echo e(Auth::user()->role['role_name']); ?> <?php endif; ?></small>
                    </p>
                  </li>
                  
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    
                    <div class="pull-center">
                      <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" id="cierre" class="btn btn-danger btn-lg btn-block" >Cerrar Sesión</button>
                    </form>
                     
                    </div>
                  </li>
                </ul>
              </li>
              
            </ul>
          </div>

        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section id="menu_lateral" class="sidebar">
          
        </section>
        <!-- /.sidebar -->
      </aside>

      <div class="content-wrapper">
        
        <section class="content">
          
          <div class="row">
            <div class="col-md-12">
              <div class="box">
                <div style="background-color:orange" class="box-header with-border">
                  <h4 id="titulo" style="color:white;font-family: 'Roboto', sans-serif;" class="box-title">Principal</h4>
                   <img id="imagen" style="display:none;float:right" src="" width="40px" height="30px"/>
                  <font style="float:right;margin-right:20px"><i style="padding-left:60px" class='fa fa fa-calendar'></i>
                     <?php echo e($fechaEnPalabras); ?> | <i class='fa fa fa-clock-o'></i>
                      <span id="clock"><?php echo e($horaActual); ?></span>
                  </font>
                 
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  	<div class="row">
	                  	<div id="contenido" class="col-md-12">
                        <?php if($tipoDashboard === 'gerencial'): ?>
                        <div class="home-dashboard">

                          
                          <div class="home-hero">
                            <div>
                              <div class="home-hero-brand">
                                <?php if(!empty($dashboardData['empresa']['logo'])): ?>
                                  <img class="home-hero-logo" src="<?php echo e(asset(ltrim($dashboardData['empresa']['logo'], '/'))); ?>" alt="Logo empresa">
                                <?php endif; ?>
                                <div>
                                  <h3><?php echo e($dashboardData['empresa']['fantasia'] ?: $dashboardData['empresa']['nombre']); ?></h3>
                                  <p>
                                    <?php echo e($dashboardData['empresa']['nombre']); ?>

                                    | <?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'Operacion Restaurant' : 'Operacion Almacen'); ?>

                                  </p>
                                </div>
                              </div>
                              <p style="margin-top:14px;max-width:720px;">
                                Panel gerencial con vision diaria, semanal, mensual y semestral del negocio.
                              </p>
                            </div>
                            <div class="home-status-card <?php echo e($dashboardData['status']['level']); ?>">
                              <div class="home-status-label">Estado general</div>
                              <div class="home-status-title"><?php echo e($dashboardData['status']['title']); ?></div>
                              <div class="home-status-text"><?php echo e($dashboardData['status']['message']); ?></div>
                              <div class="home-status-text" style="margin-top:12px;font-weight:700;">Indice: <?php echo e($dashboardData['status']['score']); ?>/100</div>
                            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge home-badge-dia">Diario</span>
                              <span class="home-section-title">Vista del dia</span>
                            </div>

                            <div class="home-section-sub">Tarjetas</div>
                            <div class="home-grid-cards">
                              <div class="home-kpi">
                                <div class="home-kpi-label">Ventas hoy</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['ventasHoy'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note">Base diaria cerrada y no anulada.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Ticket promedio hoy</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['ticketPromedioHoy'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note"><?php echo e($dashboardData['summary']['ticketsHoy']); ?> <?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'comanda(s) cerrada(s)' : 'ticket(s) emitido(s)'); ?> hoy.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Cajas abiertas</div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['summary']['cajasAbiertas']); ?></div>
                                <div class="home-kpi-note">Operacion activa en este momento.</div>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDashboardCajas" <?php echo e(count($dashboardData['details']['openCashboxes']) === 0 ? 'disabled' : ''); ?>>
                                    Ver detalle
                                  </button>
                                </div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label"><?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'Comandas activas' : 'Tickets hoy'); ?></div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? $dashboardData['summary']['comandasPendientes'] : $dashboardData['summary']['ticketsHoy']); ?></div>
                                <div class="home-kpi-note"><?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'En consumo o pendientes de pago.' : 'Cantidad de ventas registradas hoy.'); ?></div>
                              </div>
                              
                              <?php if($dashboardData['tipoNegocio'] === 'ALMACEN_PREVENTA' && isset($dashboardData['summary']['preventasPendientes'])): ?>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Preventas pendientes</div>
                                <div class="home-kpi-value" style="color: <?php echo e($dashboardData['summary']['preventasPendientes'] > 0 ? '#e74c3c' : '#27ae60'); ?>">
                                  <?php echo e($dashboardData['summary']['preventasPendientes']); ?>

                                </div>
                                <div class="home-kpi-note">Preventas por cerrar y convertir a venta.</div>
                                <?php if($dashboardData['summary']['preventasPendientes'] > 0): ?>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#modalPreventasPendientes">
                                    Ver detalle
                                  </button>
                                </div>
                                <?php endif; ?>
                              </div>
                              <?php endif; ?>
                            </div>

                            <div class="home-section-sub">Graficos</div>
                            <div class="home-panels home-panels-full">
                              <div class="home-panel">
                                <h4>Ventas por hora del dia</h4>
                                <div class="home-chart-wrap">
                                  <canvas id="homeHourlyChart"></canvas>
                                </div>
                              </div>
                            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge home-badge-semana">Semanal</span>
                              <span class="home-section-title">Ultimos 7 dias</span>
                            </div>

                            <div class="home-section-sub">Tarjetas</div>
                            <div class="home-grid-cards">
                              <div class="home-kpi">
                                <div class="home-kpi-label">Promedio 7 dias</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['promedio7Dias'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note">Promedio diario de ventas de la ultima semana.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Cumplimiento del dia</div>
                                <?php
                                  $cumplimiento = $dashboardData['summary']['promedio7Dias'] > 0
                                    ? round(($dashboardData['summary']['ventasHoy'] / $dashboardData['summary']['promedio7Dias']) * 100)
                                    : null;
                                ?>
                                <div class="home-kpi-value" style="color: <?php echo e($cumplimiento === null ? '#1d2b36' : ($cumplimiento >= 100 ? '#198754' : ($cumplimiento >= 70 ? '#d98a00' : '#b73a3a'))); ?>">
                                  <?php echo e($cumplimiento !== null ? $cumplimiento . '%' : 'N/D'); ?>

                                </div>
                                <div class="home-kpi-note">Ventas hoy vs promedio 7 dias.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Alertas de stock</div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['summary']['alertasStock']); ?></div>
                                <div class="home-kpi-note">Productos bajo o igual al minimo configurado.</div>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDashboardStockAlertas" <?php echo e(count($dashboardData['details']['stockAlerts']) === 0 ? 'disabled' : ''); ?>>
                                    Ver productos
                                  </button>
                                </div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Sin stock</div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['summary']['stockCritico']); ?></div>
                                <div class="home-kpi-note">Productos agotados que afectan venta o produccion.</div>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDashboardSinStock" <?php echo e(count($dashboardData['details']['outOfStockByCategory']) === 0 ? 'disabled' : ''); ?>>
                                    Ver categorias
                                  </button>
                                </div>
                              </div>
                            </div>

                            <div class="home-section-sub">Graficos</div>
                            <div class="home-panels">
                              <div class="home-panel">
                                <h4>Tendencia de ventas ultimos 7 dias</h4>
                                <div class="home-chart-wrap">
                                  <canvas id="homeSalesTrendChart"></canvas>
                                </div>
                              </div>
                              <div class="home-panel">
                                <h4>Promedio por dia de semana (4 semanas)</h4>
                                <div class="home-chart-wrap">
                                  <canvas id="homeDayOfWeekChart"></canvas>
                                </div>
                              </div>
                            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge home-badge-mes">Mensual</span>
                              <span class="home-section-title">Este mes</span>
                            </div>

                            <div class="home-section-sub">Tarjetas</div>
                            <div class="home-grid-cards">
                              <div class="home-kpi">
                                <div class="home-kpi-label">Ventas del mes</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['ventasMes'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note">Acumulado desde el primer dia del mes.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">vs mes anterior</div>
                                <?php $delta = $dashboardData['deltaMes']; ?>
                                <?php if($delta !== null): ?>
                                  <div class="home-kpi-value" style="color: <?php echo e($delta >= 0 ? '#198754' : '#b73a3a'); ?>">
                                    <?php echo e($delta >= 0 ? '+' : ''); ?><?php echo e($delta); ?>%
                                  </div>
                                  <div class="home-kpi-note">Mes anterior: $<?php echo e(number_format($dashboardData['ventasMesAnterior'], 0, ',', '.')); ?></div>
                                <?php else: ?>
                                  <div class="home-kpi-value" style="color:#9aabb6">N/D</div>
                                  <div class="home-kpi-note">Sin datos del mes anterior.</div>
                                <?php endif; ?>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Margen bruto estimado</div>
                                <?php if($dashboardData['margenBruto'] !== null): ?>
                                  <div class="home-kpi-value" style="color: <?php echo e($dashboardData['margenBruto'] >= 40 ? '#198754' : ($dashboardData['margenBruto'] >= 20 ? '#d98a00' : '#b73a3a')); ?>">
                                    <?php echo e($dashboardData['margenBruto']); ?>%
                                  </div>
                                  <div class="home-kpi-note">Calculado sobre productos con costo registrado.</div>
                                <?php else: ?>
                                  <div class="home-kpi-value" style="color:#9aabb6">N/D</div>
                                  <div class="home-kpi-note">Registra costos de producto para ver este indicador.</div>
                                <?php endif; ?>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">
                                  Sobrestock detectado
                                  <i class="fa fa-question-circle" style="margin-left:5px;color:#9aabb6;cursor:pointer;" data-toggle="tooltip" data-placement="top" title="Productos activos con más de 60 días de inventario disponible según su ritmo de venta. Excluye productos creados hace menos de 60 días."></i>
                                </div>
                                <div class="home-kpi-value"><?php echo e(count($dashboardData['sobrestock'])); ?></div>
                                <div class="home-kpi-note">Productos inmovilizados con exceso de stock.</div>
                              </div>
                            </div>

                            <div class="home-section-sub">Graficos</div>
                            <div class="home-panels">
                              <div class="home-panel">
                                <h4>Ventas por categoria del mes</h4>
                                <?php if(count($dashboardData['ventasPorCategoria']) > 0): ?>
                                  <div class="home-chart-wrap">
                                    <canvas id="homeCategoryChart"></canvas>
                                  </div>
                                <?php else: ?>
                                  <div class="home-empty">Aun no hay ventas por categoria este mes.</div>
                                <?php endif; ?>
                              </div>
                              <div class="home-panel">
                                <h4>Desglose por forma de pago del mes</h4>
                                <?php if(count($dashboardData['paymentBreakdown']) > 0): ?>
                                  <div class="home-payment-list">
                                    <?php $__currentLoopData = $dashboardData['paymentBreakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                      <div class="home-payment-item">
                                        <div class="home-payment-head">
                                          <span><?php echo e($payment['label']); ?></span>
                                          <span>$<?php echo e(number_format($payment['amount'], 0, ',', '.')); ?> | <?php echo e(number_format($payment['percentage'], 1, ',', '.')); ?>%</span>
                                        </div>
                                        <div class="home-payment-bar">
                                          <span style="width: <?php echo e(min(100, $payment['percentage'])); ?>%;"></span>
                                        </div>
                                      </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                  </div>
                                <?php else: ?>
                                  <div class="home-empty">Aun no hay pagos registrados este mes.</div>
                                <?php endif; ?>
                              </div>
                            </div>

                            <div class="home-section-sub">Tablas</div>
                            <div class="home-subgrid home-subgrid-3">
                              <div class="home-panel">
                                <h4>
                                  Productos mas vendidos del mes
                                  <i class="fa fa-question-circle" style="margin-left:6px;color:#9aabb6;cursor:pointer;font-size:13px;" data-toggle="tooltip" data-placement="top" title="Top 5 productos con mayor cantidad vendida en el mes en curso, ordenados por unidades. Incluye tanto el monto total como las unidades despachadas."></i>
                                </h4>
                                <?php if(count($dashboardData['topProducts']) > 0): ?>
                                  <div class="home-top-list">
                                    <?php $__currentLoopData = $dashboardData['topProducts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                      <div class="home-top-item">
                                        <div class="home-top-head">
                                          <span><?php echo e($product['nombre']); ?></span>
                                          <span><?php echo e(rtrim(rtrim(number_format($product['cantidad'], 2, ',', '.'), '0'), ',')); ?> u.</span>
                                        </div>
                                        <div style="margin-top:8px;color:#5f7381;">Venta: $<?php echo e(number_format($product['monto'], 0, ',', '.')); ?></div>
                                      </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                  </div>
                                <?php else: ?>
                                  <div class="home-empty">Sin productos vendidos en el periodo.</div>
                                <?php endif; ?>
                              </div>

                              <div class="home-panel">
                                <h4>
                                  Rotacion de inventario (30 dias)
                                  <i class="fa fa-question-circle" style="margin-left:6px;color:#9aabb6;cursor:pointer;font-size:13px;" data-toggle="tooltip" data-placement="top" title="Muestra los productos que más se vendieron en los últimos 30 días. 'Dias stock' indica cuántos días les queda inventario al ritmo actual: ≤7 días = crítico (rojo), ≤14 días = alerta (naranja), +14 días = ok (verde)."></i>
                                </h4>
                                <?php if(count($dashboardData['rotacionInventario']) > 0): ?>
                                  <div class="table-responsive">
                                    <table class="home-modal-table">
                                      <thead>
                                        <tr>
                                          <th>Producto</th>
                                          <th>Categoria</th>
                                          <th>Stock</th>
                                          <th>Vendido 30d</th>
                                          <th>Dias stock</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php $__currentLoopData = $dashboardData['rotacionInventario']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                          <tr>
                                            <td><?php echo e($r['nombre']); ?></td>
                                            <td><?php echo e($r['categoria']); ?></td>
                                            <td><?php echo e(rtrim(rtrim(number_format($r['stock'], 2, ',', '.'), '0'), ',')); ?></td>
                                            <td><?php echo e(rtrim(rtrim(number_format($r['vendido30'], 2, ',', '.'), '0'), ',')); ?></td>
                                            <td>
                                              <span class="home-dias-badge <?php echo e($r['diasStock'] <= 7 ? 'home-dias-critico' : ($r['diasStock'] <= 14 ? 'home-dias-alerta' : 'home-dias-ok')); ?>">
                                                <?php echo e($r['diasStock'] >= 999 ? '+999' : $r['diasStock']); ?> d
                                              </span>
                                            </td>
                                          </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                      </tbody>
                                    </table>
                                  </div>
                                <?php else: ?>
                                  <div class="home-empty">Sin datos de movimientos de venta en historial.</div>
                                <?php endif; ?>
                              </div>

                              <div class="home-panel">
                                <h4>
                                  Sobrestock (inmovilizados 30+ dias)
                                  <i class="fa fa-question-circle" style="margin-left:6px;color:#9aabb6;cursor:pointer;font-size:13px;" data-toggle="tooltip" data-placement="top" title="Productos con más de 60 días de inventario disponible según ventas recientes. Días de inventario = stock ÷ (ventas 30d ÷ 30). 0-30 días: saludable | 30-60 días: alto | +60 días: sobrestock. No incluye productos nuevos (menos de 60 días desde su creación)."></i>
                                </h4>
                                <?php if(count($dashboardData['sobrestock']) > 0): ?>
                                  <div class="table-responsive">
                                    <table class="home-modal-table">
                                      <thead>
                                        <tr>
                                          <th>Producto</th>
                                          <th>Categoria</th>
                                          <th>Stock</th>
                                          <th>Minimo</th>
                                          <th>Exceso</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php $__currentLoopData = $dashboardData['sobrestock']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                          <tr>
                                            <td><?php echo e($s['nombre']); ?></td>
                                            <td><?php echo e($s['categoria']); ?></td>
                                            <td><?php echo e(rtrim(rtrim(number_format($s['stock'], 2, ',', '.'), '0'), ',')); ?></td>
                                            <td><?php echo e(rtrim(rtrim(number_format($s['stockMinimo'], 2, ',', '.'), '0'), ',')); ?></td>
                                            <td style="color:#b73a3a;font-weight:700;">+<?php echo e(rtrim(rtrim(number_format($s['exceso'], 2, ',', '.'), '0'), ',')); ?></td>
                                          </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                      </tbody>
                                    </table>
                                  </div>
                                <?php else: ?>
                                  <div class="home-empty">No se detectaron productos con sobrestock.</div>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge home-badge-anual">Semestral / Anual</span>
                              <span class="home-section-title">Tendencias historicas</span>
                            </div>

                            <div class="home-section-sub">Graficos</div>
                            <div class="home-panels">
                              <div class="home-panel">
                                <h4>Evolucion 6 meses: ventas vs compras estimadas</h4>
                                <div class="home-chart-wrap">
                                  <canvas id="home6MonthsChart"></canvas>
                                </div>
                              </div>
                              <div class="home-panel">
                                <h4>Lectura gerencial</h4>
                                <div class="home-insights">
                                  <?php $__currentLoopData = $dashboardData['insights']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="home-insight-item"><?php echo e($insight); ?></div>
                                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                              </div>
                            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge" style="background:#fff0f0;color:#c0392b;border:1px solid #f5c6cb;">Control Interno</span>
                              <span class="home-section-title">Anulaciones y Mermas — <span id="ci-periodo-label">cargando...</span></span>
                            </div>

                            
                            <div style="margin-bottom:14px;">
                              <button class="btn btn-default btn-xs ci-periodo-btn" data-rango="semana">Esta semana</button>
                              <button class="btn btn-default btn-xs ci-periodo-btn" data-rango="mes">Este mes</button>
                              <button class="btn btn-default btn-xs ci-periodo-btn" data-rango="trimestre">Este trimestre</button>
                              <button class="btn btn-default btn-xs ci-periodo-btn" data-rango="semestre">Este semestre</button>
                              <button class="btn btn-default btn-xs ci-periodo-btn" data-rango="anio">Este año</button>
                            </div>

                            <div class="home-section-sub">Resumen</div>
                            <div class="home-grid-cards home-grid-ci">
                              <div class="home-kpi">
                                <div class="home-kpi-label">Anulaciones hoy</div>
                                <div class="home-kpi-value" id="ci-kpi-anu-hoy" style="color:#27ae60;">—</div>
                                <div class="home-kpi-note">Ítems anulados en el día.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Anulaciones período</div>
                                <div class="home-kpi-value" id="ci-kpi-anu-total" style="color:#e67e22;">—</div>
                                <div class="home-kpi-note">ítems anulados.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Monto anulado</div>
                                <div class="home-kpi-value" id="ci-kpi-anu-monto" style="color:#c0392b;">—</div>
                                <div class="home-kpi-note">Valor de ventas anuladas.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Costo mermas</div>
                                <div class="home-kpi-value" id="ci-kpi-merma-costo" style="color:#8e44ad;">—</div>
                                <div class="home-kpi-note" id="ci-kpi-merma-note">registros de merma.</div>
                              </div>
                            </div>

                            <div class="home-panels" style="margin-top:18px;">

                              
                              <div class="home-panel">
                                <h4><i class="fa fa-ban" style="color:#c0392b;margin-right:6px;"></i>Anulaciones por usuario</h4>
                                <div id="ci-container-anulaciones">
                                  <div class="home-empty"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>
                                </div>
                              </div>

                              
                              <div class="home-panel">
                                <h4><i class="fa fa-exclamation-triangle" style="color:#8e44ad;margin-right:6px;"></i>Mermas por producto</h4>
                                <div id="ci-container-mermas">
                                  <div class="home-empty"><i class="fa fa-spinner fa-spin"></i> Cargando...</div>
                                </div>
                              </div>

                            </div>
                          </div>

                        </div>

                        <?php elseif($tipoDashboard === 'administrador'): ?>
                        <div class="home-dashboard">
                          <div class="home-hero">
                            <div>
                              <div class="home-hero-brand">
                                <?php if(!empty($dashboardData['empresa']['logo'])): ?>
                                  <img class="home-hero-logo" src="<?php echo e(asset(ltrim($dashboardData['empresa']['logo'], '/'))); ?>" alt="Logo empresa">
                                <?php endif; ?>
                                <div>
                                  <h3><?php echo e($dashboardData['empresa']['fantasia'] ?: $dashboardData['empresa']['nombre']); ?></h3>
                                  <p>
                                    <?php echo e($dashboardData['empresa']['nombre']); ?>

                                    | <?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'Operacion Restaurant' : 'Operacion Almacen'); ?>

                                  </p>
                                </div>
                              </div>
                              <p style="margin-top:14px;max-width:720px;">
                                Panel de administracion con actividad operativa del negocio.
                              </p>
                            </div>
                            <div class="home-status-card <?php echo e($dashboardData['status']['level']); ?>">
                              <div class="home-status-label">Estado general</div>
                              <div class="home-status-title"><?php echo e($dashboardData['status']['title']); ?></div>
                              <div class="home-status-text"><?php echo e($dashboardData['status']['message']); ?></div>
                            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge home-badge-dia">Diario</span>
                              <span class="home-section-title">Vista del dia</span>
                            </div>

                            <div class="home-section-sub">Tarjetas</div>
                            <div class="home-grid-cards">
                              <div class="home-kpi">
                                <div class="home-kpi-label">Ventas hoy</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['ventasHoy'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note">Base diaria cerrada y no anulada.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Ticket promedio hoy</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['ticketPromedioHoy'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note"><?php echo e($dashboardData['summary']['ticketsHoy']); ?> <?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'comanda(s) cerrada(s)' : 'ticket(s) emitido(s)'); ?> hoy.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Cajas abiertas</div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['summary']['cajasAbiertas']); ?></div>
                                <div class="home-kpi-note">Operacion activa en este momento.</div>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDashboardCajas" <?php echo e(count($dashboardData['details']['openCashboxes']) === 0 ? 'disabled' : ''); ?>>
                                    Ver detalle
                                  </button>
                                </div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label"><?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'Comandas activas' : 'Tickets hoy'); ?></div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? $dashboardData['summary']['comandasPendientes'] : $dashboardData['summary']['ticketsHoy']); ?></div>
                                <div class="home-kpi-note"><?php echo e($dashboardData['tipoNegocio'] === 'RESTAURANT' ? 'En consumo o pendientes de pago.' : 'Cantidad de ventas registradas hoy.'); ?></div>
                              </div>
              
              <?php if($dashboardData['tipoNegocio'] === 'ALMACEN_PREVENTA' && isset($dashboardData['summary']['preventasPendientes'])): ?>
              <div class="home-kpi">
                <div class="home-kpi-label">Preventas pendientes</div>
                <div class="home-kpi-value" style="color: <?php echo e($dashboardData['summary']['preventasPendientes'] > 0 ? '#e74c3c' : '#27ae60'); ?>">
                  <?php echo e($dashboardData['summary']['preventasPendientes']); ?>

                </div>
                <div class="home-kpi-note">Preventas por cerrar y convertir a venta.</div>
                <?php if($dashboardData['summary']['preventasPendientes'] > 0): ?>
                <div class="home-kpi-actions">
                  <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#modalPreventasPendientes">
                    Ver detalle
                  </button>
                </div>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>

            <div class="home-section-sub">Graficos</div>
            <div class="home-panels home-panels-full">
              <div class="home-panel">
                <h4>Ventas por hora del dia</h4>
                <div class="home-chart-wrap">
                  <canvas id="homeHourlyChartAdmin"></canvas>
                </div>
              </div>
            </div>
                          </div>

                          
                          <div class="home-section">
                            <div class="home-section-header">
                              <span class="home-section-badge home-badge-semana">Semanal</span>
                              <span class="home-section-title">Ultimos 7 dias</span>
                            </div>

                            <div class="home-section-sub">Tarjetas</div>
                            <div class="home-grid-cards">
                              <div class="home-kpi">
                                <div class="home-kpi-label">Promedio 7 dias</div>
                                <div class="home-kpi-value">$<?php echo e(number_format($dashboardData['summary']['promedio7Dias'], 0, ',', '.')); ?></div>
                                <div class="home-kpi-note">Promedio diario de ventas de la ultima semana.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Cumplimiento del dia</div>
                                <?php
                                  $cumplimientoAdmin = $dashboardData['summary']['promedio7Dias'] > 0
                                    ? round(($dashboardData['summary']['ventasHoy'] / $dashboardData['summary']['promedio7Dias']) * 100)
                                    : null;
                                ?>
                                <div class="home-kpi-value" style="color: <?php echo e($cumplimientoAdmin === null ? '#1d2b36' : ($cumplimientoAdmin >= 100 ? '#198754' : ($cumplimientoAdmin >= 70 ? '#d98a00' : '#b73a3a'))); ?>">
                                  <?php echo e($cumplimientoAdmin !== null ? $cumplimientoAdmin . '%' : 'N/D'); ?>

                                </div>
                                <div class="home-kpi-note">Ventas hoy vs promedio 7 dias.</div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Alertas de stock</div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['summary']['alertasStock']); ?></div>
                                <div class="home-kpi-note">Productos bajo el stock minimo configurado.</div>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDashboardStockAlertas" <?php echo e(count($dashboardData['details']['stockAlerts']) === 0 ? 'disabled' : ''); ?>>
                                    Ver productos
                                  </button>
                                </div>
                              </div>
                              <div class="home-kpi">
                                <div class="home-kpi-label">Sin stock</div>
                                <div class="home-kpi-value"><?php echo e($dashboardData['summary']['stockCritico']); ?></div>
                                <div class="home-kpi-note">Productos agotados que afectan venta o produccion.</div>
                                <div class="home-kpi-actions">
                                  <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDashboardSinStock" <?php echo e(count($dashboardData['details']['outOfStockByCategory']) === 0 ? 'disabled' : ''); ?>>
                                    Ver categorias
                                  </button>
                                </div>
                              </div>
                            </div>

                            <div class="home-section-sub">Graficos</div>
                            <div class="home-panels">
                              <div class="home-panel">
                                <h4>Tendencia de ventas ultimos 7 dias</h4>
                                <div class="home-chart-wrap">
                                  <canvas id="homeSalesTrendChartAdmin"></canvas>
                                </div>
                              </div>
                              <div class="home-panel">
                                <h4>Promedio por dia de semana (4 semanas)</h4>
                                <div class="home-chart-wrap">
                                  <canvas id="homeDayOfWeekChartAdmin"></canvas>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>

                        <?php else: ?>
                        <div class="home-dashboard">
                          <div class="home-hero">
                            <div>
                              <div class="home-hero-brand">
                                <div>
                                  <h3>Bienvenido/a, <?php echo e(Auth::user()->name_complete ?? Auth::user()->name); ?></h3>
                                  <p><?php echo e(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')); ?></p>
                                </div>
                              </div>
                              <p style="margin-top:14px;max-width:720px;">
                                Usa el menú lateral para acceder a las funciones disponibles para tu usuario.
                              </p>
                            </div>
                          </div>
                        </div>
                        <?php endif; ?>
                  
                      </div>
                    </div>
		                    
                  		</div>
                  	</div><!-- /.row -->
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div><!-- /.col -->
         </div><!-- /.row -->

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <!--Fin-Contenido-->
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b></b>
        </div>
        <strong><a href="#"></a>.</strong>
      </footer>
    </div>    

    <?php if($tipoDashboard !== 'usuario'): ?>
    <div class="modal fade home-modal" id="modalDashboardStockAlertas" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Productos en alerta de stock</h4>
          </div>
          <div class="modal-body">
            <div class="home-modal-note">Listado ordenado desde el producto que aun conserva mas stock al que menos tiene dentro del rango de alerta.</div>
            <?php if(count($dashboardData['details']['stockAlerts']) > 0): ?>
              <?php
                $stockAlertsByCategory = collect($dashboardData['details']['stockAlerts'])->groupBy('categoria');
              ?>
              <form id="formStockAlertas">
                <div style="text-align:right;margin-bottom:10px">
                  <button type="button" id="btnAgregarCompraAlerta" class="btn btn-success" style="display:none">Agregar a compra</button>
                </div>
                <?php $__currentLoopData = $stockAlertsByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div class="home-category-block">
                    <div class="home-category-title"><?php echo e($categoria); ?></div>
                    <div class="table-responsive">
                      <table class="home-modal-table">
                        <thead>
                          <tr>
                            <th><input type="checkbox" class="check-all-cat-alerta"></th>
                            <th>Producto</th>
                            <th>Stock actual</th>
                            <th>Minimo</th>
                            <th>Precio venta</th>
                            <th>Cant. a pedir</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                              <td><input type="checkbox" class="check-prod-alerta" name="productos_alerta[]" value="<?php echo e(json_encode($item)); ?>"></td>
                              <td><?php echo e($item['descripcion']); ?></td>
                              <td><?php echo e(rtrim(rtrim(number_format($item['stock'], 2, ',', '.'), '0'), ',')); ?></td>
                              <td><?php echo e(rtrim(rtrim(number_format($item['stock_minimo'], 2, ',', '.'), '0'), ',')); ?></td>
                              <td>$<?php echo e(number_format($item['precio_venta'], 0, ',', '.')); ?></td>
                              <td><input type="number" min="1" class="form-control input-cant-alerta" style="width:80px" name="cantidades_alerta[]" disabled></td>
                            </tr>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </form>
            <?php else: ?>
              <div class="home-empty">No hay productos en alerta de stock.</div>
            <?php endif; ?>
          </div>
          <!-- Modal para elegir tipo de documento (alerta de stock) -->
          <div class="modal fade" id="modalTipoDocAlertas" tabindex="-1" role="dialog" aria-labelledby="modalTipoDocAlertasLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalTipoDocAlertasLabel">Selecciona tipo de documento</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label><input type="radio" name="tipo_doc_alerta" value="factura" checked> Factura de compra</label><br>
                    <label><input type="radio" name="tipo_doc_alerta" value="boleta"> Boleta de compra</label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="confirmTipoDocAlertas">Cargar productos</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade home-modal" id="modalDashboardSinStock" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Productos sin stock por categoria</h4>
          </div>
          <div class="modal-body">
            <div class="home-modal-note">Los productos agotados se agrupan por categoria para detectar rapidamente donde esta el quiebre operativo.</div>
            <?php if(count($dashboardData['details']['outOfStockByCategory']) > 0): ?>
                <form id="formSinStock">
                  <div style="text-align:right;margin-bottom:10px">
                    <button type="button" id="btnAgregarCompra" class="btn btn-success" style="display:none">Agregar a compra</button>
                  </div>
                  <?php $__currentLoopData = $dashboardData['details']['outOfStockByCategory']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="home-category-block">
                      <div class="home-category-title"><?php echo e($group['categoria']); ?></div>
                      <div class="table-responsive">
                        <table class="home-modal-table">
                          <thead>
                            <tr>
                              <th><input type="checkbox" class="check-all-cat"></th>
                              <th>Producto</th>
                              <th>Stock actual</th>
                              <th>Minimo</th>
                              <th>Precio venta</th>
                              <th>Cant. a pedir</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <tr>
                                <td><input type="checkbox" class="check-prod" name="productos[]" value="<?php echo e(json_encode($item)); ?>"></td>
                                <td><?php echo e($item['descripcion']); ?></td>
                                <td><?php echo e(rtrim(rtrim(number_format($item['stock'], 2, ',', '.'), '0'), ',')); ?></td>
                                <td><?php echo e(rtrim(rtrim(number_format($item['stock_minimo'], 2, ',', '.'), '0'), ',')); ?></td>
                                <td>$<?php echo e(number_format($item['precio_venta'], 0, ',', '.')); ?></td>
                                <td><input type="number" min="1" class="form-control input-cant" style="width:80px" name="cantidades[]" disabled></td>
                              </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </form>
            <?php else: ?>
              <div class="home-empty">No hay productos sin stock.</div>
            <?php endif; ?>
          </div>
          <!-- Modal para elegir tipo de documento -->
          <div class="modal fade" id="modalTipoDoc" tabindex="-1" role="dialog" aria-labelledby="modalTipoDocLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="modalTipoDocLabel">Selecciona tipo de documento</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label><input type="radio" name="tipo_doc" value="factura" checked> Factura de compra</label><br>
                    <label><input type="radio" name="tipo_doc" value="boleta"> Boleta de compra</label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" id="confirmTipoDoc">Cargar productos</button>
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade home-modal" id="modalDashboardCajas" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Detalle de cajas abiertas</h4>
          </div>
          <div class="modal-body">
            <div class="home-modal-note">Resumen operativo de cada caja abierta para revisar antiguedad, actividad comercial y monto esperado al cierre.</div>
            <?php if(count($dashboardData['details']['openCashboxes']) > 0): ?>
              <?php $__currentLoopData = $dashboardData['details']['openCashboxes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cashbox): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="home-cashbox-card">
                  <div class="home-cashbox-head">
                    <span>Caja #<?php echo e($cashbox['id']); ?> | <?php echo e($cashbox['tipo_caja']); ?></span>
                    <span><?php echo e($cashbox['cantidad_ventas']); ?> movimiento(s)</span>
                  </div>
                  <div class="home-cashbox-grid">
                    <div class="home-cashbox-item">
                      <strong>Cajero</strong>
                      <span><?php echo e($cashbox['cajero']); ?></span>
                    </div>
                    <div class="home-cashbox-item">
                      <strong>Apertura</strong>
                      <span><?php echo e($cashbox['apertura']); ?></span>
                    </div>
                    <div class="home-cashbox-item">
                      <strong>Tiempo abierta</strong>
                      <span><?php echo e($cashbox['tiempo_abierta']); ?></span>
                    </div>
                    <div class="home-cashbox-item">
                      <strong>Monto inicial</strong>
                      <span>$<?php echo e(number_format($cashbox['monto_inicial'], 0, ',', '.')); ?></span>
                    </div>
                    <div class="home-cashbox-item">
                      <strong>Monto vendido</strong>
                      <span>$<?php echo e(number_format($cashbox['monto_vendido'], 0, ',', '.')); ?></span>
                    </div>
                    <div class="home-cashbox-item">
                      <strong>Monto esperado</strong>
                      <span>$<?php echo e(number_format($cashbox['monto_esperado'], 0, ',', '.')); ?></span>
                    </div>
                    <div class="home-cashbox-item" style="grid-column: 1 / -1;">
                      <strong>Observaciones</strong>
                      <span><?php echo e($cashbox['observaciones'] ?: 'Sin observaciones registradas.'); ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
              <div class="home-empty">No hay cajas abiertas en este momento.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Preventas Pendientes -->
    <div class="modal fade home-modal" id="modalPreventasPendientes" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Detalle de preventas pendientes</h4>
          </div>
          <div class="modal-body">
            <div class="home-modal-note">Preventas generadas que aún no han sido cerradas y convertidas a venta final.</div>
            <div id="preventasPendientesContainer">
              <div class="text-center">
                <i class="fa fa-spinner fa-spin"></i> Cargando preventas pendientes...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <?php if($tipoDashboard === 'gerencial'): ?>
  <script>
    // Variables de datos para gráficos - Dashboard Gerencial
    window.homeTrendLabels      = <?php echo json_encode($dashboardData['trend']['labels'], 15, 512) ?>;
    window.homeTrendData        = <?php echo json_encode($dashboardData['trend']['data'], 15, 512) ?>;
    window.homeHourlyLabels     = <?php echo json_encode(collect($dashboardData['ventasPorHora'])->pluck('hora'), 15, 512) ?>;
    window.homeHourlyData       = <?php echo json_encode(collect($dashboardData['ventasPorHora'])->pluck('total'), 15, 512) ?>;
    window.homeDayLabels        = <?php echo json_encode(collect($dashboardData['ventasPorDiaSemana'])->pluck('dia'), 15, 512) ?>;
    window.homeDayData          = <?php echo json_encode(collect($dashboardData['ventasPorDiaSemana'])->pluck('total'), 15, 512) ?>;
    window.homeCategoryLabels   = <?php echo json_encode(collect($dashboardData['ventasPorCategoria'])->pluck('categoria'), 15, 512) ?>;
    window.homeCategoryData     = <?php echo json_encode(collect($dashboardData['ventasPorCategoria'])->pluck('total'), 15, 512) ?>;
    window.home6MonthsLabels    = <?php echo json_encode($dashboardData['evolucion6Meses']['labels'], 15, 512) ?>;
    window.home6MonthsVentas    = <?php echo json_encode($dashboardData['evolucion6Meses']['ventas'], 15, 512) ?>;
    window.home6MonthsCompras   = <?php echo json_encode($dashboardData['evolucion6Meses']['compras'], 15, 512) ?>;
  </script>
  <?php elseif($tipoDashboard === 'administrador'): ?>
  <script>
    // Variables de datos para gráficos - Dashboard Administrador
    window.adminTrendLabels  = <?php echo json_encode($dashboardData['trend']['labels'], 15, 512) ?>;
    window.adminTrendData    = <?php echo json_encode($dashboardData['trend']['data'], 15, 512) ?>;
    window.adminHourlyLabels = <?php echo json_encode(collect($dashboardData['ventasPorHora'])->pluck('hora'), 15, 512) ?>;
    window.adminHourlyData   = <?php echo json_encode(collect($dashboardData['ventasPorHora'])->pluck('total'), 15, 512) ?>;
    window.adminDayLabels    = <?php echo json_encode(collect($dashboardData['ventasPorDiaSemana'])->pluck('dia'), 15, 512) ?>;
    window.adminDayData      = <?php echo json_encode(collect($dashboardData['ventasPorDiaSemana'])->pluck('total'), 15, 512) ?>;
  </script>
  <?php endif; ?>
  </body>
  </html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/menu.blade.php ENDPATH**/ ?>