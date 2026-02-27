$(document).ready(function() {
    // Configurar CSRF token para todas las peticiones AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/login',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.authenticated) {
                    $('#loginError').html(response.message).css('color', 'green');
                    setTimeout(function() {
                        window.location.href = response.redirectTo;
                    }, 4000); // 4000 milisegundos = 4 segundos
                } else {
                    $('#loginError').html(response.message).css('color', 'red');
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
            }
        });
    });
});