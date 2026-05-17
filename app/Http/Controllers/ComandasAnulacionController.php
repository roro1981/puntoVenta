<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AnulacionProductoComanda;
use App\Models\Comanda;
use App\Models\DetalleComanda;

class ComandasAnulacionController extends Controller
{
    public function anularProducto(Request $request)
    {
        $request->validate([
            'comanda_id' => 'required|exists:comandas,id',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'password' => 'required|string',
            'motivo' => 'required|string|max:100',
        ]);

        $user = Auth::user();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Contraseña incorrecta'], 401);
        }
        if (!$user->tienePermiso('PERMISO_ANULAR_TICKETS')) {
            return response()->json(['success' => false, 'message' => 'No tiene permiso para anular productos'], 403);
        }

        // Buscar el detalle de la comanda
        $detalle = DetalleComanda::where('comanda_id', $request->comanda_id)
            ->where('producto_id', $request->producto_id)
            ->first();
        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'No se encontró el producto en la comanda'], 404);
        }

        // Validar que la cantidad a anular no sea mayor a la cantidad disponible
        if ($request->cantidad > $detalle->cantidad) {
            return response()->json(['success' => false, 'message' => 'No puedes anular más de ' . $detalle->cantidad . ' unidades'], 400);
        }

        // Restar la cantidad anulada del detalle
        $detalle->cantidad -= $request->cantidad;
        
        // Si la cantidad llegó a 0, eliminar el detalle
        if ($detalle->cantidad <= 0) {
            $detalle->delete();
        } else {
            // Si aún hay cantidad, actualizar
            $detalle->save();
        }

        // Registrar la anulación
        AnulacionProductoComanda::create([
            'comanda_id' => $request->comanda_id,
            'producto_id' => $request->producto_id,
            'usuario_id' => $user->id,
            'motivo' => $request->motivo,
            'cantidad' => $request->cantidad,
        ]);

        // Verificar si la comanda quedó sin detalles
        $detallesRestantes = DetalleComanda::where('comanda_id', $request->comanda_id)->count();
        if ($detallesRestantes === 0) {
            // Si no hay más detalles, cerrar la comanda automáticamente
            $comanda = Comanda::findOrFail($request->comanda_id);
            $comanda->update(['estado' => 'CERRADA']);
        }

        return response()->json(['success' => true]);
    }
}
