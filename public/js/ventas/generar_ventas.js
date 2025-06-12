$(document).ready(function() {
    let cart = [];

    // Function to format currency
    function formatCurrency(amount) {
        return '$ ' + new Intl.NumberFormat('es-CL').format(amount);
    }

    // Function to update cart total
    function updateTotal() {
        let total = cart.reduce((sum, item) => {
            let discount = parseFloat(item.discount) || 0;
            return sum + (item.precio_venta * item.quantity * (1 - discount/100));
        }, 0);
        $('#cart-total').text(formatCurrency(total));
    }

    // Function to render cart items
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
                <div class="product-total">${formatCurrency(item.precio_venta * item.quantity)}</div>
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

    // Handle product code search
    $('#product-code').on('keypress', function(e) {
        if(e.which === 13) { // Enter key
            let code = $(this).val();
            let input = $(this);
            
            // Simulate API call to get product details
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

    // Handle quantity changes
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

    // Handle discount changes
    $(document).on('change', '.discount-select', function() {
        let index = $(this).closest('.product-row').data('index');
        cart[index].discount = parseInt($(this).val());
        renderCart();
    });

    // Handle item removal
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

    // Handle adding product from search results
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

    // Handle cancel button
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

    // Handle save draft button
    $('#save-draft-btn').click(function() {
        // Simulate saving draft
        toastr.success('Borrador guardado exitosamente');
    });

    // Handle pay button
    $('#pay-btn').click(function() {
        if(cart.length === 0) {
            toastr.warning('Agregue productos al carrito para continuar');
            return;
        }
        
        // Simulate payment process
        alert('Procesando pago...');
    });

    $('.tab-btn').on('click', function() {
        // Quitar active a todos los botones
        $('.tab-btn').removeClass('active');
        // Agregar active al clicado
        $(this).addClass('active');
    
        // Ocultar todos los tab-content
        $('.tab-content').removeClass('active');
    
        // Mostrar el contenido correspondiente
        const tab = $(this).data('tab');
        $('#tab-' + tab).addClass('active');
    });
});