$(document).ready(function() {
    $('.selectpicker').selectpicker({
         style: 'btn-default'
     });
  const quitaAcentos = (str) => {
  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
} 
});
$('#fecha_desde, #fecha_hasta').datepicker({
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

    $("#btn_ver").unbind('click').bind('click', function () {  
        var tipo=$("#tip_movi").val();
        var desde=$("#fecha_desde").val();
        var hasta=$("#fecha_hasta").val();
        var idprod=$("#produ_movi").val();
        if(tipo==0 || idprod==0 || desde=="" || hasta==""){
            toastr.warning("Debe ingresar tipo de movimiento, producto y rango de fechas de consulta");
            return false;
        }
        
        var f1=desde.split("/");
        var f2=hasta.split("/");
        desde=f1[2]+"/"+f1[1]+"/"+f1[0];
        hasta=f2[2]+"/"+f2[1]+"/"+f2[0];
        desde2=f1[0]+"-"+f1[1]+"-"+f1[2];
        hasta2=f2[0]+"-"+f2[1]+"-"+f2[2];
        
        if(Date.parse(hasta) < Date.parse(desde)){
          toastr.warning("Fecha inicial (desde) no puede ser mayor a la final (hasta)");
          return false;
        }
      $.ajax({
            type	: "GET",
            url		: "/reportes/trae_movimientos",
            data	: {
                tipo_mov 	: tipo,
                idp         : idprod,
                fec_desde   : desde,
                fec_hasta   : hasta
            },
        
            success: function(r){
                if(r !="NO"){
                   $(".movis_det").html(r);
                   $("#datos_repo").val("Movimientos "+$('#produ_movi option:selected').text()+" "+desde2+" al "+hasta2);
                   $("#datos_repo2").val($('#tip_movi option:selected').text());
                   $("#tmov").val($('#tip_movi').val());
                   $("#idpr").val(idprod);
                   $("#fde").val(desde);
                   $("#fha").val(hasta);
                }
            }
        });	
    });

    $("#excel").unbind('click').bind('click', function () {
        var filas = 0;
        var tipo = $("#tmov").val();
        var id = $("#idpr").val();
        var fec1 = $("#fde").val();
        var fec2 = $("#fha").val();
    
        $("#tbl_movis tbody tr").find('td:eq(0)').each(function () {
            filas += 1;
        });
    
        if (filas > 0) {
            const url = `/reportes/exportar-movimientos?tipo_mov=${tipo}&idprod=${id}&desde=${fec1}&hasta=${fec2}`;
            window.open(url, '_blank');
        } else {
            toastr.warning("No hay registros para exportar a Excel");
        }
    });