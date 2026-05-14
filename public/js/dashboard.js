document.addEventListener("DOMContentLoaded", function () {
    // Deshabilitar caché en peticiones AJAX GET para que los módulos
    // siempre carguen datos frescos del servidor (evita que cajaAbierta
    // quede desactualizado al navegar de vuelta a un módulo)
    $.ajaxSetup({ cache: false });

    function updateClock() {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        document.getElementById('clock').textContent = `${hours}:${minutes}`;
    }

    // Inicializar el reloj con la hora actual desde el sistema del usuario
    updateClock();

    // Actualizar el reloj cada segundo
    setInterval(updateClock, 1000);

    function limpiarAyudaFlotante() {
        var $modalAyuda = $('#modalAyudaModulo');
        if ($modalAyuda.length) {
            try {
                $modalAyuda.modal('hide');
            } catch (e) {
                // noop
            }
        }

        // Remueve botón y modal de ayuda previamente inyectados al body.
        $('[data-ayuda-flotante], #btnAyudaModulo, #modalAyudaModulo').remove();
    }

    $.ajax({
        url: '/users/menus',
        method: 'GET',
        success: function (response) {
            const menuContainer = document.getElementById('menu_lateral');
            let menuItem = `<ul class="sidebar-menu"><li class="header"></li>`;
            response.forEach(menu => {
                menuItem += `<li class="treeview"><a href="#">`;
                menuItem += `<i class="${menu.fa}"></i>`;
                menuItem += `<span>${menu.name}</span><i class="fa fa-angle-left pull-right"></i></a>`;
                menuItem += `<ul class="treeview-menu">`;
                menu.submenus.forEach(submenu => {
                    //menuItem += '<li>${submenu.name} (${menu.route}${submenu.route}) </li>';
                    menuItem += `<li class="opcion_menu"><a href="${menu.route}${submenu.route}"><i class="fa fa-circle-o"></i> ${submenu.name}</a></li>`;
                });
                menuItem += `</ul></li>`;
            });
            menuItem += `</ul>`;
            menuContainer.innerHTML += menuItem;
        },
        error: function (xhr) {
            console.error('Error al obtener los menús:', xhr);
        }
    });

    $(document).ready(function () {
        $(document).on('click', '.opcion_menu a', function (e) {
            e.preventDefault();
            var ruta = $(this).attr('href');
            var menu_nombre = $(this).text().trim();
            var iconClass = $(this).closest('ul.treeview-menu').closest('li').find('i.fa').first().attr('class');

            $("#titulo").html(menu_nombre + ' <i style="color:black;font-size:20px" class="' + iconClass + '"></i>');
            limpiarAyudaFlotante();
            $('#contenido').load(ruta, function (response, status, xhr) {
                if (status == "error") {
                    var errorHtml = `
                    <div style="text-align: center; margin-top: 50px;">
                        <img src="/img/404.png" alt="Error 404">
                    </div>
                `;
                    $('#contenido').html(errorHtml);
                }
            });
        });
    });

});


