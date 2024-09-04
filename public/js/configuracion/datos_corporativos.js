$(document).ready(function() {
    $('.selectpicker').selectpicker();
    var src = $('.card-img-top').attr('src'); 
    if (!src || src.trim() === "http://127.0.0.1:8000/") {
        $(".card-img-top").attr("src", "img/avatar_producto.png");
    }
    
    $('#saveCorporateDataBtn').click(function() {
    
        var formData = {
            name_enterprise: $('#name_enterprise').val(),
            fantasy_name_enterprise: $('#fantasy_name_enterprise').val(),
            address_enterprise: $('#address_enterprise').val(),
            comuna_enterprise:$('#comuna_enterprise option:selected').text(),
            phone_enterprise: $('#phone_enterprise').val(),
            logo_enterprise: $('#nom_foto').val(),
        };

        if (formData.name_enterprise && formData.fantasy_name_enterprise && formData.address_enterprise &&
            formData.comuna_enterprise && formData.phone_enterprise && formData.logo_enterprise) {
           
            $.ajax({
                url: '/configuracion/update-corporate-data', 
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('#token').val() 
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('#contenido').load('/configuracion/datos_corp');
                },
                error: function(xhr, status, error) {
                    toastr.error("Error "+xhr.responseJSON.error+"<br>"+xhr.responseJSON.message);
                }
            });
        } else {
            toastr.warning('Por favor, complete todos los campos antes de guardar.');
        }
    });

    $(".upload").on('click', function() {
        var formData = new FormData();
        var files = $('#image')[0].files[0];
        formData.append('file',files);
        formData.append('_token', $('#token').val());
        $.ajax({
            url: '/configuracion/upload-logo',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response != 0) {
                    $(".card-img-top").attr("src", response);
                    //nombre_foto = $.trim(response).substring(17);
                    var nombre_foto = $.trim(response.replace(/^.*\/\/[^\/]+/, ''));
                    $("#nom_foto").val(nombre_foto);
                } else {
                    $("#nom_foto").val("");
                    alert('Formato de imagen incorrecto.');
                    $('#image').val(null);
                    $(".card-img-top").attr("src", "img/avatar_producto.png");
                }
            },
            error: function(jqXHR) {
                if (jqXHR.status === 422) { 
                    let errors = jqXHR.responseJSON.errors;
        
                    $.each(errors, function(key, value) {
                        let mensajeError = value.join('<br>')
                        .replace(/El file/g, 'La imagen')
                        .replace(/el file/g, 'la imagen');
                        toastr.error(mensajeError);
                    });
                } else {
                    toastr.error('Ocurrió un error inesperado. Intenta nuevamente.');
                }
            }
        });
        return false;
    });
});