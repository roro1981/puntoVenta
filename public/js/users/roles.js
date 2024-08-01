$(document).ready(function() {
    cargaRoles();   
    $(document).on('click', '.ver-btn', function() {
        var rolId = $(this).data('id');
        $.ajax({
            url: '/roles/' + rolId + '/ver',
            method: 'GET',
            success: function(response) {
                var roleName = response.role_name; // Obtener el nombre del rol
                var content = '';
                response.menus.forEach(function(menu) {
                    content += '<div class="list-group-item">';
                    content += '<h5 style="font-weight:bold" class="mb-1">' + menu.menu_name + '</h5>';
                    content += '<ul class="ml-3">';
                    menu.submenus.forEach(function(submenu) {
                        content += '<li class="mb-1">' + submenu.submenu_name + '</li>';
                    });
                    content += '</ul>';
                    content += '</div>';
                });
                $('#roleMenusContent').html(content);
                $('#roleMenusModalLabel').text('Menús y Submenús del Rol: ' + roleName);
                $('#roleMenusModal').modal('show');
            },
            error: function() {
                alert('Hubo un error al obtener los menús.');
            }
        });
    });
});
function cargaRoles(){
    var fechaActual = new Date();
    var dia = fechaActual.getDate();
    var mes = fechaActual.getMonth() + 1; // +1 porque los meses van de 0 a 11
    var ano = fechaActual.getFullYear();

    var fechaFormateada = `${dia.toString().padStart(2, '0')}-${mes.toString().padStart(2, '0')}-${ano}`;

    $('#tabla_roles').DataTable({
        responsive: true,
        destroy: true,
      "ajax": {
        "url": "/roles",
        "type": "GET"
      },
      "columns": [
        { "data": "role_name", "width": "25%" },
        { "data": "asociados", "width": "20%", "className": "text-center" },
        { "data": "created_at", "width": "20%" },
        { "data": "updated_at", "width": "20%" },
        { "data": "actions", "width": "10%", "className": "text-center" }
      ],
      "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
      "pageLength": 5,
      "searching": true,
      "language": {
        "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
    },
            "dom": 'Bfrtip',
            "buttons": [
                {
                    "extend": 'excelHtml5',
                    "text": 'Exportar a Excel',
                    className: 'btn btn-success',
                    title: 'Listado de roles al '+fechaFormateada,
                    filename: 'Listado de roles al '+fechaFormateada,
                    exportOptions: {
                        columns: function (idx, data, node) {
                            // Ocultar columnas de índice 1 y 4
                            return (idx !== 1 && idx !== 4);
                        }
                    }
                },
                {
                    "extend": 'print',
                    "text": 'Imprimir',
                    className: 'btn btn-primary',
                    exportOptions: {
                        columns: function (idx, data, node) {
                            // Ocultar columnas de índice 1 y 4
                            return (idx !== 1 && idx !== 4);
                        }
                    },
                    title: 'Listado de roles al '+fechaFormateada,
                    customize: function(win) {
                        var last = null;
                        var current = null;
                        var bod = [];

                        var css = '@page { size: landscape; }',
                            head = win.document.head || win.document.getElementsByTagName('head')[0],
                            style = win.document.createElement('style');

                        style.type = 'text/css';
                        style.media = 'print';

                        if (style.styleSheet) {
                            style.styleSheet.cssText = css;
                        } else {
                            style.appendChild(win.document.createTextNode(css));
                        }

                        head.appendChild(style);
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Exportar a PDF',
                    className: 'btn btn-danger',
                    exportOptions: {
                        columns: function (idx, data, node) {
                            // Ocultar columnas de índice 1 y 4
                            return (idx !== 1 && idx !== 4);
                        }
                    },
                    title: 'Listado de roles al '+fechaFormateada,
                    filename: 'Listado de roles al '+fechaFormateada,
                    orientation: 'landscape',
                    customize: function(doc) {
                        doc.pageMargins = [20, 20, 20, 20];
                        doc.pageSize = 'A4';
                        doc.pageOrientation = 'landscape';
                    }
                }
            ]
    });
}