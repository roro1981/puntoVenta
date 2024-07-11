/**
 * Coded by Rodrigo Panes.
 * E-Mail   : rodrigopanes@gmail.com
 * Date     : 09-03-2019/
 * Time     : 02:00 PM
 * Coded to :
 */

function soloNumeros(e){
	var evento = window.event || e;
	if((evento.charCode < 48 || evento.charCode > 57) && (evento.keyCode != 8 && evento.keyCode != 37 && evento.keyCode != 39 && evento.keyCode != 46 && evento.keyCode != 9 && evento.keycode != 101)){
		return false;
	}
}
function soloNumeros2(e){
	var evento = window.event || e;
	if((evento.charCode < 48 || evento.charCode > 57) && (evento.keyCode != 8 && evento.keyCode != 37 && evento.keyCode != 39 && evento.keyCode != 46 && evento.keyCode != 9 && evento.keycode != 101 && evento.keycode != 45 && evento.keycode != 43 && evento.keycode != 44 && evento.keycode != 47 && evento.keycode != 110)){
		return false;
	}
}
function soloLetras(e){
    var key = window.event || e;
  if ((key.charCode < 97 || key.charCode > 122)//letras mayusculas
                && (key.charCode < 65 || key.charCode > 90) //letras minusculas
                && (key.charCode != 45) //retroceso
                && (key.charCode != 241) //ñ
                 && (key.charCode != 209) //Ñ
                 && (key.charCode != 32) //espacio
                 && (key.charCode != 225) //á
                 && (key.charCode != 233) //é
                 && (key.charCode != 237) //í
                 && (key.charCode != 243) //ó
                 && (key.charCode != 250) //ú
                 && (key.charCode != 193) //Á
                 && (key.charCode != 201) //É
                 && (key.charCode != 205) //Í
                 && (key.charCode != 211) //Ó
                 && (key.charCode != 218) //Ú
 
                )
                return false;  
}

function validaRut(rut){
 var suma=0;
 var arrRut = rut.split("-");
 var rutSolo = arrRut[0];
 var verif = arrRut[1];
 var continuar = true;
 for(i=2;continuar;i++){
  suma += (rutSolo%10)*i;
  rutSolo = parseInt((rutSolo /10));
  i=(i==7)?1:i;
  continuar = (rutSolo == 0)?false:true;
 }
 resto = suma%11;
 dv = 11-resto;
 if(dv==10){
  if(verif.toUpperCase() == 'K')
   return true;
 }
 else if (dv == 11 && verif == 0)
  return true;
 else if (dv == verif)
  return true;
 else
  return false;
}
function validarEmail(valor) {
  if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/.test(valor)){
   return true;
  } else {
   return false;
  }
}



