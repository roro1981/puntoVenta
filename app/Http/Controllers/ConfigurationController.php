<?php

namespace App\Http\Controllers;

use App\Models\Comuna;
use Illuminate\Http\Request;
use App\Models\CorporateData;
use Illuminate\Support\Facades\Log;

class ConfigurationController extends Controller
{
    public function index(){
    
        $corporateData = CorporateData::all();

        $comunas = Comuna::all();
        
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
            // more code here...
            return asset('img/logo_empresa/' . $filename);
        } else {
            return 0;
        }
    }
}
