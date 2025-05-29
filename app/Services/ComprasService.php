<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\Boleta;
use App\Models\Facturas;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\PagosFactura;
use App\Models\DetalleBoleta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\HistorialMovimientos;
use Illuminate\Support\Facades\Auth;

class ComprasService
{
    public function subirFoto(UploadedFile $archivo, string $nombre, string $tipo, string $usuario): string
    {
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $nombre . '.' . $extension;
        $rutaRelativa = "img/documentos_fotos/facturas/{$nombreArchivo}";
        $directorio = public_path('img/documentos_fotos/facturas');

        if (!file_exists($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $archivo->move($directorio, $nombreArchivo);

        if ($tipo === 'Factura') {
            DB::table('facturas')
                ->where('num_factura', $nombre)
                ->update(['foto' => $rutaRelativa, 'usuario_foto' => $usuario]);
        } else {
            DB::table('boletas')
                ->where('num_boleta', $nombre)
                ->update(['foto' => $rutaRelativa, 'usuario_foto' => $usuario]);
        }

        return asset($rutaRelativa);
    }

    public function registrarPago(array $data): string
    {
        try {
            DB::beginTransaction();

            // 1. Buscar la factura
            $factura = Facturas::where('num_factura', $data['nfac'])->first();
            if (!$factura) {
                return 'NO';
            }

            // 2. Registrar el pago
            PagosFactura::create([
                'nro_factura' => $factura->num_factura,
                'monto_pago'  => $data['valpag'],
                'fpago'       => $data['forpag'],
                'num_docu'    => $data['ndocpag'],
                'fecha_pago'  => Carbon::now(),
                'usuario'     => $data['usuario']
            ]);

            // 3. Calcular total pagado
            $totalPagado = $factura->pagos()->sum('monto_pago');

            // 4. Comparar con total_fact y actualizar estado si corresponde
            if ((float) $totalPagado === (float) $factura->total_fact) {
                $factura->estado = 'P';
                $factura->save();
            }

            DB::commit();
            return 'OK';

        } catch (\Exception $e) {
            DB::rollBack();
            return 'NO';
        }
    }

    public function obtenerPagosFactura(int $numFactura): array
    {
        $pagos = PagosFactura::where('nro_factura', $numFactura)
            ->orderByDesc('fecha_pago')
            ->get();

        $total = $pagos->sum('monto_pago');

        $pagosFormateados = $pagos->map(function ($pago) {
            return [
                'fpago'      => $pago->fpago,
                'monto_pago' => number_format($pago->monto_pago, 0, ',', '.'),
                'num_docu' => $pago->num_docu ?? '',
                'fecha_pago' => Carbon::parse($pago->fecha_pago)->format('d-m-Y H:i'),
            ];
        });

        return [
            'pagos' => $pagosFormateados,
            'total' => number_format($total, 0, ',', '.'),
        ];
    }

    public function grabarCompraBoleta($items,$cabecera)
    {
        $boletaNum = $cabecera[0]['num_doc'];
        $proveedorId = $cabecera[0]['prov'];

        $id_prov = Proveedor::where('uuid', $proveedorId)->value('id');
      
        $existe = Boleta::with('proveedor')
            ->where('num_boleta', $boletaNum)
            ->where('prov_id', $id_prov)
            ->first();

        if ($existe) {
            $msg = "Boleta {$existe->num_boleta} ya esta asociada al proveedor {$existe->proveedor->razon_social} por un monto total de " .
                   number_format($existe->tot_boleta, 0, ",", ".") . " con fecha " .
                   $existe->fecha_boleta->format('d-m-Y');

            return response()->json([
                'status' => 'EXISTE',
                'message' => strtoupper($msg)
            ]);
        }
   
        DB::beginTransaction();

        try {
            $total = 0;
            $grabaBoleta=Boleta::grabarBoleta($cabecera[0]);
     
            foreach ($items as $item) {
                DetalleBoleta::grabarDetalleBoleta($item);
                $total += (float) $item->precio * (float) $item->cant;
                $producto = Producto::where('codigo', $item->cod)->first();
            
                if ($producto) {
                    $idProducto = $producto->id;
            
                    $producto->stock += $item->cant;
                    $producto->save();
            
                    HistorialMovimientos::registrarMovimiento([
                        'producto_id' => $idProducto,
                        'cantidad' => $item->cant,
                        'stock' => $producto->stock,
                        'tipo_mov' => 'BOLETA COMPRA',
                        'fecha' => now(),
                        'num_doc' => $item->nbol,
                        'obs' => '-'
                    ]);
                }
            }
            $grabaBoleta->tot_boleta = $total;
            $grabaBoleta->save();

            DB::commit();

            return response()->json([
                'status' => 'OK',
                'message' => 'Boleta grabada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
