jQuery(document).ready(function ($) {
    'use strict';

    // Initialize sortable for default fields
    $('.sk-cfe-fields-list').sortable({
        handle: '.sk-cfe-field-handle',
        placeholder: 'ui-sortable-placeholder',
        helper: 'clone',
        opacity: 0.8,
        start: function (event, ui) {
            ui.placeholder.height(ui.item.height());
        },
        update: function (event, ui) {
            var section = $(this).data('section');
            var fieldOrder = $(this).sortable('toArray', { attribute: 'data-field' });

            // Debug: Log what we're sending
            console.log('SK CFE: Section:', section);
            console.log('SK CFE: Field Order:', fieldOrder);

            // Update order numbers visually
            updateOrderNumbers($(this));

            // Update hidden order inputs
            updateOrderInputs($(this), fieldOrder);

            // Save field order via AJAX
            saveFieldOrder(section, fieldOrder);
        }
    });

    // Update order numbers visually
    function updateOrderNumbers(container) {
        container.find('.sk-cfe-field-item').each(function (index) {
            $(this).find('.order-number').text(index + 1);
        });
    }

    // Update hidden order inputs
    function updateOrderInputs(container, fieldOrder) {
        container.find('.sk-cfe-field-item').each(function (index) {
            var fieldKey = $(this).data('field');
            var orderInput = $(this).find('.sk-cfe-order-field');
            orderInput.val(index + 1);
        });
    }

    // Save field order via AJAX
    function saveFieldOrder(section, fieldOrder) {
        $.ajax({
            url: sk_cfe_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'sk_cfe_reorder_fields',
                nonce: sk_cfe_vars.nonce,
                section: section,
                field_order: fieldOrder
            },
            beforeSend: function () {
                $('.sk-cfe-wrapper').addClass('sk-cfe-loading');
                showSavingMessage();
            },
            success: function (response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    // Refresh the page to show the updated order
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotice(response.data.message || 'Error saving field order.', 'error');
                }
            },
            error: function () {
                showNotice('AJAX error occurred.', 'error');
            },
            complete: function () {
                $('.sk-cfe-wrapper').removeClass('sk-cfe-loading');
                hideSavingMessage();
            }
        });
    }

    // Show saving message
    function showSavingMessage() {
        var savingMsg = $('<div id="sk-cfe-saving" class="notice notice-info"><p>Saving field order...</p></div>');
        $('.wrap h1').after(savingMsg);
    }

    // Hide saving message
    function hideSavingMessage() {
        $('#sk-cfe-saving').fadeOut(function () {
            $(this).remove();
        });
    }

    // Show notice message
    function showNotice(message, type) {
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');

        $('.wrap h1').after(notice);

        setTimeout(function () {
            notice.fadeOut(function () {
                $(this).remove();
            });
        }, 3000);
    }

    // Handle field type change in custom fields
    $('#field_type').on('change', function () {
        var fieldType = $(this).val();
        var optionsGroup = $('.sk-cfe-options-group');

        if (fieldType === 'select' || fieldType === 'radio') {
            optionsGroup.show();
        } else {
            optionsGroup.hide();
        }
    });

    // Add option for select/radio fields
    $(document).on('click', '.sk-cfe-add-option', function () {
        var optionCount = $('.sk-cfe-option-row').length;
        var newOption = $('<div class="sk-cfe-option-row">' +
            '<input type="text" name="field_options[]" placeholder="Option ' + (optionCount + 1) + '" class="regular-text">' +
            '<button type="button" class="button sk-cfe-remove-option">-</button>' +
            '</div>');

        $('#field-options-container').append(newOption);
    });

    // Remove option for select/radio fields
    $(document).on('click', '.sk-cfe-remove-option', function () {
        if ($('.sk-cfe-option-row').length > 1) {
            $(this).closest('.sk-cfe-option-row').remove();
        } else {
            alert('You must have at least one option.');
        }
    });

    // Validate field name (no spaces, lowercase, underscores only)
    $('#field_name').on('input', function () {
        var value = $(this).val();
        var sanitized = value.toLowerCase().replace(/[^a-z0-9_]/g, '_');
        $(this).val(sanitized);
    });

    // Confirm field deletion
    $('.sk-cfe-delete-field').on('click', function (e) {
        if (!confirm(sk_cfe_vars.confirm_delete)) {
            e.preventDefault();
            return false;
        }
    });

    // Toggle field details
    $('.sk-cfe-field-header').on('click', function (e) {
        if (!$(e.target).hasClass('sk-cfe-toggle') && !$(e.target).hasClass('sk-cfe-toggle-slider')) {
            var details = $(this).next('.sk-cfe-field-details');
            details.slideToggle();
        }
    });

    // Auto-save form on checkbox toggle
    $('.sk-cfe-toggle input').on('change', function () {
        var form = $(this).closest('form');
        // Optional: auto-save when toggling
        // form.submit();
    });

    // Initialize tooltips if available
    if (typeof $.fn.tooltipster !== 'undefined') {
        $('.sk-cfe-tooltip').tooltipster({
            theme: 'tooltipster-default',
            position: 'top'
        });
    }

    // Handle file upload preview (if needed)
    $(document).on('change', 'input[type="file"]', function () {
        var input = $(this);
        var file = input[0].files[0];

        if (file) {
            var fileSize = (file.size / 1024 / 1024).toFixed(2); // in MB

            if (fileSize > 5) { // 5MB limit
                alert('File size must be less than 5MB.');
                input.val('');
                return;
            }

            // Show file info
            var fileInfo = $('<div class="sk-cfe-file-info">Selected: ' + file.name + ' (' + fileSize + ' MB)</div>');
            input.closest('.sk-cfe-form-group').append(fileInfo);
        }
    });

    // Bulk actions for default fields
    $('.sk-cfe-bulk-actions').on('change', function () {
        var action = $(this).val();

        if (action === 'enable-all' || action === 'disable-all') {
            var isChecked = action === 'enable-all';
            $('.sk-cfe-toggle input').prop('checked', isChecked);
        }

        $(this).val(''); // Reset selection
    });

    // Search/filter functionality for existing fields
    $('#sk-cfe-search-fields').on('input', function () {
        var searchTerm = $(this).val().toLowerCase();

        $('.sk-cfe-fields-table tbody tr').each(function () {
            var rowText = $(this).text().toLowerCase();

            if (rowText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Export/Import functionality
    $('#sk-cfe-export-settings').on('click', function () {
        $.ajax({
            url: sk_cfe_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'sk_cfe_export_settings',
                nonce: sk_cfe_vars.nonce
            },
            success: function (response) {
                if (response.success) {
                    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data, null, 2));
                    var downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href", dataStr);
                    downloadAnchorNode.setAttribute("download", "sk-cfe-settings.json");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                }
            }
        });
    });

    // Import settings
    $('#sk-cfe-import-file').on('change', function () {
        var file = this.files[0];

        if (file) {
            var reader = new FileReader();

            reader.onload = function (e) {
                try {
                    var settings = JSON.parse(e.target.result);

                    $.ajax({
                        url: sk_cfe_vars.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'sk_cfe_import_settings',
                            nonce: sk_cfe_vars.nonce,
                            settings: settings
                        },
                        success: function (response) {
                            if (response.success) {
                                showNotice('Settings imported successfully!', 'success');
                                setTimeout(function () {
                                    location.reload();
                                }, 1500);
                            } else {
                                showNotice('Error importing settings.', 'error');
                            }
                        }
                    });
                } catch (error) {
                    showNotice('Invalid file format.', 'error');
                }
            };

            reader.readAsText(file);
        }
    });

    // Initialize on page load
    function initializePage() {
        // Trigger field type change to show/hide options
        $('#field_type').trigger('change');

        // Add initial validation
        $('#field_name').trigger('input');

        // Initialize order numbers for all sections
        $('.sk-cfe-fields-list').each(function () {
            updateOrderNumbers($(this));
        });
    }

    initializePage();
});
