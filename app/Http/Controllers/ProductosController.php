<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;

class ProductosController extends Controller
{
    public function index(){
        return view('almacen.productos');
    }

    public function indexCat(){
        return view('almacen.categorias');
    }

    public function showCategories(){
        $categories = Categoria::select('categorias.id', 'categorias.descripcion_categoria')
            ->withCount('productos as prods_asociados')
            ->where('categorias.estado_categoria', 1)
            ->get()
            ->map(function ($categories) {
                $categories->actions = '<a href="" class="btn btn-sm btn-primary editar" data-target="#editCatModal" data-cat="'.$categories->id.'" data-toggle="modal" title="Editar categoria '.$categories->descripcion_categoria.'"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-cat="'.$categories->id.'" data-namecat="'.$categories->descripcion_categoria.'" title="Eliminar usuario '.$categories->descripcion_categoria.'"><i class="fa fa-trash"></i></a>';
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
            $user = Categoria::storeCategory($validated);
            
            $response = response()->json([
                'error' => 200,
                'message' => "Categoria creada correctamente"
            ], 200);    

        } catch (\Exception $e) {
            Log::error("Error al crear categoria ". $e->getMessage());
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
        $validated = $request->validated();
        try{
            $category = Categoria::findOrFail($id);
            $category->updateCategory($request);

            $response = response()->json([
                'error' => 200,
                'message' => "Categoria modificada correctamente"
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al modificar categoria ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function deleteCat($id)
    {
        try{
            $category = Categoria::findOrFail($id);
            
            if ($category->productos()->count() > 0) {
                $response = response()->json([
                    'error' => 400,
                    'message' => "No se puede eliminar la categoría porque tiene productos asociados"
                ], 400);
                return $response;
            }

            $category->deleteCategory();

            $response = response()->json([
                'error' => 200,
                'message' => "Categoria eliminada correctamente"
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al eliminar categoria ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
}
