<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Impuestos;
use App\Models\Producto;
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
                'imagen' => $product->imagen ? '<img src="' . $product->imagen . '" width="80" height="80">' : '<img src="https://www.edelar.com.ar/static/theme/images/sin_imagen.jpg" width="80" height="80">',
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
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-cat="' . $categories->id . '" data-namecat="' . $categories->descripcion_categoria . '" title="Eliminar usuario ' . $categories->descripcion_categoria . '"><i class="fa fa-trash"></i></a>';
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
        return view('almacen.recetas');
    }

    public function indexReceipesCreate()
    {
        return view('almacen.crear_recetas');
    }

    public function listReceipes()
    {
        $receipes = Receta::select(
            'uuid',
            'imagen',
            'nombre',
            'descripcion'
        )
            ->where('estado', 'Activo')
            ->get();
        $receipes = $receipes->map(function ($receipe) {
            return [
                'uuid' => $receipe->uuid,
                'imagen' => $receipe->imagen ? '<img src="' . $receipe->imagen . '" width="80" height="80">' : '<img src="https://www.edelar.com.ar/static/theme/images/sin_imagen.jpg" width="80" height="80">',
                'nombre' => $receipe->nombre,
                'descripcion' => $receipe->descripcion,
                'actions' => '<a href="" class="btn btn-sm btn-primary editar" data-target="#modalEditaReceta" data-uuid="' . $receipe->uuid . '" data-toggle="modal" title="Editar receta ' . $receipe->nombre . '"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-uuid="' . $receipe->uuid . '" data-namerec="' . $receipe->nombre . '" title="Eliminar receta ' . $receipe->nombre . '"><i class="fa fa-trash"></i></a>'
            ];
        });

        $response = [
            'data' => $receipes,
            'recordsTotal' => $receipes->count(),
            'recordsFiltered' => $receipes->count()
        ];

        return response()->json($response);
    }
}
