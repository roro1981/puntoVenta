<?php

namespace App\Http\Controllers;

use App\Models\PagosFactura;
use Illuminate\Http\Request;
use App\Models\DetalleBoleta;
use Illuminate\Support\Carbon;
use App\Services\ComprasService;
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
                    ? "<img src='{$pro->imagen}' height='50px' width='50px'>" 
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
            ->select(DB::raw("num_boleta as num_doc, prov_id, tot_boleta as total, fecha_boleta as fecha_doc, fec_creacion, 'Boleta' as tipo, foto, usuario_foto,'1' as estado"));

        $facturas = Facturas::with('proveedor')
            ->select(DB::raw("num_factura as num_doc, prov_id, (neto + impuestos) as total, fecha_doc, fec_creacion, 'Factura' as tipo, foto, usuario_foto, estado"));

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

            $foto = (empty($doc->foto) || $doc->foto === "-")
            ? ""
            : "<img src='img/foto_doc.jpg' class='foto_doc' width='30' height='30' style='cursor:pointer' data-toggle='modal' data-ruta='{$doc->foto}' data-numdoc='{$doc->num_doc}' data-usuario='{$doc->usuario_foto}' data-target='#ver_foto_doc' title='Ver foto de documento'>";

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
                'fec_ing'  => Carbon::parse($doc->fec_creacion)->format('d-m-Y'),
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
            $foto = $doc->foto === "" 
                ? "" 
                : "<img src='img/foto_doc.jpg' class='foto_doc' width='30' height='30' style='cursor:pointer' data-toggle='modal' data-ruta=\"{$doc->foto}\" data-numdoc=\"{$doc->num_factura}\" data-target='#ver_foto_doc' title='Ver foto de documento'>";

            $detalle = "<img src='img/detalle_doc.png' class='detalle_documento' id='{$doc->num_factura}' data-tipo='Factura' width='20' height='20' style='cursor:pointer' data-toggle='modal' data-target='#modalDetalleCompraFact' title='Ver detalle de documento'>";

            // Saldo
            $pagado = $doc->pagos()->sum('monto_pago');
            $saldo = $doc->neto + $doc->impuestos - $pagado;

            $items = DetalleFactura::where('num_factura', $doc->num_factura)->count();
            
            if ($doc->estado === 'NP') {
                $saldo = PagosFactura::where('nro_factura', $doc->num_doc)->sum('monto_pago') ?? 0;
                $pago = "<img src='img/por_pagar.jpg' class='pago_pend' width='40' height='40' style='cursor:pointer' data-toggle='modal' data-numdoc='{$doc->num_doc}' data-totdoc='{$doc->total}' data-saldodoc='{$saldo}' data-target='#modulo_pago' title='Documento con pago pendiente'>";
            } elseif ($doc->estado === 'P') {
                $pago = "<img src='img/pagada.jpg' width='30' height='30' style='cursor:pointer' title='Documento pagado'>";
            }

            $data[] = [
                'tipo'     => "Factura $foto",
                'numdoc'   => $doc->num_factura,
                'prov'     => $doc->proveedor->razon_social ?? '',
                'total'    => number_format($doc->neto + $doc->impuestos, 0, ',', '.'),
                'fec_doc'  => Carbon::parse($doc->fecha_doc)->format('d-m-Y'),
                'items'    => $items,
                'fec_ing'  => Carbon::parse($doc->fec_creacion)->format('d-m-Y'),
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

            $factura = Facturas::grabarFactura($data2[0]);
          
            foreach ($data as $item) {
                $item->nfact = $factura->num_factura;
                DetalleFactura::grabarDetalleFactura($item);

                $producto = Producto::where('codigo', $item->cod)->first();

                if ($producto) {
                    $producto->stock += $item->cant;
                    $producto->save();
                }

                $idProducto = $producto->id;

                HistorialMovimientos::registrarMovimiento([
                    'producto_id' => $idProducto,
                    'cantidad' => $item->cant,
                    'stock' => $producto->stock,
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

    public function traeDetalleDoc(Request $request)
    {
        $num_doc = $request->input('docu');
        $tipo_docu = $request->input('tipo');
       
        $detalle = $this->obtenerDetalleDocumento($num_doc, $tipo_docu);
        $saldo = ($tipo_docu === 'Factura' && $this->obtenerSaldoFactura($num_doc) > 0) ? 'SI' : 'NO';

        $data = [];
        
        foreach ($detalle as $doc) {
            if($tipo_docu === 'Factura'){
                $estado = ($doc->estado === 'NP') ? 'POR PAGAR' : 'PAGADA';
                $subt = ($doc->cantidad ?? 0) * ($doc->precio ?? 0);

                $data[] = [
                    'codigo'     => $doc->cod_producto,
                    'nombre'     => $doc->descripcion,
                    'cant'       => $doc->cantidad,
                    'precio'     => number_format($doc->precio, 0, ",", "."),
                    'descue'     => $doc->descuento,
                    'subt'       => number_format($subt, 0, ",", "."),
                    'imp1'       => $doc->impuesto,
                    'imp2'       => $doc->impuesto2,
                    'desglose'   => $doc->desglose_impuestos,
                    'neto'       => number_format($doc->neto ?? 0, 0, ",", "."),
                    'impuestos'  => number_format($doc->impuestos ?? 0, 0, ",", "."),
                    'total'      => number_format($doc->total_fact ?? 0, 0, ",", "."),
                    'dias'       => $doc->dias,
                    'venc'       => $doc->vencimiento ? Carbon::parse($doc->vencimiento)->format('d-m-Y') : null,
                    'fpago'      => $doc->fpago,
                    'estado'     => $estado,
                    'saldo'      => $saldo,
                ];
            }else{
                $data[] = [ 
                    'codigo'  => $doc->cod_producto,
                    'nombre'  => $doc->descripcion,
                    'cant'    => $doc->cantidad,
                    'precio'  => number_format($doc->precio, 0, ",", "."),
                    'descue'  => $doc->descuento,
                    'subt'    => number_format(($doc->cantidad * $doc->precio), 0, ",", "."),
                    'total'   => number_format($doc->tot_boleta, 0, ",", ".")
                ];
            }
        }

        return response()->json($data);
    }

    private function obtenerDetalleDocumento($num, $tip)
    {
        if ($tip === 'Factura') {
            return DB::table('detalle_factura as df')
                ->join('productos as p', 'p.codigo', '=', 'df.cod_producto')
                ->join('impuestos as i', 'i.id', '=', 'p.impuesto1')
                ->leftJoin('impuestos as i2', 'i2.id', '=', 'p.impuesto2')
                ->join('facturas as f', 'f.num_factura', '=', 'df.num_factura')
                ->where('df.num_factura', $num)
                ->selectRaw('df.cod_producto, p.descripcion, df.cantidad, df.precio, df.descuento,
                             i.nom_imp as impuesto, i2.nom_imp as impuesto2,
                             f.desglose_impuestos, f.neto, f.impuestos, f.total_fact,
                             f.dias, f.vencimiento, f.fpago, f.estado')
                ->get();
        }

        // Para boletas
        return DB::table('detalle_boleta as db')
            ->join('productos as p', 'p.codigo', '=', 'db.cod_prod')
            ->join('boletas as b', 'b.num_boleta', '=', 'db.num_boleta')
            ->where('db.num_boleta', $num)
            ->selectRaw('db.cod_prod as cod_producto, p.descripcion, db.cantidad, db.precio, db.descu as descuento,
                         NULL as impuesto, NULL as impuesto2, NULL as desglose_impuestos, NULL as neto,
                         NULL as impuestos, b.tot_boleta,
                         NULL as dias, NULL as vencimiento, NULL as fpago, "NP" as estado')
            ->get();
    }

    private function obtenerSaldoFactura($num)
    {
        return DB::table('pagos_factura')
            ->where('nro_factura', $num)
            ->sum('monto_pago');
    }

    public function subirFotoDoc(Request $request, ComprasService $comprasService)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png|max:1024',
            'nombre' => 'required',
            'tipo' => 'required|in:Factura,Boleta',
        ]);

        try {
            $usuario = auth()->user()->name ?? 'sistema';
            $ruta = $comprasService->subirFoto(
                $request->file('file'),
                $request->nombre,
                $request->tipo,
                $usuario
            );

            return response()->json([
                'estado' => 'ok',
                'mensaje' => 'Imagen subida correctamente.',
                'ruta' => $ruta,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al subir la imagen: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function grabaPago(Request $request, ComprasService $comprasService)
    {
        $request->validate([
            'nfac'    => 'required|integer',
            'valpag'  => 'required|numeric|min:1',
            'forpag'  => 'required|string',
            'ndocpag' => 'nullable|string',
        ]);

        $usuario = auth()->user()->name;

        $resultado = $comprasService->registrarPago([
            'nfac'     => $request->input('nfac'),
            'valpag'   => $request->input('valpag'),
            'forpag'   => $request->input('forpag'),
            'ndocpag'  => $request->input('ndocpag'),
            'usuario'  => $usuario,
        ]);

        return response()->json(['estado' => $resultado]);
    }

    public function detallePagos(Request $request, ComprasService $comprasService)
    {
        $request->validate([
            'numfac' => 'required|integer',
        ]);

        $resultado = $comprasService->obtenerPagosFactura($request->numfac);

        return response()->json($resultado);
    }

    public function grabarBoleta(Request $request, ComprasService $service)
    {
        $items = json_decode($request->input('arr'));
        $cabecera = json_decode($request->input('arr2'), true);

        return $service->grabarCompraBoleta($items, $cabecera);
    }

    public function indexMovs()
    {
        return view('compras.entradas_salidas');
    }

    public function searchProductosAll(Request $request)
    {
        $term = $request->input('q');

        $products = Producto::where(function ($query) use ($term) {
            $query->where('codigo', 'like', "%{$term}%")
                ->orWhere('descripcion', 'like', "%{$term}%");
        })
            ->where('tipo', '<>', 'S')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function cargarMovimiento(Request $request)
    {
        $data = $request->only(['idp', 'tipo_mov', 'canti']);
        return app(ComprasService::class)->cargarMovimiento($data);
    }

    public function registrarMovimientos(Request $request)
    {
        try {
            $items = json_decode($request->input('arr'), true);

            if (!$items || !is_array($items)) {
                return response()->json(['status' => 'ERROR', 'message' => 'Datos inválidos'], 400);
            }

            app(ComprasService::class)->registrarMovimientos($items);

            return response()->json(['status' => 'OK'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
