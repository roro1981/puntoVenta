<?php

namespace App\Http\Controllers;

use App\Models\PagosFactura;
use Illuminate\Http\Request;
use App\Models\DetalleBoleta;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Facturas, DetalleFactura, Producto, HistorialMovimientos, Proveedor, FormaPago, Impuestos, Region, Boleta};

class ComprasController extends Controller
{
    public function indexProveedores()
    {
        $regiones = Region::all();
        $formasPago = FormaPago::all();
        return view('compras.proveedores', compact('regiones','formasPago'));
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
                'actions' => '<a href="" class="btn btn-sm btn-primary editar" data-target="#editProveedorModal" data-uuid="' . $proveedor->uuid . '" data-toggle="modal" title="Editar proveedor ' . $proveedor->razon_social . '"><i class="fa fa-edit"></i></a>
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

    public function createProveedor(Request $request)
    {
        $validated = $request->all();

        try {
            Proveedor::storeProv($validated);

            $response = response()->json([
                'error' => 200,
                'message' => "Proveedor creado correctamente"
            ], 200);
        } catch (\Exception $e) {
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function editProveedor(string $uuid)
    {
        $proveedor = Proveedor::query()
            ->select('*')              
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($proveedor);
    }

    public function updateProveedor(Request $request, $uuid)
    {
        try {
            $data = $request->all();
            $proveedor = Proveedor::where('uuid', $uuid)->firstOrFail();
            $proveedor->updateProv($data);

            $response = response()->json([
                'error' => 200,
                'message' => "Proveedor modificado correctamente"
            ], 200);
        } catch (\Exception $e) {
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function deleteProveedor($uuid)
    {
        try {
            $proveedor = Proveedor::where('uuid', $uuid)->firstOrFail();
            $proveedor->deleteProv();

            $response = response()->json([
                'error' => 200,
                'message' => "Proveedor eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }

    public function indexIngresos()
    {
        $proveedores = Proveedor::all();
        $impuestos = Impuestos::all();
        return view('compras.ingresos', compact('proveedores','impuestos'));
    }

    public function pagoProv($uuid_prov)
    {
        $proveedor = Proveedor::with('formaPago')->where('uuid', $uuid_prov)->first();

        if ($proveedor && $proveedor->formaPago) {
            $pago = $proveedor->formaPago->descripcion_pago;
        } else {
            $pago = '';
        }

        return $pago;
    }

    public function productosCompra()
    {
        $productos = Producto::with('categoria:id,descripcion_categoria')
            ->where('estado', 'Activo')
            ->whereIn('tipo', ['P', 'I'])
            ->get(['uuid', 'descripcion', 'categoria_id', 'codigo', 'stock', 'imagen', 'precio_compra_neto', 'impuesto1', 'impuesto2']);
            $data = [];

            foreach ($productos as $pro) {
                // Simular traeImp() → aquí deberías reemplazar con la lógica real o consulta
                $imp1 = $pro->impuesto1 != 0 ? $this->traeImp($pro->impuesto1) : 0;
                $imp2 = $pro->impuesto2 != 0 ? $this->traeImp($pro->impuesto2) : 0;
        
                $opc = "<input type='text' id='{$pro->uuid}' 
                            data-impu1='{$imp1}' 
                            data-impu2='{$imp2}' 
                            data-nombre='{$pro->descripcion}' 
                            data-codigo='{$pro->codigo}' 
                            data-precio='{$pro->precio_compra_neto}' 
                            data-total='{$pro->precio_compra_neto}' 
                            onchange='agregarDetalle(\"{$pro->uuid}\")' 
                            style='width:50px' 
                            class='incant form-control'>";
        
                $foto = $pro->imagen !== null 
                    ? "<img src='img/fotos_prod/{$pro->imagen}' height='50px' width='50px'>" 
                    : "<img src='img/fotos_prod/sin_imagen.jpg' height='50px' width='50px'>";
        
                $datos = [
                    'opciones' => $opc,
                    'nombre'   => $pro->descripcion,
                    'cat'      => $pro->categoria->descripcion_categoria ?? '',
                    'codigo'   => $pro->codigo,
                    'stock'    => $pro->stock,
                    'foto'     => $foto,
                ];
        
                $data[] = $datos;
            }
        
            return response()->json($data);
    }
    private function traeImp($imp)
    {
        $impuesto = Impuestos::select('valor_imp', 'nom_imp')->where('id', $imp)->first();
        if ($impuesto) {
            return $impuesto->valor_imp . '_' . $impuesto->nom_imp;
        }

        return 0;
    }
    public function traeDocs(): JsonResponse
    {
        $boletas = Boleta::with('proveedor')
            ->select(DB::raw("num_boleta as num_doc, prov_id, tot_boleta as total, fecha_boleta as fecha_doc, fec_creacion, 'Boleta' as tipo, foto, '1' as estado"));

        $facturas = Facturas::with('proveedor')
            ->select(DB::raw("num_factura as num_doc, prov_id, (neto + impuestos) as total, fecha_doc, fec_creacion, 'Factura' as tipo, foto, estado"));

        $documentos = $boletas->unionAll($facturas)->orderBy('fec_creacion', 'desc')->get();

        $data = [];

        foreach ($documentos as $doc) {
            $items = 0;
            $saldo = 0;
            $pago = "";

            $detalleModal = $doc->tipo === 'Boleta'
                ? '#modalDetalleCompraBol'
                : '#modalDetalleCompraFact';

            $clase = $doc->tipo === 'Boleta'
                ? 'detalle_documento2'
                : 'detalle_documento';

            $detalle = "<img src='img/detalle_doc.png' class='{$clase}' id='{$doc->num_doc}' data-tipo='{$doc->tipo}' width='20' height='20' style='cursor:pointer' data-toggle='modal' data-target='{$detalleModal}' title='Ver detalle de documento'>";

            $foto = $doc->foto === "-" ? "" : "<img src='img/foto_doc.jpg' class='foto_doc' width='30' height='30' style='cursor:pointer' data-toggle='modal' data-ruta='{$doc->foto}' data-numdoc='{$doc->num_doc}' data-target='#ver_foto_doc' title='Ver foto de documento'>";

            if ($doc->tipo === 'Boleta') {
                $items = DetalleBoleta::where('num_boleta', $doc->num_doc)->count();
            } else {
                $items = DetalleFactura::where('num_factura', $doc->num_doc)->count();
            }

            if ($doc->estado === 'NP') {
                $saldo = PagosFactura::where('nro_factura', $doc->num_doc)->sum('monto_pago') ?? 0;
                $pago = "<img src='img/por_pagar.jpg' class='pago_pend' width='40' height='40' style='cursor:pointer' data-toggle='modal' data-numdoc='{$doc->num_doc}' data-totdoc='{$doc->total}' data-saldodoc='{$saldo}' data-target='#modulo_pago' title='Documento con pago pendiente'>";
            } elseif ($doc->estado === 'P') {
                $pago = "<img src='img/pagada.jpg' width='30' height='30' style='cursor:pointer' title='Documento pagado'>";
            }

            $data[] = [
                'tipo'     => $doc->tipo . ' ' . $foto,
                'numdoc'   => $doc->num_doc,
                'prov'     => $doc->proveedor->razon_social ?? '',
                'total'    => number_format($doc->total, 0, ",", "."),
                'fec_doc'  => Carbon::parse($doc->fecha_doc)->format('d-m-Y'),
                'items'    => $items,
                'fec_ing'  => "<span style='display:none'>" . Carbon::parse($doc->fecha_ing)->format('Y-m-d H:i:s') . "</span>" . Carbon::parse($doc->fecha_ing)->format('d-m-Y H:i:s'),
                'opciones' => $detalle . ' ' . $pago,
            ];
        }

        return response()->json($data);
    }

    public function facturasCalendario()
    {
        $hoy = \Carbon\Carbon::today();
        $data = [];

        $facturas = Facturas::with('proveedor')->get();

        foreach ($facturas as $fac) {
            $fechaVenc = \Carbon\Carbon::parse($fac->vencimiento);

            if ($fac->estado === 'P') {
                $estadoTexto = ' (PAGADA)';
                $color = 'green';
            } elseif ($hoy->gt($fechaVenc)) {
                $estadoTexto = ' (VENCIDA)';
                $color = 'red';
            } elseif ($hoy->eq($fechaVenc)) {
                $estadoTexto = ' (VENCE HOY)';
                $color = 'blue';
            } else {
                $estadoTexto = ' (POR VENCER)';
                $color = 'orange';
            }

            $data[] = [
                'title' => $fac->proveedor->razon_social . ' | Factura: ' . $fac->num_factura . ' | Monto: ' . number_format($fac->total_fact, 0, ',', '.') . ' FORMA DE PAGO: ' . $fac->fpago . $estadoTexto,
                'start' => $fac->vencimiento,
                'end' => $fac->vencimiento,
                'backgroundColor' => $color,
            ];
        }

        return response()->json($data);
    }

    public function traeDocsPorEstado($estado)
    {
        $data = [];

        $facturas = Facturas::with('proveedor')
            ->where('estado', $estado)
            ->get();

        foreach ($facturas as $doc) {
            $foto = $doc->foto === "-" 
                ? "" 
                : "<img src='img/foto_doc.jpg' class='foto_doc' width='30' height='30' style='cursor:pointer' data-toggle='modal' data-ruta=\"{$doc->foto}\" data-numdoc=\"{$doc->num_factura}\" data-target='#ver_foto_doc' title='Ver foto de documento'>";

            $detalle = "<img src='img/detalle_doc.png' class='detalle_documento' id='{$doc->num_factura}' data-tipo='Factura' width='20' height='20' style='cursor:pointer' data-toggle='modal' data-target='#modalDetalleCompraFact' title='Ver detalle de documento'>";

            // Cantidad de ítems
            $items = $doc->detalles()->count();

            // Saldo
            $pagado = $doc->pagos()->sum('monto_pago');
            $saldo = $doc->neto + $doc->impuestos - $pagado;

            $pago = "<img src='img/por_pagar.jpg' class='pago_pend' width='40' height='40' style='cursor:pointer' data-toggle='modal' data-numdoc=\"{$doc->num_factura}\" data-totdoc=\"".($doc->neto + $doc->impuestos)."\" data-saldodoc=\"{$saldo}\" data-target='#modulo_pago' title='Documento con pago pendiente'>";

            $data[] = [
                'tipo'     => "Factura $foto",
                'numdoc'   => $doc->num_factura,
                'prov'     => $doc->proveedor->razon_social ?? '',
                'total'    => number_format($doc->neto + $doc->impuestos, 0, ',', '.'),
                'fec_doc'  => Carbon::parse($doc->fecha_doc)->format('d-m-Y'),
                'items'    => $items,
                'fec_ing'  => "<span style='display:none'>" . Carbon::parse($doc->fecha_ing)->format('Y-m-d H:i:s') . "</span>" . Carbon::parse($doc->fecha_ing)->format('d-m-Y H:i:s'),
                'opciones' => "$detalle $pago",
            ];
        }

        return response()->json($data);
    }

    public function grabaCompra(Request $request): JsonResponse
    {
        $data = json_decode($request->input('arr'));
        $data2 = json_decode($request->input('arr2'));
        $neto = 0;

        DB::beginTransaction();

        try {
            if (!$data || !$data2) {
                return response()->json(['status' => 'ERROR', 'message' => 'Datos incompletos'], 422);
            }
        
            $numDoc = $data2[0]->num_doc;
            $provId = $data2[0]->prov;

            // Verificar si existe la factura
            $existe = Facturas::with('proveedor')
                ->where('num_factura', $numDoc)
                ->where('id', $provId)
                ->first();

            if ($existe) {
                $desglose = "Factura {$numDoc} del proveedor {$existe->proveedor->razon_social} por un monto total de " .
                    number_format($existe->total_fact, 0, ",", ".") .
                    " fue ingresada el " . Carbon::parse($existe->fecha_ing)->format('d-m-Y') .
                    " a las " . Carbon::parse($existe->fecha_ing)->format('H:i:s');

                return response()->json([
                    'status' => 'EXISTE',
                    'message' => strtoupper($desglose)
                ], 200);
            }   

            $factura = Facturas::iniciarCabecera($data2[0]);

            foreach ($data as $item) {
                $item->nfact = $factura->num_factura;
                DetalleFactura::grabarDetalle($item);

                Producto::where('codigo', $item->cod)->increment('stock', $item->cant);

                $producto = Producto::where('codigo', $item->cod)->first();
                $idProducto = $producto->id;
                $stockActual = $producto->stock;

                HistorialMovimientos::create([
                    'producto_id' => $idProducto,
                    'cantidad' => $item->cant,
                    'stock' => $stockActual,
                    'tipo_mov' => 'FACTURA COMPRA',
                    'fecha' => now(),
                    'num_doc' => $item->nfact,
                    'obs' => '-',
                ]);

                $neto += intval($item->precio * $item->cant);
            }
            $factura->update([
                'neto' => $neto,
                'total_fact' => $neto + intval($data2[0]->impuestos)
            ]);
            DB::commit();

            return response()->json([
                'status' => 'OK',
                'message' => 'Factura grabada exitosamente'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Error al grabar la factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
