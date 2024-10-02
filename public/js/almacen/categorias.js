$(document).ready(function() {
    cargaCategorias();  
});
function cargaCategorias(){

    $('#tabla_categorias').DataTable({
        responsive: true,
        destroy: true,
      "ajax": {
        "url": "/almacen/traeCategorias",
        "type": "GET"
      },
      "columns": [
        { "data": "id" },
        { "data": "descripcion_categoria" },
        { "data": "prods_asociados" },
        { "data": "actions" }
      ],
      "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
      "pageLength": 10,
      "searching": true,
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
    },
           
    });
}

$('#createCatForm').submit(function(event) {
    event.preventDefault(); // Evita que el formulario se envíe por defecto

    var formData = {
        'descripcion_categoria': $('#descripcion_categoria').val()
    };

    $.ajax({
        type: 'POST',
        url: '/almacen/createCat', // Ruta a la que se envía la solicitud
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $("#token").val()
        },
        success: function(data) {
            toastr.success(data.message);
            $("#createCatModal").modal("hide");
            $('#contenido').load('/almacen/categorias');
        },
        error: function(xhr, status, error) {
            var errorCode = xhr.status;
            var errorResponse = xhr.responseJSON;

            if (errorResponse) {
                var errorMessages = [];

                $.each(errorResponse.errors, function(field, messages) {
                    $.each(messages, function(index, message) {
                        errorMessages.push(message+"<br>");
                    });
                });

                toastr.error(errorMessages, "Error " + errorCode);
            }
        }
    });
});

$('#editCatForm').submit(function(event) {
    event.preventDefault();

    var formData = {
        'descripcion_categoria': $('#descripcion_categoria_edit').val()
    };

    var cat_id=$("#cat_id").val();

    $.ajax({
        type: 'PUT',
        url: '/almacen/'+ cat_id +'/edit',
        data: formData, 
        headers: {
            'X-CSRF-TOKEN': $('#token').val()
        },
        success: function(data) {
            toastr.success(data.message);
            $("#editCatModal").modal("hide");
            $('#contenido').load('/almacen/categorias');
        },
        error: function(xhr, status, error) {
            var errorCode = xhr.status;
            var errorResponse = xhr.responseJSON;

            if (errorResponse) {
                var errorMessages = [];

                $.each(errorResponse.errors, function(field, messages) {
                    $.each(messages, function(index, message) {
                        errorMessages.push(message+"<br>");
                    });
                });

                toastr.error(errorMessages, "Error " + errorCode);
            }
        }
    });
});
$('#editCatModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var catId = button.data('cat');
    var modal = $(this);
  
    // Llena el formulario con los datos del usuario seleccionado
    $.ajax({
      type: 'GET',
      url: '/almacen/' + catId + '/show',
      success: function(data) {
        modal.find('#id_edit').val(data.id);
        modal.find('#descripcion_categoria_edit').val(data.descripcion_categoria);
        modal.find('#cat_id').val(catId);
      }
    });
  });
  $(document).on('click', '.eliminar', function(event) {
    event.preventDefault();
    var catId = $(this).data('cat'); 
    var nombreCat = $(this).data('namecat');
    Swal.fire({
        title: "Eliminar categoria",
        text: "¿Estás seguro de eliminar la categoria "+nombreCat+"?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'DELETE',
                url: '/almacen/' + catId + '/delete',
                headers: {
                    'X-CSRF-TOKEN': $('#token').val()
                },
                success: function(data) {
                  toastr.success(data.message);
                  $('#contenido').load('/almacen/categorias');
                },
                error: function(xhr, status, error) {
                    toastr.error("Error "+xhr.responseJSON.error+"<br>"+xhr.responseJSON.message);
                }
            })
        }else{
            toastr.error("Eliminación cancelada");
        }
      });
    
  });

