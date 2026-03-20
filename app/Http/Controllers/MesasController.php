<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MesasController extends Controller
{
    private const LAYOUT_FILE = 'layouts/restaurant_mesas_layout.json';

    private function sincronizarLayoutMesas(): void
    {
        $disk = Storage::disk('local');

        $layout = [
            'canvas' => [
                'width' => 1200,
                'height' => 700,
                'grid' => 20,
            ],
            'mesas' => [],
        ];

        if ($disk->exists(self::LAYOUT_FILE)) {
            $contenido = json_decode($disk->get(self::LAYOUT_FILE), true);
            if (is_array($contenido)) {
                $layout = array_merge($layout, $contenido);
                if (!isset($layout['canvas']) || !is_array($layout['canvas'])) {
                    $layout['canvas'] = [
                        'width' => 1200,
                        'height' => 700,
                        'grid' => 20,
                    ];
                }
                if (!isset($layout['mesas']) || !is_array($layout['mesas'])) {
                    $layout['mesas'] = [];
                }
            }
        }

        $mesasExistentes = collect($layout['mesas'])->keyBy(function ($mesa) {
            return (string) ($mesa['mesa_id'] ?? '');
        });

        $mesasDb = Mesa::where('activa', true)
            ->orderBy('orden')
            ->get(['id', 'nombre', 'capacidad']);

        $mesasLayout = [];
        $maxColumnas = 6;
        $separacionX = 180;
        $separacionY = 140;

        foreach ($mesasDb as $index => $mesaDb) {
            $existente = $mesasExistentes->get((string) $mesaDb->id);

            $columna = $index % $maxColumnas;
            $fila = intdiv($index, $maxColumnas);

            $mesasLayout[] = [
                'mesa_id' => $mesaDb->id,
                'nombre' => $mesaDb->nombre,
                'capacidad' => $mesaDb->capacidad,
                'x' => $existente['x'] ?? (40 + ($columna * $separacionX)),
                'y' => $existente['y'] ?? (40 + ($fila * $separacionY)),
                'width' => $existente['width'] ?? 130,
                'height' => $existente['height'] ?? 90,
                'shape' => $existente['shape'] ?? 'rect',
                'rotation' => $existente['rotation'] ?? 0,
            ];
        }

        $layout['mesas'] = $mesasLayout;
        $layout['updated_at'] = now()->toDateTimeString();
        $layout['updated_by'] = optional(auth()->user())->name ?? 'SISTEMA';

        $disk->put(self::LAYOUT_FILE, json_encode($layout, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function index()
    {
        return view('restaurant.mesas');
    }

    public function obtener()
    {
        try {
            $mesas = Mesa::orderBy('orden')->get();
            
            return response()->json([
                'success' => true,
                'mesas' => $mesas
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las mesas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ultimaOrden = Mesa::max('orden');
            
            $mesa = Mesa::create([
                'nombre' => $request->nombre,
                'capacidad' => $request->capacidad,
                'orden' => $ultimaOrden + 1,
                'activa' => true
            ]);

            $this->sincronizarLayoutMesas();

            return response()->json([
                'success' => true,
                'message' => 'Mesa creada correctamente',
                'mesa' => $mesa
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:1|max:20',
            'activa' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $mesa = Mesa::findOrFail($id);
            
            $mesa->update([
                'nombre' => $request->nombre,
                'capacidad' => $request->capacidad,
                'activa' => $request->activa
            ]);

            $this->sincronizarLayoutMesas();

            return response()->json([
                'success' => true,
                'message' => 'Mesa actualizada correctamente',
                'mesa' => $mesa
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            $mesa = Mesa::findOrFail($id);
            $mesa->delete();

            $this->sincronizarLayoutMesas();

            return response()->json([
                'success' => true,
                'message' => 'Mesa eliminada correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }
}
