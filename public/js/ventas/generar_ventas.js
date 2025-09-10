$(document).ready(function() {
    let cart = [];

    function formatCurrency(amount) {
        return '$ ' + new Intl.NumberFormat('es-CL').format(amount);
    }

    function updateTotal() {
        let total = cart.reduce((sum, item) => {
            let discount = parseFloat(item.discount) || 0;
            return sum + (item.precio_venta * item.quantity * (1 - discount/100));
        }, 0);
        $('#cart-total').text(formatCurrency(Math.round(total)));
    }

    function renderCart() {
        let cartHtml = cart.map((item, index) => `
            <div class="product-row" data-index="${index}">
                <div class="quantity-controls">
                    <button class="quantity-btn minus-btn">-</button>
                    <input type="text" class="quantity-input" value="${item.quantity}">
                    <button class="quantity-btn plus-btn">+</button>
                </div>
                <div class="product-info">
                    <div class="product-name">${item.descripcion}</div>
                    <div class="product-price">$/unidad: ${formatCurrency(item.precio_venta)}</div>
                </div>
                <div class="product-total">${formatCurrency(Math.round(item.precio_venta * item.quantity))}</div>
                <select class="discount-select">
                    <option ${item.discount === 0 ? 'selected' : ''}>0 %</option>
                    <option ${item.discount === 5 ? 'selected' : ''}>5 %</option>
                    <option ${item.discount === 10 ? 'selected' : ''}>10 %</option>
                    <option ${item.discount === 15 ? 'selected' : ''}>15 %</option>
                </select>
                <button class="delete-btn">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `).join('');
        
        $('#cart-items').html(cartHtml);
        updateTotal();
    }

    $('#product-code').on('keypress', function(e) {
        if(e.which === 13) { 
            let code = $(this).val();
            let input = $(this);
            
            $.ajax({
                url: '/ventas/buscarProducto',
                method: 'GET',
                data: { q: code, tipo:1 },
                success: function(product) {
                    if(product.length > 0){
                        cart.push({
                            id: product[0].id,
                            descripcion: product[0].descripcion,
                            precio_venta: product[0].precio_venta,
                            quantity: 1,
                            discount: 0
                        });
                        
                        renderCart();
                        input.val('');
                    }else{
                        toastr.error("Producto no existe");
                    }
                    
                }
            });
        }
    });

    $(document).on('click', '.plus-btn', function() {
        let index = $(this).closest('.product-row').data('index');
        cart[index].quantity++;
        renderCart();
    });

    $(document).on('click', '.minus-btn', function() {
        let index = $(this).closest('.product-row').data('index');
        if(cart[index].quantity > 1) {
            cart[index].quantity--;
            renderCart();
        }
    });

    $(document).on('change', '.discount-select', function() {
        let index = $(this).closest('.product-row').data('index');
        cart[index].discount = parseInt($(this).val());
        renderCart();
    });

    $(document).on('click', '.delete-btn', function() {
        let index = $(this).closest('.product-row').data('index');
        cart.splice(index, 1);
        renderCart();
    });

    $('#product-search').on('keyup', function() {
        let query = $(this).val().toLowerCase();
        if (!query){
            $('#product-list').html("");
            return false;
        }
        $.ajax({
            url: '/ventas/buscarProducto',
            method: 'GET',
            data: { q: query, tipo:2 },
            success: function(products) {
                
                let productsHtml = products.map(product => `
                    <div class="product-item">
                        <span>${product.descripcion}</span>
                        <div class="product-actions">
                            <i class="fa fa-plus action-icon add-to-cart" data-id="${product.id}" data-name="${product.descripcion}" data-price="${product.precio_venta}"></i>
                        </div>
                    </div>
                `).join('');
                
                $('#product-list').html(productsHtml);
            }
        });
    });

    $(document).on('click', '.add-to-cart', function() {
        let product = {
            id: $(this).data('id'),
            descripcion: $(this).data('name'),
            precio_venta: $(this).data('price'),
            quantity: 1,
            discount: 0
        };
        
        cart.push(product);
        renderCart();
    });

    $('#cancel-btn').click(function() {
        Swal.fire({
            title: "Cancelar venta",
            text: "¿Está seguro de cancelar la venta?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Sí",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                cart = [];
                renderCart();
            } else {
                toastr.error("Eliminación cancelada");
            }
        });
    });

    $('#save-draft-btn').click(function() {

        if (cart.length === 0) {
            toastr.warning('El carrito está vacío. Agrega productos antes de guardar el borrador.');
            return;
        }

        let uuid_borrador = crypto.randomUUID(); 
        const now = new Date();
        const timezoneOffset = now.getTimezoneOffset() * 60000;
        const localTime = new Date(now.getTime() - timezoneOffset);
        const fecha = localTime.toISOString().slice(0, 19).replace('T', ' ');
    
        let productos = cart.map(item => ({
            id: item.id,
            descripcion: item.descripcion,
            precio_venta: item.precio_venta,
            cantidad: item.quantity,
            descuento: item.discount,
            uuid_borrador: uuid_borrador,
            fec_creacion: fecha
        }));
    
        $.ajax({
            url: '/ventas/guardar-borrador',
            method: 'POST',
            data: {
                _token: $("#token").val(),
                productos: productos
            },
            success: function (response) {
                if (response.status === 'OK') {
                    toastr.success(response.message);
                    cart = [];
                    renderCart();
                } else {
                    toastr.warning(response.message);
                }
            }
        });
    });

    $('#pay-btn').click(function() {
        if(cart.length === 0) {
            toastr.warning('Agregue productos al carrito para continuar');
            return;
        }
        
        alert('Procesando pago...');
    });

    $('.tab-btn').on('click', function() {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        const tab = $(this).data('tab');
        $('#tab-' + tab).addClass('active');
    });

    $('#cart-items').on('keypress', '.quantity-input', function (e) {
        let char = String.fromCharCode(e.which);
        let allowed = /^[0-9.]$/;
    
        if (!allowed.test(char)) {
            e.preventDefault(); // bloquea letras y símbolos
        }
    
        // Solo permitir un punto decimal
        if (char === '.' && $(this).val().includes('.')) {
            e.preventDefault();
        }
    })

    $('#cart-items').on('input', '.quantity-input', function () {
        let $input = $(this);
        let index = $input.closest('.product-row').data('index');

        let value = $input.val().replace(/[^0-9.]/g, '');
        let parts = value.split('.');

        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        if (parts[1]?.length > 1) {
            parts[1] = parts[1].substring(0, 1);
            value = parts[0] + '.' + parts[1];
        }

        $input.val(value);

        let newQuantity = parseFloat(value);
        if (isNaN(newQuantity) || newQuantity <= 0) {
            newQuantity = 1;
        }

        // Actualizar el carrito sin redibujar todo
        cart[index].quantity = newQuantity;

        // Actualizar solo el total de ese producto
        let total = Math.round(cart[index].precio_venta * cart[index].quantity);
        $input.closest('.product-row').find('.product-total').text(formatCurrency(total));

        // Actualizar el total general
        updateTotal();
    });
});