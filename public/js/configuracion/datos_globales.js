$(document).ready(function() {
    cargaGlobales();    
});
function cargaGlobales(){

    $('#tabla_variables').DataTable({
        responsive: true,
        destroy: true,
      "ajax": {
        "url": "/configuracion/datos_globales",
        "type": "GET"
      },
      "columns": [
        { "data": "nom_var", "width": "25%" },
        { "data": "valor_var", "width": "20%", "className": "text-center" },
        { "data": "descrip_var", "width": "45%"},
        { "data": "actions", "width": "10%", "className": "text-center" }
      ],
      "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
      "pageLength": 5,
      "searching": true,
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}

$(document).off('click', '.editar');
$(document).on('click', '.editar', function(e) {
    e.preventDefault();

    var id = $(this).data('id');
    
    var valor = $('#valor_var_' + id).val();
    
    if ($.trim(valor) === '') {
        toastr.warning('El valor variable no puede estar vacío');
        return;
    }

    $.ajax({
        url: '/configuracion/update-global/'+id, 
        method: 'PUT',
        data: {
            valor_var: valor,
            _token: $("#token").val()
        },
        success: function(response) {
            toastr.success('Valor actualizado exitosamente');
            $('#contenido').load('/configuracion/datos_glob');
        },
        error: function(xhr, status, error) {
           toastr.error("Error "+xhr.responseJSON.error+"<br>"+xhr.responseJSON.message);
        }
    });
});


