<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use App\Models\Globales;
use App\Models\Impuestos;
use Illuminate\Http\Request;
use App\Models\CorporateData;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\GlobalsRequest;
use App\Http\Requests\ImpuestosRequest;

class ConfigurationController extends Controller
{
    public function index(){
    
        $corporateData = CorporateData::all();

        $comunas = Comuna::orderBy('nom_comuna', 'asc')->get();
        
        return view('configuration.corporate_data', [
            'corporateData' => $corporateData,
            'comunas' => $comunas
        ]);
    }

    public function updateCorporateData(Request $request)
    {   

        try{
            $dataToUpdate = [
                'name_enterprise' => $request->input('name_enterprise'),
                'fantasy_name_enterprise' => $request->input('fantasy_name_enterprise'),
                'address_enterprise' => $request->input('address_enterprise'),
                'comuna_enterprise' => $request->input('comuna_enterprise'),
                'phone_enterprise' => $request->input('phone_enterprise'),
                'logo_enterprise' => $request->input('logo_enterprise'),
            ];

            foreach ($dataToUpdate as $itemName => $itemValue) {
                CorporateData::where('item', $itemName)
                    ->update(['description_item' => $itemValue]);
            }

            $response = response()->json([
                'error' => 200,
                'message' => "Datos de la empresa actualizados exitosamente."
            ], 200); 
            return $response;
        
        }catch (\Exception $e){
            Log::error("Error al actualizar datos de empresa ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
            return $response;
        }
    }


    public function uploadLogo(Request $request)
    {
        $fec = date("dmYHis");
        $nom = "logo_emp" . $fec;

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg',
        ]);
     
        $image = $request->file('file');
        $extension = $image->getClientOriginalExtension();
        $filename = $nom . '.' . $extension;

        if ($image->move(public_path('img/logo_empresa'), $filename)) {
            return asset('img/logo_empresa/' . $filename);
        } else {
            return 0;
        }
    }

    public function globalesTable()
    {
        $query = Globales::select('globales.id', 'globales.nom_var', 'globales.valor_var', 'globales.descrip_var');
        
        // Solo mostrar TIPO_NEGOCIO si el usuario es Rodrigo Panes
        if (auth()->user()->name_complete !== 'Rodrigo Panes') {
            $query->where('nom_var', '!=', 'TIPO_NEGOCIO');
        }
        
        $globales = $query->get()
            ->map(function ($globales) {      
                $globales->valor_var = '<input type="text" class="valor-var-input" id="valor_var_'.$globales->id.'" value="'.e($globales->valor_var).'" data-id="'.$globales->id.'">';             
                $globales->actions = '<a href="" class="btn btn-sm btn-primary editar" data-id="'.$globales->id.'" title="Actualizar variable '.$globales->nom_var.'"><i class="fa fa-edit"></i></a>';
                return $globales;
            });

        $response = [
            'data' => $globales,
            'recordsTotal' => $globales->count(),
            'recordsFiltered' => $globales->count()
        ];
        return response()->json($response);
    }
    public function indexGlobales()
    {
        return view('configuration.globals_var');
    }

    public function updateGlobal(GlobalsRequest $request, $id)
    {
        $validated = $request->validated();
        try{
            $global = Globales::findOrFail($id);
            $global->updateVar($request);

            $response = response()->json([
                'error' => 200,
                'message' => "Variable modificada correctamente"
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al modificar variable ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function indexImpuestos()
    {
        return view('configuration.impuestos');
    }

    public function impuestosTable()
    {
        $impuestos = Impuestos::select('impuestos.id', 'impuestos.nom_imp', 'impuestos.valor_imp', 'impuestos.descrip_imp', 'impuestos.last_activity')
            ->orderby('id')
            ->get()
            ->map(function ($impuestos) {
                $impuestos->last_activity = date('d/m/Y H:i:s', strtotime($impuestos->last_activity));      
                $impuestos->valor_imp = '<input type="text" class="valor-imp-input" id="valor_imp_'.$impuestos->id.'" value="'.e($impuestos->valor_imp).'" data-id="'.$impuestos->id.'">';             
                $impuestos->actions = '<a href="" class="btn btn-sm btn-primary editar" data-id="'.$impuestos->id.'" title="Actualizar impuesto '.$impuestos->nom_imp.'"><i class="fa fa-edit"></i></a>';
                return $impuestos;
            });

        $response = [
            'data' => $impuestos,
            'recordsTotal' => $impuestos->count(),
            'recordsFiltered' => $impuestos->count()
        ];
        return response()->json($response);
    }

    public function updateImpuesto(ImpuestosRequest $request, $id)
    {
        $validated = $request->validated();
        try{
            $impuesto = Impuestos::findOrFail($id);
            $impuesto->updateImp($request);

            $response = response()->json([
                'error' => 200,
                'message' => "Impuesto modificado correctamente"
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al modificar impuesto ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
}
