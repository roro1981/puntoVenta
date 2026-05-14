<!DOCTYPE html>
<html>
  <head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
    
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg: #eceff3;
      --panel: #ffffff;
      --text: #20262e;
      --muted: #6b7280;
      --stroke: #d8dee7;
      --accent: #2b3747;
      --accent-hover: #1f2937;
    }

    body.login-page {
      min-height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background:
        radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.78), transparent 46%),
        linear-gradient(145deg, #eef1f5 0%, #dfe4eb 100%);
      font-family: 'Manrope', 'Segoe UI', sans-serif;
      color: var(--text);
    }

    .login-box {
      width: 400px;
      max-width: calc(100vw - 32px);
      margin: 16px;
      position: relative;
    }

    .login-logo {
      height: auto;
      margin: 0 0 14px;
      text-align: center;
      color: #1f2937;
    }

    .login-logo img {
      width: 82px;
      height: 82px;
      opacity: 0.96;
      margin-bottom: 12px;
    }

    .login-logo b {
      font-weight: 800;
      letter-spacing: 0.14em;
      font-size: 22px;
      text-shadow: 0 2px 6px rgba(31, 41, 55, 0.25);
    }

    .login-box-body {
      background: var(--panel);
      border: 1px solid var(--stroke);
      border-radius: 14px;
      padding: 28px 24px 22px;
      box-shadow: 0 16px 36px rgba(17, 24, 39, 0.11);
    }

    .login-box-msg {
      margin: 0 0 20px;
      font-size: 14px;
      font-weight: 600;
      color: var(--muted);
    }

    .form-control {
      height: 44px;
      border-radius: 10px;
      border: 1px solid var(--stroke);
      padding: 10px 12px;
      font-size: 14px;
      color: var(--text);
      box-shadow: none;
      transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .form-control:focus {
      border-color: #a9b4c3;
      box-shadow: 0 0 0 3px rgba(43, 55, 71, 0.12);
    }

    #ingresar {
      height: 46px;
      border: none;
      border-radius: 10px;
      background: var(--accent);
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.03em;
      text-transform: uppercase;
      transition: background-color 0.2s ease;
    }

    #ingresar:hover,
    #ingresar:focus {
      background: var(--accent-hover);
    }

    #loginError {
      margin-top: 12px;
      min-height: 20px;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      color: #b91c1c;
    }

    .login-alert {
      margin-bottom: 12px;
      border-radius: 10px;
      font-size: 13px;
      padding: 10px 12px;
    }

    .login-link-wrap {
      text-align: center;
      margin-top: 8px;
    }

    .login-link {
      border: none;
      background: transparent;
      color: #4b5563;
      font-size: 13px;
      font-weight: 600;
      text-decoration: underline;
      padding: 2px 4px;
    }

    .login-link:hover,
    .login-link:focus {
      color: #1f2937;
      outline: none;
    }

    .recovery-help {
      color: #6b7280;
      font-size: 12px;
      margin-bottom: 10px;
      line-height: 1.45;
    }

    #recoveryError {
      min-height: 18px;
      font-size: 12px;
      font-weight: 600;
      margin-top: 8px;
      text-align: center;
      color: #b91c1c;
    }

    .login-overlay {
      position: absolute;
      inset: 0;
      display: none;
      background: rgba(238, 242, 247, 0.84);
      backdrop-filter: blur(3px);
      z-index: 120;
      pointer-events: all;
      border-radius: 14px;
    }

    .login-overlay.show {
      display: flex;
    }

    .login-overlay-card {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: calc(100% - 34px);
      max-width: 320px;
      border: 1px solid #d7dee8;
      border-radius: 12px;
      background: #ffffff;
      box-shadow: 0 14px 26px rgba(15, 23, 42, 0.16);
      padding: 20px 16px;
      text-align: center;
    }

    .login-spinner {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: 3px solid #dce3ec;
      border-top-color: #2b3747;
      margin: 0 auto 12px;
      animation: login-spin 0.85s linear infinite;
    }

    .login-overlay-title {
      margin: 0;
      font-size: 15px;
      font-weight: 800;
      color: #1f2937;
    }

    .login-overlay-msg {
      margin: 7px 0 0;
      font-size: 13px;
      color: #4b5563;
      line-height: 1.45;
    }

    @keyframes login-spin {
      to { transform: rotate(360deg); }
    }

    @media (max-width: 460px) {
      .login-box-body {
        padding: 22px 18px 18px;
      }

      .login-logo b {
        letter-spacing: 0.1em;
        font-size: 18px;
      }
    }
  </style>
	
	<!-- jQuery 2.1.4 -->
    <script src="js/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="js/bootstrap.min.js"></script>
    <script src="<?php echo e(asset('js/login.js')); ?>"></script>
	
  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
      <div class="login-logo">
	  <img src="img/apple-touch-icon.png" alt="Logo"/><br>
        <b >PUNTO VENTA <?php echo date('Y');?></b>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <?php if(session('error')): ?>
            <div class="alert alert-danger login-alert">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <p class="login-box-msg">Ingrese sus datos de Acceso</p>
        <form id="loginForm" method="post" action="<?php echo e(route('login')); ?>">
          <?php echo csrf_field(); ?>
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
        <div id="loginError"></div>
        <div class="login-link-wrap">
          <button type="button" class="login-link" data-toggle="modal" data-target="#passwordRecoveryModal">
            ¿Olvidaste tu clave?
          </button>
        </div>

      </div><!-- /.login-box-body -->

      <div id="loginSuccessOverlay" class="login-overlay" aria-hidden="true">
        <div class="login-overlay-card">
          <div class="login-spinner"></div>
          <p class="login-overlay-title">Acceso concedido</p>
          <p class="login-overlay-msg" id="loginOverlayMessage">Redirigiendo al panel principal...</p>
        </div>
      </div>
    </div><!-- /.login-box -->

    <div class="modal fade" id="passwordRecoveryModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Recuperar contraseña</h4>
          </div>
          <div class="modal-body">
            <p class="recovery-help">Ingresa tu usuario para enviar un código a tu correo registrado. Luego ingresa ese código y tu nueva clave.</p>

            <div class="form-group">
              <label for="recovery_name">Usuario</label>
              <input type="text" class="form-control" id="recovery_name" autocomplete="off" placeholder="Tu usuario">
            </div>

            <div class="form-group">
              <button type="button" class="btn btn-default btn-block" id="btnSendRecoveryCode">Enviar código al correo</button>
            </div>

            <hr>

            <div class="form-group">
              <label for="recovery_code">Código (6 dígitos)</label>
              <input type="text" class="form-control" id="recovery_code" maxlength="6" autocomplete="off" placeholder="Ej: 123456">
            </div>

            <div class="form-group">
              <label for="recovery_password">Nueva contraseña</label>
              <input type="password" class="form-control" id="recovery_password" autocomplete="off" placeholder="Mínimo 6 caracteres">
            </div>

            <div class="form-group">
              <label for="recovery_password_confirm">Confirmar contraseña</label>
              <input type="password" class="form-control" id="recovery_password_confirm" autocomplete="off" placeholder="Repite la nueva contraseña">
            </div>

            <div id="recoveryError"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnResetPassword">Actualizar contraseña</button>
          </div>
        </div>
      </div>
    </div>

  
    
  </body>
</html>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/index.blade.php ENDPATH**/ ?>