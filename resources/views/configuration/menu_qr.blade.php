<style>
    .menu-qr-page {
        --bg: #f6f3ed;
        --panel: #ffffff;
        --panel-soft: #fbfaf7;
        --border: #e6ded1;
        --text: #2d2a26;
        --muted: #736a5d;
        --accent: #b45f2d;
        --accent-2: #5f6f52;
        --danger: #a63d40;
        background: linear-gradient(180deg, #fcfaf6 0%, #f6f3ed 100%);
        color: var(--text);
        border-radius: 18px;
        padding: 18px;
    }
    .menu-qr-hero {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 16px;
        align-items: stretch;
        margin-bottom: 16px;
    }
    .menu-qr-card {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(60, 47, 35, .07);
        overflow: hidden;
    }
    .menu-qr-card .card-head {
        padding: 16px 18px 0;
    }
    .menu-qr-card .card-body {
        padding: 16px 18px 18px;
    }
    .menu-qr-brand {
        display: flex;
        gap: 14px;
        align-items: center;
    }
    .menu-qr-logo {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 18px;
        border: 1px solid var(--border);
        background: #fff;
        flex: 0 0 auto;
    }
    .menu-qr-title {
        margin: 0;
        font-size: 26px;
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .menu-qr-subtitle {
        margin: 4px 0 0;
        color: var(--muted);
    }
    .menu-qr-linkbox {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 14px;
    }
    .menu-qr-linkbox input {
        flex: 1;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: #fff;
        padding: 10px 12px;
    }
    .menu-qr-linkbox button,
    .menu-qr-actions button {
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 700;
    }
    .menu-qr-linkbox button {
        background: var(--accent);
        color: white;
    }
    .menu-qr-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .menu-qr-actions .btn-save {
        background: var(--accent-2);
        color: #fff;
    }
    .menu-qr-actions .btn-pdf {
        background: #1f4e5f;
        color: #fff;
    }
    .menu-qr-actions .copies-input {
        width: 110px;
        border-radius: 12px;
        border: 1px solid var(--border);
        padding: 10px 12px;
    }
    .menu-qr-qrbox {
        display: grid;
        grid-template-columns: 180px 1fr;
        gap: 16px;
        align-items: center;
    }
    .menu-qr-qrbox img {
        width: 180px;
        height: 180px;
        border-radius: 16px;
        background: #fff;
        border: 1px solid var(--border);
        padding: 10px;
    }
    .menu-qr-helper {
        color: var(--muted);
        line-height: 1.5;
    }
    .menu-qr-grid {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 16px;
    }
    .menu-qr-sidebar,
    .menu-qr-main {
        min-width: 0;
    }
    .menu-qr-sidebar .section-title,
    .menu-qr-main .section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
        font-weight: 800;
        font-size: 16px;
    }
    .menu-qr-category {
        margin-bottom: 12px;
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        background: var(--panel-soft);
    }
    .menu-qr-category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: #fff;
        border-bottom: 1px solid var(--border);
    }
    .menu-qr-category-header strong {
        font-size: 15px;
    }
    .menu-qr-category-header .meta {
        color: var(--muted);
        font-size: 12px;
    }
    .menu-qr-items {
        padding: 12px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
    }
    .menu-qr-item {
        border: 1px solid var(--border);
        background: #fff;
        border-radius: 16px;
        padding: 12px;
        position: relative;
    }
    .menu-qr-item .topline {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }
    .menu-qr-item img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid var(--border);
        background: #fff;
        flex: 0 0 auto;
    }
    .menu-qr-item .name {
        font-weight: 800;
        margin: 0 0 4px;
        font-size: 15px;
    }
    .menu-qr-item .desc {
        margin: 4px 0 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.4;
    }
    .menu-qr-item .price {
        font-size: 17px;
        font-weight: 900;
        color: var(--accent);
    }
    .menu-qr-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 8px;
    }
    .menu-qr-badge.ok { background: #ecf7ee; color: #2f6d41; }
    .menu-qr-badge.off { background: #fdeeee; color: #a63d40; }
    .menu-qr-category.is-hidden .menu-qr-items { display: none; }
    .menu-qr-item.is-disabled {
        opacity: .55;
        filter: grayscale(0.15);
    }
    .menu-qr-item .checkbox-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 8px;
    }
    .menu-qr-item .checkbox-wrap input { transform: scale(1.15); }
    @media (max-width: 1100px) {
        .menu-qr-hero,
        .menu-qr-grid { grid-template-columns: 1fr; }
    }
</style>

<script type="text/javascript" src="/js/configuracion/menu_qr.js"></script>

<div class="menu-qr-page">
    <input type="hidden" id="token" value="{{ csrf_token() }}">
    <input type="hidden" id="menu_qr_save_url" value="{{ route('menu-qr.guardar') }}">
    <input type="hidden" id="menu_qr_pdf_url" value="{{ route('menu-qr.pdf', ['token' => $configuracion->public_token]) }}">
    <input type="hidden" id="menu_qr_public_url" value="{{ $menuUrl }}">
    <input type="hidden" id="menu_qr_reload_url" value="{{ route('menu-qr.index') }}">

    <div class="menu-qr-hero">
        <div class="menu-qr-card">
            <div class="card-head">
                <div class="menu-qr-brand">
                    <img class="menu-qr-logo" src="{{ !empty($corporateData['logo_enterprise']) ? asset($corporateData['logo_enterprise']) : asset('img/sin_imagen.jpg') }}" alt="Logo empresa">
                    <div>
                        <h2 class="menu-qr-title">Configurar Menú QR</h2>
                        <p class="menu-qr-subtitle">Selecciona categorías y productos para el menú público.</p>
                    </div>
                </div>
                <div class="menu-qr-linkbox">
                    <input type="text" id="menu_qr_link" value="{{ $menuUrl }}" readonly>
                    <button type="button" id="btnCopyMenuQrLink">Copiar link</button>
                </div>
                <div class="menu-qr-actions">
                    <input type="number" class="copies-input" id="menu_qr_copias" min="1" max="50" value="1">
                    <button type="button" class="btn-pdf" id="btnPrintMenuQrPdf">Imprimir QR en PDF</button>
                    <button type="button" class="btn-save" id="btnSaveMenuQr">Guardar configuración</button>
                </div>
            </div>
            <div class="card-body">
                <div class="menu-qr-helper">
                    <div><strong>Empresa:</strong> {{ $corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Sin datos') }}</div>
                    <div><strong>Dirección:</strong> {{ $corporateData['address_enterprise'] ?? 'Sin datos' }}</div>
                    <div><strong>Comuna:</strong> {{ $corporateData['comuna_enterprise'] ?? 'Sin datos' }}</div>
                    <div><strong>Teléfono:</strong> {{ $corporateData['phone_enterprise'] ?? 'Sin datos' }}</div>
                </div>
            </div>
        </div>

        <div class="menu-qr-card">
            <div class="card-head">
                <div class="menu-qr-qrbox">
                        <img src="{{ $qrDataUri }}" alt="QR del menú">
                    <div>
                        <h3 style="margin:0 0 10px;font-size:20px;font-weight:800;">Enlace directo</h3>
                        <div class="menu-qr-helper">Este enlace abre la vista pública del menú QR. El código se actualiza automáticamente al guardar cambios.</div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="menu-qr-helper">
                    <strong>Notas operativas:</strong>
                    <div>Si un producto o receta se elimina o queda inactivo, desaparecerá del menú público.</div>
                    <div>Los productos tipo <strong>PRODUCTO</strong> se marcan como no disponibles si el stock llega a cero.</div>
                    <div>Los productos tipo <strong>NO AFECTO A STOCK</strong> permanecen disponibles sin control de stock.</div>
                    <div>Las recetas se validan por sus ingredientes activos y con stock suficiente.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="menu-qr-grid">
        <div class="menu-qr-sidebar">
            <div class="section-title">
                <span>Categorías visibles</span>
                <span style="color:var(--muted);font-size:12px;">{{ count($categorias) }} categorías</span>
            </div>
            @forelse($categorias as $categoria)
                @php
                    $categoriaId = (string) $categoria['id'];
                    $categoriaChecked = in_array($categoriaId, $selectedCategories, true);
                    $totalItems = count($categoria['items'] ?? []);
                @endphp
                <div class="menu-qr-category {{ $categoriaChecked ? '' : 'is-hidden' }}" data-category-id="{{ $categoriaId }}">
                    <div class="menu-qr-category-header">
                        <label style="display:flex;align-items:center;gap:10px;margin:0;cursor:pointer;">
                            <input type="checkbox" class="menu-qr-category-check" data-category-id="{{ $categoriaId }}" {{ $categoriaChecked ? 'checked' : '' }}>
                            <strong>{{ $categoria['nombre'] }}</strong>
                        </label>
                        <span class="meta">{{ $totalItems }} ítems</span>
                    </div>
                    <div class="menu-qr-items">
                        @foreach($categoria['items'] as $item)
                            @php
                                $itemKey = $item['tipo'] . ':' . $item['id'];
                                $itemChecked = in_array($itemKey, $selectedItems, true);
                            @endphp
                            <label class="menu-qr-item {{ $itemChecked ? '' : 'is-disabled' }}" data-category-id="{{ $categoriaId }}" data-item-key="{{ $itemKey }}">
                                <div class="checkbox-wrap">
                                    <input type="checkbox" class="menu-qr-item-check" data-category-id="{{ $categoriaId }}" value="{{ $itemKey }}" {{ $itemChecked ? 'checked' : '' }}>
                                </div>
                                <div class="topline">
                                    <div style="min-width:0;flex:1;">
                                        <p class="name">{{ $item['nombre'] }}</p>
                                        @if($item['tipo'] === 'recipe' && !empty($item['descripcion']))
                                            <p class="desc">{{ $item['descripcion'] }}</p>
                                        @endif
                                        <div class="price">${{ number_format($item['precio'], 0, ',', '.') }}</div>
                                        <span class="menu-qr-badge {{ $item['disponible'] ? 'ok' : 'off' }}">
                                            {{ $item['disponible'] ? 'Disponible' : 'No disponible' }}
                                        </span>
                                    </div>
                                    <img src="{{ $item['imagen'] }}" alt="{{ $item['nombre'] }}">
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="menu-qr-card">
                    <div class="card-body">
                        No hay categorías activas disponibles para configurar.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="menu-qr-main">
            <div class="section-title">
                <span>Vista previa lógica</span>
                <span style="color:var(--muted);font-size:12px;">El menú público usa esta selección y valida stock en tiempo real</span>
            </div>
            <div class="menu-qr-card">
                <div class="card-body">
                    <div class="menu-qr-helper">
                        <strong>Comportamiento esperado:</strong>
                        <ul style="margin:10px 0 0 18px;padding:0;line-height:1.8;">
                            <li>Selecciona una categoría para mostrar sus ítems en el menú.</li>
                            <li>Los productos tipo P se ocultan como no disponibles cuando no tienen stock.</li>
                            <li>Las recetas se revisan por sus ingredientes activos y stock disponible.</li>
                            <li>Los artículos eliminados o inactivos desaparecen automáticamente.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.modal_ayuda', ['modulo' => 'config_menu_qr'])