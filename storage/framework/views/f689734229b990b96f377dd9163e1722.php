<style>
    .public-menu-qr {
        --bg: #f4f6f8;
        --card: #ffffff;
        --border: #dbe3ea;
        --text: #1f2d3d;
        --muted: #6a7a8c;
        --brand: #0f5f8f;
        --brand-soft: #e8f3fa;
        --success: #2f855a;
        --danger: #c53030;
        min-height: 100vh;
        background: radial-gradient(circle at 80% -20%, #dbefff 0%, transparent 35%), linear-gradient(180deg, #f8fbfe 0%, #eef3f7 100%);
        color: var(--text);
        padding: 28px 14px 44px;
        font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    }
    .public-shell {
        max-width: 1180px;
        margin: 0 auto;
    }
    .hero {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 18px;
    }
    .surface {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(31, 45, 61, .08);
    }
    .surface-body {
        padding: 18px;
    }
    .brand-box {
        display: flex;
        gap: 14px;
        align-items: center;
    }
    .logo {
        width: 82px;
        height: 82px;
        border-radius: 14px;
        object-fit: cover;
        border: 1px solid var(--border);
        background: #fff;
        flex: 0 0 auto;
    }
    .title {
        margin: 0;
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1;
        letter-spacing: -.03em;
        font-weight: 800;
    }
    .subtitle {
        margin: 6px 0 0;
        font-size: 15px;
        color: var(--brand);
        font-weight: 700;
    }
    .meta {
        margin-top: 10px;
        color: var(--muted);
        line-height: 1.6;
        font-size: 14px;
    }
    .qr-grid {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 14px;
        align-items: center;
    }
    .qr-grid img {
        width: 140px;
        height: 140px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: #fff;
        padding: 8px;
    }
    .category-nav {
        margin-top: 16px;
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        position: sticky;
        top: 8px;
        z-index: 10;
    }
    .category-nav a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid #c5d7e5;
        background: var(--brand-soft);
        color: #0f5178;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }
    .menu-section {
        margin-top: 18px;
    }
    .menu-section-title {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 10px;
    }
    .menu-section-title h3 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
        letter-spacing: -.02em;
    }
    .menu-section-title span {
        color: var(--muted);
        font-size: 13px;
    }
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 14px;
    }
    .item-card {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(31, 45, 61, .06);
        position: relative;
    }
    .item-card.unavailable {
        opacity: .66;
    }
    .item-image {
        width: 100%;
        height: 184px;
        object-fit: contain;
        display: block;
        background: #f4f7fa;
        padding: 8px;
        border-bottom: 1px solid var(--border);
    }
    .item-content {
        padding: 12px;
    }
    .item-name {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.01em;
    }
    .item-desc {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.45;
        min-height: 38px;
    }
    .item-footer {
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    .item-price {
        font-size: 21px;
        font-weight: 900;
        color: var(--brand);
    }
    .badge-state {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
    }
    .badge-state.ok {
        background: #e6f6ee;
        color: var(--success);
    }
    .badge-state.off {
        background: #fdecec;
        color: var(--danger);
    }
    .item-card.unavailable::before {
        content: 'No disponible';
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(197, 48, 48, .95);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 8px;
        border-radius: 999px;
        letter-spacing: .02em;
    }
    .empty-state {
        margin-top: 16px;
        text-align: center;
        color: var(--muted);
        padding: 32px;
        border: 1px dashed #b8c8d5;
        border-radius: 14px;
        background: rgba(255, 255, 255, .72);
    }
    .to-top {
        position: fixed;
        right: 18px;
        bottom: 18px;
        width: 48px;
        height: 48px;
        border: none;
        border-radius: 50%;
        background: var(--brand);
        color: #fff;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        box-shadow: 0 10px 24px rgba(15, 95, 143, .36);
        opacity: 0;
        transform: translateY(8px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease;
        z-index: 50;
    }
    .to-top.is-visible {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
    @media (max-width: 960px) {
        .hero {
            grid-template-columns: 1fr;
        }
        .qr-grid {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .qr-grid img {
            margin: 0 auto;
        }
        .category-nav {
            top: 4px;
        }
        .to-top {
            width: 44px;
            height: 44px;
            right: 12px;
            bottom: 12px;
        }
    }
</style>

<div class="public-menu-qr">
    <div class="public-shell">
        <div class="hero">
            <div class="surface">
                <div class="surface-body">
                    <div class="brand-box">
                        <img class="logo" src="<?php echo e(!empty($corporateData['logo_enterprise']) ? asset($corporateData['logo_enterprise']) : asset('img/sin_imagen.jpg')); ?>" alt="Logo empresa">
                        <div>
                            <h1 class="title"><?php echo e($corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Menú QR')); ?></h1>
                            <p class="subtitle"><?php echo e($corporateData['name_enterprise'] ?? ''); ?></p>
                            <div class="meta">
                                <div><?php echo e($corporateData['address_enterprise'] ?? ''); ?></div>
                                <div><?php echo e($corporateData['comuna_enterprise'] ?? ''); ?></div>
                                <div><?php echo e($corporateData['phone_enterprise'] ?? ''); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="surface">
                <div class="surface-body">
                    <div class="qr-grid">
                        <img src="<?php echo e($qrDataUri); ?>" alt="QR menú">
                        <div>
                            <h2 style="margin:0 0 8px;font-size:24px;font-weight:800;letter-spacing:-.02em;">Carta Digital</h2>
                            <p style="margin:0;color:var(--muted);line-height:1.6;font-size:14px;">Aquí ves el menú seleccionado por el restaurante. Los productos disponibles aparecen primero en cada categoría.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if(count($categorias) > 0): ?>
            <nav class="category-nav">
                <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#cat-<?php echo e($categoria['id']); ?>"><?php echo e($categoria['nombre']); ?> <span>(<?php echo e(count($categoria['items'])); ?>)</span></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
        <?php endif; ?>

        <?php $__empty_1 = true; $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <section id="cat-<?php echo e($categoria['id']); ?>" class="menu-section">
                <div class="menu-section-title">
                    <h3><?php echo e($categoria['nombre']); ?></h3>
                    <span><?php echo e(count($categoria['items'])); ?> opciones</span>
                </div>

                <div class="items-grid">
                    <?php $__currentLoopData = $categoria['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="item-card <?php echo e($item['disponible'] ? '' : 'unavailable'); ?>">
                            <img class="item-image" src="<?php echo e($item['imagen']); ?>" alt="<?php echo e($item['nombre']); ?>">
                            <div class="item-content">
                                <h4 class="item-name"><?php echo e($item['nombre']); ?></h4>

                                <?php if($item['tipo'] === 'recipe' && !empty($item['descripcion'])): ?>
                                    <p class="item-desc"><?php echo e($item['descripcion']); ?></p>
                                <?php else: ?>
                                    <p class="item-desc">&nbsp;</p>
                                <?php endif; ?>

                                <div class="item-footer">
                                    <span class="item-price">$<?php echo e(number_format($item['precio'], 0, ',', '.')); ?></span>
                                    <span class="badge-state <?php echo e($item['disponible'] ? 'ok' : 'off'); ?>"><?php echo e($item['disponible'] ? 'Disponible' : 'No disponible'); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state">
                Este menú todavía no tiene categorías o productos configurados.
            </div>
        <?php endif; ?>
    </div>

    <button id="btnToTop" class="to-top" type="button" aria-label="Volver arriba" title="Volver arriba">↑</button>
</div>

<script>
    (function () {
        var btn = document.getElementById('btnToTop');
        if (!btn) return;

        function toggleBtn() {
            if (window.scrollY > 400) {
                btn.classList.add('is-visible');
            } else {
                btn.classList.remove('is-visible');
            }
        }

        window.addEventListener('scroll', toggleBtn, { passive: true });
        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        toggleBtn();
    })();
</script><?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/public/menu_qr.blade.php ENDPATH**/ ?>