$(document).ready(function () {
    function syncCategoryState(categoryId) {
        var $category = $('.menu-qr-category[data-category-id="' + categoryId + '"]');
        var checkedItems = $category.find('.menu-qr-item-check:checked').length;
        var totalItems = $category.find('.menu-qr-item-check').length;
        var $categoryCheck = $('.menu-qr-category-check[data-category-id="' + categoryId + '"]');

        if (checkedItems > 0) {
            $categoryCheck.prop('checked', true);
            $category.removeClass('is-hidden');
        } else if (checkedItems === 0 && totalItems > 0) {
            $categoryCheck.prop('checked', false);
            $category.addClass('is-hidden');
        }
    }

    $(document).on('change', '.menu-qr-category-check', function () {
        var categoryId = String($(this).data('category-id'));
        var checked = $(this).is(':checked');
        var $category = $('.menu-qr-category[data-category-id="' + categoryId + '"]');
        var $itemChecks = $category.find('.menu-qr-item-check');
        var $items = $category.find('.menu-qr-item');

        if (checked) {
            $category.removeClass('is-hidden');
            $itemChecks.prop('checked', true);
            $items.removeClass('is-disabled');
        } else {
            $category.addClass('is-hidden');
            $itemChecks.prop('checked', false);
            $items.addClass('is-disabled');
        }
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
                selected_items: selectedItems
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