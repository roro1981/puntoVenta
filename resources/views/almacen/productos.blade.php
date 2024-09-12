<script type="text/javascript" src="js/almacen/productos.js"></script>
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Gestión de Productos</h1>
            <!-- Botón para agregar un nuevo producto -->
            <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#createProductModal">
                Crear Producto
            </button>
            <!-- Tabla de productos -->
            <table id="productsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ejemplo de datos para productos -->
                    <tr>
                        <td>1</td>
                        <td>Producto A</td>
                        <td>$10.00</td>
                        <td>100</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-1">Editar</button>
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Producto B</td>
                        <td>$20.00</td>
                        <td>50</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-1">Editar</button>
                            <button class="btn btn-danger btn-sm">Eliminar</button>
                        </td>
                    </tr>
                    <!-- Aquí se agregarán más filas dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para crear producto -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createProductModalLabel">Crear Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createProductForm">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="productName" required>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="productPrice" required>
                    </div>
                    <div class="mb-3">
                        <label for="productQuantity" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="productQuantity" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>