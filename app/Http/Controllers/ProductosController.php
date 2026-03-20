<?php

namespace App\Http\Controllers;

use App\Exports\ProductosPlantillaExport;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductosController extends Controller
{
    private const PRODUCT_IMPORT_HEADERS = [
        'codigo',
        'descripcion',
        'precio_compra_neto',
        'impuesto_1',
        'impuesto_2',
        'precio_compra_bruto',
        'precio_venta',
        'stock_minimo',
        'categoria',
        'unidad_medida',
        'tipo',
        'nom_foto',
    ];

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
            'productos.uuid',
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
            ->orderBy('productos.fec_creacion', 'desc')
            ->orderBy('categorias.descripcion_categoria', 'asc')
            ->get();
        $products = $products->map(function ($product) {
            $fecCreacion = $product->fec_creacion ? Carbon::parse($product->fec_creacion) : null;

            return [
                'codigo' => $product->codigo,
                'descripcion' => $product->descripcion,
                'precio_venta' => $product->precio_venta,
                'categoria' => $product->descripcion_categoria,
                'imagen' => $product->imagen ? '<img src="' . $product->imagen . '" width="80" height="80">' : '<img src="/img/fotos_prod/sin_imagen.jpg" width="80" height="80">',
                'fec_creacion' => $fecCreacion ? $fecCreacion->format('d-m-Y | H:i:s') : '',
                'fec_creacion_sort' => $fecCreacion ? $fecCreacion->timestamp : 0,
                'fec_modificacion' => $product->fec_modificacion ? Carbon::parse($product->fec_modificacion)->format('d-m-Y | H:i:s') : '',
                'actions' => '<a href="" class="btn btn-sm btn-primary editar_prod" data-target="#modalEditarProducto" data-uuid="' . $product->uuid . '" data-toggle="modal" title="Editar producto ' . $product->descripcion . '"><i class="fa fa-edit"></i></a>
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
    public function downloadProductsXlsxTemplate()
    {
        $categories = Categoria::where('estado_categoria', 1)
            ->orderBy('descripcion_categoria')
            ->pluck('descripcion_categoria')
            ->map(fn($value) => strtoupper(trim($value)))
            ->values()
            ->all();

        $taxes = Impuestos::orderBy('id')
            ->get(['id', 'nom_imp', 'valor_imp'])
            ->map(fn($tax) => sprintf('%s (%s%%)', strtoupper(trim($tax->nom_imp)), rtrim(rtrim((string) $tax->valor_imp, '0'), '.')))
            ->values()
            ->all();

        $fileName = 'plantilla_productos_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ProductosPlantillaExport(self::PRODUCT_IMPORT_HEADERS, $categories, $taxes), $fileName);
    }

    public function importProductsXlsx(Request $request)
    {
        $request->validate([
            'archivo_excel' => ['required', 'file', 'mimes:xlsx'],
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel.',
            'archivo_excel.file' => 'El archivo seleccionado no es valido.',
            'archivo_excel.mimes' => 'El archivo debe estar en formato XLSX.',
        ]);

        try {
            $parsedWorkbook = $this->parseProductsImportWorkbook($request->file('archivo_excel')->getRealPath());
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 422,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Error al leer Excel de productos: ' . $e->getMessage());

            return response()->json([
                'error' => 500,
                'message' => 'No se pudo procesar el archivo Excel.',
            ], 500);
        }

        if (empty($parsedWorkbook['rows'])) {
            return response()->json([
                'error' => 422,
                'message' => 'La hoja Productos no contiene filas para importar.',
            ], 422);
        }

        $errors = [];
        $rowsToInsert = [];
        $codesInFile = [];
        $descriptionsInFile = [];

        foreach ($parsedWorkbook['rows'] as $item) {
            $rowNumber = $item['row_number'];
            $row = $this->normalizeImportedProductRow($item['data']);

            $validator = Validator::make(
                $row,
                ProductoRequest::buildRules(false),
                ProductoRequest::buildMessages()
            );

            ProductoRequest::applyCrossEntityValidation($validator, $row, true);

            $normalizedCode = strtoupper(trim((string) ($row['codigo'] ?? '')));
            $normalizedDescription = strtoupper(trim((string) ($row['descripcion'] ?? '')));

            if ($normalizedCode !== '') {
                if (isset($codesInFile[$normalizedCode])) {
                    $validator->errors()->add('codigo', 'El código está repetido dentro del archivo Excel.');
                } else {
                    $codesInFile[$normalizedCode] = $rowNumber;
                }
            }

            if ($normalizedDescription !== '') {
                if (isset($descriptionsInFile[$normalizedDescription])) {
                    $validator->errors()->add('descripcion', 'La descripción está repetida dentro del archivo Excel.');
                } else {
                    $descriptionsInFile[$normalizedDescription] = $rowNumber;
                }
            }

            if ($validator->fails()) {
                $messages = [];

                foreach ($validator->errors()->messages() as $fieldMessages) {
                    foreach ($fieldMessages as $message) {
                        $messages[] = $message;
                    }
                }

                $errors[] = 'Fila ' . $rowNumber . ': ' . implode(' | ', $messages);
                continue;
            }

            $rowsToInsert[] = $row;
        }

        if (!empty($errors)) {
            return response()->json([
                'error' => 422,
                'message' => 'Se detectaron errores en el archivo Excel.',
                'details' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($rowsToInsert) {
            foreach ($rowsToInsert as $row) {
                $product = new Producto();
                $product->crearProducto($row);
            }
        });

        return response()->json([
            'error' => 200,
            'message' => count($rowsToInsert) . ' productos importados correctamente.',
        ]);
    }

    public function showProduct($uuid)
    {
        $producto = Producto::where('uuid', $uuid)->firstOrFail();
        return response()->json($producto);
    }
    public function updateProduct(ProductoRequest $request, $uuid)
    {
        try {
            $validated = $request->validated();
            $product = Producto::where('uuid', $uuid)->firstOrFail();
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

    public function indexDeletedCategories()
    {
        return view('reactivaciones.categorias_eliminadas');
    }

    public function listDeletedCategories()
    {
        $categories = Categoria::select('id', 'descripcion_categoria', 'fec_eliminacion', 'user_eliminacion')
            ->where('estado_categoria', 0)
            ->orderByDesc('fec_eliminacion')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'descripcion_categoria' => $category->descripcion_categoria,
                    'fec_eliminacion' => $category->fec_eliminacion ? Carbon::parse($category->fec_eliminacion)->format('d-m-Y | H:i:s') : '',
                    'user_eliminacion' => $category->user_eliminacion ?? 'SISTEMA',
                    'actions' => '<button class="btn btn-sm btn-success reactivar-cat" data-cat="' . $category->id . '" data-namecat="' . $category->descripcion_categoria . '" title="Reactivar categoria ' . $category->descripcion_categoria . '"><i class="fa fa-refresh"></i></button>',
                ];
            });

        return response()->json([
            'data' => $categories,
            'recordsTotal' => $categories->count(),
            'recordsFiltered' => $categories->count(),
        ]);
    }

    public function reactivateCategory($id)
    {
        try {
            $category = Categoria::findOrFail($id);
            $category->reactivateCategory();

            return response()->json([
                'error' => 200,
                'message' => 'Categoria reactivada correctamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al reactivar categoria ' . $e->getMessage());

            return response()->json([
                'error' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function indexDeletedProducts()
    {
        return view('reactivaciones.productos_eliminados');
    }

    public function listDeletedProducts()
    {
        $products = Producto::select(
            'productos.id',
            'productos.uuid',
            'productos.codigo',
            'productos.descripcion',
            'categorias.descripcion_categoria',
            'productos.fec_eliminacion',
            'productos.user_eliminacion'
        )
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('productos.estado', 'Inactivo')
            ->orderByDesc('productos.fec_eliminacion')
            ->get()
            ->map(function ($product) {
                return [
                    'id'                   => $product->id,
                    'uuid'                 => $product->uuid,
                    'codigo'               => $product->codigo,
                    'descripcion'          => $product->descripcion,
                    'descripcion_categoria' => $product->descripcion_categoria,
                    'fec_eliminacion'      => $product->fec_eliminacion ? Carbon::parse($product->fec_eliminacion)->format('d-m-Y | H:i:s') : '',
                    'user_eliminacion'     => $product->user_eliminacion ?? 'SISTEMA',
                    'actions'              => '<button class="btn btn-sm btn-success reactivar-prod" data-uuid="' . $product->uuid . '" data-nameprod="' . $product->descripcion . '" title="Reactivar producto ' . $product->descripcion . '"><i class="fa fa-refresh"></i></button>',
                ];
            });

        return response()->json([
            'data'            => $products,
            'recordsTotal'    => $products->count(),
            'recordsFiltered' => $products->count(),
        ]);
    }

    public function reactivateProduct($uuid)
    {
        try {
            $product = Producto::where('uuid', $uuid)->firstOrFail();
            $product->reactivateProduct();

            return response()->json([
                'error'   => 200,
                'message' => 'Producto reactivado correctamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al reactivar producto ' . $e->getMessage());

            return response()->json([
                'error'   => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function showCategories()
    {
        $categories = Categoria::select('categorias.id', 'categorias.descripcion_categoria', 'categorias.fec_creacion')
            ->withCount('productos as prods_asociados')
            ->where('categorias.estado_categoria', 1)
            ->where('categorias.id', '<>', 1)
            ->get()
            ->map(function ($categories) {
                $categories->fec_creacion = $categories->fec_creacion ? Carbon::parse($categories->fec_creacion)->format('d-m-Y | H:i:s') : '';
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

            $codigoExisteEnRecetas = Receta::where('codigo', $data['codigo'])->exists();
            $codigoExisteEnProductos = Producto::where('codigo', $data['codigo'])->exists();
            $codigoExisteEnPromociones = Promocion::where('codigo', $data['codigo'])->exists();
            $nombreExisteEnRecetas = Receta::where('nombre', $data['nombre'])->exists();
            $nombreExisteEnProductos = Producto::where('descripcion', $data['nombre'])->exists();
            $nombreExisteEnPromociones = Promocion::where('nombre', $data['nombre'])->exists();

            if ($codigoExisteEnRecetas || $codigoExisteEnProductos || $codigoExisteEnPromociones || $nombreExisteEnRecetas || $nombreExisteEnProductos || $nombreExisteEnPromociones) {
                return response()->json([
                    'status' => 400,
                    'message' => 'El código o el nombre ya está registrado en recetas, productos o promociones.'
                ], 400);
            }

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

    public function indexDeletedReceipes()
    {
        return view('reactivaciones.recetas_eliminadas');
    }

    public function listDeletedReceipes()
    {
        $recetas = Receta::select(
            'recetas.uuid',
            'recetas.codigo',
            'recetas.nombre',
            'categorias.descripcion_categoria',
            'recetas.fec_eliminacion',
            'recetas.user_eliminacion'
        )
            ->join('categorias', 'recetas.categoria_id', '=', 'categorias.id')
            ->where('recetas.estado', 'Inactivo')
            ->orderByDesc('recetas.fec_eliminacion')
            ->get()
            ->map(function ($receta) {
                return [
                    'uuid'                  => $receta->uuid,
                    'codigo'                => $receta->codigo,
                    'nombre'                => $receta->nombre,
                    'descripcion_categoria' => $receta->descripcion_categoria,
                    'fec_eliminacion'       => $receta->fec_eliminacion ? Carbon::parse($receta->fec_eliminacion)->format('d-m-Y | H:i:s') : '',
                    'user_eliminacion'      => $receta->user_eliminacion ?? 'SISTEMA',
                    'actions'               => '<button class="btn btn-sm btn-success reactivar-receta" data-uuid="' . $receta->uuid . '" data-nomreceta="' . $receta->nombre . '" title="Reactivar receta ' . $receta->nombre . '"><i class="fa fa-refresh"></i></button>',
                ];
            });

        return response()->json([
            'data'            => $recetas,
            'recordsTotal'    => $recetas->count(),
            'recordsFiltered' => $recetas->count(),
        ]);
    }

    public function reactivateReceipe($uuid)
    {
        try {
            $receta = Receta::where('uuid', $uuid)->firstOrFail();
            $receta->reactivarReceta();

            return response()->json([
                'error'   => 200,
                'message' => 'Receta reactivada correctamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al reactivar receta ' . $e->getMessage());

            return response()->json([
                'error'   => 500,
                'message' => $e->getMessage(),
            ], 500);
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

            $codigoExisteEnRecetas = Receta::where('codigo', $data['codigo'])->exists();
            $codigoExisteEnProductos = Producto::where('codigo', $data['codigo'])->exists();
            $codigoExisteEnPromociones = Promocion::where('codigo', $data['codigo'])->exists();
            $nombreExisteEnRecetas = Receta::where('nombre', $data['nombre'])->exists();
            $nombreExisteEnProductos = Producto::where('descripcion', $data['nombre'])->exists();
            $nombreExisteEnPromociones = Promocion::where('nombre', $data['nombre'])->exists();

            if ($codigoExisteEnRecetas || $codigoExisteEnProductos || $codigoExisteEnPromociones || $nombreExisteEnRecetas || $nombreExisteEnProductos || $nombreExisteEnPromociones) {
                return response()->json([
                    'status' => 400,
                    'message' => 'El código o el nombre ya está registrado en promociones, productos o recetas.'
                ], 400);
            }

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

    public function indexDeletedPromos()
    {
        return view('reactivaciones.promociones_eliminadas');
    }

    public function listDeletedPromos()
    {
        $promos = Promocion::select(
            'promociones.uuid',
            'promociones.codigo',
            'promociones.nombre',
            'categorias.descripcion_categoria',
            'promociones.fec_eliminacion',
            'promociones.user_eliminacion'
        )
            ->join('categorias', 'promociones.categoria_id', '=', 'categorias.id')
            ->where('promociones.estado', 'Inactivo')
            ->orderByDesc('promociones.fec_eliminacion')
            ->get()
            ->map(function ($promo) {
                return [
                    'uuid'                  => $promo->uuid,
                    'codigo'                => $promo->codigo,
                    'nombre'                => $promo->nombre,
                    'descripcion_categoria' => $promo->descripcion_categoria,
                    'fec_eliminacion'       => $promo->fec_eliminacion ? Carbon::parse($promo->fec_eliminacion)->format('d-m-Y | H:i:s') : '',
                    'user_eliminacion'      => $promo->user_eliminacion ?? 'SISTEMA',
                    'actions'               => '<button class="btn btn-sm btn-success reactivar-promo" data-uuid="' . $promo->uuid . '" data-namepromo="' . $promo->nombre . '" title="Reactivar promoción ' . $promo->nombre . '"><i class="fa fa-refresh"></i></button>',
                ];
            });

        return response()->json([
            'data'            => $promos,
            'recordsTotal'    => $promos->count(),
            'recordsFiltered' => $promos->count(),
        ]);
    }

    public function reactivatePromo($uuid)
    {
        try {
            $promo = Promocion::where('uuid', $uuid)->firstOrFail();
            $promo->reactivarPromocion();

            return response()->json([
                'error'   => 200,
                'message' => 'Promoción reactivada correctamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al reactivar promoción ' . $e->getMessage());

            return response()->json([
                'error'   => 500,
                'message' => $e->getMessage(),
            ], 500);
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

    private function parseProductsImportWorkbook(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getSheetByName('Productos') ?? $spreadsheet->getSheet(0);
        $rows = $worksheet->toArray(null, false, false, false);

        if (empty($rows)) {
            throw new \InvalidArgumentException('La hoja Productos está vacía.');
        }

        $headers = array_map(fn($header) => $this->sanitizeImportHeader($header), $rows[0]);
        $missingHeaders = array_diff(self::PRODUCT_IMPORT_HEADERS, $headers);

        if (!empty($missingHeaders)) {
            throw new \InvalidArgumentException(
                'La hoja Productos no es válida. Faltan las columnas: ' . implode(', ', $missingHeaders)
            );
        }

        $rows = [];
        for ($index = 1; $index < count($worksheet->toArray(null, false, false, false)); $index++) {
            $row = $worksheet->toArray(null, false, false, false)[$index];

            $currentRow = [];
            foreach (self::PRODUCT_IMPORT_HEADERS as $header) {
                $columnIndex = array_search($header, $headers, true);
                $currentRow[$header] = $columnIndex !== false ? trim((string) ($row[$columnIndex] ?? '')) : null;
            }

            if ($this->isImportedRowEmpty($currentRow)) {
                continue;
            }

            $rows[] = [
                'row_number' => $index + 1,
                'data' => $currentRow,
            ];
        }

        return ['rows' => $rows];
    }

    private function sanitizeImportHeader($header): string
    {
        $header = (string) $header;
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

        return trim(Str::lower($header));
    }

    private function isImportedRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeImportedProductRow(array $row): array
    {
        $precioCompraNeto = $this->normalizeIntegerValue($row['precio_compra_neto'] ?? null);
        $impuesto1 = $this->resolveTaxId($row['impuesto_1'] ?? null);
        $impuesto2 = $this->resolveTaxId($row['impuesto_2'] ?? null, true);
        $precioCompraBruto = $this->normalizeIntegerValue($row['precio_compra_bruto'] ?? null);

        if (($precioCompraBruto === null || $precioCompraBruto === '') && $precioCompraNeto !== null && $impuesto1 !== null) {
            $precioCompraBruto = $this->calculateGrossPrice($precioCompraNeto, $impuesto1, $impuesto2);
        }

        return [
            'codigo' => trim((string) ($row['codigo'] ?? '')),
            'descripcion' => trim((string) ($row['descripcion'] ?? '')),
            'precio_compra_neto' => $precioCompraNeto,
            'impuesto_1' => $impuesto1,
            'impuesto_2' => $impuesto2,
            'precio_compra_bruto' => $precioCompraBruto,
            'precio_venta' => $this->normalizeIntegerValue($row['precio_venta'] ?? null),
            'stock_minimo' => $this->normalizeDecimalValue($row['stock_minimo'] ?? null),
            'categoria' => $this->resolveCategoryId($row['categoria'] ?? null),
            'unidad_medida' => $this->normalizeUnit($row['unidad_medida'] ?? null),
            'tipo' => $this->normalizeProductType($row['tipo'] ?? null),
            'nom_foto' => $this->normalizeOptionalString($row['nom_foto'] ?? null),
        ];
    }

    private function resolveCategoryId($category): ?int
    {
        $category = trim((string) $category);

        if ($category === '') {
            return null;
        }

        if (ctype_digit($category)) {
            return Categoria::where('estado_categoria', 1)->where('id', (int) $category)->value('id');
        }

        $normalizedCategory = strtoupper(trim(Str::ascii($category)));

        return Categoria::where('estado_categoria', 1)
            ->get(['id', 'descripcion_categoria'])
            ->first(function ($item) use ($normalizedCategory) {
                return strtoupper(trim(Str::ascii($item->descripcion_categoria))) === $normalizedCategory;
            })?->id;
    }

    private function resolveTaxId($tax, bool $allowNull = false): ?int
    {
        $tax = trim((string) $tax);

        if ($tax === '' || $tax === '0') {
            return $allowNull ? null : null;
        }

        if (ctype_digit($tax)) {
            return Impuestos::where('id', (int) $tax)->value('id');
        }

        $normalizedTax = strtoupper(trim(Str::ascii($tax)));

        return Impuestos::get(['id', 'nom_imp'])
            ->first(function ($item) use ($normalizedTax) {
                return strtoupper(trim(Str::ascii($item->nom_imp))) === $normalizedTax;
            })?->id;
    }

    private function normalizeUnit($unit): ?string
    {
        $unit = strtoupper(trim(Str::ascii((string) $unit)));

        if ($unit === '') {
            return null;
        }

        return match ($unit) {
            'UN', 'UNIDAD', 'UNIDADES' => 'UN',
            'L', 'LT', 'LITRO', 'LITROS' => 'L',
            'KG', 'KILO', 'KILOGRAMO', 'KILOGRAMOS' => 'KG',
            'CJ', 'CAJA', 'CAJAS' => 'CJ',
            default => $unit,
        };
    }

    private function normalizeProductType($type): ?string
    {
        $type = strtoupper(trim(Str::ascii((string) $type)));

        if ($type === '') {
            return null;
        }

        return match ($type) {
            'P', 'PRODUCTO' => 'P',
            'S', 'SERVICIO', 'NO AFECTO A STOCK', 'NO AFECTO STOCK', 'SIN STOCK' => 'S',
            'I', 'INSUMO' => 'I',
            'PR', 'PROMOCION' => 'PR',
            'R', 'RECETA' => 'R',
            default => $type,
        };
    }

    private function normalizeIntegerValue($value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.-]/', '', $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (int) round((float) $normalized);
    }

    private function normalizeDecimalValue($value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.-]/', '', $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function normalizeOptionalString($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function calculateGrossPrice(int $netPrice, int $tax1Id, ?int $tax2Id = null): int
    {
        $tax1Rate = (float) (Impuestos::where('id', $tax1Id)->value('valor_imp') ?? 0);
        $tax2Rate = $tax2Id ? (float) (Impuestos::where('id', $tax2Id)->value('valor_imp') ?? 0) : 0;

        $tax1Amount = ($netPrice * $tax1Rate) / 100;
        $tax2Amount = ($netPrice * $tax2Rate) / 100;

        return (int) round($netPrice + $tax1Amount + $tax2Amount);
    }
}
