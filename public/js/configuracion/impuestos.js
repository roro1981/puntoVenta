$(document).ready(function() {
    cargaImpuestos();    
});
function cargaImpuestos(){

    $('#tabla_impuestos').DataTable({
        responsive: true,
        destroy: true,
      "ajax": {
        "url": "/configuracion/impuestos-table",
        "type": "GET"
      },
      "columns": [
        { "data": "nom_imp", "width": "20%" },
        { "data": "valor_imp", "width": "10%", "className": "text-center" },
        { "data": "descrip_imp", "width": "45%"},
        { "data": "last_activity", "width": "15%", "className": "text-center" },
        { "data": "actions", "width": "10%", "className": "text-center" }
      ],
      "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
      "pageLength": 5,
      "searching": true,
      "ordering": false,
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}
$(document).off('click', '.editar');
$(document).on('click', '.editar', function(e) {
    e.preventDefault();
    toastr.clear();

    var id = $(this).data('id');
    
    var valor = $('#valor_imp_' + id).val();
    
    if ($.trim(valor) === '') {
        toastr.warning('El valor de impuesto no puede estar vacío');
        return;
    }

    var regex = /^\d+(\.\d)?$/;
    if (!regex.test(valor)) {
        toastr.warning('El valor de impuesto debe ser un número de maximo 3 digitos y 1 decimal');
        return;
    }
    $.ajax({
        url: '/configuracion/update-impuesto/'+id, 
        method: 'PUT',
        data: {
            valor_imp: valor,
            _token: $("#token").val()
        },
        success: function(response) {
            toastr.success('Impuesto actualizado exitosamente');
            $('#contenido').load('/configuracion/impuestos');
        },
        error: function(xhr, status, error) {
           toastr.error("Error "+xhr.responseJSON.error+"<br>"+xhr.responseJSON.message);
        }
    });
});


