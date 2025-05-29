$(document).ready(function() {
    $('#proveedor_compra, #proveedor_compra2').selectpicker({
        title: '',  // fuerza a no mostrar texto inicial
        showContent: true,
        showSubtext: false
    });
    $('.bootstrap-select .dropdown-toggle').removeAttr('title');
    $('#proveedor_compra, #proveedor_compra2').on('changed.bs.select', function () {
        $('.bootstrap-select .dropdown-toggle').removeAttr('title');
    });
    var initialLocaleCode = 'es';
    var localeSelectorEl = document.getElementById('locale-selector');
    var calendarEl = document.getElementById('calendar');
    var hoy = new Date();
    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list','googleCalendar' ],
      header: {
        left: 'prev,next',
        center: 'title',
        right: 'dayGridMonth'
      },
      defaultDate: hoy,
      locale: initialLocaleCode,
      buttonIcons: false, // show the prev/next text
      weekNumbers: true,
      navLinks: true, // can click day/week names to navigate views
      editable: true,
      eventLimit: false, // allow "more" link when too many events
     
      events: "/compras/facturas-calendario",
      
    });
    
    calendar.render();

    // build the locale selector's options
    calendar.getAvailableLocaleCodes().forEach(function(localeCode) {
      var optionEl = document.createElement('option');
      optionEl.value = localeCode;
      optionEl.selected = localeCode == initialLocaleCode;
      optionEl.innerText = localeCode;
      localeSelectorEl.appendChild(optionEl);
    });

    // when the selected option changes, dynamically change the calendar option
    localeSelectorEl.addEventListener('change', function() {
      if (this.value) {
        calendar.setOption('locale', this.value);
      }
    });
    
    trae_productos_compra();
    trae_documentos();

});
    $('#fecha_doc, #fecha_venc_doc, #fecha_doc2, #vencimientos').datepicker({
        clearText: 'Borra',
		clearStatus: 'Borra fecha actual',
		closeText: 'Cerrar',
		closeStatus: 'Cerrar sin guardar',
		prevText: '<Ant',
		prevBigText: '<<',
		prevStatus: 'Mostrar mes anterior',
		prevBigStatus: 'Mostrar año anterior',
		nextText: 'Sig>',
		nextBigText: '>>',
		nextStatus: 'Mostrar mes siguiente',
		nextBigStatus: 'Mostrar año siguiente',
		currentText: 'Hoy',
		currentStatus: 'Mostrar mes actual',
		monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio', 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
		monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
		monthStatus: 'Seleccionar otro mes',
		yearStatus: 'Seleccionar otro año',
		weekHeader: 'Sm',
		weekStatus: 'Semana del año',
		dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
		dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
		dayStatus: 'Set DD as first week day',
		dateStatus: 'Select D, M d',
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		initStatus: 'Seleccionar fecha',
		isRTL: false
    });
    
    function trae_productos_compra(){
        $.ajax({
                url: "/compras/productos-compra",
                type: 'GET',
                dataType: 'json',
                success :  function(result){
                        $('#tblarticulos').DataTable({
                            destroy: true,
                           "language": {
                                "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                            },
                            data: result,
                            //here we get the array data from the ajax call.
                            columns: [
                              {"data" : "opciones"},
                              {"data" : "nombre"},
                              {"data" : "cat"}, 
                              {"data" : "codigo"},
                              {"data" : "stock"},
                              {"data" : "foto"},
                              ],
                               
                        });
                }
                   
         });
     
    }
    function trae_documentos(){
        $.ajax({
                url: "/compras/trae-docs",
                type: 'GET',
                dataType: 'json',
                cache: false,
                success :  function(result){
                        $('#tbldocumentos').DataTable({
                            destroy: true,
                            "autoWidth": true,
                           "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"},
                            data: result, //here we get the array data from the ajax call.
                            "aaSorting": [[ 6, "desc" ]],
                            
                            'columnDefs': [
                              {
                                  "targets": 0, 
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 1,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 2,
                                  "autoWidth": true,
                             },
                             {
                                  "targets": 3,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 4,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 5,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 6,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                              {
                                  "targets": 7,
                                  "autoWidth": true
                             }
                             ],
                            columns: [
                              {"data" : "tipo"},    
                              {"data" : "numdoc"},
                              {"data" : "prov"},
                              {"data" : "total"}, 
                              {"data" : "fec_doc"},
                              {"data" : "items"},
                              {"data" : "fec_ing"},
                              {"data" : "opciones"}
                              ],
                               
                        });
                }
                   
         });
     
    }
    function trae_facturas(estado){
        $.ajax({
                url: "/compras/facturas/" + estado,
                type: 'GET',
                dataType: 'json',
                cache: false,
                success :  function(result){
                        $('#tbldocumentos').DataTable({
                            destroy: true,
                            "autoWidth": true,
                           "language": {
                            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"},
                            data: result,
                            "aaSorting": [[ 6, "desc" ]],
                            
                            'columnDefs': [
                              {
                                  "targets": 0, 
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 1,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 2,
                                  "autoWidth": true,
                             },
                             {
                                  "targets": 3,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 4,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 5,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                             {
                                  "targets": 6,
                                  "className": "dt-body-center",
                                  "autoWidth": true
                             },
                              {
                                  "targets": 7,
                                  "autoWidth": true
                             }
                             ],
                            columns: [
                              {"data" : "tipo"},    
                              {"data" : "numdoc"},
                              {"data" : "prov"},
                              {"data" : "total"}, 
                              {"data" : "fec_doc"},
                              {"data" : "items"},
                              {"data" : "fec_ing"},
                              {"data" : "opciones"}
                              ],
                               
                        });
                }
                   
         });
     
    }
   
   
    function subtotales(){
     var total=0;  
     var total_imp=0;
     var iva=0;
     var licor=0;
     var vino_cerv=0;
     var beb=0;
     var sa=0;
     var carne=0;
     var harina=0;
       $(".subt").each(function(){
			total=Math.round(total+parseFloat($(this).text()));
		});
		
		$(".impuesto_prod").each(function(){

			iva=Math.round(iva+parseFloat($(this).data("vim1")));
    		var impue2=	$(this).data("im2");
    
			switch(impue2) {
              case "LICORES":
                licor=Math.round(licor+parseFloat($(this).data("vim2")));
              break;
              case "VINO-CERVEZAS":
                vino_cerv=Math.round(vino+parseFloat($(this).data("vim2")));
              break;
              case "BEBIDAS-AZUCARADAS":
                beb=Math.round(beb+parseFloat($(this).data("vim2")));
              break;
              case "BEBIDAS-NO-AZUCARADAS":
                sa=Math.round(sa+parseFloat($(this).data("vim2")));
              break;
              case "CARNES":
                carne=Math.round(carne+parseFloat($(this).data("vim2")));
              break;
              case "HARINAS":
                harina=Math.round(harina+parseFloat($(this).data("vim2")));
              break;
            }
		});
	
		$("#IVA").text(iva);
		$("#LICORES").text(licor);
		$("#VINOS-CERVEZAS").text(vino_cerv);
		$("#BEBIDAS-AZUCARADAS").text(beb);
		$("#BEBIDAS-NO-AZUCARADAS").text(sa);
		$("#CARNES").text(carne);
		$("#HARINAS").text(harina);
		$(".val_imp").each(function(){
			total_imp=Math.round(total_imp+parseFloat($(this).text()));
		});
		
        $("#tot_neto").text(total.toLocaleString('es-CL'));
        $("#tot_neto2").text(total);
        $("#tot_imp").text(total_imp.toLocaleString('es-CL'));
        $("#tot_imp2").text(total_imp);
        var bruto=total+total_imp;
        $("#tot_bruto").text(bruto.toLocaleString('es-CL'));
        $("#tot_bruto2").text(bruto);
        
    }
    function subtotales2(){
     var total=0;  
 
       $(".subt2").each(function(){
			total=Math.round(total+parseFloat($(this).text()));
		});
		
        $("#tot_boleta").text(total.toLocaleString('es-CL'));
        
    }
    $("#proveedor_compra").unbind('change').bind('change', function () { 
         var uuid_prov=$(this).val();
         if($("#proveedor_compra").val()==0){
               $("#dias").val("");
               $("#fecha_doc").val("");
               $("#fecha_venc_doc").val("");
               $("#dias").prop("disabled",false);
               $("#fpago_compra").val("");
             return false;
         }
         $.ajax({
                url: "/compras/pago-proveedor/"+uuid_prov,
                type: 'GET',
                dataType: 'text',
             success: function(data) {
               $("#fpago_compra").val(data.trim());
               if(data.trim()=="CONTADO" || data.trim()=="CHEQUE AL DIA"){
                   $("#dias").val("0");
                   $("#dias").prop("disabled",true);
                   $("#fecha_doc").val("");
                   $("#fecha_venc_doc").val("");
               }else{
                   $("#dias").val("");
                   $("#fecha_doc").val("");
                   $("#fecha_venc_doc").val("");
                   $("#dias").prop("disabled",false);
               }
             }
          });
     });
     $("#fecha_doc").unbind('change').bind('change', function () { 
         if($("#dias").val() != ""){
            var fecha = $("#fecha_doc").datepicker("getDate");
            var dias=parseInt($("#dias").val());
            fecha.setDate(fecha.getDate() + dias); 
            $('#fecha_venc_doc').datepicker("setDate", fecha);
         }
     });     
    function mostrarform(tipo){
        if(tipo==1){    
            $("#principal").hide();
            $("#formularioregistros").show();
            $("#formularioregistros2").hide();
            $("#formulariovenc").hide();
            $("#form_activo").val("factura");
            $("#titulo_comp").text("Factura de compra");
        }else if(tipo==2){
            $("#principal").hide();
            $("#formularioregistros2").show();
            $("#formularioregistros").hide();
            $("#formulariovenc").hide();
            $("#form_activo").val("boleta");
            $("#titulo_comp").text("Boleta de compra");
        }else if(tipo==3){
            $("#principal").hide();
            $("#formularioregistros2").hide();
            $("#formulariovenc").show();
            $("#formularioregistros").hide();
            $("#form_activo").val("vencimientos");
            $("#titulo_comp").text("Vencimientos por mes");
            $(".fc-dayGridMonth-button").trigger('click');
        }else{
            $("#principal").show();
            $("#formularioregistros").hide();
            $("#formularioregistros2").hide();
            $("#formulariovenc").hide();
            $("#form_activo").val("");
            $("#titulo_comp").text("Listado de compras");
        }    
    }
    function agregarDetalle(id){
        var num_al=getRandomInt(100,10000);
      	var cantidad=$("#"+id).val();
      	var precio_compra=Math.floor($("#"+id).data('precio'));
      	var codigo=$("#"+id).data('codigo');
        var desc_prod=$("#"+id).data('nombre');
        var imp1=$("#"+id).data('impu1');
        var imp2=$("#"+id).data('impu2');
        var i1=imp1.split("_");
        ida=num_al;
        if(imp2 !=0){
            var i2=imp2.split("_");
        }    
               
        if (cantidad !="" && cantidad !=0){
            
            if($("#form_activo").val()=="factura"){
                	var subtotal=cantidad*precio_compra;
                	multi=parseInt(subtotal) * parseFloat(i1[0]);
                	tot_impu1=Math.round(parseInt(multi)/100);
                	if(imp2==0){
                	    nom_imp2="NO";
                	    tot_impu2=0;
                	    val_imp2=0;
                	}else{
                	    multi=parseInt(subtotal) * parseFloat(i2[0]);
                	    nom_imp2=i2[1];
                	    tot_impu2=Math.round(parseInt(multi)/100);
                	    val_imp2=i2[0];
                	}
                	var fila='<tr class="filas" id="produ_'+ida+'">'+
                	'<td><button type="button" id="elimina_'+id+'" class="borrar btn btn-danger">X</button></td>'+
                	'<td id="cod_'+ida+'">'+codigo+'</td>'+
                	'<td id="nom_'+ida+'">'+desc_prod+'</td>'+
                	'<td style="text-align:center"><input type="number" class="cantCompra" style="width:60px;text-align:center" id="canti_'+ida+'" value="'+cantidad+'"></td>'+
                	'<td style="text-align:center"><input type="text" style="text-align:center" class="precio_produ" data-precio="'+precio_compra+'" id="valor_'+ida+'" value="'+precio_compra+'" /></td>'+
                	'<td style="text-align:center"><input type="number" class="descuCompra" id="descu_'+ida+'" style="width:60px;text-align:center" value="0"></td>'+
                	'<td style="text-align:center" class="subt" id="subtotal_'+ida+'" data-imp1="'+imp1+'" data-imp2="'+imp2+'">'+subtotal+'</td>'+
                	'<input id="oculto2_'+ida+'" type="hidden" value="'+cantidad+'" ><input type="hidden" id="calc_imp_'+ida+'" class="impuesto_prod" data-im1="'+i1[1]+'" data-vim1="'+tot_impu1+'" data-valor1="'+i1[0]+'" data-im2="'+nom_imp2+'" data-vim2="'+tot_impu2+'" data-valor2="'+val_imp2+'" ></tr>';
                	$('.compras').append(fila);
                   
                	subtotales();
            }
            if($("#form_activo").val()=="boleta"){
                	multi=parseInt(precio_compra) * parseFloat(i1[0]);
                	tot_impu1=Math.round(parseInt(multi)/100);
                	if(imp2 != 0){
                    	multi=parseInt(precio_compra) * parseFloat(i2[0]);
                    	tot_impu2=Math.round(parseInt(multi)/100);
                	}else{
                	    tot_impu2=0;
                	}
                	var precio_prod=precio_compra+tot_impu1+tot_impu2;
                	var subtotal=precio_prod*cantidad;
                	
                	var fila='<tr class="filas" id="produ_'+ida+'">'+
                	'<td><button type="button" id="elimina_'+id+'" class="borrar btn btn-danger">X</button></td>'+
                	'<td id="cod_'+ida+'">'+codigo+'</td>'+
                	'<td id="nom_'+ida+'">'+desc_prod+'</td>'+
                	'<td style="text-align:center"><input type="number" class="cantCompra2" style="width:60px;text-align:center" id="canti_'+ida+'" value="'+cantidad+'"></td>'+
                	'<td style="text-align:center"><input type="text" style="text-align:center" class="precio_prod" data-precio="'+precio_prod+'" id="valor_'+ida+'" value="'+precio_prod+'" /></td>'+
                	'<td style="text-align:center"><input type="number" class="descuCompra2" id="descu_'+ida+'" style="width:60px;text-align:center" value="0"></td>'+
                	'<td style="text-align:center" class="subt2" id="subtotal_'+ida+'">'+subtotal+'</td>'+
                	'<input id="oculto2_'+ida+'" type="hidden" value="'+cantidad+'" ></tr>';
                	$('.compras2').append(fila);
                   
                	subtotales2();
            }
            
        }
        $("#"+id).val("");
    }
$(document).on('input','.precio_produ',function(e){
    var id=$(this).attr("id").split("_")[1];
    var cant=parseFloat($("#canti_"+id).val());
    var precio=parseFloat($("#valor_"+id).val());
    if(isNaN(precio)==true || precio==0){
        $("#valor_"+id).val($("#valor_"+id).data("precio"));
        nuevo_total=Math.round(cant*parseFloat($("#valor_"+id).val()),0);
        $("#subtotal_"+id).html(nuevo_total);
        $("#descu_"+id).val("0");
        subtotales();
        return false;
    }
    nuevo_total=Math.round(cant*precio,0);
    var imp1=$("#calc_imp_"+id).data("valor1");
    var imp2=$("#calc_imp_"+id).data("valor2");
    multi=parseInt(nuevo_total) * parseFloat(imp1);
    nuevo_tot_impu1=Math.round(parseInt(multi)/100);
    $("#calc_imp_"+id).data("vim1",nuevo_tot_impu1);
    if(imp2 !=0){
        multi=parseInt(nuevo_total) * parseFloat(imp2);
        nuevo_tot_impu2=Math.round(parseInt(multi)/100);
        $("#calc_imp_"+id).data("vim2",nuevo_tot_impu2);
    }
    $("#valor_"+id).data("precio",precio);
    $("#subtotal_"+id).html(nuevo_total); 
    $("#descu_"+id).val("0");
    subtotales();
}); 
$(document).on('input','.cantCompra',function(e){
    var id=$(this).attr("id").split("_")[1];
    var cant=parseFloat($("#canti_"+id).val());
    var precio=parseFloat($("#valor_"+id).val());
    if(isNaN(cant)==true || cant==0){
        $("#canti_"+id).val($("#oculto2_"+id).val());
        subtotales();
        return false;
    }
    nuevo_total=Math.round(cant*precio,0);
    $("#subtotal_"+id).html(nuevo_total); 
    $("#oculto2_"+id).val(cant);
    var imp1=$("#calc_imp_"+id).data("valor1");
    var imp2=$("#calc_imp_"+id).data("valor2");
    multi=parseInt(nuevo_total) * parseFloat(imp1);
    nuevo_tot_impu1=Math.round(parseInt(multi)/100);
    $("#calc_imp_"+id).data("vim1",nuevo_tot_impu1);
    if(imp2 !=0){
        multi=parseInt(nuevo_total) * parseFloat(imp2);
        nuevo_tot_impu2=Math.round(parseInt(multi)/100);
        $("#calc_imp_"+id).data("vim2",nuevo_tot_impu2);
    }
    subtotales();
});
$(document).on('keypress','.cantCompra2',function(e){
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
		var id=$(this).attr("id").split("_")[1];
		var cant=parseFloat($("#canti_"+id).val());
		var precio=parseFloat($("#valor_"+id).val());
		if(isNaN(cant)==true || cant==0){
		    $("#canti_"+id).val($("#oculto2_"+id).val());
		    subtotales2();
			return false;
		}
		nuevo_total=Math.round(cant*precio,0);
        $("#subtotal_"+id).html(nuevo_total); 
        $("#oculto2_"+id).val(cant);
        
		subtotales2();
    }
});
$(document).on('blur','.cantCompra2',function(e){
    	var id=$(this).attr("id").split("_")[1];
		var cant=parseFloat($("#canti_"+id).val());
		var precio=parseFloat($("#valor_"+id).val());
		if(isNaN(cant)==true || cant==0){
		    $("#canti_"+id).val($("#oculto2_"+id).val());
		    subtotales2();
			return false;
		}
		nuevo_total=Math.round(cant*precio,0);
        $("#subtotal_"+id).html(nuevo_total); 
        $("#oculto2_"+id).val(cant);
        subtotales2();
});    
$(document).on('keypress','.precio_prod',function(e){
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
		var id=$(this).attr("id").split("_")[1];
		var cant=parseFloat($("#canti_"+id).val());
		var precio=parseFloat($("#valor_"+id).val());
		if(isNaN(precio)==true || precio==0){
		    $("#valor_"+id).val($("#valor_"+id).data("precio"));
		    nuevo_total=Math.round(cant*parseFloat($("#valor_"+id).val()),0);
            $("#subtotal_"+id).html(nuevo_total);
            $("#descu_"+id).val("0");
		    subtotales2();
			return false;
		}
		$("#valor_"+id).data("precio",precio);
		nuevo_total=Math.round(cant*precio,0);
        $("#subtotal_"+id).html(nuevo_total); 
        $("#descu_"+id).val("0");
        
		subtotales2();
    }
});
$(document).on('blur','.precio_prod',function(e){
        var id=$(this).attr("id").split("_")[1];
		var cant=parseFloat($("#canti_"+id).val());
		var precio=parseFloat($("#valor_"+id).val());
		if(isNaN(precio)==true || precio==0){
		    $("#valor_"+id).val($("#valor_"+id).data("precio"));
		    nuevo_total=Math.round(cant*parseFloat($("#valor_"+id).val()),0);
            $("#subtotal_"+id).html(nuevo_total);
            $("#descu_"+id).val("0");
		    subtotales2();
			return false;
		}
		$("#valor_"+id).data("precio",precio);
		nuevo_total=Math.round(cant*precio,0);
        $("#subtotal_"+id).html(nuevo_total); 
        $("#descu_"+id).val("0");
        
		subtotales2();
		
});    
$(document).on('input','.descuCompra',function(e){
    
    var id=$(this).attr("id").split("_")[1];
    var desc=parseFloat($("#descu_"+id).val());
    var precio=parseFloat($("#valor_"+id).data("precio"));
    
    if(isNaN(desc)==true){
        desc=0;
    }
    
    nuevo_precio=Math.round(precio-((precio*parseFloat(desc))/100),0);
    $("#valor_"+id).val(nuevo_precio);
    cant=$("#canti_"+id).val();
    nuevo_total=Math.round(cant*nuevo_precio,0);
    $("#subtotal_"+id).html(nuevo_total); 
    var imp1=$("#calc_imp_"+id).data("valor1");
    var imp2=$("#calc_imp_"+id).data("valor2");
    multi=parseInt(nuevo_total) * parseFloat(imp1);
    nuevo_tot_impu1=Math.round(parseInt(multi)/100);
    $("#calc_imp_"+id).data("vim1",nuevo_tot_impu1);
    if(imp2 !=0){
        multi=parseInt(nuevo_total) * parseFloat(imp2);
        nuevo_tot_impu2=Math.round(parseInt(multi)/100);
        $("#calc_imp_"+id).data("vim2",nuevo_tot_impu2);
    }
    subtotales();
});
$(document).on('keypress','.descuCompra2',function(e){
    var keycode = (e.keyCode ? e.keyCode : e.which);
    if (keycode == '13') {
		var id=$(this).attr("id").split("_")[1];
		var desc=parseFloat($("#descu_"+id).val());
		var precio=parseFloat($("#valor_"+id).data("precio"));
	    
		if(isNaN(desc)==true){
			desc=0;
			$("#descu_"+id).val("0");
		}
	    
		nuevo_precio=Math.round(precio-((precio*parseFloat(desc))/100),0);
	    $("#valor_"+id).val(nuevo_precio);
		cant=$("#canti_"+id).val();
		nuevo_total=Math.round(cant*nuevo_precio,0);
        $("#subtotal_"+id).html(nuevo_total); 
        
		subtotales2();
    }
});
$(document).on('blur','.descuCompra2',function(e){
        var id=$(this).attr("id").split("_")[1];
		var desc=parseFloat($("#descu_"+id).val());
		var precio=parseFloat($("#valor_"+id).data("precio"));
	    
		if(isNaN(desc)==true){
			desc=0;
			$("#descu_"+id).val("0");
		}
	    
		nuevo_precio=Math.round(precio-((precio*parseFloat(desc))/100),0);
	    $("#valor_"+id).val(nuevo_precio);
		cant=$("#canti_"+id).val();
		nuevo_total=Math.round(cant*nuevo_precio,0);
        $("#subtotal_"+id).html(nuevo_total); 
        
		subtotales2();
});
$(document).on('click', '.borrar', function (event) {
    var id=$(this).attr("id").split("_")[1];
    $(this).closest('tr').remove();
    if($("#form_activo").val()=="factura"){
        subtotales();
    }else{
        subtotales2();
    }    
});
function getRandomInt(min, max) {
  return Math.floor(Math.random() * (max - min)) + min;
}

$("#btn_guarda_fact").unbind('click').bind('click', function () {      
	var DATA 	= [];
	var TABLA 	= $("#detalles tbody > tr");
    var prov=     $("#proveedor_compra").val();
	var ndoc=     $("#num_doc").val();
	var fpago=    $("#fpago_compra").val();
	var dias=     $("#dias").val();
	var fec_doc=  $("#fecha_doc").val();
	var venc_doc= $("#fecha_venc_doc").val();
	if(prov==0 || ndoc==0 || ndoc==0 || fpago=="" || dias=="" || fec_doc=="" || venc_doc==""){
	    toastr.error("Complete todos los datos del encabezado de la factura de compra");
	    return false;
	}
	
	TABLA.each(function(){
        var cod 		= $(this).find("td:eq(1)").html(),
			cant    	= $(this).find("input[id*='canti_']").val(),
			precio    	= $(this).find("input[id*='valor_']").val(),
			desc    	= $(this).find("input[id*='descu_']").val();
			imp1        = $(this).find("input[id*='calc_imp_']").data("valor1");
			imp2        = $(this).find("input[id*='calc_imp_']").data("valor2");
			
		item = {};
		//item ["cod"] 	= "'"+cod+"'";
		item ["nfact"]  = ndoc;
		item ["cod"] 	= cod;
		item ['cant'] 	= cant;
		item ['descu'] 	= desc;
		item ['precio']	= precio;
		item ['imp1']	= imp1;
		item ['imp2']	= imp2;
        //una vez agregados los datos al array "item" declarado anteriormente hacemos un .push() para agregarlos a nuestro array principal "DATA".
        DATA.push(item);
                   
    });	
		prods 	= JSON.stringify(DATA);
	 
		 var nFilas = parseInt(TABLA.length);
		
		if (nFilas>0){
			var DATA2 	= [];
			var total_impu=0;
			var descrip_imp="";
			var impu="";
			$(".val_imp").each(function(){
			    total_impu=Math.round(total_impu+parseFloat($(this).text()));
			    if($(this).text() != "0"){
			        impu +=$(this).attr("id")+":"+$(this).text()+"|";
			    }
		    });
		    if(total_impu>0){
		        descrip_imp = impu.slice(0,-1);
		    }
		    var fecha_fact=fec_doc.split("/");
		    var venc_fact=venc_doc.split("/");
			item = {};
			item ["prov"] 	    = prov;
			item ["num_doc"] 	= ndoc;
			item ["f_pago"] 	= fpago;
			item ["dias"] 	    = dias;
			item ["fec_doc"] 	= fecha_fact[2]+"-"+fecha_fact[1]+"-"+fecha_fact[0];
			item ["venc_doc"] 	= venc_fact[2]+"-"+venc_fact[1]+"-"+venc_fact[0];
			item ["impuestos"]  = total_impu;
			item ["desc_impuestos"]  = descrip_imp;
	        DATA2.push(item);
			info 	= JSON.stringify(DATA2);
		 }else{
		     toastr.error("Factura sin productos");
		     return false;
		 }
		
         $.ajax({
            url: '/compras/facturas/grabaFactura', // ajusta a tu ruta
            type: 'POST',
            data: {
                arr: prods,
                arr2: info
            },
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            success: function(response) {
                if (response.status === 'EXISTE') {
                    toastr.error(response.message);
                } else if (response.status === 'OK') {
                    toastr.success(response.message);
                    $('#contenido').load('/compras/ingresos');
                } else {
                    toastr.error("Respuesta inesperada del servidor.");
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || "Error al grabar la factura.";
                toastr.error(msg);
            }
        });
				
});	

$("#btn_guarda_bol").unbind('click').bind('click', function () {     
	var DATA 	= [];
	var TABLA 	= $("#detalles2 tbody > tr");
    var prov=     $("#proveedor_compra2").val();
	var ndoc=     $("#num_doc2").val();
	var fec_doc=  $("#fecha_doc2").val();
	if(prov==0 || ndoc==0 || ndoc==0 || fec_doc==""){
	    toastr.warning("Complete todos los datos del encabezado de la boleta de compra");
	    return false;
	}
	
	TABLA.each(function(){
        var cod 		= $(this).find("td:eq(1)").html(),
			cant    	= $(this).find("input[id*='canti_']").val(),
			precio    	= $(this).find("input[id*='valor_']").val(),
			desc    	= $(this).find("input[id*='descu_']").val();
			
		item = {};
		//item ["cod"] 	= "'"+cod+"'";
		item ["nbol"]  = ndoc;
		item ["cod"] 	= cod;
		item ['cant'] 	= cant;
		item ['descu'] 	= desc;
		item ['precio']	= precio;
        //una vez agregados los datos al array "item" declarado anteriormente hacemos un .push() para agregarlos a nuestro array principal "DATA".
        DATA.push(item);
                   
    });	
		prods 	= JSON.stringify(DATA);
	 
		 var nFilas = parseInt(TABLA.length);
		
		if (nFilas>0){
			var DATA2 	= [];
		    
		    var fecha_fact=fec_doc.split("/");
		    
			item = {};
			item ["prov"] 	    = prov;
			item ["num_doc"] 	= ndoc;
			item ["fec_doc"] 	= fecha_fact[2]+"-"+fecha_fact[1]+"-"+fecha_fact[0];
			
	        DATA2.push(item);
			info 	= JSON.stringify(DATA2);
		 }else{
		    toastr.warning("Boleta sin productos");
		    return false;
		 }
		
			$.ajax({
                url: '/compras/boleta/grabar',
                type: 'POST',
                data: {
                    arr: prods,
                    arr2: info
                },
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val()
    			},
    		
    			success: function(r){
    				if(r.status=="EXISTE"){
    				    toastr.warning(r.message);
    				}else if(r.status=="OK"){
    				    $("#cabecera2 input").each(function() {
                			if ($(this).prop('required') == true) {
                			    $(this).val("");
                			}
                		});
                		$("#proveedor_compra2").val("").selectpicker('refresh');
                		$(".compras2").html("");
    				    $("#tot_boleta").html("");
    				    toastr.success("Boleta grabada exitosamente");
                        $('#contenido').load('/compras/ingresos');
    				}else{
    				    toastr.error("error al grabar");
    				}
    			},
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || "Error al grabar la boleta.";
                    toastr.error(msg);
                }
			});
				
});	
$(document).on('click','.detalle_documento',function(e){    
    var num_doc=$(this).attr("id"); 
    var tipo_doc=$(this).data("tipo");
    $.ajax({
        url		: "/compras/facturas/detalle-doc",
		type	: "GET",
        dataType: 'json',
		data	: {
    	docu 	: num_doc,
		tipo    : tipo_doc
		},
	
		success: function(r){
            respuesta=r;
            $(".listado_prods").html("");
            for (var clave in respuesta){
                if(respuesta[clave]["imp2"]==null){
                    impu2="NO";
                }else{
                    impu2=respuesta[clave]["imp2"];
                }
                $(".listado_prods").append('<tr><td>'+respuesta[clave]["codigo"]+'</td><td>'+respuesta[clave]["nombre"]+'</td><td>'+respuesta[clave]["cant"]+'</td><td>'+respuesta[clave]["precio"]+'</td><td>'+respuesta[clave]["descue"]+'</td><td>'+respuesta[clave]["subt"]+'</td><td>'+respuesta[clave]["imp1"]+'</td><td>'+impu2+'</td></tr>');
                neto=respuesta[clave]["neto"];
                impu=respuesta[clave]["impuestos"];
                tot=respuesta[clave]["total"];
            }
            
            $("#cab_factura").html("<strong style='color:blue'>Estado documento:</strong><label style='margin-left:5px;margin-right:10px;'>"+respuesta[clave]["estado"]+"</label>|<strong style='margin-left:10px;color:blue'>Forma de pago:</strong><label style='margin-left:5px;margin-right:10px'>"+respuesta[clave]["fpago"]+"</label>");
            if(respuesta[clave]["fpago"]=="CHEQUE A FECHA" || respuesta[clave]["fpago"]=="CREDITO A X DIAS"){
                $("#cab_factura").append("|<strong style='color:blue;margin-left:10px'>Días de plazo:</strong><label style='margin-left:5px;margin-right:10px;'>"+respuesta[clave]["dias"]+"</label>|<strong style='margin-left:10px;color:blue'>Vencimiento:</strong><label style='margin-left:5px'>"+respuesta[clave]["venc"]+"</label>");  
            }
                var f = new Date();
                fec_actual=f.getDate() + "-" + (f.getMonth() +1) + "-" + f.getFullYear();
                if(!comparaFecha(fec_actual,respuesta[clave]["venc"]) && respuesta[clave]["estado"]=="POR PAGAR" && (respuesta[clave]["fpago"]=="CHEQUE A FECHA" || respuesta[clave]["fpago"]=="CREDITO A X DIAS")){
                    vencida="<label style='color:red;font-weight:bold;margin-left:20px'>FACTURA VENCIDA</label>";
                }else{
                    vencida="";
                }
            $("#titulo_compra").html("FACTURA DE COMPRA "+num_doc+vencida);
            $("#cab_factura").append("<br>");
            detalle_imp=respuesta[clave]["desglose"].split("|");
            $("#cab_factura").append("<label style='margin-right:10px;color:blue'>DESGLOSE DE IMPUESTOS:</label>");
            for (var i=0; i < detalle_imp.length; i++) {
                    $("#cab_factura").append("<label style='font-weight:bold;margin-right:10px'>"+detalle_imp[i] + "</label>");
            }
            if(respuesta[clave]["saldo"]=="SI"){
                $("#cab_factura").append("<button type='button' data-toggle='modal' data-ndoc='"+num_doc+"' data-target='#pagos_ing' style='margin-left:10px' class='mostrar_pags btn btn-primary btn-sm'>Pagos realizados</button>");
            }
            $("#neto_doc").html(neto);
            $("#imps_doc").html(impu);
            $("#bruto_doc").html(tot);
            $("#docu_ing").val(num_doc);
            $("#docu_tip").val(tipo_doc);
        }
    
	});
});
$(document).on('click', '.mostrar_pags', function (e) {
    var numf = $(this).data("ndoc");

    $.ajax({
        type: "GET",
        url: "/compras/detalle-pagos",
        data: { numfac: numf },
        success: function (r) {
            $(".listado_pagos").html("");
            r.pagos.forEach(function (pago) {
                $(".listado_pagos").append(
                    `<tr>
                        <td>${pago.fpago}</td>
                        <td>${pago.monto_pago}</td>
                        <td>${pago.num_docu}</td>
                        <td>${pago.fecha_pago}</td>
                    </tr>`
                );
            });
            $("#titulo_pagos_ing").html("TOTAL PAGOS: " + r.total);
        },
        error: function () {
            toastr.error("Error al cargar los pagos.");
        }
    });
});
function comparaFecha(fecha1, fecha2){
    console.log(fecha1+" "+fecha2);
    //Split de las fechas recibidas para separarlas
    var x = fecha1.split("-");
    var z = fecha2.split("-");

    //Cambiamos el orden al formato americano, de esto dd/mm/yyyy a esto mm/dd/yyyy
    fecha1 = x[1] + "-" + x[0] + "-" + x[2];
    fecha2 = z[1] + "-" + z[0] + "-" + z[2];

    //Comparamos las fechas
    if (Date.parse(fecha1) > Date.parse(fecha2)){
        return false;
    }else{
        return true;
    }
}
$(document).on('click','.detalle_documento2',function(e){    
    var num_doc=$(this).attr("id"); 
    var tipo_doc=$(this).data("tipo");
    $.ajax({
        url		: "/compras/facturas/detalle-doc",
		type	: "GET",
        dataType: 'json',
		data	: {
    	docu 	: num_doc,
		tipo    : tipo_doc
		},
	
		success: function(r){
            respuesta=r;
            $(".listado_prods2").html("");
            for (var clave in respuesta){
                $(".listado_prods2").append('<tr><td>'+respuesta[clave]["codigo"]+'</td><td>'+respuesta[clave]["nombre"]+'</td><td>'+respuesta[clave]["cant"]+'</td><td>'+respuesta[clave]["precio"]+'</td><td>'+respuesta[clave]["descue"]+'</td><td>'+respuesta[clave]["subt"]+'</td></tr>');
                tot=respuesta[clave]["total"];
            }
            $("#titulo_compra2").html("BOLETA DE COMPRA "+num_doc);
            $("#bruto_doc2").html(tot);
            $("#docu_ing").val(num_doc);
            $("#docu_tip").val(tipo_doc);
            
        }
	});
});
 $(document).on('click','.foto_doc',function(e){
	var link_foto=$(this).data('ruta');
	var documento=$(this).data('numdoc');
    var usuario=$(this).data('usuario');

	$("#num_docu").html("Documento "+documento);
	$("#ver_imagen_doc").html("<center><img width='500' height='500' src='"+link_foto+"' /></center>");
	$("#subida_por").text("Subida por el usuario: "+usuario);
});
    $(".upload").on('click', function () {
        var files = $('#image')[0].files[0];
        var fileSize = files.size;
        var tam_max = 1024; // KB
        var sizeKB = parseInt(fileSize / 1024);

        if (sizeKB > tam_max) {
            toastr.error("Imagen muy grande, el tamaño máximo es 1MB");
            return false;
        }

        var formData = new FormData();
        formData.append('file', files);
        formData.append('nombre', $("#docu_ing").val());
        formData.append('tipo', $("#docu_tip").val());

        $.ajax({
            url: '/compras/subir-foto-doc',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.estado === "ok") {
                    toastr.success("Imagen subida correctamente.");
                    $('#image').val('');
                    trae_documentos();
                } else {
                    toastr.errort("Error: " + response.mensaje);
                }
            },
            error: function (err) {
                toastr.error("Error al subir la imagen.");
                console.log(err);
            }
        });

        return false;
    });
    $(".upload2").on('click', function () {
        var files = $('#image2')[0].files[0];
        var fileSize = files.size;
        var tam_max = 1024; // KB
        var sizeKB = parseInt(fileSize / 1024);

        if (sizeKB > tam_max) {
            toastr.error("Imagen muy grande, el tamaño máximo es 1MB");
            return false;
        }

        var formData = new FormData();
        formData.append('file', files);
        formData.append('nombre', $("#docu_ing").val());
        formData.append('tipo', $("#docu_tip").val());

        $.ajax({
            url: '/compras/subir-foto-doc',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.estado === "ok") {
                    toastr.success("Imagen subida correctamente.");
                    $('#image2').val('');
                    trae_documentos();
                } else {
                    toastr.errort("Error: " + response.mensaje);
                }
            },
            error: function (err) {
                toastr.error("Error al subir la imagen.");
                console.log(err);
            }
        });

        return false;
    });
   
    $("#fpago_pag").on('change', function() {
        if($(this).val()=="CONTADO"){
            $("#monto_pago").show();
            $("#monto_pago").focus();
            $("#num_docu_pago").hide();
            $("#num_docu_pago").val("");
            $("#btn-pagar-docu").show();
        }else if($(this).val()=="CHEQUE AL DIA" || $(this).val()=="CHEQUE A FECHA"){
            $("#monto_pago").show();
            $("#num_docu_pago").show();
            $("#btn-pagar-docu").show();
            $("#monto_pago").focus();
        }else{
            $("#monto_pago").hide();
            $("#monto_pago").val("");
            $("#num_docu_pago").val("");
            $("#num_docu_pago").hide();
            $("#btn-pagar-docu").hide();
            $("#num_docu_pago").val("");
            $("#monto_pago").val("");
        }
    });
     $(document).on('click','.pago_pend',function(e){
       var num_doc=$(this).data("numdoc");
       var tot_doc=$(this).data("totdoc");
       var saldo=$(this).data("saldodoc");
       var por_pagar=parseInt(tot_doc)-parseInt(saldo);
       if(!por_pagar){
           por_pagar=0;
       }
       $("#titulo_pago").html("<p style='color:blue;font-size:20px;font-weight:bold'>PAGO FACTURA "+num_doc+"</p><p style='font-weight:bold'>MONTO "+tot_doc.toLocaleString('es-CL')+"</p><p style='font-weight:bold;color:red'>SALDO POR PAGAR "+por_pagar.toLocaleString('es-CL')+"</p>");
       $("#docu_ing").val(num_doc); 
       $("#docu_saldo").val(por_pagar);
       $("#fpago_pag").val(0);
       $("#fpago_pag").change();
         
     });
     $("#btn-pagar-docu").on('click', function () {
        var num_fac = $("#docu_ing").val();
        var fpago = $("#fpago_pag").val();
        var monto_pago = $("#monto_pago").val();
        var num_docu_pag = $("#num_docu_pago").val();
        var saldo = $("#docu_saldo").val();
    
        if (fpago === "CONTADO" && (!monto_pago || monto_pago == 0)) {
            toastr.warning("Ingrese monto del pago");
            $("#monto_pago").focus();
            return false;
        }
    
        if ((fpago === "CHEQUE A FECHA" || fpago === "CHEQUE AL DIA") && (!monto_pago || monto_pago == 0)) {
            toastr.warning("Ingrese monto del pago");
            $("#monto_pago").focus();
            return false;
        }
    
        if ((fpago === "CHEQUE A FECHA" || fpago === "CHEQUE AL DIA") && (!num_docu_pag || num_docu_pag == 0)) {
            toastr.warning("Ingrese número del documento");
            $("#num_docu_pago").focus();
            return false;
        }
    
        if (parseInt(monto_pago) > parseInt(saldo)) {
            toastr.warning("Monto del pago sobrepasa el saldo del documento");
            $("#monto_pago").focus();
            return false;
        }
    
        $.ajax({
            url: '/compras/registrar-pago',
            type: 'POST',
            data: {
                nfac: num_fac,
                forpag: fpago,
                valpag: monto_pago,
                ndocpag: num_docu_pag
            },
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            success: function (response) {
                if (response.estado === "OK") {
                    $("#monto_pago").val("");
                    $("#num_docu_pago").val("");
                    $("#fpago_pag").val(0);
                    $('#modulo_pago').modal('hide');
                    trae_documentos();
                } else {
                    toastr.error("No se pudo registrar el pago.");
                }
            },
            error: function () {
                toastr.error("Error al comunicarse con el servidor.");
            }
        });
    
        return false;
    });
    function filtro(tip){
        console.log(tip);
        switch(tip){
            case 1:
                trae_facturas('NP');
            break;
            
            case 2:
                trae_facturas('P');
            break;
            
            case 3:
                trae_documentos();
            break;
        }
    }