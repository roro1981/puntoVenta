$(document).ready(function () {
    cargaRecetas();
    $('.filter-pill').on('click', function (e) {
        e.preventDefault(); // Evita saltos de página
        let categoria = $(this).data('categoria');
        let texto = categoria === '' ? 'Todas' : categoria;
        $('#categoriaSeleccionada').text('Categoría: ' + texto);

    });
    $('.category-pills').on('wheel', function (e) {
        e.preventDefault();
        this.scrollLeft += e.originalEvent.deltaY;
    });
});

function cargaRecetas() {
    var fechaActual = new Date();
    var dia = fechaActual.getDate();
    var mes = fechaActual.getMonth() + 1; // +1 porque los meses van de 0 a 11
    var ano = fechaActual.getFullYear();

    var fechaFormateada = `${dia.toString().padStart(2, '0')}-${mes.toString().padStart(2, '0')}-${ano}`;

    $('#tabla_recetas').DataTable({
        responsive: true,
        destroy: true,
        "ajax": {
            "url": "/almacen/recetasCarga",
            "type": "GET"
        },
        "columns": [
            { "data": "imagen" },
            { "data": "nombre" },
            { "data": "descripcion" },
            { "data": "actions" }
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        "pageLength": 10,
        "searching": true,
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
}