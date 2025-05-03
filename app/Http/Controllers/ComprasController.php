<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ComprasController extends Controller
{
    public function indexProveedores()
    {
        $regiones = Region::all();
        return view('compras.proveedores', compact('regiones'));
    }

    public function listProveedores()
    {
        $proveedores = Proveedor::select(
            'proveedores.uuid',
            'proveedores.razon_social',
            'proveedores.giro',
            'regiones.nom_region',
            'comunas.nom_comuna',
            'proveedores.fec_creacion',
            'proveedores.fec_modificacion'
        )
            ->join('regiones', 'proveedores.region_id', '=', 'regiones.id')
            ->join('comunas', 'proveedores.comuna_id', '=', 'comunas.id')
            ->where('proveedores.estado', 'Activo')
            ->get();
        $proveedores = $proveedores->map(function ($proveedor) {
            return [
                'razon_social' => $proveedor->razon_social,
                'giro' => $proveedor->giro,
                'region-comuna' => $proveedor->nom_region . '-' . $proveedor->nom_comuna,
                'fec_creacion' => $proveedor->fec_creacion ? Carbon::parse($proveedor->fec_creacion)->format('d-m-Y | H:i:s') : '',
                'fec_modificacion' => $proveedor->fec_modificacion ? Carbon::parse($proveedor->fec_modificacion)->format('d-m-Y | H:i:s') : '',
                'actions' => '<a href="" class="btn btn-sm btn-primary editar" data-target="#modalEditarProveedor" data-uuid="' . $proveedor->uuid . '" data-toggle="modal" title="Editar proveedor ' . $proveedor->razon_social . '"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-uuid="' . $proveedor->uuid . '" data-nameprov="' . $proveedor->razon_social . '" title="Eliminar proveedor ' . $proveedor->razon_social . '"><i class="fa fa-trash"></i></a>'
            ];
        });

        $response = [
            'data' => $proveedores,
            'recordsTotal' => $proveedores->count(),
            'recordsFiltered' => $proveedores->count()
        ];

        return response()->json($response);
    }

    public function getComunas(Region $region)
    {
        $comunas = $region->comunas()
            ->select('id', 'nom_comuna')
            ->orderBy('nom_comuna')
            ->get();

        return response()->json($comunas);
    }
}
