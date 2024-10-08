$(document).ready(function() {
    
    $('#tabla_productos').DataTable();

    $("#calcular-margen").click(calcularPrecioVenta);
    $("#precio_venta_publico").on("input", calcularMargen);

    $(".upload").on('click', function() {
        var formData = new FormData();
        var files = $('#image')[0].files[0];
        formData.append('file',files);
        formData.append('_token', $('#token').val());
        $.ajax({
            url: '/almacen/upload-foto',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response != 0) {
                    $(".card-img-top").attr("src", response);
                    var nombre_foto = $.trim(response.replace(/^.*\/\/[^\/]+/, ''));
                    $("#nom_foto").val(nombre_foto);
                } else {
                    $("#nom_foto").val("");
                    toastr.error('Formato de imagen incorrecto.');
                    $('#image').val(null);
                    $(".card-img-top").attr("src", "https://www.edelar.com.ar/static/theme/images/sin_imagen.jpg");
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

function calcula(valor){
    if($("#precio_compra_neto").val()==0 || $("#precio_compra_neto").val()==""){
          $("#precio_compra_bruto").val(0); 
    }else{
        if(valor==0){
            $("#impuesto_2").val(0);
            $("#precio_compra_bruto").val("");
            return false;
        }
        var trozos=valor.split("_"); 
        valor_iva=parseInt($("#precio_compra_neto").val() * trozos[1])/100;
        calculo1=Math.round(parseInt($("#precio_compra_neto").val())+valor_iva);
        $("#precio_compra_bruto").val(calculo1);
    }
  }

function calcula2(valor){
    if($("#precio_compra_neto").val()==0 || $("#precio_compra_neto").val()==""){
        $("#precio_compra_bruto").val(0); 
    }else{  
        var trozos=valor.split("_"); 
        
        if($("#impuesto_1").val()==0){
            toastr.error("Seleccione impuesto 1");
            $("#impuesto_2").val(0);
        }else{
            iva=$("#impuesto_1").val();
            var trozos2=iva.split("_");
            valor_imp1=parseInt($("#precio_compra_neto").val() * trozos2[1])/100;
            if(valor==0){
                valor_imp2=0;  
            }else{
                valor_imp2=parseInt($("#precio_compra_neto").val() * trozos[1])/100;
            }
            console.log(valor_imp1);
            console.log(valor_imp2);
            calculo2=Math.round(parseInt($("#precio_compra_neto").val())+valor_imp1+valor_imp2);
            $("#precio_compra_bruto").val(calculo2);
        }
    }
}

function calcularPrecioVenta() {
    var precioCompraBruto = parseFloat($("#precio_compra_bruto").val());
    var margen = parseFloat($("#margen").val());
    
    if (isNaN(precioCompraBruto) || isNaN(margen)) {
      toastr.error("Por favor, ingrese un número válido para el precio de compra bruto y el margen.");
      return;
    }
    
    var precioVentaPublico = Math.round(precioCompraBruto + (precioCompraBruto * (margen / 100)));
    $("#precio_venta_publico").val(precioVentaPublico);
}

function calcularMargen() {
    var precioCompraBruto = parseFloat($("#precio_compra_bruto").val());
    var precioVentaPublico = parseFloat($("#precio_venta_publico").val());
    
    if (isNaN(precioCompraBruto) || isNaN(precioVentaPublico)) {
      toastr.error("Por favor, ingrese un número válido para el precio de compra bruto y el precio de venta público.");
      return;
    }
    
    var margen = ((precioVentaPublico - precioCompraBruto) / precioCompraBruto) * 100;
    margen = Math.round(margen);
    
    $("#margen").val(margen);
}