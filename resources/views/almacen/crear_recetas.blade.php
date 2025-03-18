
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Nueva Receta</h4>
        </div>
        <div class="card-body">
            <form id="formReceta">
                <!-- Fila 1: Código y Nombre -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Ej: REC-001">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej: Pizza Napolitana">
                    </div>
                </div>

                <!-- Fila 2: Categoría e Imagen -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" name="categoria" class="form-control">
                            <option value="">Seleccione una categoría</option>
                            <!-- Opciones de ejemplo; reemplaza con tus datos -->
                            <option value="1">Entradas</option>
                            <option value="2">Platos Fuertes</option>
                            <option value="3">Postres</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="imagen">Imagen</label>
                        <input type="file" id="imagen" name="imagen" class="form-control-file">
                    </div>
                </div>

                <!-- Fila 3: Descripción (ocupa todo el ancho) -->
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3"
                                  placeholder="Descripción de la receta..."></textarea>
                    </div>
                </div>

                <!-- Fila 4: Precio Costo y Precio Venta -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="precio_costo">Precio Costo</label>
                        <input type="text" id="precio_costo" name="precio_costo" class="form-control" placeholder="0.00" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="precio_venta">Precio Venta</label>
                        <input type="text" id="precio_venta" name="precio_venta" class="form-control" placeholder="0.00">
                    </div>
                </div>
            </form>
        </div>
    </div> <!-- Fin card Receta -->

    <!-- Card de Ingredientes -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Ingredientes de la Receta</h5>
        </div>
        <div class="card-body">
            <!-- Fila para búsqueda y cantidad -->
            <div class="form-row">
                <div class="form-group col-md-5 position-relative">
                    <label for="buscarIngrediente">Buscar Ingrediente</label>
                    <input type="text" id="buscarIngrediente" class="form-control" placeholder="Escriba para buscar...">
                    <!-- Contenedor para sugerencias (typeahead) -->
                    <div id="sugerencias" class="list-group position-absolute w-100" style="z-index: 999;"></div>
                </div>
                <div class="form-group col-md-3">
                    <label for="cantidad">Cantidad</label>
                    <input type="number" id="cantidad" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group col-md-4 d-flex align-items-end">
                    <button type="button" id="agregarIngrediente" class="btn btn-success btn-block">Agregar Ingrediente</button>
                </div>
            </div>

            <!-- Tabla de ingredientes -->
            <div class="table-responsive">
                <table class="table table-bordered mt-3" id="tablaIngredientes">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Precio Costo</th>
                            <th scope="col">Unidad</th>
                            <th scope="col" style="width: 90px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se llenará dinámicamente con jQuery -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer de la Card con el botón de Guardar -->
        <div class="card-footer text-right">
            <button type="submit" form="formReceta" class="btn btn-primary">Guardar Receta</button>
        </div>
    </div> <!-- Fin card Ingredientes -->
</div>

