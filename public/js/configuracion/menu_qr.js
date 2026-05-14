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

    function getCurrentConfigId() {
        return String($('#menu_qr_current_config_id').val() || '');
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
    var menuConfigs = parseJsonFromNode('menu_qr_configs_json');
    var menuConfigsById = {};
    var EVENT_NS = '.menuQrConfig';
    var isLoadingMenuConfig = false;
    var isSavingMenuQr = false;
    var isDeletingMenuQr = false;

    if ($.isArray(menuConfigs)) {
        $.each(menuConfigs, function (_, config) {
            var configId = String((config && config.id) ? config.id : '');
            if (!configId) {
                return;
            }

            menuConfigsById[configId] = config;
        });
    }

    syncThemePreviewFromInputs();

    function markActiveMenuCard(configId) {
        $('.menu-qr-menu-card').each(function () {
            var $card = $(this);
            var isActive = String($card.data('config-id') || '') === String(configId || '');
            $card.toggleClass('is-active', isActive);
            $card.find('.menu-qr-menu-badge').toggleClass('is-hidden', !isActive);
        });
    }

    function applySelectionStateToItems(selectedCategories, selectedItems) {
        var selectedCategoryMap = {};
        var selectedItemMap = {};

        $.each(selectedCategories || [], function (_, categoryId) {
            selectedCategoryMap[String(categoryId)] = true;
        });

        $.each(selectedItems || [], function (_, itemKey) {
            selectedItemMap[String(itemKey)] = true;
        });

        $('.menu-qr-category-check').each(function () {
            var categoryId = String($(this).data('category-id') || '');
            $(this).prop('checked', !!selectedCategoryMap[categoryId]);
        });

        $('.menu-qr-item-check').each(function () {
            var itemKey = String($(this).val() || '');
            $(this).prop('checked', !!selectedItemMap[itemKey]);
        });

        $('.menu-qr-category').each(function () {
            var $category = $(this);
            var categoryId = String($category.data('category-id') || '');
            var categoryChecked = !!selectedCategoryMap[categoryId];
            var $items = $category.find('.menu-qr-item');
            var $itemChecks = $category.find('.menu-qr-item-check');

            if (!categoryChecked) {
                $items.addClass('is-disabled');
            } else {
                $items.removeClass('is-disabled');
            }

            $itemChecks.each(function () {
                var checked = $(this).is(':checked');
                $(this).closest('.menu-qr-item').toggleClass('is-disabled', !checked || !categoryChecked);
            });

            syncCategoryState(categoryId);
        });
    }

    function applyMenuConfigLocally(configId) {
        var targetId = String(configId || '');
        var config = menuConfigsById[targetId];

        if (!config) {
            return;
        }

        $('#menu_qr_current_config_id').val(targetId);
        $('#menu_qr_name').val(String(config.nombre || ''));
        $('#menu_qr_link').val(String(config.menu_url || ''));
        $('#menu_qr_pdf_url').val(String(config.pdf_url || ''));
        $('#menu_qr_reload_url').val(String(config.reload_url || $('#menu_qr_reload_url').val() || ''));
        $('#menu_qr_design_theme').val(String(config.design_theme || 'clasico_sobrio'));

        if (config.qr_image_url) {
            $('#menu_qr_preview_image').attr('src', String(config.qr_image_url));
        }

        applyTokensToInputs(config.design_tokens || {});
        applyStyleOptionsToInputs(config.design_options || {});
        applySelectionStateToItems(config.selected_categories || [], config.selected_items || []);
        markActiveMenuCard(targetId);
        syncThemePreviewFromInputs();
    }

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

    $(document)
        .off('change' + EVENT_NS, '.menu-qr-category-check')
        .on('change' + EVENT_NS, '.menu-qr-category-check', function () {
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

    $(document)
        .off('click' + EVENT_NS, '.menu-qr-toggle')
        .on('click' + EVENT_NS, '.menu-qr-toggle', function () {
        var categoryId = String($(this).data('category-id'));
        var $category = $('.menu-qr-category[data-category-id="' + categoryId + '"]');
        $category.toggleClass('is-hidden');
    });

    $(document)
        .off('change' + EVENT_NS, '.menu-qr-item-check')
        .on('change' + EVENT_NS, '.menu-qr-item-check', function () {
        var categoryId = String($(this).data('category-id'));
        var $item = $(this).closest('.menu-qr-item');
        if ($(this).is(':checked')) {
            $item.removeClass('is-disabled');
        } else {
            $item.addClass('is-disabled');
        }
        syncCategoryState(categoryId);
    });

    $(document)
        .off('change' + EVENT_NS, '#menu_qr_design_theme')
        .on('change' + EVENT_NS, '#menu_qr_design_theme', function () {
        restorePresetTokens();
    });

    $(document)
        .off('click' + EVENT_NS, '#btnMenuQrThemeReset')
        .on('click' + EVENT_NS, '#btnMenuQrThemeReset', function () {
        restorePresetTokens();
        toastr.info('Colores restablecidos al preset seleccionado.');
    });

    $(document)
        .off('click' + EVENT_NS, '#btnMenuQrThemeToggle')
        .on('click' + EVENT_NS, '#btnMenuQrThemeToggle', function () {
        $('#menu_qr_theme_panel').toggleClass('is-hidden');
    });

    $(document)
        .off('click' + EVENT_NS, '#btnMenuQrStyleToggle')
        .on('click' + EVENT_NS, '#btnMenuQrStyleToggle', function () {
        $('#menu_qr_style_panel').toggleClass('is-hidden');
    });

    $(document)
        .off('click' + EVENT_NS, '#btnMenuQrStyleReset')
        .on('click' + EVENT_NS, '#btnMenuQrStyleReset', function () {
        applyStyleOptionsToInputs(styleDefaults);
        syncThemePreviewFromInputs();
        toastr.info('Tipografía y estilo restablecidos.');
    });

    $(document)
        .off('input' + EVENT_NS + ' change' + EVENT_NS, '.menu-qr-token-input')
        .on('input' + EVENT_NS + ' change' + EVENT_NS, '.menu-qr-token-input', function () {
        syncThemePreviewFromInputs();
    });

    $(document)
        .off('change' + EVENT_NS, '.menu-qr-style-select')
        .on('change' + EVENT_NS, '.menu-qr-style-select', function () {
        syncThemePreviewFromInputs();
    });

    $(document)
        .off('click' + EVENT_NS, '#btnCopyMenuQrLink')
        .on('click' + EVENT_NS, '#btnCopyMenuQrLink', function () {
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

    $(document)
        .off('click' + EVENT_NS, '#btnPrintMenuQrPdf')
        .on('click' + EVENT_NS, '#btnPrintMenuQrPdf', function () {
        var copies = parseInt($('#menu_qr_copias').val(), 10) || 1;
        copies = Math.max(1, Math.min(50, copies));
        var url = $('#menu_qr_pdf_url').val();
        window.open(url + '?copias=' + copies, '_blank');
    });

    function loadMenuConfigFromCard(configId) {
        if (!configId || isLoadingMenuConfig) {
            return;
        }

        isLoadingMenuConfig = true;

        applyMenuConfigLocally(configId);
        isLoadingMenuConfig = false;
    }

    $(document)
        .off('click' + EVENT_NS, '.menu-qr-menu-card[data-config-id]')
        .on('click' + EVENT_NS, '.menu-qr-menu-card[data-config-id]', function () {
        var configId = String($(this).data('config-id') || '');
        if (!configId || $(this).hasClass('is-active')) {
            return;
        }

        loadMenuConfigFromCard(configId);
    });

    $(document)
        .off('keydown' + EVENT_NS, '.menu-qr-menu-card[data-config-id]')
        .on('keydown' + EVENT_NS, '.menu-qr-menu-card[data-config-id]', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        event.preventDefault();
        var configId = String($(this).data('config-id') || '');
        if (!configId || $(this).hasClass('is-active')) {
            return;
        }

        loadMenuConfigFromCard(configId);
    });

    function submitMenuQr(createNew) {
        if (isSavingMenuQr) {
            return;
        }

        var selectedCategories = [];
        var selectedItems = [];

        $('.menu-qr-category-check:checked').each(function () {
            selectedCategories.push($(this).data('category-id'));
        });

        $('.menu-qr-item-check:checked').each(function () {
            selectedItems.push($(this).val());
        });

        isSavingMenuQr = true;

        $.ajax({
            url: $('#menu_qr_save_url').val(),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('#token').val(),
                config_id: getCurrentConfigId(),
                create_new: createNew ? 1 : 0,
                nombre: $('#menu_qr_name').val(),
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
                $('#menu_qr_current_config_id').val(response.config_id || $('#menu_qr_current_config_id').val());
                $('#menu_qr_reload_url').val(response.reload_url || $('#menu_qr_reload_url').val());
                $('#contenido').load(response.reload_url || $('#menu_qr_reload_url').val());
            },
            error: function (xhr) {
                var message = 'Error al guardar la configuración del menú QR';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            },
            complete: function () {
                isSavingMenuQr = false;
            }
        });
    }

    function executeDeleteMenuQr(configId) {
        if (isDeletingMenuQr) {
            return;
        }

        isDeletingMenuQr = true;

        $.ajax({
            url: $('#menu_qr_delete_url').val(),
            type: 'POST',
            dataType: 'json',
            data: {
                _token: $('#token').val(),
                config_id: configId
            },
            success: function (response) {
                if (!response || !response.success) {
                    toastr.error((response && response.message) ? response.message : 'No se pudo eliminar el menú QR');
                    return;
                }

                toastr.success(response.message || 'Menú QR eliminado');

                var reloadUrl = response.reload_url || $('#menu_qr_reload_url').val();
                if ($('#contenido').length) {
                    $('#contenido').load(reloadUrl);
                    return;
                }

                window.location.href = reloadUrl;
            },
            error: function (xhr) {
                var message = 'Error al eliminar el menú QR';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            },
            complete: function () {
                isDeletingMenuQr = false;
            }
        });
    }

    function confirmDeleteMenuQr(configId, configName) {
        if (!configId || isDeletingMenuQr) {
            return;
        }

        Swal.fire({
            title: 'Eliminar menú QR',
            text: '¿Estás seguro de eliminar el menú ' + String(configName || 'seleccionado') + '?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                executeDeleteMenuQr(configId);
                return;
            }

            toastr.info('Eliminación cancelada');
        });
    }

    $(document)
        .off('click' + EVENT_NS, '#btnSaveMenuQr')
        .on('click' + EVENT_NS, '#btnSaveMenuQr', function () {
        submitMenuQr(false);
    });

    $(document)
        .off('click' + EVENT_NS, '#btnDuplicateMenuQr')
        .on('click' + EVENT_NS, '#btnDuplicateMenuQr', function () {
        submitMenuQr(true);
    });

    $(document)
        .off('click' + EVENT_NS, '.menu-qr-menu-delete-btn')
        .on('click' + EVENT_NS, '.menu-qr-menu-delete-btn', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if ($(this).is(':disabled')) {
            return;
        }

        var configId = String($(this).data('delete-config-id') || '');
        var configName = String($(this).data('delete-config-name') || 'este menú');
        if (!configId) {
            return;
        }

        confirmDeleteMenuQr(configId, configName);
    });
});