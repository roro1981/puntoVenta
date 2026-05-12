$(document).ready(function () {
    var designThemes = {};

    function parseJsonFromNode(id) {
        var node = document.getElementById(id);
        if (!node) {
            return {};
        }

        try {
            return JSON.parse(node.textContent || '{}');
        } catch (e) {
            return {};
        }
    }

    function getCurrentThemeKey() {
        return String($('#menu_qr_design_theme').val() || '');
    }

    function getThemeTokens(themeKey) {
        if (!themeKey || !designThemes[themeKey]) {
            return {};
        }

        return designThemes[themeKey];
    }

    function collectDesignOptionsFromInputs() {
        var options = {};

        $('.menu-qr-style-select').each(function () {
            var key = String($(this).data('option') || '');
            var value = String($(this).val() || '');
            if (!key || !value) {
                return;
            }

            options[key] = value;
        });

        return options;
    }

    function applyStyleOptionsToInputs(options) {
        $('.menu-qr-style-select').each(function () {
            var key = String($(this).data('option') || '');
            if (!key || typeof options[key] === 'undefined') {
                return;
            }
            $(this).val(String(options[key]));
        });
    }

    function collectDesignTokensFromInputs() {
        var tokens = {};

        $('.menu-qr-token-input').each(function () {
            var token = String($(this).data('token') || '');
            var value = String($(this).val() || '').toUpperCase();

            if (!token || !/^#[0-9A-F]{6}$/.test(value)) {
                return;
            }

            tokens[token] = value;
        });

        return tokens;
    }

    function applyTokensToInputs(tokens) {
        $('.menu-qr-token-input').each(function () {
            var token = String($(this).data('token') || '');
            if (!token || !tokens[token]) {
                return;
            }
            $(this).val(String(tokens[token]).toUpperCase());
        });
    }

    function hexToRgb(hex) {
        var normalized = String(hex || '').replace('#', '');
        if (normalized.length !== 6) {
            return null;
        }

        return {
            r: parseInt(normalized.substring(0, 2), 16),
            g: parseInt(normalized.substring(2, 4), 16),
            b: parseInt(normalized.substring(4, 6), 16)
        };
    }

    function luminance(rgb) {
        function channel(v) {
            var srgb = v / 255;
            return srgb <= 0.03928 ? srgb / 12.92 : Math.pow((srgb + 0.055) / 1.055, 2.4);
        }

        return (0.2126 * channel(rgb.r)) + (0.7152 * channel(rgb.g)) + (0.0722 * channel(rgb.b));
    }

    function contrastRatio(hexA, hexB) {
        var rgbA = hexToRgb(hexA);
        var rgbB = hexToRgb(hexB);

        if (!rgbA || !rgbB) {
            return 1;
        }

        var lumA = luminance(rgbA);
        var lumB = luminance(rgbB);
        var lighter = Math.max(lumA, lumB);
        var darker = Math.min(lumA, lumB);

        return (lighter + 0.05) / (darker + 0.05);
    }

    function paintThemePreview(tokens) {
        var $preview = $('#menu_qr_theme_preview');
        if (!$preview.length) {
            return;
        }

        $preview.css('--preview-bg', tokens['bg'] || '#F3F4F6');
        $preview.css('--preview-surface', tokens['surface'] || '#FFFFFF');
        $preview.css('--preview-border', tokens['border'] || '#E5E7EB');
        $preview.css('--preview-text', tokens['text'] || '#111827');
        $preview.css('--preview-muted', tokens['muted'] || '#6B7280');
        $preview.css('--preview-accent', tokens['accent'] || '#334155');
        $preview.css('--preview-popular-start', tokens['popular-start'] || '#EA580C');
        $preview.css('--preview-popular-end', tokens['popular-end'] || '#DC2626');

        var textRatio = contrastRatio(tokens['text'], tokens['surface']);
        var accentRatio = contrastRatio(tokens['accent'], tokens['surface']);
        var minRatio = Math.min(textRatio, accentRatio);
        var $contrast = $('#menu_qr_theme_contrast');

        if (!$contrast.length) {
            return;
        }

        if (minRatio >= 4.5) {
            $contrast
                .removeClass('warn')
                .addClass('ok')
                .text('Contraste correcto: lectura recomendada (ratio mínimo ' + minRatio.toFixed(2) + ').');
        } else {
            $contrast
                .removeClass('ok')
                .addClass('warn')
                .text('Contraste bajo: ajusta Texto principal o Color acento para mejorar legibilidad (ratio mínimo ' + minRatio.toFixed(2) + ').');
        }
    }

    function paintStylePreview(options) {
        var $preview = $('#menu_qr_theme_preview');
        if (!$preview.length) {
            return;
        }

        var titleFonts = {
            elegante_serif: 'Georgia, "Times New Roman", Times, serif',
            moderna_sans: '"Trebuchet MS", "Segoe UI", sans-serif',
            display_condensada: '"Arial Narrow", "Franklin Gothic Medium", Arial, sans-serif'
        };
        var bodyFonts = {
            limpia_humanista: '"Segoe UI", "Helvetica Neue", Arial, sans-serif',
            sans_neutra: 'Verdana, "Trebuchet MS", sans-serif',
            serif_clasica: 'Cambria, Georgia, serif'
        };
        var radiusMap = { sm: '10px', md: '14px', lg: '18px' };
        var shadowMap = {
            none: 'none',
            soft: '0 8px 18px rgba(15, 23, 42, 0.10)',
            strong: '0 14px 24px rgba(15, 23, 42, 0.16)'
        };

        var titleFont = titleFonts[options.font_title] || titleFonts.elegante_serif;
        var bodyFont = bodyFonts[options.font_body] || bodyFonts.limpia_humanista;
        var radius = radiusMap[options.radius_scale] || radiusMap.md;
        var shadow = shadowMap[options.shadow_level] || shadowMap.soft;

        $preview.css('--preview-radius', radius);
        $preview.css('--preview-shadow', shadow);
        $preview.css('--preview-title-font', titleFont);
        $preview.css('--preview-body-font', bodyFont);

        paintAnimationPreview(options.animation_style || 'stagger');
    }

    function paintAnimationPreview(animationStyle) {
        var $items = $('#menu_qr_anim_preview .menu-qr-anim-preview-item');
        if (!$items.length) {
            return;
        }

        $items.each(function (index) {
            var $item = $(this);
            $item.removeClass('anim-fade anim-stagger');
            $item.css('animation-delay', '0ms');

            // Reinicia la animacion para que se vea en cada cambio.
            this.offsetWidth;

            if (animationStyle === 'none') {
                return;
            }

            if (animationStyle === 'fade') {
                $item.addClass('anim-fade');
                return;
            }

            $item.addClass('anim-stagger');
            $item.css('animation-delay', String(index * 70) + 'ms');
        });
    }

    function syncThemePreviewFromInputs() {
        var tokens = collectDesignTokensFromInputs();
        paintThemePreview(tokens);
        paintStylePreview(collectDesignOptionsFromInputs());
    }

    function restorePresetTokens() {
        var themeKey = getCurrentThemeKey();
        applyTokensToInputs(getThemeTokens(themeKey));
        syncThemePreviewFromInputs();
    }

    designThemes = parseJsonFromNode('menu_qr_themes_json');
    var styleDefaults = parseJsonFromNode('menu_qr_style_defaults_json');
    syncThemePreviewFromInputs();

    function syncCategoryState(categoryId) {
        var $category = $('.menu-qr-category[data-category-id="' + categoryId + '"]');
        var checkedItems = $category.find('.menu-qr-item-check:checked').length;
        var totalItems = $category.find('.menu-qr-item-check').length;
        var $categoryCheck = $('.menu-qr-category-check[data-category-id="' + categoryId + '"]');

        if (checkedItems > 0) {
            $categoryCheck.prop('checked', true);
        } else if (checkedItems === 0 && totalItems > 0) {
            $categoryCheck.prop('checked', false);
        }
    }

    $(document).on('change', '.menu-qr-category-check', function () {
        var categoryId = String($(this).data('category-id'));
        var checked = $(this).is(':checked');
        var $category = $('.menu-qr-category[data-category-id="' + categoryId + '"]');
        var $itemChecks = $category.find('.menu-qr-item-check');
        var $items = $category.find('.menu-qr-item');

        if (checked) {
            $itemChecks.prop('checked', true);
            $items.removeClass('is-disabled');
        } else {
            $itemChecks.prop('checked', false);
            $items.addClass('is-disabled');
        }
    });

    $(document).on('click', '.menu-qr-toggle', function () {
        var categoryId = String($(this).data('category-id'));
        var $category = $('.menu-qr-category[data-category-id="' + categoryId + '"]');
        $category.toggleClass('is-hidden');
    });

    $(document).on('change', '.menu-qr-item-check', function () {
        var categoryId = String($(this).data('category-id'));
        var $item = $(this).closest('.menu-qr-item');
        if ($(this).is(':checked')) {
            $item.removeClass('is-disabled');
        } else {
            $item.addClass('is-disabled');
        }
        syncCategoryState(categoryId);
    });

    $('#menu_qr_design_theme').on('change', function () {
        restorePresetTokens();
    });

    $('#btnMenuQrThemeReset').on('click', function () {
        restorePresetTokens();
        toastr.info('Colores restablecidos al preset seleccionado.');
    });

    $('#btnMenuQrThemeToggle').on('click', function () {
        $('#menu_qr_theme_panel').toggleClass('is-hidden');
    });

    $('#btnMenuQrStyleToggle').on('click', function () {
        $('#menu_qr_style_panel').toggleClass('is-hidden');
    });

    $('#btnMenuQrStyleReset').on('click', function () {
        applyStyleOptionsToInputs(styleDefaults);
        syncThemePreviewFromInputs();
        toastr.info('Tipografía y estilo restablecidos.');
    });

    $(document).on('input change', '.menu-qr-token-input', function () {
        syncThemePreviewFromInputs();
    });

    $(document).on('change', '.menu-qr-style-select', function () {
        syncThemePreviewFromInputs();
    });

    $('#btnCopyMenuQrLink').on('click', function () {
        var link = $('#menu_qr_link').val();
        if (!link) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function () {
                toastr.success('Enlace copiado al portapapeles');
            }).catch(function () {
                $('#menu_qr_link').trigger('select');
                document.execCommand('copy');
                toastr.success('Enlace copiado al portapapeles');
            });
            return;
        }

        $('#menu_qr_link').trigger('select');
        document.execCommand('copy');
        toastr.success('Enlace copiado al portapapeles');
    });

    $('#btnPrintMenuQrPdf').on('click', function () {
        var copies = parseInt($('#menu_qr_copias').val(), 10) || 1;
        copies = Math.max(1, Math.min(50, copies));
        var url = $('#menu_qr_pdf_url').val();
        window.open(url + '?copias=' + copies, '_blank');
    });

    $('#btnSaveMenuQr').on('click', function () {
        var selectedCategories = [];
        var selectedItems = [];

        $('.menu-qr-category-check:checked').each(function () {
            selectedCategories.push($(this).data('category-id'));
        });

        $('.menu-qr-item-check:checked').each(function () {
            selectedItems.push($(this).val());
        });

        $.ajax({
            url: $('#menu_qr_save_url').val(),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('#token').val(),
                selected_categories: selectedCategories,
                selected_items: selectedItems,
                design_theme: $('#menu_qr_design_theme').val(),
                design_tokens: collectDesignTokensFromInputs(),
                design_options: collectDesignOptionsFromInputs()
            },
            success: function (response) {
                if (!response || !response.success) {
                    toastr.error((response && response.message) ? response.message : 'No se pudo guardar el menú QR');
                    return;
                }

                toastr.success(response.message || 'Configuración guardada');
                $('#menu_qr_link').val(response.menu_url || $('#menu_qr_link').val());
                $('#contenido').load($('#menu_qr_reload_url').val());
            },
            error: function (xhr) {
                var message = 'Error al guardar la configuración del menú QR';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    });
});