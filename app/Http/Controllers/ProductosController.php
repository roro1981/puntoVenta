<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Impuestos;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\RangoPrecio;
use App\Models\Receta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class ProductosController extends Controller
{
    public function index()
    {
        $impuesto_iva = Impuestos::where('id', 1)->get();
        $impuesto_ad = Impuestos::where('id', '!=', 1)->get();
        $categorias = Categoria::where('estado_categoria', 1)->get();
        return view('almacen.productos', compact("impuesto_iva", "impuesto_ad", "categorias"));
    }
    public function listProducts()
    {
        $products = Producto::select(
            'productos.id',
            'productos.codigo',
            'productos.descripcion',
            'productos.precio_venta',
            'categorias.descripcion_categoria',
            'productos.imagen',
            'productos.fec_creacion',
            'productos.fec_modificacion'
        )
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('productos.estado', 'Activo')
            ->get();
        $products = $products->map(function ($product) {
            return [
                'codigo' => $product->codigo,
                'descripcion' => $product->descripcion,
                'precio_venta' => $product->precio_venta,
                'categoria' => $product->descripcion_categoria,
                'imagen' => $product->imagen ? '<img src="' . $product->imagen . '" width="80" height="80">' : '<img src="/img/fotos_prod/sin_imagen.jpg" width="80" height="80">',
                'fec_creacion' => $product->fec_creacion ? Carbon::parse($product->fec_creacion)->format('d-m-Y | H:i:s') : '',
                'fec_modificacion' => $product->fec_modificacion ? Carbon::parse($product->fec_modificacion)->format('d-m-Y | H:i:s') : '',
                'actions' => '<a href="" class="btn btn-sm btn-primary editar" data-target="#modalEditarProducto" data-prod="' . $product->id . '" data-toggle="modal" title="Editar producto ' . $product->descripcion . '"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-prod="' . $product->id . '" data-nameprod="' . $product->descripcion . '" title="Eliminar producto ' . $product->descripcion . '"><i class="fa fa-trash"></i></a>'
            ];
        });

        $response = [
            'data' => $products,
            'recordsTotal' => $products->count(),
            'recordsFiltered' => $products->count()
        ];

        return response()->json($response);
    }
    public function storeProduct(ProductoRequest $request)
    {
        try {
            $validated = $request->validated();
            $producto = new Producto();
            $producto = $producto->crearProducto($validated);

            $response = response()->json([
                'error' => 200,
                'message' => "Producto creado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al grabar producto " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function showProduct($id)
    {
        $producto = Producto::find($id);
        return response()->json($producto);
    }
    public function updateProduct(ProductoRequest $request, $producto)
    {
        try {
            $validated = $request->validated();
            $product = Producto::findOrFail($producto);
            $product = $product->editarProducto($validated);

            $response = response()->json([
                'error' => 200,
                'message' => "Producto modificado exitosamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al modificar producto " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function deleteProd($id)
    {
        try {
            $product = Producto::findOrFail($id);
            $product->deleteProduct();

            $response = response()->json([
                'error' => 200,
                'message' => "Producto eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar producto " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function indexCat()
    {
        return view('almacen.categorias');
    }

    public function showCategories()
    {
        $categories = Categoria::select('categorias.id', 'categorias.descripcion_categoria')
            ->withCount('productos as prods_asociados')
            ->where('categorias.estado_categoria', 1)
            ->where('categorias.id', '<>', 1)
            ->get()
            ->map(function ($categories) {
                $categories->actions = '<a href="" class="btn btn-sm btn-primary editar" data-target="#editCatModal" data-cat="' . $categories->id . '" data-toggle="modal" title="Editar categoria ' . $categories->descripcion_categoria . '"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-cat="' . $categories->id . '" data-namecat="' . $categories->descripcion_categoria . '" title="Eliminar categoria ' . $categories->descripcion_categoria . '"><i class="fa fa-trash"></i></a>';
                return $categories;
            });

        $response = [
            'data' => $categories,
            'recordsTotal' => $categories->count(),
            'recordsFiltered' => $categories->count()
        ];

        return response()->json($response);
    }

    public function createCategory(CategoriaRequest $request)
    {
        $validated = $request->validated();

        try {
            Categoria::storeCategory($validated);
            $response = response()->json([
                'error' => 200,
                'message' => "Categoria creada correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al crear categoria " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function getCategory($id)
    {
        $category = Categoria::find($id);
        return response()->json($category);
    }

    public function updateCategory(CategoriaRequest $request, $id)
    {
        $request->validated();
        try {
            $category = Categoria::findOrFail($id);
            $category->updateCategory($request);

            $response = response()->json([
                'error' => 200,
                'message' => "Categoria modificada correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al modificar categoria " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function deleteCat($id)
    {
        try {
            $category = Categoria::findOrFail($id);

            if ($category->productos()->count() > 0) {
                return $response = response()->json([
                    'error' => 400,
                    'message' => "No se puede eliminar la categoría porque tiene productos asociados"
                ], 400);
            }

            $category->deleteCategory();

            $response = response()->json([
                'error' => 200,
                'message' => "Categoria eliminada correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar categoria " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function uploadPhotoProduct(Request $request)
    {
        $fec = date("dmYHis");
        $nom = "foto_prod_" . $fec;

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $image = $request->file('file');
        $extension = $image->getClientOriginalExtension();
        $filename = $nom . '.' . $extension;

        if ($image->move(public_path('img/fotos_prod'), $filename)) {
            return asset('img/fotos_prod/' . $filename);
        } else {
            return 0;
        }
    }

    public function indexReceipes()
    {
        $categorias = Categoria::whereHas('recetas', function ($query) {
            $query->where('estado', 'Activo');
        })->get();
        return view('almacen.recetas', compact("categorias"));
    }

    public function indexReceipesCreate()
    {
        $categorias = Categoria::where('estado_categoria', 1)->where('id', '<>', 1)->get();
        return view('almacen.crear_recetas', compact("categorias"));
    }

    public function listReceipes(Request $request)
    {
        $query = Receta::select('uuid', 'imagen', 'nombre', 'descripcion')->where('estado', 'Activo');
        if ($request->has('categoria_id') && $request->categoria_id != 0) {
            $query->where('categoria_id', $request->categoria_id);
        }
        $receipes = $query->get();
        $receipes = $receipes->map(function ($receipe) {
            return [
                'uuid' => $receipe->uuid,
                'imagen' => $receipe->imagen ? '<img src="' . $receipe->imagen . '" style="width: 280px; height: 160px;">' : '<img src="/img/fotos_prod/sin_imagen.jpg" style="width: 280px; height: 160px;">',
                'nombre' => $receipe->nombre,
                'descripcion' => $receipe->descripcion,
                'actions' => '<button id="editarReceta" class="btn btn-sm btn-primary" data-uuid="' . $receipe->uuid . '" title="Editar receta ' . $receipe->nombre . '"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-uuid="' . $receipe->uuid . '" data-namerec="' . $receipe->nombre . '" title="Eliminar receta ' . $receipe->nombre . '"><i class="fa fa-trash"></i></a></button>'
            ];
        });

        $response = [
            'data' => $receipes,
            'recordsTotal' => $receipes->count(),
            'recordsFiltered' => $receipes->count()
        ];

        return response()->json($response);
    }

    public function uploadPhotoReceipe(Request $request)
    {
        $fec = date("dmYHis");
        $nom = "foto_receta_" . $fec;

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        $image = $request->file('file');
        $extension = $image->getClientOriginalExtension();
        $filename = $nom . '.' . $extension;

        if ($image->move(public_path('img/fotos_prod/recetas'), $filename)) {
            return asset('img/fotos_prod/recetas/' . $filename);
        } else {
            return 0;
        }
    }
    public function searchInsumos(Request $request)
    {
        $term = $request->input('q');

        $products = Producto::where(function ($query) use ($term) {
            $query->where('codigo', 'like', "%{$term}%")
                ->orWhere('descripcion', 'like', "%{$term}%");
        })
            ->where('tipo', '=', 'I')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
    public function findInsumo(Request $request)
    {
        $codigo = $request->input('codigo');

        $producto = Producto::where('codigo', $codigo)->where('tipo', '=', 'I')->first();

        if (!$producto) {
            return response()->json([
                'status' => 404,
                'mensaje' => 'No se encontró el producto'
            ]);
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion,
                'precio_unit' => $producto->precio_compra_neto,
                'unidad_medida' => $producto->unidad_medida
            ]
        ]);
    }

    public function storeReceipe(Request $request)
    {
        try {
            $data = $request->all();
            Receta::crearRecetaConIngredientes($data);

            return response()->json([
                'status' => 200,
                'message' => "Receta creada exitosamente"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editReceipe($uuid)
    {
        $receta = Receta::with('ingredientes.producto')->where('uuid', $uuid)->firstOrFail();
        $categorias = Categoria::select('id', 'descripcion_categoria')
            ->where('estado_categoria', 1)
            ->where('id', '<>', 1)
            ->get();
        return view('almacen.editar_recetas', [
            'receta' => $receta,
            'categorias' => $categorias,
        ]);
    }

    public function updateReceipe(Request $request, $uuid)
    {
        try {
            $data = $request->all();
            Receta::actualizarRecetaConIngredientes($uuid, $data);

            return response()->json([
                'status' => 200,
                'message' => "Receta modificada exitosamente"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteReceipe($uuid)
    {
        try {
            Receta::eliminarReceta($uuid);

            return response()->json([
                'status' => 200,
                'message' => 'Receta eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la receta.'
            ]);
        }
    }

    public function indexPromoCreate()
    {
        $categorias = Categoria::where('estado_categoria', 1)->where('id', '<>', 1)->get();
        return view('almacen.crear_promocion', compact("categorias"));
    }

    public function searchProductos(Request $request)
    {
        $term = $request->input('q');

        $products = Producto::where(function ($query) use ($term) {
            $query->where('codigo', 'like', "%{$term}%")
                ->orWhere('descripcion', 'like', "%{$term}%");
        })
            ->where('tipo', '<>', 'I')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
    public function findProducto(Request $request)
    {
        $codigo = $request->input('codigo');

        $producto = Producto::where('codigo', $codigo)->where('tipo', '<>', 'I')->first();

        if (!$producto) {
            return response()->json([
                'status' => 404,
                'mensaje' => 'No se encontró el producto'
            ]);
        }

        return response()->json([
            'status' => 200,
            'data' => [
                'codigo' => $producto->codigo,
                'descripcion' => $producto->descripcion,
                'precio_unit' => $producto->precio_compra_neto,
                'unidad_medida' => $producto->unidad_medida
            ]
        ]);
    }
    public function storePromo(Request $request)
    {
        try {
            $data = $request->all();
            Promocion::crearPromocionConProductos($data);

            return response()->json([
                'status' => 200,
                'message' => "Promoción creada exitosamente"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listPromos(Request $request)
    {
        $query = Promocion::select('uuid', 'codigo', 'nombre', 'precio_costo', 'precio_venta')->where('estado', 'Activo');
        if ($request->has('categoria_id') && $request->categoria_id != 0) {
            $query->where('categoria_id', $request->categoria_id);
        }
        $promos = $query->get();
        $promos = $promos->map(function ($promo) {
            return [
                'codigo' => $promo->codigo,
                'nombre' => $promo->nombre,
                'precio_costo' => $promo->precio_costo,
                'precio_venta' => $promo->precio_venta,
                'actions' => '<button id="editarPromo" class="btn btn-sm btn-primary" data-uuid="' . $promo->uuid . '" title="Editar promoción ' . $promo->nombre . '"><i class="fa fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-uuid="' . $promo->uuid . '" data-namepromo="' . $promo->nombre . '" title="Eliminar promoción ' . $promo->nombre . '"><i class="fa fa-trash"></i></a></button>'
            ];
        });

        $response = [
            'data' => $promos,
            'recordsTotal' => $promos->count(),
            'recordsFiltered' => $promos->count()
        ];

        return response()->json($response);
    }

    public function indexPromos()
    {
        $categorias = Categoria::whereHas('promociones', function ($query) {
            $query->where('estado', 'Activo');
        })->get();
        return view('almacen.promociones', compact("categorias"));
    }

    public function editPromos($uuid)
    {
        $promo = Promocion::with('detallePromocion.producto')->where('uuid', $uuid)->firstOrFail();
        $categorias = Categoria::select('id', 'descripcion_categoria')
            ->where('estado_categoria', 1)
            ->where('id', '<>', 1)
            ->get();
        return view('almacen.editar_promociones', [
            'promo' => $promo,
            'categorias' => $categorias,
        ]);
    }

    public function updatePromo(Request $request, $uuid)
    {
        try {
            $data = $request->all();
            Promocion::actualizarPromocionConProductos($uuid, $data);

            return response()->json([
                'status' => 200,
                'message' => "Promoción modificada exitosamente"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deletePromo($uuid)
    {
        try {
            Promocion::eliminarPromocion($uuid);

            return response()->json([
                'status' => 200,
                'message' => 'Promoción eliminada correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la promoción.'
            ]);
        }
    }

    public function indexRange()
    {
        return view('almacen.rango_precios');
    }

    public function listProductsRange()
    {
        $ranges = RangoPrecio::select(
            'rangos_precios.uuid',
            'productos.codigo',
            'productos.descripcion',
            'rangos_precios.cantidad_minima',
            'rangos_precios.cantidad_maxima',
            'rangos_precios.precio_unitario',
            'rangos_precios.fec_modificacion'
        )
            ->join('productos', 'rangos_precios.producto_id', '=', 'productos.id')
            ->get();
        $ranges = $ranges->map(function ($range) {
            return [
                'codigo' => $range->codigo,
                'descripcion' => $range->descripcion,
                'cantidad_minima' => intval($range->cantidad_minima),
                'cantidad_maxima' => $range->cantidad_maxima ? intval($range->cantidad_maxima) : '',
                'precio_unitario' => intval($range->precio_unitario),
                'fec_modificacion' => $range->fec_modificacion ? Carbon::parse($range->fec_modificacion)->format('d-m-Y | H:i:s') : '',
                'actions' => '<a href="" class="btn btn-sm btn-primary editar" data-target="#modalEditarRango" data-uuid="' . $range->uuid . '" data-toggle="modal" title="Editar rango ' . $range->descripcion . '"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-uuid="' . $range->uuid . '" data-nameprod="' . $range->descripcion . '" title="Eliminar rango ' . $range->descripcion . '"><i class="fa fa-trash"></i></a>'
            ];
        });

        $response = [
            'data' => $ranges,
            'recordsTotal' => $ranges->count(),
            'recordsFiltered' => $ranges->count()
        ];

        return response()->json($response);
    }

    public function storeRange(Request $request)
    {
        try {
            $data = $request->all();
            $rango = new RangoPrecio();

            $producto = Producto::where('uuid', $data['uuid'])->first();

            $yaExiste = RangoPrecio::where('producto_id', $producto->id)
                ->where('cantidad_minima', $data['cantidad_minima'])
                ->where('cantidad_maxima', $data['cantidad_maxima'])
                ->exists();

            if ($yaExiste) {
                return response()->json([
                    'error' => 400,
                    'message' => 'Ya existe un rango para ese producto con ese rango de cantidades.'
                ], 400);
            }
            $rango->crearRango($data);

            $response = response()->json([
                'error' => 200,
                'message' => "Rango creado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al grabar rango " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function showProductRange($uuid)
    {
        $range = RangoPrecio::with(['producto:id,codigo,descripcion,precio_venta,uuid'])->where('uuid', $uuid)->firstOrFail();
        return response()->json($range);
    }

    public function updateRange(Request $request, $uuid)
    {
        try {
            $data = $request->all();

            $rango = RangoPrecio::where('uuid', $uuid)->firstOrFail();
            $producto = Producto::where('uuid', $data['uuid'])->firstOrFail();

            $existe = RangoPrecio::where('producto_id', $producto->id)
                ->where('uuid', '!=', $uuid)
                ->where('cantidad_minima', $data['cantidad_minima'])
                ->where('cantidad_maxima', $data['cantidad_maxima'])
                ->exists();

            if ($existe) {
                return response()->json([
                    'error' => 400,
                    'message' => 'Ya existe un rango para ese producto con esas cantidades.'
                ], 400);
            }
            $rango->actualizarRango($data);

            $response = response()->json([
                'error' => 200,
                'message' => "Rango modificado exitosamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al modificar producto " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function deleteRange($uuid)
    {
        try {
            $rango = RangoPrecio::where('uuid', $uuid)->firstOrFail();
            $rango->delete();

            return response()->json([
                'error' => 200,
                'message' => 'Rango eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar el rango: ' . $e->getMessage());

            return response()->json([
                'error' => 500,
                'message' => 'No se pudo eliminar el rango.'
            ], 500);
        }
    }
}
