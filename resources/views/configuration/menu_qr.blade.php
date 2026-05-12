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
        min-width: 0;
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
        box-sizing: border-box;
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
    .menu-qr-company-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 14px;
    }
    @media (max-width: 768px) {
        .menu-qr-company-grid {
            grid-template-columns: 1fr;
        }
    }
    .menu-qr-theme-control {
        margin-top: 14px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }
    .menu-qr-theme-control label {
        display: block;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text);
    }
    .menu-qr-theme-control select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 9px 10px;
        background: #fff;
        color: var(--text);
    }
    .menu-qr-theme-control .hint {
        margin-top: 8px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
    }
    .menu-qr-theme-panel {
        margin-top: 10px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }
    .menu-qr-theme-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid var(--border);
        background: #fff;
    }
    .menu-qr-theme-panel-header strong {
        font-size: 13px;
        line-height: 1.2;
    }
    .menu-qr-theme-panel-body {
        padding: 10px;
    }
    .menu-qr-theme-panel.is-hidden .menu-qr-theme-panel-body {
        display: none;
    }
    .menu-qr-theme-panel.is-hidden .menu-qr-theme-toggle {
        transform: rotate(-90deg);
    }
    .menu-qr-theme-toggle {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--muted);
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .2s ease, color .2s ease, border-color .2s ease;
        font-size: 14px;
        line-height: 1;
    }
    .menu-qr-theme-toggle:hover {
        border-color: #ccbfae;
        color: #4f463c;
    }
    .menu-qr-theme-editor {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 9px;
    }
    .menu-qr-theme-field {
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        padding: 8px;
    }
    .menu-qr-theme-field label {
        display: block;
        margin: 0 0 6px;
        font-size: 12px;
        line-height: 1.3;
        color: var(--muted);
        font-weight: 700;
    }
    .menu-qr-theme-field input[type="color"] {
        width: 100%;
        height: 36px;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 2px;
        background: #fff;
        cursor: pointer;
    }
    .menu-qr-theme-tools {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .menu-qr-theme-reset {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--text);
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 700;
        cursor: pointer;
    }
    .menu-qr-theme-contrast {
        margin-top: 8px;
        font-size: 12px;
        font-weight: 700;
    }
    .menu-qr-theme-contrast.ok {
        color: #2f6d41;
    }
    .menu-qr-theme-contrast.warn {
        color: #a63d40;
    }
    .menu-qr-theme-preview {
        margin-top: 10px;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 10px;
        background: #fff;
    }
    .menu-qr-theme-preview-box {
        border-radius: var(--preview-radius, 12px);
        border: 1px solid var(--preview-border, #e5e7eb);
        background: linear-gradient(180deg, #f8fafc 0%, var(--preview-bg, #f3f4f6) 100%);
        padding: 10px;
        box-shadow: var(--preview-shadow, 0 8px 18px rgba(15, 23, 42, 0.10));
    }
    .menu-qr-theme-preview-card {
        border-radius: var(--preview-radius, 10px);
        border: 1px solid var(--preview-border, #e5e7eb);
        background: var(--preview-surface, #ffffff);
        color: var(--preview-text, #111827);
        padding: 10px;
        box-shadow: var(--preview-shadow, 0 8px 18px rgba(15, 23, 42, 0.10));
        font-family: var(--preview-body-font, "Segoe UI", "Helvetica Neue", Arial, sans-serif);
    }
    .menu-qr-theme-preview-title {
        font-size: 14px;
        font-weight: 800;
        margin: 0;
        font-family: var(--preview-title-font, Georgia, "Times New Roman", Times, serif);
    }
    .menu-qr-theme-preview-sub {
        margin: 4px 0 0;
        color: var(--preview-muted, #6b7280);
        font-size: 12px;
    }
    .menu-qr-theme-preview-accent {
        display: inline-flex;
        margin-top: 8px;
        border-radius: 999px;
        background: var(--preview-accent, #334155);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 8px;
    }
    .menu-qr-theme-preview-popular {
        display: inline-flex;
        margin-top: 8px;
        margin-left: 6px;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--preview-popular-start, #ea580c) 0%, var(--preview-popular-end, #dc2626) 100%);
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 8px;
    }
    .menu-qr-anim-preview {
        margin-top: 10px;
        border: 1px dashed var(--border);
        border-radius: 10px;
        padding: 8px;
        background: #fff;
    }
    .menu-qr-anim-preview-title {
        margin: 0 0 6px;
        font-size: 12px;
        font-weight: 800;
        color: var(--text);
    }
    .menu-qr-anim-preview-list {
        display: grid;
        gap: 6px;
    }
    .menu-qr-anim-preview-item {
        border: 1px solid var(--preview-border, #e5e7eb);
        border-radius: var(--preview-radius, 10px);
        background: var(--preview-surface, #ffffff);
        color: var(--preview-text, #111827);
        padding: 7px 8px;
        font-size: 12px;
        font-family: var(--preview-body-font, "Segoe UI", "Helvetica Neue", Arial, sans-serif);
    }
    .menu-qr-anim-preview-item .head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .menu-qr-anim-preview-item .name {
        margin: 0;
        font-size: 12px;
        font-weight: 800;
        font-family: var(--preview-title-font, Georgia, "Times New Roman", Times, serif);
        color: var(--preview-text, #111827);
    }
    .menu-qr-anim-preview-item .desc {
        margin: 4px 0 0;
        color: var(--preview-muted, #6b7280);
        font-size: 11px;
        line-height: 1.3;
    }
    .menu-qr-anim-preview-item .meta {
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .menu-qr-anim-preview-item .price {
        font-size: 12px;
        font-weight: 900;
        color: var(--preview-accent, #334155);
    }
    .menu-qr-anim-preview-item .status {
        border-radius: 999px;
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 800;
        color: #fff;
        background: var(--preview-accent, #334155);
    }
    .menu-qr-anim-preview-item .popular {
        border-radius: 999px;
        padding: 2px 7px;
        font-size: 9px;
        font-weight: 900;
        color: #fff;
        background: linear-gradient(135deg, var(--preview-popular-start, #ea580c) 0%, var(--preview-popular-end, #dc2626) 100%);
    }
    .menu-qr-anim-preview-item.anim-fade {
        animation: menuQrAnimFade .34s ease both;
    }
    .menu-qr-anim-preview-item.anim-stagger {
        animation: menuQrAnimStagger .42s ease both;
    }
    @keyframes menuQrAnimFade {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes menuQrAnimStagger {
        from { opacity: 0; transform: translateY(10px) scale(.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
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
    .menu-qr-category-header .header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .menu-qr-toggle {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--muted);
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .2s ease, color .2s ease, border-color .2s ease;
        font-size: 14px;
        line-height: 1;
    }
    .menu-qr-toggle:hover {
        border-color: #ccbfae;
        color: #4f463c;
    }
    .menu-qr-category.is-hidden .menu-qr-toggle {
        transform: rotate(-90deg);
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

        .menu-qr-grid {
            gap: 14px;
        }

        .menu-qr-sidebar,
        .menu-qr-main {
            width: 100%;
        }
    }

    @media (max-width: 900px) {
        .menu-qr-page {
            padding: 14px;
            border-radius: 14px;
        }

        .menu-qr-title {
            font-size: 23px;
        }

        .menu-qr-linkbox {
            flex-direction: column;
            align-items: stretch;
        }

        .menu-qr-linkbox button {
            width: 100%;
        }

        .menu-qr-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .menu-qr-actions .copies-input,
        .menu-qr-actions .btn-pdf,
        .menu-qr-actions .btn-save {
            width: 100%;
        }

        .menu-qr-qrbox {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .menu-qr-qrbox img {
            margin: 0 auto;
        }

        .menu-qr-items {
            grid-template-columns: 1fr;
        }

        .menu-qr-sidebar .section-title {
            position: sticky;
            top: 8px;
            z-index: 30;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 10px;
            margin-bottom: 10px;
            box-shadow: 0 6px 16px rgba(60, 47, 35, .08);
        }
    }

    @media (max-width: 768px) {
        .menu-qr-page {
            padding: 10px;
            border-radius: 12px;
        }

        .menu-qr-card {
            border-radius: 14px;
        }

        .menu-qr-card .card-head,
        .menu-qr-card .card-body {
            padding-left: 12px;
            padding-right: 12px;
        }

        .menu-qr-card .card-head {
            padding-top: 12px;
        }

        .menu-qr-card .card-body {
            padding-bottom: 12px;
        }

        .menu-qr-brand {
            gap: 10px;
            align-items: flex-start;
        }

        .menu-qr-logo {
            width: 56px;
            height: 56px;
            border-radius: 12px;
        }

        .menu-qr-title {
            font-size: 20px;
            line-height: 1.15;
        }

        .menu-qr-subtitle {
            font-size: 13px;
        }

        .menu-qr-actions {
            grid-template-columns: 1fr;
        }

        .menu-qr-qrbox img {
            width: 150px;
            height: 150px;
            padding: 8px;
        }

        .menu-qr-category-header {
            padding: 10px 12px;
            gap: 8px;
        }

        .menu-qr-category-header strong {
            font-size: 14px;
            line-height: 1.2;
        }

        .menu-qr-category-header .meta {
            font-size: 11px;
        }

        .menu-qr-toggle {
            width: 26px;
            height: 26px;
            border-radius: 7px;
        }

        .menu-qr-items {
            padding: 10px;
            gap: 10px;
        }

        .menu-qr-item {
            border-radius: 12px;
            padding: 10px;
        }

        .menu-qr-item .topline {
            gap: 8px;
        }

        .menu-qr-item img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
        }

        .menu-qr-item .name {
            font-size: 14px;
        }

        .menu-qr-item .desc {
            font-size: 12px;
        }

        .menu-qr-item .price {
            font-size: 16px;
        }

        .menu-qr-badge {
            font-size: 11px;
            padding: 5px 8px;
        }
    }

    @media (max-width: 480px) {
        .menu-qr-page {
            padding: 8px;
        }

        .menu-qr-title {
            font-size: 18px;
        }

        .menu-qr-subtitle {
            font-size: 12px;
        }

        .menu-qr-linkbox input,
        .menu-qr-linkbox button,
        .menu-qr-actions .copies-input,
        .menu-qr-actions .btn-pdf,
        .menu-qr-actions .btn-save {
            font-size: 13px;
            padding: 9px 10px;
        }

        .menu-qr-qrbox img {
            width: 136px;
            height: 136px;
        }

        .menu-qr-item .topline {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .menu-qr-item img {
            width: 100%;
            height: 110px;
            object-fit: cover;
        }

        .menu-qr-item .checkbox-wrap input {
            transform: scale(1.05);
        }
    }
</style>

<script type="text/javascript" src="/js/configuracion/menu_qr.js"></script>

<div class="menu-qr-page">
    <input type="hidden" id="token" value="{{ csrf_token() }}">
    <input type="hidden" id="menu_qr_save_url" value="{{ route('menu-qr.guardar') }}">
    <input type="hidden" id="menu_qr_pdf_url" value="{{ route('menu-qr.pdf', ['token' => $configuracion->public_token]) }}">
    <input type="hidden" id="menu_qr_public_url" value="{{ $menuUrl }}">
    <input type="hidden" id="menu_qr_reload_url" value="{{ route('menu-qr.index') }}">
    <script type="application/json" id="menu_qr_themes_json">{!! json_encode(collect($designThemes)->mapWithKeys(fn ($themeData, $themeKey) => [$themeKey => $themeData['tokens']])->all(), JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/json" id="menu_qr_style_defaults_json">{!! json_encode($styleOptionDefaults, JSON_UNESCAPED_UNICODE) !!}</script>

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
                <div class="menu-qr-helper menu-qr-company-grid" style="margin-top:10px;">
                    <div><strong>Empresa:</strong> {{ $corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Sin datos') }}</div>
                    <div><strong>Dirección:</strong> {{ $corporateData['address_enterprise'] ?? 'Sin datos' }}</div>
                    <div><strong>Comuna:</strong> {{ $corporateData['comuna_enterprise'] ?? 'Sin datos' }}</div>
                    <div><strong>Teléfono:</strong> {{ $corporateData['phone_enterprise'] ?? 'Sin datos' }}</div>
                </div>
                <div class="menu-qr-theme-control">
                    <label for="menu_qr_design_theme">Preset visual del menu publico</label>
                    <select id="menu_qr_design_theme">
                        @foreach($designThemes as $themeKey => $themeData)
                            <option value="{{ $themeKey }}" {{ $selectedDesignTheme === $themeKey ? 'selected' : '' }}>{{ $themeData['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="hint">Etapa 2: puedes ajustar colores base y ver una previsualización en vivo antes de guardar.</div>

                    <div id="menu_qr_theme_panel" class="menu-qr-theme-panel is-hidden">
                        <div class="menu-qr-theme-panel-header">
                            <strong>Ajustes de color</strong>
                            <button type="button" class="menu-qr-theme-toggle" id="btnMenuQrThemeToggle" aria-label="Mostrar u ocultar ajustes de color" title="Mostrar/ocultar">▾</button>
                        </div>
                        <div class="menu-qr-theme-panel-body">
                            <div class="menu-qr-theme-editor">
                                @foreach($designTokenMeta as $tokenKey => $tokenLabel)
                                    <div class="menu-qr-theme-field">
                                        <label for="menu_qr_token_{{ $tokenKey }}">{{ $tokenLabel }}</label>
                                        <input
                                            type="color"
                                            id="menu_qr_token_{{ $tokenKey }}"
                                            class="menu-qr-token-input"
                                            data-token="{{ $tokenKey }}"
                                            value="{{ strtoupper($previewDesignTokens[$tokenKey] ?? '#000000') }}"
                                        >
                                    </div>
                                @endforeach
                            </div>

                            <div class="menu-qr-theme-tools">
                                <button type="button" class="menu-qr-theme-reset" id="btnMenuQrThemeReset">Restablecer colores del preset</button>
                            </div>

                            <div id="menu_qr_theme_contrast" class="menu-qr-theme-contrast"></div>

                            <div class="menu-qr-theme-preview">
                                <div id="menu_qr_theme_preview" class="menu-qr-theme-preview-box">
                                    <div class="menu-qr-theme-preview-card">
                                        <p class="menu-qr-theme-preview-title">Vista previa del estilo</p>
                                        <p class="menu-qr-theme-preview-sub">Así se verá la tarjeta principal del menú público.</p>
                                        <span class="menu-qr-theme-preview-accent">Acento</span>
                                        <span class="menu-qr-theme-preview-popular">Más popular</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="menu_qr_style_panel" class="menu-qr-theme-panel is-hidden" style="margin-top:10px;">
                        <div class="menu-qr-theme-panel-header">
                            <strong>Tipografía y estilo</strong>
                            <button type="button" class="menu-qr-theme-toggle" id="btnMenuQrStyleToggle" aria-label="Mostrar u ocultar tipografía y estilo" title="Mostrar/ocultar">▾</button>
                        </div>
                        <div class="menu-qr-theme-panel-body">
                            <div class="menu-qr-theme-editor">
                                @foreach($styleOptionChoices as $optionKey => $optionData)
                                    <div class="menu-qr-theme-field">
                                        <label for="menu_qr_style_{{ $optionKey }}">{{ $optionData['label'] }}</label>
                                        <select id="menu_qr_style_{{ $optionKey }}" class="menu-qr-style-select" data-option="{{ $optionKey }}" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:8px;background:#fff;color:var(--text);">
                                            @foreach($optionData['options'] as $choiceKey => $choiceLabel)
                                                <option value="{{ $choiceKey }}" {{ ($selectedStyleOptions[$optionKey] ?? '') === $choiceKey ? 'selected' : '' }}>{{ $choiceLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            <div class="menu-qr-theme-tools">
                                <button type="button" class="menu-qr-theme-reset" id="btnMenuQrStyleReset">Restablecer tipografía y estilo</button>
                            </div>

                            <div class="menu-qr-anim-preview" id="menu_qr_anim_preview">
                                <p class="menu-qr-anim-preview-title">Vista previa completa de tipografía, estilo y animación</p>
                                <div class="menu-qr-anim-preview-list">
                                    <div class="menu-qr-anim-preview-item">
                                        <div class="head">
                                            <p class="name">Producto demo 1</p>
                                            <span class="popular">Más popular</span>
                                        </div>
                                        <p class="desc">Texto de muestra para validar estilo tipográfico y contraste.</p>
                                        <div class="meta">
                                            <span class="price">$9.900</span>
                                            <span class="status">Disponible</span>
                                        </div>
                                    </div>
                                    <div class="menu-qr-anim-preview-item">
                                        <div class="head">
                                            <p class="name">Producto demo 2</p>
                                            <span class="menu-qr-theme-preview-accent" style="margin:0;">Acento</span>
                                        </div>
                                        <p class="desc">Otra tarjeta para observar radios, sombras y familia de texto.</p>
                                        <div class="meta">
                                            <span class="price">$7.500</span>
                                            <span class="status">Disponible</span>
                                        </div>
                                    </div>
                                    <div class="menu-qr-anim-preview-item">
                                        <div class="head">
                                            <p class="name">Producto demo 3</p>
                                        </div>
                                        <p class="desc">La animación se aplica al cambiar el selector de animación.</p>
                                        <div class="meta">
                                            <span class="price">$5.200</span>
                                            <span class="status">Disponible</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <div>La categoría <strong>Mas vendidos</strong> aparece al inicio del listado y puedes activarla o desactivarla desde esta misma configuración.</div>
                    <div>El ranking de <strong>Mas vendidos</strong> usa los <strong>10 productos</strong> con mayor cantidad vendida en los <strong>últimos 30 días incluyendo hoy</strong>, ordenados de mayor a menor.</div>
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
                <div class="menu-qr-category is-hidden" data-category-id="{{ $categoriaId }}">
                    <div class="menu-qr-category-header">
                        <label style="display:flex;align-items:center;gap:10px;margin:0;cursor:pointer;">
                            <input type="checkbox" class="menu-qr-category-check" data-category-id="{{ $categoriaId }}" {{ $categoriaChecked ? 'checked' : '' }}>
                            <strong>{{ $categoria['nombre'] }}</strong>
                        </label>
                        <div class="header-right">
                            <span class="meta">{{ $totalItems }} ítems</span>
                            <button type="button" class="menu-qr-toggle" data-category-id="{{ $categoriaId }}" aria-label="Mostrar u ocultar categoría" title="Mostrar/ocultar">▾</button>
                        </div>
                    </div>
                    <div class="menu-qr-items">
                        @foreach($categoria['items'] as $item)
                            @php
                                $itemKey = $item['tipo'] . ':' . $item['id'];
                                $itemChecked = in_array($itemKey, $selectedItems, true);
                                $isTopSellersCategory = $categoriaId === 'top-sellers';
                            @endphp
                            <label class="menu-qr-item {{ $isTopSellersCategory ? '' : ($itemChecked ? '' : 'is-disabled') }}" data-category-id="{{ $categoriaId }}" data-item-key="{{ $itemKey }}">
                                @if(!$isTopSellersCategory)
                                    <div class="checkbox-wrap">
                                        <input type="checkbox" class="menu-qr-item-check" data-category-id="{{ $categoriaId }}" value="{{ $itemKey }}" {{ $itemChecked ? 'checked' : '' }}>
                                    </div>
                                @endif
                                <div class="topline">
                                    <div style="min-width:0;flex:1;">
                                        <p class="name">{{ $item['nombre'] }}</p>
                                        @if(!empty($item['descripcion']))
                                            <p class="desc">{{ $item['descripcion'] }}</p>
                                        @endif
                                        <div class="price">${{ number_format($item['precio'], 0, ',', '.') }}</div>
                                        @if($isTopSellersCategory)
                                            <span class="menu-qr-badge ok">Auto por ventas</span>
                                        @else
                                            <span class="menu-qr-badge {{ $item['disponible'] ? 'ok' : 'off' }}">
                                                {{ $item['disponible'] ? 'Disponible' : 'No disponible' }}
                                            </span>
                                        @endif
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
                            <li>Los productos tipo PRODUCTO se ocultan como no disponibles cuando no tienen stock.</li>
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