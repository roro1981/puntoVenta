$(".upload").on('click', function () {
    var formData = new FormData();
    var files = $('#image')[0].files[0];
    formData.append('file', files);
    formData.append('_token', $('#token').val());
    $.ajax({
        url: '/almacen/upload-foto-receta',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response != 0) {
                $(".card-img-top").attr("src", response);
                var nombre_foto = $.trim(response.replace(/^.*\/\/[^\/]+/, ''));
                $("#foto_receta").val(nombre_foto);
            } else {
                $("#foto_receta").val("");
                toastr.error('Formato de imagen incorrecto.');
                $('#foto_receta').val(null);
                $(".card-img-top").attr("src", "/img/fotos_prod/sin_imagen.jpg");
            }
        },
        error: function (jqXHR) {
            if (jqXHR.status === 422) {
                let errors = jqXHR.responseJSON.errors;

                $.each(errors, function (key, value) {
                    let mensajeError = value.join('<br>')
                        .replace(/El file/g, 'La imagen')
                        .replace(/el file/g, 'la imagen');
                    toastr.error(mensajeError);
                });
                $("#" + foto).val("");
                $('#' + image).val(null);
                $(".card-img-top").attr("src", "/img/fotos_prod/sin_imagen.jpg");
            } else {
                toastr.error('Ocurrió un error inesperado. Intenta nuevamente.');
            }
        }
    });
    return false;
});
