@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
<div>
    <!-- Nothing worth having comes easy. - Theodore Roosevelt -->
</div>
<!DOCTYPE html>
<html>
  <head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CONTROL-TOTAL | Punto Venta Inicio de sesión <?php echo date('Y');?></title>
	<link rel="shortcut icon" href="img/favicon.ico">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font-awesome.css">
   
    <!-- Theme style -->
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="blue.css">
	
	<!-- jQuery 2.1.4 -->
    <script src="js/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="js/bootstrap.min.js"></script>
    <script src="{{ asset('js/login.js') }}"></script>
	
  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
      <div style="height:120px" class="login-logo">
	  <img src="img/apple-touch-icon.png" width="100px" height="100px"/><br>
        <b >PUNTO VENTA <?php echo date('Y');?></b>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg">Ingrese sus datos de Acceso</p>
        <form id="loginForm" method="post" action="{{route('login')}}">
          @csrf
          <div class="form-group has-feedback">
            <input id="name" type="text" autocomplete="off" class="form-control" placeholder="Usuario" name="name" autofocus>
          </div>
          <div class="form-group has-feedback">
            <input id="password" type="password" autocomplete="off" class="form-control" placeholder="Password" name="password">
          </div>
          <div class="row">
            
            <div class="col-xs-12">
             <button type="submit" id="ingresar" class="btn btn-primary btn-lg btn-block">Ingresar</button>
            </div><!-- /.col -->
          </div>
        </form>
        <div id="loginError" style="color: red;"></div>
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

  
    
  </body>
</html>
