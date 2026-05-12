<script>
    (function () {
        if (!document.querySelector('meta[name="viewport"]')) {
            var meta = document.createElement('meta');
            meta.name = 'viewport';
            meta.content = 'width=device-width, initial-scale=1, viewport-fit=cover';
            document.head.appendChild(meta);
        }
    })();
</script>

<style>
    .qr-menu {
        --bg: #f3f4f6;
        --surface: #ffffff;
        --surface-soft: #f8fafc;
        --border: #e5e7eb;
        --text: #111827;
        --muted: #6b7280;
        --accent: #334155;
        --accent-soft: #e2e8f0;
        --ok-bg: #ecfdf5;
        --ok-text: #166534;
        --off-bg: #fef2f2;
        --off-text: #991b1b;
        --popular-start: #ea580c;
        --popular-end: #dc2626;
        --font-title-family: Georgia, "Times New Roman", Times, serif;
        --font-body-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        --radius-size: 14px;
        --shadow-level: 0 10px 24px rgba(15, 23, 42, 0.08);
        min-height: 100vh;
        background: radial-gradient(circle at 15% 0%, #e9edf2 0%, transparent 35%), linear-gradient(180deg, #f8fafc 0%, var(--bg) 100%);
        color: var(--text);
        padding: 18px 12px 34px;
        font-family: var(--font-body-family);
    }

    .qr-shell {
        max-width: 980px;
        margin: 0 auto;
    }

    .qr-hero {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-size);
        padding: 14px;
        box-shadow: var(--shadow-level);
        margin-bottom: 14px;
    }

    .qr-brand {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .qr-logo {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid var(--border);
        background: #fff;
        flex: 0 0 auto;
    }

    .qr-title {
        margin: 0;
        font-size: clamp(26px, 5.5vw, 34px);
        line-height: 1.05;
        letter-spacing: -0.02em;
        font-weight: 800;
        color: var(--text);
        font-family: var(--font-title-family);
    }

    .qr-subtitle {
        margin: 6px 0 0;
        color: var(--accent);
        font-weight: 700;
        font-size: 15px;
    }

    .qr-meta {
        margin-top: 8px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .qr-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-size);
        box-shadow: var(--shadow-level);
    }

    .qr-panel-body {
        padding: 14px;
    }

    .qr-panel-title {
        margin: 0;
        font-size: 21px;
        letter-spacing: -0.01em;
        font-weight: 800;
    }

    .qr-panel-subtitle {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .category-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .category-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        width: 100%;
        border: 1px solid var(--border);
        background: var(--surface-soft);
        border-radius: calc(var(--radius-size) - 2px);
        padding: 12px 13px;
        color: var(--text);
        font-weight: 700;
        font-size: 14px;
        text-align: left;
        cursor: pointer;
        transition: background .2s ease, border-color .2s ease, transform .2s ease;
    }

    .category-card:hover {
        border-color: var(--accent-soft);
        background: var(--accent-soft);
        transform: translateY(-1px);
    }

    .category-count {
        background: var(--accent-soft);
        color: var(--accent);
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .products-view {
        display: none;
    }

    .products-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .btn-back-categories {
        border: 1px solid var(--border);
        background: #fff;
        color: var(--accent);
        font-weight: 700;
        font-size: 13px;
        border-radius: calc(var(--radius-size) - 4px);
        padding: 7px 11px;
        cursor: pointer;
    }

    .products-current-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.15;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .category-strip {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding: 8px 6px 8px;
        margin-bottom: 10px;
        position: sticky;
        top: 6px;
        z-index: 15;
        border: 1px solid var(--border);
        border-radius: calc(var(--radius-size) - 2px);
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(6px);
    }

    .category-strip::-webkit-scrollbar {
        height: 6px;
    }

    .category-strip::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .category-pill {
        border: 1px solid var(--border);
        background: var(--surface-soft);
        color: var(--accent);
        border-radius: calc(var(--radius-size) + 12px);
        padding: 8px 12px;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        cursor: pointer;
    }

    .category-pill.active {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent);
    }

    .category-products {
        display: none;
    }

    .category-products.active {
        display: block;
    }

    .products-list {
        display: grid;
        gap: 9px;
    }

    .product-row {
        border: 1px solid var(--border);
        border-radius: calc(var(--radius-size) - 2px);
        background: var(--surface-soft);
        overflow: hidden;
        display: grid;
        grid-template-columns: 70% 30%;
        min-height: 156px;
        box-shadow: var(--shadow-level);
    }

    .product-row.unavailable {
        opacity: .68;
    }

    .product-info {
        padding: 12px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .popular-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 11px;
        line-height: 1;
        font-weight: 900;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: #ffffff;
        background: linear-gradient(135deg, rgba(234, 88, 12, 0.78) 0%, rgba(220, 38, 38, 0.78) 100%);
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.22);
        opacity: 0.88;
        white-space: nowrap;
        flex: 0 0 auto;
        backdrop-filter: blur(2px);
    }

    .product-image-wrap .popular-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 2;
        pointer-events: none;
    }

    .product-name {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: -0.01em;
        color: var(--text);
        line-height: 1.15;
        flex: 1 1 auto;
        min-width: 0;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-desc {
        margin: 5px 0 0;
        font-size: 12px;
        line-height: 1.35;
        color: var(--muted);
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 52px;
    }

    .product-meta {
        margin-top: 7px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        flex-wrap: wrap;
    }

    .product-price {
        font-size: 21px;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: var(--accent);
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .product-status {
        font-size: 12px;
        font-weight: 700;
        border-radius: 999px;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .product-status.ok {
        background: var(--ok-bg);
        color: var(--ok-text);
    }

    .product-status.off {
        background: var(--off-bg);
        color: var(--off-text);
    }

    .product-image-wrap {
        border-left: 1px solid var(--border);
        background: var(--surface-soft);
        position: relative;
        min-height: 100%;
        overflow: hidden;
        box-sizing: border-box;
    }

    .product-image {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
        display: block;
        padding: 0;
    }

    .empty-state {
        text-align: center;
        border: 1px dashed var(--border);
        background: #fff;
        border-radius: calc(var(--radius-size) - 2px);
        padding: 24px 16px;
        color: var(--muted);
        line-height: 1.55;
        margin-top: 12px;
    }

    .product-row.anim-fade {
        animation: qrFadeIn .35s ease both;
    }

    .product-row.anim-stagger {
        animation: qrStaggerIn .42s ease both;
    }

    @keyframes qrFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes qrStaggerIn {
        from { opacity: 0; transform: translateY(12px) scale(.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .to-top {
        position: fixed;
        right: 14px;
        bottom: 14px;
        width: 48px;
        height: 48px;
        border: none;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        font-size: 21px;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(30, 41, 59, .3);
        opacity: 0;
        transform: translateY(10px);
        pointer-events: none;
        transition: opacity .2s ease, transform .2s ease;
        z-index: 30;
    }

    .to-top.is-visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    @media (max-width: 760px) {
        .qr-menu {
            padding: 10px 10px 24px;
        }

        .qr-panel-body {
            padding: 10px;
        }

        .qr-hero,
        .qr-panel {
            border-radius: 14px;
        }

        .qr-hero {
            padding: 10px;
            margin-bottom: 10px;
        }

        .qr-logo {
            width: 68px;
            height: 68px;
            border-radius: 10px;
            object-fit: cover;
            object-position: center;
        }

        .qr-title {
            font-size: clamp(17px, 5.2vw, 21px);
            line-height: 1.05;
            letter-spacing: -0.01em;
        }

        .qr-subtitle {
            display: none;
        }

        .qr-meta {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            line-height: 1.3;
        }

        .qr-panel-title {
            font-size: 21px;
            line-height: 1.1;
        }

        .qr-panel-subtitle {
            font-size: 13px;
            line-height: 1.45;
            margin-top: 4px;
        }

        .category-grid {
            grid-template-columns: 1fr;
            gap: 11px;
        }

        .category-card {
            font-size: 18px;
            padding: 14px 15px;
            border-radius: 13px;
        }

        .category-count {
            font-size: 14px;
            padding: 6px 11px;
        }

        .products-current-title {
            font-size: 16px;
        }

        .btn-back-categories {
            font-size: 13px;
            padding: 8px 12px;
        }

        .category-pill {
            font-size: 16px;
            padding: 10px 14px;
        }

        .category-strip {
            top: 4px;
            padding: 7px 5px;
            margin-bottom: 9px;
        }

        .product-row {
            display: flex;
            align-items: stretch;
            min-height: 0;
            height: 142px;
            border-radius: 13px;
        }

        .product-info {
            flex: 1 1 auto;
            min-width: 0;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-name {
            font-size: 14px;
            line-height: 1.15;
            -webkit-line-clamp: 1;
        }

        .popular-badge {
            font-size: 10px;
            padding: 4px 8px;
        }

        .product-image-wrap .popular-badge {
            top: 6px;
            right: 6px;
        }

        .product-desc {
            font-size: 11px;
            line-height: 1.28;
            -webkit-line-clamp: 3;
            min-height: 44px;
            margin-top: 4px;
        }

        .product-price {
            font-size: 18px;
        }

        .product-status {
            font-size: 12px;
            padding: 5px 9px;
        }

        .product-image-wrap {
            flex: 0 0 142px;
            width: 142px;
            height: 100%;
            border-left: 1px solid var(--border);
            overflow: hidden;
        }

        .product-image {
            padding: 0;
            object-fit: cover;
        }

        .product-meta {
            margin-top: 6px;
            justify-content: flex-start;
        }

        .products-list {
            gap: 7px;
        }

        .to-top {
            width: 52px;
            height: 52px;
            font-size: 24px;
        }
    }

    @media (max-width: 420px) {
        .product-desc {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 42px;
            font-size: 10px;
            line-height: 1.25;
        }

        .product-row {
            height: 132px;
            display: flex;
            align-items: stretch;
        }

        .product-image-wrap {
            flex-basis: 132px;
            width: 132px;
            height: 100%;
            overflow: hidden;
        }

        .product-name {
            font-size: 12px;
        }

        .popular-badge {
            font-size: 9px;
            padding: 3px 7px;
        }

        .product-image-wrap .popular-badge {
            top: 5px;
            right: 5px;
        }

        .product-price {
            font-size: 16px;
        }

        .product-status {
            font-size: 10px;
            padding: 3px 7px;
        }
    }
</style>

<?php
    $menuThemeStyle = collect($designTokens ?? [])->map(function ($value, $key) {
        return '--' . $key . ': ' . $value;
    })->implode('; ');

    $menuVisualStyle = collect([
        'font-title-family' => $designVisualOptions['font_title_family'] ?? 'Georgia, "Times New Roman", Times, serif',
        'font-body-family' => $designVisualOptions['font_body_family'] ?? '"Segoe UI", "Helvetica Neue", Arial, sans-serif',
        'radius-size' => $designVisualOptions['radius_size'] ?? '14px',
        'shadow-level' => $designVisualOptions['shadow_level'] ?? '0 10px 24px rgba(15, 23, 42, 0.08)',
    ])->map(function ($value, $key) {
        return '--' . $key . ': ' . $value;
    })->implode('; ');

    $menuAnimStyle = $designVisualOptions['animation_style'] ?? 'stagger';
?>

<div class="qr-menu" data-animation-style="<?php echo e($menuAnimStyle); ?>" style="<?php echo e($menuThemeStyle); ?>; <?php echo e($menuVisualStyle); ?>">
    <div class="qr-shell">
        <header class="qr-hero">
            <div class="qr-brand">
                <img class="qr-logo" src="<?php echo e(!empty($corporateData['logo_enterprise']) ? asset($corporateData['logo_enterprise']) : asset('img/sin_imagen.jpg')); ?>" alt="Logo empresa">
                <div>
                    <h1 class="qr-title"><?php echo e($corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Menú QR')); ?></h1>
                    <p class="qr-subtitle"><?php echo e($corporateData['name_enterprise'] ?? ''); ?></p>
                    <div class="qr-meta">
                        <div><?php echo e($corporateData['address_enterprise'] ?? ''); ?></div>
                        <div><?php echo e($corporateData['comuna_enterprise'] ?? ''); ?></div>
                        <div><?php echo e($corporateData['phone_enterprise'] ?? ''); ?></div>
                    </div>
                </div>
            </div>
        </header>

        <?php if(count($categorias) > 0): ?>
            <section id="categoriesView" class="qr-panel">
                <div class="qr-panel-body">
                    <h2 class="qr-panel-title">Elige una categoría</h2>
                    <p class="qr-panel-subtitle">Primero selecciona una categoría y luego verás todos sus productos con detalle.</p>

                    <div class="category-grid" id="categoryGrid">
                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                type="button"
                                class="category-card"
                                data-category-id="<?php echo e($categoria['id']); ?>"
                                data-category-name="<?php echo e($categoria['nombre']); ?>"
                            >
                                <span><?php echo e($categoria['nombre']); ?></span>
                                <span class="category-count"><?php echo e(count($categoria['items'])); ?></span>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

            <section id="productsView" class="qr-panel products-view">
                <div class="qr-panel-body">
                    <div class="products-topbar">
                        <button id="btnBackCategories" class="btn-back-categories" type="button">← Categorías</button>
                        <h2 id="currentCategoryTitle" class="products-current-title"></h2>
                    </div>

                    <div class="category-strip" id="categoryStrip">
                        <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" class="category-pill" data-category-id="<?php echo e($categoria['id']); ?>"><?php echo e($categoria['nombre']); ?></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <section class="category-products" data-category-id="<?php echo e($categoria['id']); ?>" data-category-name="<?php echo e($categoria['nombre']); ?>">
                            <div class="products-list">
                                <?php $__currentLoopData = $categoria['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <article class="product-row <?php echo e($item['disponible'] ? '' : 'unavailable'); ?>">
                                        <div class="product-info">
                                            <div>
                                                <div class="product-head">
                                                    <h3 class="product-name"><?php echo e($item['nombre']); ?></h3>
                                                </div>
                                                <?php if(!empty($item['descripcion'])): ?>
                                                    <p class="product-desc"><?php echo e($item['descripcion']); ?></p>
                                                <?php else: ?>
                                                    <p class="product-desc">&nbsp;</p>
                                                <?php endif; ?>
                                            </div>

                                            <div class="product-meta">
                                                <span class="product-price">$<?php echo e(number_format($item['precio'], 0, ',', '.')); ?></span>
                                                <span class="product-status <?php echo e($item['disponible'] ? 'ok' : 'off'); ?>"><?php echo e($item['disponible'] ? 'Disponible' : 'No disponible'); ?></span>
                                            </div>
                                        </div>

                                        <div class="product-image-wrap">
                                            <?php if(($item['tipo'] ?? null) === 'product' && !empty($item['popular'])): ?>
                                                <span class="popular-badge">Mas popular</span>
                                            <?php endif; ?>
                                            <img class="product-image" src="<?php echo e($item['imagen']); ?>" alt="<?php echo e($item['nombre']); ?>">
                                        </div>
                                    </article>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </section>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php else: ?>
            <div class="empty-state">
                Este menú todavía no tiene categorías o productos configurados.
            </div>
        <?php endif; ?>
    </div>

    <button id="btnToTop" class="to-top" type="button" aria-label="Volver arriba" title="Volver arriba">↑</button>
</div>

<script>
    (function () {
        var categoriesView = document.getElementById('categoriesView');
        var productsView = document.getElementById('productsView');
        var categoryButtons = document.querySelectorAll('.category-card');
        var categoryPills = document.querySelectorAll('.category-pill');
        var categorySections = document.querySelectorAll('.category-products');
        var currentCategoryTitle = document.getElementById('currentCategoryTitle');
        var btnBack = document.getElementById('btnBackCategories');
        var btnTop = document.getElementById('btnToTop');

        if (categoryButtons.length && productsView) {
            categoryButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    openCategory(String(btn.getAttribute('data-category-id') || ''));
                });
            });
        }

        if (categoryPills.length) {
            categoryPills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    openCategory(String(pill.getAttribute('data-category-id') || ''));
                });
            });
        }

        if (btnBack && categoriesView && productsView) {
            btnBack.addEventListener('click', function () {
                productsView.style.display = 'none';
                categoriesView.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        function openCategory(categoryId) {
            if (!categoryId || !productsView || !categoriesView) {
                return;
            }

            categoriesView.style.display = 'none';
            productsView.style.display = 'block';

            categorySections.forEach(function (section) {
                var isActive = String(section.getAttribute('data-category-id')) === String(categoryId);
                section.classList.toggle('active', isActive);
                if (isActive && currentCategoryTitle) {
                    currentCategoryTitle.textContent = section.getAttribute('data-category-name') || 'Categoría';
                }
            });

            categoryPills.forEach(function (pill) {
                var isActive = String(pill.getAttribute('data-category-id')) === String(categoryId);
                pill.classList.toggle('active', isActive);
            });

            applyRowAnimations(categoryId);

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function applyRowAnimations(categoryId) {
            var root = document.querySelector('.qr-menu');
            var animationStyle = root ? String(root.getAttribute('data-animation-style') || 'stagger') : 'stagger';
            var rows = document.querySelectorAll('.category-products[data-category-id="' + categoryId + '"] .product-row');

            rows.forEach(function (row, index) {
                row.classList.remove('anim-fade', 'anim-stagger');
                row.style.animationDelay = '';

                if (animationStyle === 'none') {
                    return;
                }

                if (animationStyle === 'fade') {
                    row.classList.add('anim-fade');
                    row.style.animationDelay = '0ms';
                    return;
                }

                row.classList.add('anim-stagger');
                row.style.animationDelay = String(index * 35) + 'ms';
            });
        }

        if (btnTop) {
            function toggleBtn() {
                if (window.scrollY > 320) {
                    btnTop.classList.add('is-visible');
                } else {
                    btnTop.classList.remove('is-visible');
                }
            }

            window.addEventListener('scroll', toggleBtn, { passive: true });
            btnTop.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            toggleBtn();
        }
    })();
</script>
<?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/public/menu_qr.blade.php ENDPATH**/ ?>