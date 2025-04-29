<script type="text/javascript" src="js/almacen/editar_recetas.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/crear_recetas.css">

<div class="container">
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <button id="volver" class="btn btn-secondary mb-3">
        ← Volver al listado
    </button>

    <h2>Editar Receta</h2>

        <div class="row">
            <div class="col-md-6">

                {{-- Código de la receta --}}
                <div class="form-group mb-3">
                    <label for="codigo">Código</label>
                    <input type="text" name="codigo" id="codigo" class="form-control"
                           value="{{ old('codigo', $receta->codigo) }}" required readonly>
                </div>

                {{-- Nombre de la receta --}}
                <div class="form-group mb-3">
                    <label for="nombre">Nombre de la Receta</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                           value="{{ old('nombre', $receta->nombre) }}" required>
                </div>

                {{-- Categoría --}}
                <div class="form-group mb-3">
                    <label for="categoria_id">Categoría</label>
                    <select name="categoria_id" id="categoria_id" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($categorias as $cat)
                            <option 
                                value="{{ $cat->id }}"
                                {{ (old('categoria_id', $receta->categoria_id) == $cat->id) ? 'selected' : '' }}
                            >
                                {{ $cat->descripcion_categoria }} 
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Descripción o preparación --}}
                <div class="form-group mb-3">
                    <label for="descripcion">Preparación / Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control">{{ old('descripcion', $receta->descripcion) }}</textarea>
                </div>

            </div> {{-- col-md-6 --}}

            <div class="col-md-6">

                {{-- Precio costo (lectura) --}}
                <div class="form-group mb-3">
                    <label for="precio_costo">Precio Costo</label>
                    <input type="text" name="precio_costo" id="precio_costo" class="form-control"
                           value="{{ old('precio_costo', $receta->precio_costo) }}"
                           readonly>
                </div>

                {{-- Margen (si usas margen) --}}
                <div class="form-group mb-3">
                    <label for="margen">Margen (%)</label>
                    <input type="text" id="margen" class="form-control" placeholder="Ej: 25.0" onkeypress="return soloNumeros(event)">
                </div>

                {{-- Precio venta --}}
                <div class="form-group mb-3">
                    <label for="precio_venta">Precio Venta</label>
                    <input type="text" name="precio_venta" id="precio_venta" class="form-control" onkeypress="return soloNumeros(event)"
                           value="{{ old('precio_venta', $receta->precio_venta) }}">
                </div>

                {{-- Foto (opcional) --}}
                <div class="form-group mb-3">
                    <label for="image" class="form-label">Foto receta</label>
                    <div class="d-flex align-items-center">
                        <!-- Contenedor para el input de archivo y botón -->
                        <div class="flex-grow-1 pr-3">
                            <img class="card-img-top" src="{{ $receta->imagen ? $receta->imagen : '/img/fotos_prod/sin_imagen.jpg' }}"
                                 style="max-width:100px; max-height:100px; object-fit:cover;float:right"
                                 alt="Foto de la receta">
                            <input type="file" 
                                   title="Solo formato jpg, png o gif" 
                                   class="form-control-file" 
                                   id="image">
                            <input type="button" class="btn btn-primary upload mt-2" value="Subir">
                            <input type="hidden" id="foto_receta" name="foto_receta">
                        </div>
                        <!-- Imagen: se alinea a la derecha gracias a flex-grow-1 en el contenedor anterior -->
                        
                    </div>
                </div>
            </div> {{-- col-md-6 --}}
        </div> {{-- row --}}

        <hr>

        <div class='col-md-8' >
            <form class="form-inline" role="form">
            <div class="form-group" style="position: relative; display: inline-block;">
               <span style="width:140px; font-size:15px; font-weight:bold; display:inline-block;" class="form-control bg-aqua">
                   Ingrese código
               </span>
           
               <!-- Campo de texto -->
               <input 
                   type="text" 
                   class="form-control" 
                   id="insumo" 
                   autocomplete="off"
                   style="width:150px; font-size:15px; text-align:center; font-weight:bold; display:inline-block;"
                   autofocus 
                   tabindex="1"
               >
           
               <div id="listaResultados" class="suggestion-box" style="display: none;"></div>
            </div>
                <div class="form-group">
                    <span style="width:85px;font-size:15px;font-weight: bold;" class="form-control bg-blue">Cantidad</span><input type='text' class="form-control" autocomplete="off" id='cant_insumo' onkeypress="return soloNumeros(event)" style="width:90px;font-size:15px; text-align:center; font-weight: bold;" tabindex="2">		   
                </div>
                <button type="button" id="act_receta" class="btn btn-success">Guardar cambios</button>
                <input type="hidden" id="uuid_receta" value="{{$receta->uuid}}">
            </form>
        </div>
        <br>
        {{-- Tabla de ingredientes --}}
        <div class='col-md-12'>
            <div>
                <div class='box-header'>
                    <h3 class='box-title'>Detalle de receta</h3>
                </div>
                
                <div class='box-body table-responsive' >
                    <table class="table table-bordered" id="tabla_ingredientes">
                        <thead>
                            <tr>
                                <th>Código Prod</th>
                                <th>Nombre</th>
                                <th>Unidad medida</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receta->ingredientes as $ingrediente)
                                <tr data-codigo="{{ $ingrediente->producto->codigo }}">
                                    <td>{{ $ingrediente->producto->codigo }}</td>
                                    <td>{{ $ingrediente->producto->descripcion }}</td>
                                    <td class="td-unidad">{{ $ingrediente->unidad }}</td>
                                    <td class="td-cantidad">{{ $ingrediente->cantidad }}</td>
                                    <td class="td-precio">{{ $ingrediente->producto->precio_compra_neto }}</td>
                                    <td class="td-total">{{ $ingrediente->cantidad * $ingrediente->producto->precio_compra_neto }}</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm btnEliminar">
                                            Eliminar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>