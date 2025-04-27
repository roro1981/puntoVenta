<script type="text/javascript" src="js/almacen/editar_promos.js"></script>
<link rel="stylesheet" type="text/css" href="css/almacen/crear_promocion.css">

<div class="container">
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <button id="volver" class="btn btn-secondary mb-3">
        ← Volver al listado
    </button>

    <h2>Editar Promoción</h2>

        <div class="row">
            <div class="col-md-6">

                {{-- Código de la promocion --}}
                <div class="form-group mb-3">
                    <label for="codigo">Código</label>
                    <input type="text" name="codigo" id="codigo" class="form-control"
                           value="{{ old('codigo', $promo->codigo) }}" required readonly>
                </div>

                {{-- Nombre de la promocion --}}
                <div class="form-group mb-3">
                    <label for="nombre">Nombre promoción</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                           value="{{ old('nombre', $promo->nombre) }}" required>
                </div>

                {{-- Categoría --}}
                <div class="form-group mb-3">
                    <label for="categoria_id">Categoría</label>
                    <select name="categoria_id" id="categoria_id" class="form-control">
                        <option value="">-- Seleccione --</option>
                        @foreach($categorias as $cat)
                            <option 
                                value="{{ $cat->id }}"
                                {{ (old('categoria_id', $promo->categoria_id) == $cat->id) ? 'selected' : '' }}
                            >
                                {{ $cat->descripcion_categoria }} 
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card mt-3 mx-auto" style="max-width: 600px; border:1px solid #ccc; padding:15px; background-color: #f9f9f9;">
                    <h4 class="text-primary mb-3">
                        <i class="fa fa-calendar-alt"></i> Vigencia de la promoción
                    </h4>
                
                    <div class="row align-items-center">
                        <!-- Fecha de inicio -->
                        <div class="col-md-6 d-flex align-items-center">
                            <label for="fecha_inicio" class="font-weight-bold mr-2 mb-0" style="min-width:120px;">Fecha de inicio</label>
                            <input type="date" id="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', $promo->fecha_inicio) }}" style="font-size:14px;"
                            {{ is_null($promo->fecha_inicio) && is_null($promo->fecha_fin) ? 'disabled' : '' }}>
                        </div>
                
                        <!-- Fecha de término -->
                        <div class="col-md-6 d-flex align-items-center mt-2 mt-md-0">
                            <label for="fecha_termino" class="font-weight-bold mr-2 mb-0" style="min-width:120px;">Fecha de término</label>
                            <input type="date" id="fecha_termino" class="form-control" value="{{ old('fecha_fin', $promo->fecha_fin) }}" style="font-size:14px;"
                            {{ is_null($promo->fecha_inicio) && is_null($promo->fecha_fin) ? 'disabled' : '' }}>
                        </div>
                    </div>
                
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="sin_fecha"
                        {{ is_null($promo->fecha_inicio) && is_null($promo->fecha_fin) ? 'checked' : '' }}>
                        <label class="form-check-label" for="sin_fecha">
                            Esta promoción estará siempre activa
                        </label>
                    </div>
                </div>
            </div> {{-- col-md-6 --}}

            <div class="col-md-6">

                {{-- Precio costo (lectura) --}}
                <div class="form-group mb-3">
                    <label for="precio_costo">Precio Costo</label>
                    <input type="text" name="precio_costo" id="precio_costo" class="form-control"
                           value="{{ old('precio_costo', $promo->precio_costo) }}" 
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
                           value="{{ old('precio_venta', $promo->precio_venta) }}">
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
                   id="producto" 
                   autocomplete="off"
                   style="width:150px; font-size:15px; text-align:center; font-weight:bold; display:inline-block;"
                   autofocus 
                   tabindex="1"
               >
           
               <div id="listaProductos" class="suggestion-box" style="display: none;"></div>
            </div>
                <div class="form-group">
                    <span style="width:85px;font-size:15px;font-weight: bold;" class="form-control bg-blue">Cantidad</span><input type='text' class="form-control" autocomplete="off" id='cant_producto' onkeypress="return soloNumeros(event)" style="width:90px;font-size:15px; text-align:center; font-weight: bold;" tabindex="2">		   
                </div>
                <button type="button" id="act_promo" class="btn btn-success">Guardar cambios</button>
                <input type="hidden" id="uuid_promo" value="{{$promo->uuid}}">
            </form>
        </div>
        <br>
        {{-- Tabla de detalle promo --}}
        <div class='col-md-12'>
            <div>
                <div class='box-header'>
                    <h3 class='box-title'>Detalle de promoción</h3>
                </div>
                
                <div class='box-body table-responsive' >
                    <table class="table table-bordered" id="tabla_detalle">
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
                            @foreach($promo->detallePromocion as $det)
                                <tr data-codigo="{{ $det->producto->codigo }}">
                                    <td>{{ $det->producto->codigo }}</td>
                                    <td>{{ $det->producto->descripcion }}</td>
                                    <td class="td-unidad">{{ $det->unidad }}</td>
                                    <td class="td-cantidad">{{ $det->cantidad }}</td>
                                    <td class="td-precio">{{ $det->producto->precio_compra_neto }}</td>
                                    <td class="td-total">{{ $det->cantidad * $det->producto->precio_compra_neto }}</td>
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