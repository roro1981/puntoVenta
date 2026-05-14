<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CorporateData;
use App\Models\Globales;
use App\Models\MenuQrConfiguration;
use App\Models\Receta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuQrController extends Controller
{
    private const TOP_SELLERS_CATEGORY_ID = 'top-sellers';
    private const TOP_SELLERS_OFF_MARKER = 'top-sellers:off';
    private const MAX_MENU_QR_CONFIGS = 5;

    public function index(Request $request)
    {
        $configuracion = $this->obtenerOCrearConfiguracion($request->integer('config_id'));
        $menuConfiguraciones = $this->listarConfiguracionesMenuQr()->map(function (MenuQrConfiguration $menuConfiguracion, int $index) use ($configuracion) {
            $designTheme = $this->normalizarTemaQr($menuConfiguracion->design_theme);

            return [
                'id' => $menuConfiguracion->id,
                'nombre' => $this->nombreConfiguracionMenuQr($menuConfiguracion, $index + 1),
                'menu_url' => route('menu-qr.publico', ['token' => $menuConfiguracion->public_token]),
                'pdf_url' => route('menu-qr.pdf', ['token' => $menuConfiguracion->public_token]),
                'reload_url' => route('menu-qr.index', ['config_id' => $menuConfiguracion->id]),
                'qr_image_url' => $this->qrImageUrl(route('menu-qr.publico', ['token' => $menuConfiguracion->public_token])),
                'design_theme' => $designTheme,
                'design_tokens' => $this->tokensTemaQr($designTheme, (array) ($menuConfiguracion->design_tokens ?? [])),
                'design_options' => $this->sanitizarStyleOptionsQr((array) ($menuConfiguracion->design_options ?? [])),
                'selected_categories' => collect($menuConfiguracion->selected_categories ?? [])->map(fn ($value) => (string) $value)->all(),
                'selected_items' => collect($menuConfiguracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all(),
                'selected_categories_count' => collect($menuConfiguracion->selected_categories ?? [])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->count(),
                'selected_items_count' => collect($menuConfiguracion->selected_items ?? [])
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->count(),
                'activo' => $menuConfiguracion->id === $configuracion->id,
            ];
        })->values();
        $designThemes = $this->qrDesignThemes();
        $selectedDesignTheme = $this->normalizarTemaQr($configuracion->design_theme);
        $selectedDesignTokens = $this->sanitizarTokensQr((array) ($configuracion->design_tokens ?? []));
        $previewDesignTokens = $this->tokensTemaQr($selectedDesignTheme, $selectedDesignTokens);
        $styleOptionChoices = $this->qrStyleOptionChoices();
        $styleOptionDefaults = $this->qrStyleOptionDefaults();
        $selectedStyleOptions = $this->sanitizarStyleOptionsQr((array) ($configuracion->design_options ?? []));
        $corporateData = $this->corporateDataMap();
        $categorias = $this->construirCatalogoConfiguracion($configuracion);
        $menuUrl = route('menu-qr.publico', ['token' => $configuracion->public_token]);
        $qrImageUrl = $this->qrImageUrl($menuUrl);

        return view('configuration.menu_qr', [
            'configuracion' => $configuracion,
            'menuConfiguraciones' => $menuConfiguraciones,
            'menuConfiguracionesCount' => $menuConfiguraciones->count(),
            'maxMenuConfiguraciones' => self::MAX_MENU_QR_CONFIGS,
            'activeMenuId' => $configuracion->id,
            'currentMenuName' => $this->nombreConfiguracionMenuQr($configuracion, $menuConfiguraciones->search(fn ($menu) => $menu['id'] === $configuracion->id) + 1),
            'corporateData' => $corporateData,
            'categorias' => $categorias,
            'menuUrl' => $menuUrl,
            'qrImageUrl' => $qrImageUrl,
            'menuReloadUrl' => route('menu-qr.index', ['config_id' => $configuracion->id]),
            'designThemes' => $designThemes,
            'selectedDesignTheme' => $selectedDesignTheme,
            'designTokenMeta' => $this->qrTokenCustomizables(),
            'selectedDesignTokens' => $selectedDesignTokens,
            'previewDesignTokens' => $previewDesignTokens,
            'styleOptionChoices' => $styleOptionChoices,
            'styleOptionDefaults' => $styleOptionDefaults,
            'selectedStyleOptions' => $selectedStyleOptions,
            'selectedCategories' => collect($configuracion->selected_categories ?? [])->map(fn ($value) => (string) $value)->all(),
            'selectedItems' => collect($configuracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all(),
        ]);
    }

    public function guardar(Request $request)
    {
        $themeKeys = array_keys($this->qrDesignThemes());

        $validated = $request->validate([
            'config_id' => ['nullable', 'integer'],
            'create_new' => ['nullable', 'boolean'],
            'nombre' => ['nullable', 'string', 'max:120'],
            'selected_categories' => ['nullable', 'array'],
            'selected_categories.*' => ['string'],
            'selected_items' => ['nullable', 'array'],
            'selected_items.*' => ['string'],
            'design_theme' => ['nullable', 'string', Rule::in($themeKeys)],
            'design_tokens' => ['nullable', 'array'],
            'design_tokens.*' => ['nullable', 'string'],
            'design_options' => ['nullable', 'array'],
            'design_options.*' => ['nullable', 'string'],
        ]);

        $catalogo = $this->catalogoCategoriasMenu();
        $categoriasPermitidasNumericas = $catalogo->pluck('id')->map(fn ($value) => (int) $value)->all();
        $categoriasPermitidas = array_merge(
            array_map('strval', $categoriasPermitidasNumericas),
            [self::TOP_SELLERS_CATEGORY_ID]
        );
        $itemsPermitidos = [];
        foreach ($catalogo as $categoria) {
            foreach ($categoria->productos as $producto) {
                $itemsPermitidos[] = 'product:' . $producto->id;
            }
            foreach ($categoria->recetas as $receta) {
                $itemsPermitidos[] = 'recipe:' . $receta->id;
            }
        }

        $selectedCategoryTokens = array_values(array_filter(
            array_map('strval', $validated['selected_categories'] ?? []),
            fn ($id) => in_array($id, $categoriasPermitidas, true)
        ));

        $masVendidosActivo = in_array(self::TOP_SELLERS_CATEGORY_ID, $selectedCategoryTokens, true);

        $selectedCategories = array_values(array_filter(
            array_map('intval', $selectedCategoryTokens),
            fn ($id) => in_array($id, $categoriasPermitidasNumericas, true)
        ));

        $selectedCategories[] = $masVendidosActivo
            ? self::TOP_SELLERS_CATEGORY_ID
            : self::TOP_SELLERS_OFF_MARKER;

        $selectedItems = array_values(array_filter(
            array_map('strval', $validated['selected_items'] ?? []),
            fn ($item) => in_array($item, $itemsPermitidos, true)
        ));

        $nombre = trim((string) ($validated['nombre'] ?? ''));
        $configuracionId = $request->integer('config_id');
        $createNew = $request->boolean('create_new');

        $designTheme = $this->normalizarTemaQr($validated['design_theme'] ?? null);
        $designTokens = $this->sanitizarTokensQr((array) ($validated['design_tokens'] ?? []));
        $designOptions = $this->sanitizarStyleOptionsQr((array) ($validated['design_options'] ?? []));

        if ($createNew) {
            if ($this->listarConfiguracionesMenuQr()->count() >= self::MAX_MENU_QR_CONFIGS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya alcanzaste el máximo de 5 menús QR.',
                ], 422);
            }

            $configuracion = $this->crearConfiguracionMenuQr([
                'nombre' => $nombre !== '' ? $nombre : null,
                'selected_categories' => $selectedCategories,
                'selected_items' => $selectedItems,
                'design_theme' => $designTheme,
                'design_tokens' => $designTokens,
                'design_options' => $designOptions,
                'activo' => true,
            ]);
        } else {
            $configuracion = $this->obtenerOCrearConfiguracion($configuracionId);
            $configuracion->nombre = $nombre !== '' ? $nombre : $configuracion->nombre;
            $configuracion->selected_categories = $selectedCategories;
            $configuracion->selected_items = $selectedItems;
            $configuracion->design_theme = $designTheme;
            $configuracion->design_tokens = $designTokens;
            $configuracion->design_options = $designOptions;
            $configuracion->activo = true;
            $configuracion->save();
        }

        return response()->json([
            'success' => true,
            'message' => $createNew ? 'Menú QR duplicado correctamente.' : 'Configuración del menú QR guardada correctamente.',
            'menu_url' => route('menu-qr.publico', ['token' => $configuracion->public_token]),
            'reload_url' => route('menu-qr.index', ['config_id' => $configuracion->id]),
            'config_id' => $configuracion->id,
        ]);
    }

    public function eliminar(Request $request)
    {
        $validated = $request->validate([
            'config_id' => ['required', 'integer'],
        ]);

        $total = MenuQrConfiguration::query()->count();
        if ($total <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Debe existir al menos un menú QR configurado.',
            ], 422);
        }

        $configuracion = MenuQrConfiguration::query()->find($validated['config_id']);
        if (!$configuracion) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el menú QR a eliminar.',
            ], 404);
        }

        $configuracion->delete();

        $siguienteConfiguracion = MenuQrConfiguration::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();

        if (!$siguienteConfiguracion) {
            $siguienteConfiguracion = $this->crearConfiguracionMenuQr();
        }

        return response()->json([
            'success' => true,
            'message' => 'Menú QR eliminado correctamente.',
            'reload_url' => route('menu-qr.index', ['config_id' => $siguienteConfiguracion->id]),
            'config_id' => $siguienteConfiguracion->id,
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
        $designTheme = $this->normalizarTemaQr($configuracion->design_theme);
        $designTokens = $this->tokensTemaQr($designTheme, (array) ($configuracion->design_tokens ?? []));
        $designOptions = $this->sanitizarStyleOptionsQr((array) ($configuracion->design_options ?? []));
        $designVisualOptions = $this->mapStyleOptionsForPublic($designOptions);

        return view('public.menu_qr', [
            'configuracion' => $configuracion,
            'corporateData' => $corporateData,
            'categorias' => $categorias,
            'selectedDesignTheme' => $designTheme,
            'designTokens' => $designTokens,
            'designVisualOptions' => $designVisualOptions,
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

    private function obtenerOCrearConfiguracion(?int $configuracionId = null): MenuQrConfiguration
    {
        if ($configuracionId) {
            $configuracion = MenuQrConfiguration::query()->find($configuracionId);

            if ($configuracion) {
                return $configuracion;
            }
        }

        $configuracion = MenuQrConfiguration::query()->orderBy('created_at')->orderBy('id')->first();

        if ($configuracion) {
            return $configuracion;
        }

        return $this->crearConfiguracionMenuQr();
    }

    private function listarConfiguracionesMenuQr()
    {
        return MenuQrConfiguration::query()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    private function crearConfiguracionMenuQr(array $data = []): MenuQrConfiguration
    {
        [$selectedCategories, $selectedItems] = $this->seleccionPorDefectoMenuQr();

        return MenuQrConfiguration::create([
            'nombre' => trim((string) ($data['nombre'] ?? '')) !== ''
                ? trim((string) $data['nombre'])
                : $this->nombreMenuQrPorDefecto(MenuQrConfiguration::query()->count() + 1),
            'public_token' => (string) Str::uuid(),
            'selected_categories' => $data['selected_categories'] ?? $selectedCategories,
            'selected_items' => $data['selected_items'] ?? $selectedItems,
            'design_theme' => $this->normalizarTemaQr($data['design_theme'] ?? 'clasico_sobrio'),
            'design_tokens' => $this->sanitizarTokensQr((array) ($data['design_tokens'] ?? [])),
            'design_options' => $this->sanitizarStyleOptionsQr((array) ($data['design_options'] ?? $this->qrStyleOptionDefaults())),
            'activo' => (bool) ($data['activo'] ?? true),
        ]);
    }

    private function seleccionPorDefectoMenuQr(): array
    {
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

        $selectedCategories[] = self::TOP_SELLERS_CATEGORY_ID;

        return [$selectedCategories, $selectedItems];
    }

    private function nombreMenuQrPorDefecto(int $numero): string
    {
        return 'Menu QR ' . max(1, $numero);
    }

    private function nombreConfiguracionMenuQr(MenuQrConfiguration $configuracion, int $fallbackNumero = 1): string
    {
        $nombre = trim((string) ($configuracion->nombre ?? ''));

        return $nombre !== '' ? $nombre : $this->nombreMenuQrPorDefecto($fallbackNumero);
    }

    private function qrStyleOptionDefaults(): array
    {
        return [
            'font_title' => 'elegante_serif',
            'font_body' => 'limpia_humanista',
            'radius_scale' => 'md',
            'shadow_level' => 'soft',
            'animation_style' => 'stagger',
        ];
    }

    private function qrStyleOptionChoices(): array
    {
        return [
            'font_title' => [
                'label' => 'Tipografía títulos',
                'options' => [
                    'elegante_serif' => 'Elegante Serif',
                    'moderna_sans' => 'Moderna Sans',
                    'display_condensada' => 'Display Condensada',
                ],
            ],
            'font_body' => [
                'label' => 'Tipografía texto',
                'options' => [
                    'limpia_humanista' => 'Limpia Humanista',
                    'sans_neutra' => 'Sans Neutra',
                    'serif_clasica' => 'Serif Clasica',
                ],
            ],
            'radius_scale' => [
                'label' => 'Radio de bordes',
                'options' => [
                    'sm' => 'Suave',
                    'md' => 'Medio',
                    'lg' => 'Redondeado',
                ],
            ],
            'shadow_level' => [
                'label' => 'Sombras',
                'options' => [
                    'none' => 'Sin sombra',
                    'soft' => 'Sutil',
                    'strong' => 'Intensa',
                ],
            ],
            'animation_style' => [
                'label' => 'Animación',
                'options' => [
                    'none' => 'Sin animación',
                    'fade' => 'Desvanecer',
                    'stagger' => 'Entrada escalonada',
                ],
            ],
        ];
    }

    private function qrTokenCustomizables(): array
    {
        return [
            'bg' => 'Fondo general',
            'surface' => 'Tarjetas principales',
            'surface-soft' => 'Tarjetas secundarias',
            'border' => 'Bordes',
            'text' => 'Texto principal',
            'muted' => 'Texto secundario',
            'accent' => 'Color acento',
            'accent-soft' => 'Acento suave',
            'popular-start' => 'Popular (inicio)',
            'popular-end' => 'Popular (fin)',
        ];
    }

    private function qrDesignThemes(): array
    {
        return [
            'clasico_sobrio' => [
                'label' => 'Clasico sobrio',
                'tokens' => [
                    'bg' => '#f3f4f6',
                    'surface' => '#ffffff',
                    'surface-soft' => '#f8fafc',
                    'border' => '#e5e7eb',
                    'text' => '#111827',
                    'muted' => '#6b7280',
                    'accent' => '#334155',
                    'accent-soft' => '#e2e8f0',
                    'ok-bg' => '#ecfdf5',
                    'ok-text' => '#166534',
                    'off-bg' => '#fef2f2',
                    'off-text' => '#991b1b',
                    'popular-start' => '#ea580c',
                    'popular-end' => '#dc2626',
                ],
            ],
            'cafe_calido' => [
                'label' => 'Cafe calido',
                'tokens' => [
                    'bg' => '#f5efe7',
                    'surface' => '#fffdf8',
                    'surface-soft' => '#f8f3ea',
                    'border' => '#e6d8c7',
                    'text' => '#2f261f',
                    'muted' => '#6d5e4f',
                    'accent' => '#6f4e37',
                    'accent-soft' => '#eadccf',
                    'ok-bg' => '#edf8ef',
                    'ok-text' => '#2f6d41',
                    'off-bg' => '#fbeaea',
                    'off-text' => '#9b3c3c',
                    'popular-start' => '#d97706',
                    'popular-end' => '#b45309',
                ],
            ],
            'oceano_limpio' => [
                'label' => 'Oceano limpio',
                'tokens' => [
                    'bg' => '#eef6fb',
                    'surface' => '#ffffff',
                    'surface-soft' => '#f3f9fd',
                    'border' => '#d6e6f2',
                    'text' => '#0f2233',
                    'muted' => '#4f6678',
                    'accent' => '#1d4f73',
                    'accent-soft' => '#d9eaf6',
                    'ok-bg' => '#e8f7ef',
                    'ok-text' => '#1f6b49',
                    'off-bg' => '#fdecec',
                    'off-text' => '#a23d3d',
                    'popular-start' => '#0e7490',
                    'popular-end' => '#0f766e',
                ],
            ],
        ];
    }

    private function normalizarTemaQr(?string $theme): string
    {
        $theme = trim((string) $theme);
        $themes = $this->qrDesignThemes();

        if ($theme !== '' && array_key_exists($theme, $themes)) {
            return $theme;
        }

        return 'clasico_sobrio';
    }

    private function tokensTemaQr(string $theme, array $customTokens = []): array
    {
        $themes = $this->qrDesignThemes();
        $theme = $this->normalizarTemaQr($theme);
        $customTokens = $this->sanitizarTokensQr($customTokens);

        return array_replace(
            $themes[$theme]['tokens'] ?? $themes['clasico_sobrio']['tokens'],
            $customTokens
        );
    }

    private function sanitizarStyleOptionsQr(array $options): array
    {
        $defaults = $this->qrStyleOptionDefaults();
        $choices = $this->qrStyleOptionChoices();
        $result = $defaults;

        foreach ($choices as $key => $data) {
            $value = trim((string) ($options[$key] ?? ''));
            if ($value !== '' && array_key_exists($value, $data['options'])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function mapStyleOptionsForPublic(array $options): array
    {
        $options = $this->sanitizarStyleOptionsQr($options);

        $titleFonts = [
            'elegante_serif' => 'Georgia, "Times New Roman", Times, serif',
            'moderna_sans' => '"Trebuchet MS", "Segoe UI", sans-serif',
            'display_condensada' => '"Arial Narrow", "Franklin Gothic Medium", Arial, sans-serif',
        ];

        $bodyFonts = [
            'limpia_humanista' => '"Segoe UI", "Helvetica Neue", Arial, sans-serif',
            'sans_neutra' => 'Verdana, "Trebuchet MS", sans-serif',
            'serif_clasica' => 'Cambria, Georgia, serif',
        ];

        $radius = [
            'sm' => '10px',
            'md' => '14px',
            'lg' => '18px',
        ];

        $shadows = [
            'none' => 'none',
            'soft' => '0 10px 24px rgba(15, 23, 42, 0.08)',
            'strong' => '0 16px 32px rgba(15, 23, 42, 0.16)',
        ];

        return [
            'font_title_family' => $titleFonts[$options['font_title']] ?? $titleFonts['elegante_serif'],
            'font_body_family' => $bodyFonts[$options['font_body']] ?? $bodyFonts['limpia_humanista'],
            'radius_size' => $radius[$options['radius_scale']] ?? $radius['md'],
            'shadow_level' => $shadows[$options['shadow_level']] ?? $shadows['soft'],
            'animation_style' => $options['animation_style'] ?? 'stagger',
        ];
    }

    private function sanitizarTokensQr(array $tokens): array
    {
        $permitidos = array_keys($this->qrTokenCustomizables());
        $sanitizados = [];

        foreach ($permitidos as $key) {
            if (!array_key_exists($key, $tokens)) {
                continue;
            }

            $value = strtoupper(trim((string) $tokens[$key]));
            if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
                $sanitizados[$key] = $value;
            }
        }

        return $sanitizados;
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
        $selectedCategoryTokens = collect($configuracion->selected_categories ?? [])->map(fn ($value) => (string) $value)->all();
        $selectedCategories = collect($selectedCategoryTokens)
            ->filter(fn ($value) => ctype_digit((string) $value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();
        $mostrarMasVendidos = $this->topSellersEnabledFromTokens($selectedCategoryTokens);
        $selectedItems = collect($configuracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all();
        $stockNegativo = Cache::remember('global_STOCK_NEGATIVO', 300, fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var'));
        $permitirStockNegativo = ($stockNegativo == '1');
        $selectedProductUuids = $this->obtenerProductUuidsSeleccionados($selectedItems);
        $topVendidos = $this->obtenerTopVendidosUltimoMesCerrado($selectedProductUuids, 10);
        $popularUuids = array_flip(array_keys($topVendidos));

        $categorias = Categoria::query()
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
            ->map(function ($categoria) use ($selectedItems, $permitirStockNegativo, $popularUuids) {
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
                        'descripcion' => in_array($producto->tipo, ['P', 'S'], true) ? $producto->descrip_detallada : null,
                        'precio' => (float) $producto->precio_venta,
                        'imagen' => $this->resolverImagen($producto->imagen),
                        'disponible' => $disponible,
                        'motivo' => $disponible ? null : 'Sin stock',
                        'stock' => $stock,
                        'tipo_producto' => $producto->tipo,
                        'popular' => isset($popularUuids[$producto->uuid]),
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
                        'popular' => false,
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
                    'nombre' => $this->formatearNombreCategoria($categoria->descripcion_categoria),
                    'items' => $itemsOrdenados,
                ];
            })
            ->filter(fn ($categoria) => !empty($categoria['items']))
            ->values();

        $itemsPorUuid = [];
        foreach ($categorias as $categoria) {
            foreach ($categoria['items'] as $item) {
                if (($item['tipo'] ?? null) !== 'product') {
                    continue;
                }

                $uuid = (string) ($item['uuid'] ?? '');
                if ($uuid !== '' && !isset($itemsPorUuid[$uuid])) {
                    $itemsPorUuid[$uuid] = $item;
                }
            }
        }

        $topItems = [];
        foreach (array_keys($topVendidos) as $uuid) {
            if (isset($itemsPorUuid[$uuid])) {
                $topItems[] = $itemsPorUuid[$uuid];
            }
        }

        if ($mostrarMasVendidos && !empty($topItems)) {
            $categorias->prepend([
                'id' => self::TOP_SELLERS_CATEGORY_ID,
                'nombre' => 'Mas vendidos',
                'items' => array_values($topItems),
            ]);
        }

        return $categorias->values();
    }

    private function obtenerProductUuidsSeleccionados(array $selectedItems): array
    {
        return collect($selectedItems)
            ->filter(fn ($item) => Str::startsWith((string) $item, 'product:'))
            ->map(fn ($item) => (int) str_replace('product:', '', (string) $item))
            ->filter(fn ($id) => $id > 0)
            ->pipe(function ($productIds) {
                if ($productIds->isEmpty()) {
                    return [];
                }

                return DB::table('productos')
                    ->whereIn('id', $productIds->all())
                    ->pluck('uuid')
                    ->filter(fn ($uuid) => !empty($uuid))
                    ->values()
                    ->all();
            });
    }

    private function obtenerTopVendidosUltimoMesCerrado(array $productUuids, int $limit = 10): array
    {
        if (empty($productUuids) || $limit <= 0) {
            return [];
        }

        $fin = Carbon::now()->endOfDay();
        $inicio = Carbon::now()->subDays(29)->startOfDay();

        return DB::table('detalles_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->whereIn('dv.producto_uuid', $productUuids)
            ->whereNull('dv.promo_id')
            ->where(function ($query) {
                $query->whereNull('dv.anulado')
                    ->orWhere('dv.anulado', false)
                    ->orWhere('dv.anulado', 0);
            })
            ->whereBetween('v.fecha_venta', [$inicio, $fin])
            ->whereRaw('LOWER(COALESCE(v.estado, ?)) <> ?', ['completada', 'anulada'])
            ->groupBy('dv.producto_uuid')
            ->select('dv.producto_uuid', DB::raw('SUM(dv.cantidad) as total_vendido'))
            ->orderByDesc('total_vendido')
            ->limit($limit)
            ->pluck('total_vendido', 'producto_uuid')
            ->toArray();
    }

    private function construirCatalogoConfiguracion(MenuQrConfiguration $configuracion)
    {
        $selectedItems = collect($configuracion->selected_items ?? [])->map(fn ($value) => (string) $value)->all();
        $stockNegativo = Cache::remember('global_STOCK_NEGATIVO', 300, fn () => Globales::where('nom_var', 'STOCK_NEGATIVO')->value('valor_var'));
        $permitirStockNegativo = ($stockNegativo == '1');

        $categorias = $this->catalogoCategoriasMenu()
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
                        'descripcion' => in_array($producto->tipo, ['P', 'S'], true) ? $producto->descrip_detallada : null,
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
                    'nombre' => $this->formatearNombreCategoria($categoria->descripcion_categoria),
                    'items' => $items->values()->all(),
                ];
            })
            ->values();

        $selectedProductUuids = $this->obtenerProductUuidsSeleccionados($selectedItems);
        $topVendidos = $this->obtenerTopVendidosUltimoMesCerrado($selectedProductUuids, 10);

        $itemsPorUuid = [];
        foreach ($categorias as $categoria) {
            foreach (($categoria['items'] ?? []) as $item) {
                if (($item['tipo'] ?? null) !== 'product') {
                    continue;
                }

                if (!($item['seleccionado'] ?? false)) {
                    continue;
                }

                $uuid = (string) ($item['uuid'] ?? '');
                if ($uuid !== '' && !isset($itemsPorUuid[$uuid])) {
                    $itemsPorUuid[$uuid] = $item;
                }
            }
        }

        $topItems = [];
        foreach (array_keys($topVendidos) as $uuid) {
            if (isset($itemsPorUuid[$uuid])) {
                $topItems[] = $itemsPorUuid[$uuid];
            }
        }

        $categorias->prepend([
            'id' => self::TOP_SELLERS_CATEGORY_ID,
            'nombre' => 'Mas vendidos',
            'items' => array_values($topItems),
        ]);

        return $categorias->values();
    }

    private function topSellersEnabledFromTokens(array $selectedCategoryTokens): bool
    {
        if (in_array(self::TOP_SELLERS_OFF_MARKER, $selectedCategoryTokens, true)) {
            return false;
        }

        if (in_array(self::TOP_SELLERS_CATEGORY_ID, $selectedCategoryTokens, true)) {
            return true;
        }

        return true;
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

    private function formatearNombreCategoria(?string $nombre): string
    {
        $nombre = trim(preg_replace('/\s+/', ' ', (string) $nombre));

        if ($nombre === '') {
            return '';
        }

        $stopWords = ['y', 'e', 'de', 'del', 'la', 'las', 'el', 'los', 'o', 'u', 'en', 'con', 'por', 'para', 'al', 'a', 'un', 'una', 'unos', 'unas'];
        $palabras = preg_split('/\s+/', mb_strtolower($nombre)) ?: [];
        $formateadas = [];

        foreach ($palabras as $index => $palabra) {
            if ($index > 0 && in_array($palabra, $stopWords, true)) {
                $formateadas[] = $palabra;
                continue;
            }

            $palabra = mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');

            $formateadas[] = $palabra;
        }

        return implode(' ', $formateadas);
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