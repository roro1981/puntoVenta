
<!DOCTYPE html>
<html>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href='js/fullcalendar-4.4.2/packages/core/main.css' rel='stylesheet' />
    <link href='js/fullcalendar-4.4.2/packages/daygrid/main.css' rel='stylesheet' />
    <link href='js/fullcalendar-4.4.2/packages/timegrid/main.css' rel='stylesheet' />
    <link href='js/fullcalendar-4.4.2/packages/list/main.css' rel='stylesheet' />
    <script src='js/fullcalendar-4.4.2/packages/core/main.js'></script>
    <script src='js/fullcalendar-4.4.2/packages/core/locales-all.js'></script>
    <script src='js/fullcalendar-4.4.2/packages/interaction/main.js'></script>
    <script src='js/fullcalendar-4.4.2/packages/daygrid/main.js'></script>
    <script src='js/fullcalendar-4.4.2/packages/timegrid/main.js'></script>
    <script src='js/fullcalendar-4.4.2/packages/list/main.js'></script>
    <script src='js/fullcalendar-4.4.2/packages/google-calendar/main.js'></script>
    <script src="js/jsPDF/examples/libs/jspdf.umd.js"></script>
    <script src="js/jsPDF/dist/jspdf.plugin.autotable.js"></script>
    <script type="text/javascript" src="js/excel_js/table_export/libs/FileSaver/FileSaver.min.js"></script>
    <script type="text/javascript" src="js/excel_js/table_export/tableExport.js"></script>
    <script type="text/javascript" src="js/excel_js/table_export/libs/js-xlsx/xlsx.core.min.js"></script>
    <script src="js/excel_js/table2excel/src/jquery.table2excel.js"></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CONTROL-TOTAL | Principal</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<!-- Css -->
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font-awesome.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="css/_all-skins.min.css">
    
    <link rel="shortcut icon" href="img/favicon.ico">
    <link rel="stylesheet" type="text/css" href="js/sb/shadowbox.css" />
	<!--<link rel="stylesheet" type="text/css" href="resources/pdf/fpdf.css" />-->
    <script type="text/javascript" src="resources/alertify/lib/alertify.js"></script>
  
    <link rel="stylesheet" href="resources/alertify/themes/alertify.core.css" />
    <link rel="stylesheet" href="resources/alertify/themes/alertify.default.css" />
	<!-- Javascript -->
	<!-- jQuery 2.1.4 -->
   <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="js/jQuery-2.1.4.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.validate.js" type="text/javascript"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="js/app.min.js"></script>
    <script type="text/javascript" src="js/sb/shadowbox.js"></script>
    <script type="text/javascript" src="js/js.js?<?php echo date("YmdHis")+1; ?>"></script>
    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="js/DataTables/datatables.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script>	
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/stacktable.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
    <link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css'>
    <script type="text/javascript" charset="utf8" src="js/stacktable.js"></script>
    <script type="text/javascript" charset="utf8" src="js/jquery-ui/jquery-ui.js"></script>
    <link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.css">
  </head>
  <body class="hold-transition skin-blue sidebar-mini">
   
    <div class="wrapper">

      <header class="main-header">
        <a href="inicio.php" class="logo">
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
                  <small class="bg-green usuario_activo"><i style="color:green;font-size:15px" class='fa fa fa-user'></i>@auth Usuario activo: {{Auth::user()->name}} @endauth</small>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header" style="height: auto">
                    
                    <p>
                      {{Auth::user()->name_complete}}
                      <small>@auth {{Auth::user()->role['role_name']}} @endauth</small>
                    </p>
                  </li>
                  
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    
                    <div class="pull-center">
                      <form method="POST" action="{{ route('logout') }}">
                        @csrf
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
                     {{ $fechaEnPalabras }} | <i class='fa fa fa-clock-o'></i>
                      <span id="clock">{{ $horaActual }}</span>
                  </font>
                 
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  	<div class="row">
	                  	<div id="contenido" class="col-md-12">

                  
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
  </body>
</html>
