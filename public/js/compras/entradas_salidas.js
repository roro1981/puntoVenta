$(document).ready(function () {
   
    $('#produ_movi').on('keyup', function () {
        let query = $(this).val().trim();

        if (query.length >= 2) {
            $.ajax({
                url: "/compras/searchProductosAll",
                type: "GET",
                data: { q: query },
                
                success: function (data) {
                    $('#cod').val("");
                    $('#descrip').val("");
                    mostrarSugerencias(data);
                },
                error: function (xhr, status, error) {
                    toastr.error(error);
                }
            });
        } else {
            $('#listaProds').empty();
        }
    });

    function mostrarSugerencias(productos) {
        let html = '';

        if (productos.length === 0) {
            html = '<p>Sin resultados</p>';
        } else {
            html += '<table style="width:100%;">';
            html += '<thead>';
            html += '<tr><th>Código</th><th>Descripción</th></tr>';
            html += '</thead>';
            html += '<tbody>';

            $.each(productos, function (index, producto) {
                html += '<tr class="fila-sugerencia" data-codigo="' + producto.id + '" data-dsc="' + producto.descripcion + '">';
                html += '<td>' + producto.codigo + '</td>';
                html += '<td>' + producto.descripcion + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
        }

        $('#listaProds').html(html).show();

        $('.fila-sugerencia').on('click', function () {
            const codigo = $(this).data('codigo');
            const descripcion = $(this).data('dsc');
            $('#produ_movi').val(descripcion);
            $("#canti_mov").focus();
            $('#listaProds').hide();
            $('#cod').val(codigo);
            $('#descrip').val(descripcion);
        });
    }

    $("#btn_cargar").unbind('click').bind('click', function () {  
        var tm=$("#tip_movi").val();
        var idProd=$("#cod").val();
        var dprod=$("#descrip").val();
        var cant=$("#canti_mov").val();
        if(tm==0 || dprod==0 || cant==""){
            toastr.warning("Debe ingresar tipo de movimiento, producto y cantidad");
            return false;
        }
        if(cant==0 || cant<0){
            toastr.warning("Cantidad debe ser mayor a 0");
            return false;
        }
        var cont=0;
        $("#tbl_movis tbody tr").find('td:eq(1)').each(function () {
            cod_prod = $(this).attr("id").split("_")[1];
        
            if(cod_prod==idProd){
                cont++;
            }
                   
        });
        if(cont>0){
            toastr.warning("Producto ya fue ingresado en este listado");
            return false;
        }

        $.ajax({
            url: "/compras/cargaProdMov",
            type: "GET",
            data: { tipo_mov: tm, idp: idProd, canti:cant },
            
            success: function(r){
                var respu=r;
                if(respu !="NO"){
                   $(".movis").append(respu);
                   $("#tip_movi").val(0);
                   $("#produ_movi").val("").selectpicker('refresh');
                   $("#canti_mov").val("");
                }else{
                    toastr.warning("Producto no puede quedar con stock negativo");
                }
            }
        });	
    });    
});    

$(document).on('click', '.borrar', function (event) {
    $(this).closest('tr').remove();
});

$("#grabar_movs").unbind('click').bind('click', function () {      
	var DATA 	= [];
	var TABLA 	= $("#tbl_movis tbody > tr");
	var con=0;
    
	TABLA.each(function(){
        var cod 		= $(this).find("td:eq(1)").attr("id").split("_")[1],
			cant    	= $(this).find("td:eq(3)").html(),
			tipo    	= $(this).find("td:eq(4)").html(),
			obs     	= $(this).find("textarea[id*='obs_']").val();
			
		item = {};
		item ["idp"]    = cod;
		item ["cant"] 	= cant;
		item ['tipo'] 	= tipo.charAt(0);
		item ['obs'] 	= obs;
		
        DATA.push(item);
        con++;           
    });	
    if(con==0){
        toastr.warning("No hay productos para procesar");
        return false;
    }
	prods 	= JSON.stringify(DATA);
	
    Swal.fire({
        title: "Generar movimientos",
        text: "Se realizarán movimientos que modificarán stock, ¿Está seguro de continuar?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, estoy seguro",
        cancelButtonText: "No"
      }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "/compras/movimientos/grabar",
                type: "POST",
                data: { arr: prods },
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
    			success: function(r){
    			    resp=r;
    			    if(resp.status=="OK"){
    			        toastr.success("Movimientos grabados exitosamente");
    			        $(".movis").html("");
    			    }else{
    			        toastr.error("Error al grabar");
    			    }
    			}
			})
        }else{
            toastr.error("Operación cancelada");
        }
      });
			
				
});	