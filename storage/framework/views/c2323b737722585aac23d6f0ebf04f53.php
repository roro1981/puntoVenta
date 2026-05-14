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
    .menu-qr-collection {
        margin-bottom: 16px;
    }
    .menu-qr-collection-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }
    .menu-qr-collection-head h3 {
        margin: 0;
        font-size: 18px;
        line-height: 1.2;
    }
    .menu-qr-collection-head p {
        margin: 4px 0 0;
        color: var(--muted);
        font-size: 13px;
    }
    .menu-qr-collection-count {
        min-width: 72px;
        padding: 8px 12px;
        border-radius: 999px;
        background: var(--panel);
        border: 1px solid var(--border);
        font-weight: 800;
        text-align: center;
    }
    .menu-qr-collection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }
    .menu-qr-menu-card {
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 8px 22px rgba(60, 47, 35, .05);
        padding: 14px;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .menu-qr-menu-card:hover {
        transform: translateY(-2px);
        border-color: #cfbea9;
        box-shadow: 0 12px 26px rgba(60, 47, 35, .09);
    }
    .menu-qr-menu-card.is-active {
        border-color: rgba(180, 95, 45, .35);
        box-shadow: 0 12px 28px rgba(180, 95, 45, .10);
    }
    .menu-qr-menu-card-head {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .menu-qr-menu-card-head-left {
        min-width: 0;
    }
    .menu-qr-menu-card-head strong {
        font-size: 15px;
        line-height: 1.25;
    }
    .menu-qr-menu-card-tools {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .menu-qr-menu-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #f4e7df;
        color: #8a4a25;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }
    .menu-qr-menu-badge.is-hidden {
        display: none;
    }
    .menu-qr-menu-delete-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #e2cbc4;
        background: #fff8f6;
        color: #a63d40;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
    }
    .menu-qr-menu-delete-btn:hover {
        background: #fde9e5;
        border-color: #d9a79a;
    }
    .menu-qr-menu-delete-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }
    .menu-qr-menu-delete-btn svg {
        width: 15px;
        height: 15px;
    }
    .menu-qr-menu-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 10px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.4;
        margin-bottom: 8px;
    }
    .menu-qr-menu-card-note {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
    }
    .menu-qr-name-control {
        margin-top: 14px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }
    .menu-qr-name-control label {
        display: block;
        font-weight: 800;
        margin-bottom: 8px;
    }
    .menu-qr-name-control input {
        width: 100%;
        border-radius: 10px;
        border: 1px solid var(--border);
        padding: 10px 12px;
        box-sizing: border-box;
    }
    .menu-qr-name-control .hint {
        margin-top: 8px;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.45;
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
    <input type="hidden" id="token" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" id="menu_qr_current_config_id" value="<?php echo e($activeMenuId); ?>">
    <input type="hidden" id="menu_qr_save_url" value="<?php echo e(route('menu-qr.guardar')); ?>">
    <input type="hidden" id="menu_qr_delete_url" value="<?php echo e(route('menu-qr.eliminar')); ?>">
    <input type="hidden" id="menu_qr_pdf_url" value="<?php echo e(route('menu-qr.pdf', ['token' => $configuracion->public_token])); ?>">
    <input type="hidden" id="menu_qr_public_url" value="<?php echo e($menuUrl); ?>">
    <input type="hidden" id="menu_qr_reload_url" value="<?php echo e($menuReloadUrl); ?>">
    <script type="application/json" id="menu_qr_themes_json"><?php echo json_encode(collect($designThemes)->mapWithKeys(fn ($themeData, $themeKey) => [$themeKey => $themeData['tokens']])->all(), JSON_UNESCAPED_UNICODE); ?></script>
    <script type="application/json" id="menu_qr_style_defaults_json"><?php echo json_encode($styleOptionDefaults, JSON_UNESCAPED_UNICODE); ?></script>
    <script type="application/json" id="menu_qr_configs_json"><?php echo json_encode($menuConfiguraciones->values()->all(), JSON_UNESCAPED_UNICODE); ?></script>

    <div class="menu-qr-collection">
        <div class="menu-qr-collection-head">
            <div>
                <h3>Menús QR creados</h3>
                <p>Replica esta configuración hasta <?php echo e($maxMenuConfiguraciones); ?> veces sin salir de esta pantalla.</p>
            </div>
            <div class="menu-qr-collection-count"><?php echo e($menuConfiguracionesCount); ?>/<?php echo e($maxMenuConfiguraciones); ?></div>
        </div>

        <div class="menu-qr-collection-grid">
            <?php $__currentLoopData = $menuConfiguraciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuConfiguracion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="menu-qr-menu-card <?php echo e($menuConfiguracion['activo'] ? 'is-active' : ''); ?>"
                    data-config-id="<?php echo e($menuConfiguracion['id']); ?>"
                    role="button"
                    tabindex="0"
                    aria-label="Seleccionar <?php echo e($menuConfiguracion['nombre']); ?>"
                >
                    <div class="menu-qr-menu-card-head">
                        <div class="menu-qr-menu-card-head-left">
                            <strong><?php echo e($menuConfiguracion['nombre']); ?></strong>
                        </div>
                        <div class="menu-qr-menu-card-tools">
                            <span class="menu-qr-menu-badge <?php echo e($menuConfiguracion['activo'] ? '' : 'is-hidden'); ?>">Actual</span>
                            <button
                                type="button"
                                class="menu-qr-menu-delete-btn"
                                data-delete-config-id="<?php echo e($menuConfiguracion['id']); ?>"
                                data-delete-config-name="<?php echo e($menuConfiguracion['nombre']); ?>"
                                title="Eliminar este menú QR"
                                aria-label="Eliminar <?php echo e($menuConfiguracion['nombre']); ?>"
                                <?php echo e($menuConfiguracionesCount <= 1 ? 'disabled' : ''); ?>

                            >
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M4 7H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M9 7V5.8C9 5.08 9.58 4.5 10.3 4.5H13.7C14.42 4.5 15 5.08 15 5.8V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M18 7L17.3 18.2C17.24 19.16 16.45 19.9 15.49 19.9H8.51C7.55 19.9 6.76 19.16 6.7 18.2L6 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10 10.2V16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M14 10.2V16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="menu-qr-menu-card-meta">
                        <span><?php echo e($menuConfiguracion['selected_categories_count']); ?> categorías</span>
                        <span><?php echo e($menuConfiguracion['selected_items_count']); ?> ítems</span>
                    </div>
                    <div class="menu-qr-menu-card-note">Haz click para cargar este menú en el editor de abajo.</div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="menu-qr-hero">
        <div class="menu-qr-card">
            <div class="card-head">
                <div class="menu-qr-brand">
                    <img class="menu-qr-logo" src="<?php echo e(!empty($corporateData['logo_enterprise']) ? asset($corporateData['logo_enterprise']) : asset('img/sin_imagen.jpg')); ?>" alt="Logo empresa">
                    <div>
                        <h2 class="menu-qr-title">Configurar Menú QR</h2>
                        <p class="menu-qr-subtitle">Selecciona categorías y productos para el menú público.</p>
                    </div>
                </div>
                <div class="menu-qr-linkbox">
                    <input type="text" id="menu_qr_link" value="<?php echo e($menuUrl); ?>" readonly>
                    <button type="button" id="btnCopyMenuQrLink">Copiar link</button>
                </div>
                <div class="menu-qr-name-control">
                    <label for="menu_qr_name">Nombre del menú</label>
                    <input type="text" id="menu_qr_name" value="<?php echo e($currentMenuName); ?>" maxlength="120">
                    <div class="hint">Usa un nombre claro para identificar cada menú QR cuando tengas varios creados.</div>
                </div>
                <div class="menu-qr-actions">
                    <input type="number" class="copies-input" id="menu_qr_copias" min="1" max="50" value="1">
                    <button type="button" class="btn-pdf" id="btnPrintMenuQrPdf">Imprimir QR en PDF</button>
                    <button type="button" class="btn-save" id="btnSaveMenuQr">Guardar configuración</button>
                    <?php if($menuConfiguracionesCount < $maxMenuConfiguraciones): ?>
                        <button type="button" class="btn-save" id="btnDuplicateMenuQr">Duplicar como nuevo</button>
                    <?php endif; ?>
                </div>
                <div class="menu-qr-helper menu-qr-company-grid" style="margin-top:10px;">
                    <div><strong>Empresa:</strong> <?php echo e($corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Sin datos')); ?></div>
                    <div><strong>Dirección:</strong> <?php echo e($corporateData['address_enterprise'] ?? 'Sin datos'); ?></div>
                    <div><strong>Comuna:</strong> <?php echo e($corporateData['comuna_enterprise'] ?? 'Sin datos'); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo e($corporateData['phone_enterprise'] ?? 'Sin datos'); ?></div>
                </div>
                <div class="menu-qr-theme-control">
                    <label for="menu_qr_design_theme">Preset visual del menu publico</label>
                    <select id="menu_qr_design_theme">
                        <?php $__currentLoopData = $designThemes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $themeKey => $themeData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($themeKey); ?>" <?php echo e($selectedDesignTheme === $themeKey ? 'selected' : ''); ?>><?php echo e($themeData['label']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="hint">Etapa 2: puedes ajustar colores base y ver una previsualización en vivo antes de guardar.</div>

                    <div id="menu_qr_theme_panel" class="menu-qr-theme-panel is-hidden">
                        <div class="menu-qr-theme-panel-header">
                            <strong>Ajustes de color</strong>
                            <button type="button" class="menu-qr-theme-toggle" id="btnMenuQrThemeToggle" aria-label="Mostrar u ocultar ajustes de color" title="Mostrar/ocultar">▾</button>
                        </div>
                        <div class="menu-qr-theme-panel-body">
                            <div class="menu-qr-theme-editor">
                                <?php $__currentLoopData = $designTokenMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tokenKey => $tokenLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="menu-qr-theme-field">
                                        <label for="menu_qr_token_<?php echo e($tokenKey); ?>"><?php echo e($tokenLabel); ?></label>
                                        <input
                                            type="color"
                                            id="menu_qr_token_<?php echo e($tokenKey); ?>"
                                            class="menu-qr-token-input"
                                            data-token="<?php echo e($tokenKey); ?>"
                                            value="<?php echo e(strtoupper($previewDesignTokens[$tokenKey] ?? '#000000')); ?>"
                                        >
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <?php $__currentLoopData = $styleOptionChoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionKey => $optionData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="menu-qr-theme-field">
                                        <label for="menu_qr_style_<?php echo e($optionKey); ?>"><?php echo e($optionData['label']); ?></label>
                                        <select id="menu_qr_style_<?php echo e($optionKey); ?>" class="menu-qr-style-select" data-option="<?php echo e($optionKey); ?>" style="width:100%;border:1px solid var(--border);border-radius:8px;padding:8px;background:#fff;color:var(--text);">
                                            <?php $__currentLoopData = $optionData['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choiceKey => $choiceLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($choiceKey); ?>" <?php echo e(($selectedStyleOptions[$optionKey] ?? '') === $choiceKey ? 'selected' : ''); ?>><?php echo e($choiceLabel); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <img id="menu_qr_preview_image" src="<?php echo e($qrImageUrl); ?>" alt="QR del menú">
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
                <span style="color:var(--muted);font-size:12px;"><?php echo e(count($categorias)); ?> categorías</span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $categoriaId = (string) $categoria['id'];
                    $categoriaChecked = in_array($categoriaId, $selectedCategories, true);
                    $totalItems = count($categoria['items'] ?? []);
                ?>
                <div class="menu-qr-category is-hidden" data-category-id="<?php echo e($categoriaId); ?>">
                    <div class="menu-qr-category-header">
                        <label style="display:flex;align-items:center;gap:10px;margin:0;cursor:pointer;">
                            <input type="checkbox" class="menu-qr-category-check" data-category-id="<?php echo e($categoriaId); ?>" <?php echo e($categoriaChecked ? 'checked' : ''); ?>>
                            <strong><?php echo e($categoria['nombre']); ?></strong>
                        </label>
                        <div class="header-right">
                            <span class="meta"><?php echo e($totalItems); ?> ítems</span>
                            <button type="button" class="menu-qr-toggle" data-category-id="<?php echo e($categoriaId); ?>" aria-label="Mostrar u ocultar categoría" title="Mostrar/ocultar">▾</button>
                        </div>
                    </div>
                    <div class="menu-qr-items">
                        <?php $__currentLoopData = $categoria['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $itemKey = $item['tipo'] . ':' . $item['id'];
                                $itemChecked = in_array($itemKey, $selectedItems, true);
                                $isTopSellersCategory = $categoriaId === 'top-sellers';
                            ?>
                            <label class="menu-qr-item <?php echo e($isTopSellersCategory ? '' : ($itemChecked ? '' : 'is-disabled')); ?>" data-category-id="<?php echo e($categoriaId); ?>" data-item-key="<?php echo e($itemKey); ?>">
                                <?php if(!$isTopSellersCategory): ?>
                                    <div class="checkbox-wrap">
                                        <input type="checkbox" class="menu-qr-item-check" data-category-id="<?php echo e($categoriaId); ?>" value="<?php echo e($itemKey); ?>" <?php echo e($itemChecked ? 'checked' : ''); ?>>
                                    </div>
                                <?php endif; ?>
                                <div class="topline">
                                    <div style="min-width:0;flex:1;">
                                        <p class="name"><?php echo e($item['nombre']); ?></p>
                                        <?php if(!empty($item['descripcion'])): ?>
                                            <p class="desc"><?php echo e($item['descripcion']); ?></p>
                                        <?php endif; ?>
                                        <div class="price">$<?php echo e(number_format($item['precio'], 0, ',', '.')); ?></div>
                                        <?php if($isTopSellersCategory): ?>
                                            <span class="menu-qr-badge ok">Auto por ventas</span>
                                        <?php else: ?>
                                            <span class="menu-qr-badge <?php echo e($item['disponible'] ? 'ok' : 'off'); ?>">
                                                <?php echo e($item['disponible'] ? 'Disponible' : 'No disponible'); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <img src="<?php echo e($item['imagen']); ?>" alt="<?php echo e($item['nombre']); ?>">
                                </div>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="menu-qr-card">
                    <div class="card-body">
                        No hay categorías activas disponibles para configurar.
                    </div>
                </div>
            <?php endif; ?>
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

<?php echo $__env->make('partials.modal_ayuda', ['modulo' => 'config_menu_qr'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\pventa-app\resources\views/configuration/menu_qr.blade.php ENDPATH**/ ?>