$(document).ready(function() {
    // Configurar CSRF token para todas las peticiones AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        var $btnIngresar = $('#ingresar');
        var textoOriginalBtn = $btnIngresar.data('original-text') || $btnIngresar.text();
        $btnIngresar.data('original-text', textoOriginalBtn);

        function restaurarBoton() {
            $btnIngresar.prop('disabled', false).text(textoOriginalBtn);
        }

        function bloquearBotonTemporal(texto) {
            $btnIngresar.prop('disabled', true).text(texto || 'Procesando...');
        }

        function mostrarOverlayBienvenida(userName) {
            var nombre = (userName || $('#name').val() || 'usuario').trim();
            $('#loginOverlayMessage').text('Bienvenido, ' + nombre + '. Ingresando al sistema...');
            $('#loginSuccessOverlay').addClass('show').attr('aria-hidden', 'false');
        }

        bloquearBotonTemporal('Validando...');
        $('#loginError').html('');

        $.ajax({
            url: '/login',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.authenticated) {
                    mostrarOverlayBienvenida(response.userName);
                    bloquearBotonTemporal('Ingresando...');
                    setTimeout(function() {
                        window.location.href = response.redirectTo;
                    }, 1800);
                } else {
                    $('#loginError').html(response.message).css('color', 'red');
                    restaurarBoton();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = 'Credenciales inválidas. Por favor intente de nuevo.';
                    $('#loginError').html(errorMessage).css('color', 'red');
                } else if (xhr.status === 419) {
                    $('#loginError').html('Sesión expirada. Por favor recargue la página.').css('color', 'red');
                } else {
                    $('#loginError').html('Error al procesar la solicitud.').css('color', 'red');
                }

                restaurarBoton();
            }
        });
    });

    $('#btnSendRecoveryCode').on('click', function () {
        var username = ($('#recovery_name').val() || '').trim();

        if (!username) {
            $('#recoveryError').html('Debes ingresar tu usuario.').css('color', 'red');
            return;
        }

        $.ajax({
            url: '/password/recovery/request',
            method: 'POST',
            data: { name: username },
            success: function (response) {
                $('#recoveryError').html(response.message).css('color', 'green');
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'No fue posible enviar el código.';
                $('#recoveryError').html(msg).css('color', 'red');
            }
        });
    });

    $('#btnResetPassword').on('click', function () {
        var username = ($('#recovery_name').val() || '').trim();
        var code = ($('#recovery_code').val() || '').trim();
        var password = $('#recovery_password').val() || '';
        var passwordConfirmation = $('#recovery_password_confirm').val() || '';

        if (!username || !code || !password || !passwordConfirmation) {
            $('#recoveryError').html('Completa todos los campos para actualizar la contraseña.').css('color', 'red');
            return;
        }

        $.ajax({
            url: '/password/recovery/reset',
            method: 'POST',
            data: {
                name: username,
                code: code,
                password: password,
                password_confirmation: passwordConfirmation
            },
            success: function (response) {
                $('#recoveryError').html(response.message).css('color', 'green');
                setTimeout(function () {
                    $('#passwordRecoveryModal').modal('hide');
                    $('#recovery_code').val('');
                    $('#recovery_password').val('');
                    $('#recovery_password_confirm').val('');
                    $('#loginError').html('Contraseña actualizada. Ya puedes iniciar sesión.').css('color', 'green');
                }, 1500);
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'No fue posible actualizar la contraseña.';
                $('#recoveryError').html(msg).css('color', 'red');
            }
        });
    });
});