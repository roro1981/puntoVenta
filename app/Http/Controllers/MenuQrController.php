<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CorporateData;
use App\Models\Globales;
use App\Models\MenuQrConfiguration;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MenuQrController extends Controller
{
    public function index()
    {
        $configuracion = $this->obtenerOCrearConfiguracion();
        $corporateData = $this->corporateDataMap();
        $categorias = $this->construirCatalogoConfiguracion($configuracion);
        $menuUrl = route('menu-qr.publico', ['token' => $configuracion->public_token]);
        $qrDataUri = $this->qrDataUri($menuUrl);

        return view('configuration.menu_qr', [
            'configuracion' => $configuracion,
            'corporateData' => $corporateData,
            'categorias' => $categorias,
            'menuUrl' => $menuUrl,
            'qrDataUri' => $qrDataUri,
            'selectedCategories' => collect($configuracion->selected_categories ?? [])->map(fn ($value) => (string) $value)->all(),
            'selectedItems' => collect($configuracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all(),
        ]);
    }

    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'selected_categories' => ['nullable', 'array'],
            'selected_categories.*' => ['integer'],
            'selected_items' => ['nullable', 'array'],
            'selected_items.*' => ['string'],
        ]);

        $catalogo = $this->catalogoCategoriasMenu();
        $categoriasPermitidas = $catalogo->pluck('id')->map(fn ($value) => (int) $value)->all();
        $itemsPermitidos = [];
        foreach ($catalogo as $categoria) {
            foreach ($categoria->productos as $producto) {
                $itemsPermitidos[] = 'product:' . $producto->id;
            }
            foreach ($categoria->recetas as $receta) {
                $itemsPermitidos[] = 'recipe:' . $receta->id;
            }
        }

        $selectedCategories = array_values(array_filter(
            array_map('intval', $validated['selected_categories'] ?? []),
            fn ($id) => in_array($id, $categoriasPermitidas, true)
        ));

        $selectedItems = array_values(array_filter(
            array_map('strval', $validated['selected_items'] ?? []),
            fn ($item) => in_array($item, $itemsPermitidos, true)
        ));

        $configuracion = $this->obtenerOCrearConfiguracion();
        $configuracion->selected_categories = $selectedCategories;
        $configuracion->selected_items = $selectedItems;
        $configuracion->activo = true;
        $configuracion->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración del menú QR guardada correctamente.',
            'menu_url' => route('menu-qr.publico', ['token' => $configuracion->public_token]),
        ]);
    }

    public function publico(string $token)
    {
        $configuracion = MenuQrConfiguration::query()
            ->where('public_token', $token)
            ->where('activo', true)
            ->firstOrFail();

        $corporateData = $this->corporateDataMap();
        $categorias = $this->construirMenuPublico($configuracion);

        return view('public.menu_qr', [
            'configuracion' => $configuracion,
            'corporateData' => $corporateData,
            'categorias' => $categorias,
            'menuUrl' => route('menu-qr.publico', ['token' => $configuracion->public_token]),
            'qrDataUri' => $this->qrDataUri(route('menu-qr.publico', ['token' => $configuracion->public_token])),
        ]);
    }

    public function pdf(Request $request, string $token)
    {
        $configuracion = MenuQrConfiguration::query()
            ->where('public_token', $token)
            ->where('activo', true)
            ->firstOrFail();

        $copias = max(1, min(50, (int) $request->input('copias', 1)));
        $menuUrl = route('menu-qr.publico', ['token' => $configuracion->public_token]);
        $corporateData = $this->corporateDataMap();
        $qrDataUri = $this->qrDataUri($menuUrl);

        $pdf = app('dompdf.wrapper')->loadView('configuration.menu_qr_pdf', [
            'configuracion' => $configuracion,
            'corporateData' => $corporateData,
            'menuUrl' => $menuUrl,
            'qrDataUri' => $qrDataUri,
            'copias' => $copias,
        ]);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('menu-qr-' . $configuracion->public_token . '.pdf');
    }

    private function obtenerOCrearConfiguracion(): MenuQrConfiguration
    {
        $configuracion = MenuQrConfiguration::query()->first();

        if ($configuracion) {
            return $configuracion;
        }

        $categorias = $this->catalogoCategoriasMenu();
        $selectedCategories = $categorias->pluck('id')->map(fn ($value) => (int) $value)->all();
        $selectedItems = [];

        foreach ($categorias as $categoria) {
            foreach ($categoria->productos as $producto) {
                $selectedItems[] = 'product:' . $producto->id;
            }

            foreach ($categoria->recetas as $receta) {
                $selectedItems[] = 'recipe:' . $receta->id;
            }
        }

        return MenuQrConfiguration::create([
            'public_token' => (string) Str::uuid(),
            'selected_categories' => $selectedCategories,
            'selected_items' => $selectedItems,
            'activo' => true,
        ]);
    }

    private function catalogoCategoriasMenu()
    {
        return Categoria::query()
            ->where('estado_categoria', 1)
            ->whereRaw('LOWER(TRIM(descripcion_categoria)) <> ?', ['insumos'])
            ->with([
                'productos' => function ($query) {
                    $query->where('estado', 'Activo')->orderBy('descripcion');
                },
                'recetas' => function ($query) {
                    $query->where('estado', 'Activo')->orderBy('nombre');
                },
            ])
            ->orderBy('descripcion_categoria')
            ->get();
    }

    private function construirMenuPublico(MenuQrConfiguration $configuracion)
    {
        $selectedCategories = collect($configuracion->selected_categories ?? [])->map(fn ($value) => (int) $value)->all();
        $selectedItems = collect($configuracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all();
        $stockNegativo = Cache::remember('global_STOCK_NEGATIVO', 300, fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var'));
        $permitirStockNegativo = ($stockNegativo == '1');

        return Categoria::query()
            ->where('estado_categoria', 1)
            ->whereRaw('LOWER(TRIM(descripcion_categoria)) <> ?', ['insumos'])
            ->whereIn('id', $selectedCategories ?: [0])
            ->with([
                'productos' => function ($query) {
                    $query->where('estado', 'Activo')->orderBy('descripcion');
                },
                'recetas' => function ($query) {
                    $query->where('estado', 'Activo')
                        ->with(['ingredientes.producto'])
                        ->orderBy('nombre');
                },
            ])
            ->orderBy('descripcion_categoria')
            ->get()
            ->map(function ($categoria) use ($selectedItems, $permitirStockNegativo) {
                $items = collect();

                foreach ($categoria->productos as $producto) {
                    $itemKey = 'product:' . $producto->id;
                    if (!in_array($itemKey, $selectedItems, true)) {
                        continue;
                    }

                    $stock = (float) ($producto->stock ?? 0);
                    $disponible = $producto->tipo === 'S' ? true : ($permitirStockNegativo || $stock > 0);

                    $items->push([
                        'tipo' => 'product',
                        'id' => $producto->id,
                        'uuid' => $producto->uuid,
                        'nombre' => $producto->descripcion,
                        'descripcion' => null,
                        'precio' => (float) $producto->precio_venta,
                        'imagen' => $this->resolverImagen($producto->imagen),
                        'disponible' => $disponible,
                        'motivo' => $disponible ? null : 'Sin stock',
                        'stock' => $stock,
                        'tipo_producto' => $producto->tipo,
                    ]);
                }

                foreach ($categoria->recetas as $receta) {
                    $itemKey = 'recipe:' . $receta->id;
                    if (!in_array($itemKey, $selectedItems, true)) {
                        continue;
                    }

                    $evaluacion = $this->evaluarDisponibilidadReceta($receta, $permitirStockNegativo);
                    $items->push([
                        'tipo' => 'recipe',
                        'id' => $receta->id,
                        'uuid' => $receta->uuid,
                        'nombre' => $receta->nombre,
                        'descripcion' => $receta->descripcion,
                        'precio' => (float) $receta->precio_venta,
                        'imagen' => $this->resolverImagen($receta->imagen),
                        'disponible' => $evaluacion['disponible'],
                        'motivo' => $evaluacion['disponible'] ? null : 'Sin insumos',
                        'stock' => null,
                    ]);
                }

                $itemsOrdenados = $items
                    ->sortBy([
                        fn ($item) => $item['disponible'] ? 0 : 1,
                        fn ($item) => $item['tipo'] === 'product' ? 0 : 1,
                        fn ($item) => mb_strtolower((string) $item['nombre']),
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->descripcion_categoria,
                    'items' => $itemsOrdenados,
                ];
            })
            ->filter(fn ($categoria) => !empty($categoria['items']))
            ->values();
    }

    private function construirCatalogoConfiguracion(MenuQrConfiguration $configuracion)
    {
        $selectedItems = collect($configuracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all();
        $stockNegativo = Cache::remember('global_STOCK_NEGATIVO', 300, fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var'));
        $permitirStockNegativo = ($stockNegativo == '1');

        return $this->catalogoCategoriasMenu()
            ->map(function ($categoria) use ($selectedItems, $permitirStockNegativo) {
                $items = collect();

                foreach ($categoria->productos as $producto) {
                    $itemKey = 'product:' . $producto->id;
                    $stock = (float) ($producto->stock ?? 0);
                    $disponible = $producto->tipo === 'S' ? true : ($permitirStockNegativo || $stock > 0);

                    $items->push([
                        'tipo' => 'product',
                        'id' => $producto->id,
                        'uuid' => $producto->uuid,
                        'nombre' => $producto->descripcion,
                        'descripcion' => null,
                        'precio' => (float) $producto->precio_venta,
                        'imagen' => $this->resolverImagen($producto->imagen),
                        'disponible' => $disponible,
                        'motivo' => $disponible ? null : 'Sin stock',
                        'stock' => $stock,
                        'tipo_producto' => $producto->tipo,
                        'seleccionado' => in_array($itemKey, $selectedItems, true),
                    ]);
                }

                foreach ($categoria->recetas as $receta) {
                    $itemKey = 'recipe:' . $receta->id;
                    $evaluacion = $this->evaluarDisponibilidadReceta($receta, $permitirStockNegativo);

                    $items->push([
                        'tipo' => 'recipe',
                        'id' => $receta->id,
                        'uuid' => $receta->uuid,
                        'nombre' => $receta->nombre,
                        'descripcion' => $receta->descripcion,
                        'precio' => (float) $receta->precio_venta,
                        'imagen' => $this->resolverImagen($receta->imagen),
                        'disponible' => $evaluacion['disponible'],
                        'motivo' => $evaluacion['disponible'] ? null : 'Sin insumos',
                        'stock' => null,
                        'seleccionado' => in_array($itemKey, $selectedItems, true),
                    ]);
                }

                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->descripcion_categoria,
                    'items' => $items->values()->all(),
                ];
            })
            ->values();
    }

    private function evaluarDisponibilidadReceta(Receta $receta, bool $permitirStockNegativo): array
    {
        if ($permitirStockNegativo) {
            return ['disponible' => true, 'faltantes' => []];
        }

        $faltantes = [];

        foreach ($receta->ingredientes as $ingrediente) {
            $producto = $ingrediente->producto;

            if (!$producto) {
                $faltantes[] = 'Insumo no disponible';
                continue;
            }

            if (!in_array($producto->tipo, ['P', 'I'], true)) {
                continue;
            }

            $requerido = (float) $ingrediente->cantidad;
            $stockDisponible = (float) ($producto->stock ?? 0);

            if ($stockDisponible < $requerido) {
                $faltantes[] = $producto->descripcion ?: $producto->codigo ?: 'Insumo';
            }
        }

        return [
            'disponible' => empty($faltantes),
            'faltantes' => array_values(array_unique($faltantes)),
        ];
    }

    private function corporateDataMap(): array
    {
        return Cache::remember('corporate_data', 3600, fn () => CorporateData::pluck('description_item', 'item')->toArray());
    }

    private function resolverImagen(?string $ruta): string
    {
        $ruta = trim((string) $ruta);

        if ($ruta === '') {
            return asset('img/sin_imagen.jpg');
        }

        if (Str::startsWith($ruta, ['http://', 'https://'])) {
            return $ruta;
        }

        return asset(ltrim($ruta, '/'));
    }

    private function qrImageUrl(string $menuUrl): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=360x360&margin=10&data=' . urlencode($menuUrl);
    }

    private function qrDataUri(string $menuUrl): string
    {
        $response = Http::timeout(15)->get($this->qrImageUrl($menuUrl));

        if (!$response->successful()) {
            return $this->placeholderQrDataUri($menuUrl);
        }

        $mime = $response->header('Content-Type') ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($response->body());
    }

    private function placeholderQrDataUri(string $menuUrl): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="360" height="360" viewBox="0 0 360 360"><rect width="360" height="360" fill="#ffffff"/><rect x="20" y="20" width="320" height="320" fill="#f4f4f4" stroke="#222" stroke-width="4"/><text x="180" y="165" text-anchor="middle" font-family="Arial" font-size="18" fill="#333">QR no disponible</text><text x="180" y="195" text-anchor="middle" font-family="Arial" font-size="12" fill="#666">%s</text></svg>',
            e(substr($menuUrl, 0, 32))
        );

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}