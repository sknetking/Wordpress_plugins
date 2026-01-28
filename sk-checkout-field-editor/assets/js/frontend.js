jQuery(document).ready(function ($) {
    'use strict';

    // Add frontend variables if not already defined
    if (typeof sk_cfe_frontend_vars === 'undefined') {
        sk_cfe_frontend_vars = {
            ajax_url: wc_checkout_params.ajax_url || ajaxurl,
            nonce: ''
        };
    }

    // Handle file uploads
    $(document).on('change', '.sk-cfe-file-field input[type="file"]', function () {
        var input = $(this);
        var file = this.files[0];
        var fieldWrapper = input.closest('.sk-cfe-custom-field');

        if (file) {
            // Validate file size (5MB limit)
            var maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                showError(fieldWrapper, 'File size must be less than 5MB.');
                input.val('');
                return;
            }

            // Validate file type
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'text/plain'];
            if (allowedTypes.indexOf(file.type) === -1) {
                showError(fieldWrapper, 'Invalid file type. Allowed types: JPG, PNG, GIF, PDF, DOC, TXT.');
                input.val('');
                return;
            }

            // Show loading state
            fieldWrapper.addClass('loading');

            // Create FormData for upload
            var formData = new FormData();
            formData.append('file', file);
            formData.append('field_id', input.attr('name'));
            formData.append('action', 'sk_cfe_upload_file');
            formData.append('nonce', sk_cfe_frontend_vars.nonce);

            // Upload file via AJAX
            $.ajax({
                url: sk_cfe_frontend_vars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    fieldWrapper.removeClass('loading');

                    if (response.success) {
                        showFilePreview(fieldWrapper, response.data);
                        clearError(fieldWrapper);
                    } else {
                        showError(fieldWrapper, response.data.message || 'Upload failed.');
                    }
                },
                error: function () {
                    fieldWrapper.removeClass('loading');
                    showError(fieldWrapper, 'Upload failed. Please try again.');
                }
            });
        }
    });

    // Show file preview
    function showFilePreview(fieldWrapper, fileData) {
        var preview = $('<div class="sk-cfe-file-preview">' +
            '<div class="file-name">' + fileData.filename + '</div>' +
            '<div class="file-size">Uploaded successfully</div>' +
            '<button type="button" class="sk-cfe-remove-file">Remove File</button>' +
            '<input type="hidden" name="' + fieldWrapper.find('input[type="file"]').attr('name') + '_attachment_id" value="' + fileData.attachment_id + '">' +
            '</div>');

        // Remove existing preview
        fieldWrapper.find('.sk-cfe-file-preview').remove();

        // Add new preview
        fieldWrapper.append(preview);
    }

    // Remove uploaded file
    $(document).on('click', '.sk-cfe-remove-file', function () {
        var button = $(this);
        var fieldWrapper = button.closest('.sk-cfe-custom-field');

        // Remove preview
        button.closest('.sk-cfe-file-preview').remove();

        // Clear file input
        fieldWrapper.find('input[type="file"]').val('');

        // Remove hidden field with attachment ID
        fieldWrapper.find('input[type="hidden"]').remove();
    });

    // Show error message
    function showError(fieldWrapper, message) {
        clearError(fieldWrapper);

        var error = $('<div class="error-message">' + message + '</div>');
        fieldWrapper.append(error);
        fieldWrapper.addClass('woocommerce-invalid');
    }

    // Clear error message
    function clearError(fieldWrapper) {
        fieldWrapper.find('.error-message').remove();
        fieldWrapper.removeClass('woocommerce-invalid');
    }

    // Handle conditional fields
    $(document).on('change', '.sk-cfe-conditional-trigger', function () {
        var trigger = $(this);
        var condition = trigger.data('condition');
        var value = trigger.val();
        var targetFields = $('.sk-cfe-conditional-field[data-condition="' + condition + '"]');

        targetFields.each(function () {
            var target = $(this);
            var requiredValue = target.data('required-value');

            if (value === requiredValue) {
                target.addClass('show');
                target.find('input, select, textarea').prop('disabled', false);
            } else {
                target.removeClass('show');
                target.find('input, select, textarea').prop('disabled', true);
            }
        });
    });

    // Initialize conditional fields
    $('.sk-cfe-conditional-trigger').trigger('change');

    // Handle field validation on blur
    $(document).on('blur', '.sk-cfe-custom-field input, .sk-cfe-custom-field textarea, .sk-cfe-custom-field select', function () {
        var field = $(this);
        var fieldWrapper = field.closest('.sk-cfe-custom-field');
        var fieldType = field.attr('type');
        var value = field.val();
        var isRequired = field.prop('required');

        // Clear previous errors
        clearError(fieldWrapper);

        // Check if required field is empty
        if (isRequired && !value.trim()) {
            showError(fieldWrapper, 'This field is required.');
            return;
        }

        // Validate specific field types
        if (value.trim()) {
            switch (fieldType) {
                case 'email':
                    if (!isValidEmail(value)) {
                        showError(fieldWrapper, 'Please enter a valid email address.');
                    }
                    break;

                case 'tel':
                    if (!isValidPhone(value)) {
                        showError(fieldWrapper, 'Please enter a valid phone number.');
                    }
                    break;

                case 'number':
                    if (!isValidNumber(value)) {
                        showError(fieldWrapper, 'Please enter a valid number.');
                    }
                    break;
            }
        }
    });

    // Email validation
    function isValidEmail(email) {
        var pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }

    // Phone validation
    function isValidPhone(phone) {
        var pattern = /^[\d\s\-\+\(\)]+$/;
        return pattern.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }

    // Number validation
    function isValidNumber(number) {
        return !isNaN(number) && isFinite(number);
    }

    // Auto-format phone numbers
    $(document).on('input', '.sk-cfe-custom-field input[type="tel"]', function () {
        var value = $(this).val().replace(/\D/g, '');
        var formattedValue = '';

        if (value.length > 0) {
            if (value.length <= 3) {
                formattedValue = value;
            } else if (value.length <= 6) {
                formattedValue = value.slice(0, 3) + '-' + value.slice(3);
            } else if (value.length <= 10) {
                formattedValue = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
            } else {
                formattedValue = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
            }
        }

        $(this).val(formattedValue);
    });

    // Handle character limits
    $(document).on('input', '.sk-cfe-custom-field[data-max-length] input, .sk-cfe-custom-field[data-max-length] textarea', function () {
        var field = $(this);
        var fieldWrapper = field.closest('.sk-cfe-custom-field');
        var maxLength = parseInt(fieldWrapper.data('max-length'));
        var currentLength = field.val().length;

        // Remove character counter if it exists
        fieldWrapper.find('.sk-cfe-char-counter').remove();

        // Add character counter
        var counter = $('<div class="sk-cfe-char-counter">Characters: ' + currentLength + '/' + maxLength + '</div>');
        fieldWrapper.append(counter);

        // Truncate if over limit
        if (currentLength > maxLength) {
            field.val(field.val().substring(0, maxLength));
            counter.text('Characters: ' + maxLength + '/' + maxLength);
            counter.css('color', '#d63638');
        } else if (currentLength > maxLength * 0.9) {
            counter.css('color', '#f57c00');
        } else {
            counter.css('color', '#666');
        }
    });

    // Show/hide password
    $(document).on('click', '.sk-cfe-toggle-password', function () {
        var button = $(this);
        var passwordField = button.prev('input[type="password"]');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            button.text('Hide');
        } else {
            passwordField.attr('type', 'password');
            button.text('Show');
        }
    });

    // Add password toggle buttons
    $('.sk-cfe-custom-field input[type="password"]').each(function () {
        var toggleButton = $('<button type="button" class="sk-cfe-toggle-password">Show</button>');
        $(this).after(toggleButton);
    });

    // Handle form submission
    $('form.woocommerce-checkout').on('submit', function () {
        var form = $(this);
        var hasErrors = false;

        // Validate all custom fields
        $('.sk-cfe-custom-field input, .sk-cfe-custom-field textarea, .sk-cfe-custom-field select').each(function () {
            var field = $(this);
            var fieldWrapper = field.closest('.sk-cfe-custom-field');

            // Trigger blur validation
            field.trigger('blur');

            // Check for errors
            if (fieldWrapper.hasClass('woocommerce-invalid')) {
                hasErrors = true;
            }
        });

        if (hasErrors) {
            // Scroll to first error
            var firstError = $('.sk-cfe-custom-field.woocommerce-invalid').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
            }

            return false;
        }
    });

    // Initialize tooltips
    $('.sk-cfe-tooltip').each(function () {
        var tooltip = $(this);
        var tooltipText = tooltip.data('tooltip');

        if (tooltipText) {
            tooltip.tooltipster({
                theme: 'tooltipster-default',
                position: 'top',
                content: tooltipText
            });
        }
    });

    // Progress indicator for multi-step forms
    function updateProgress() {
        var totalFields = $('.sk-cfe-custom-field').length;
        var filledFields = $('.sk-cfe-custom-field input, .sk-cfe-custom-field textarea, .sk-cfe-custom-field select').filter(function () {
            return $(this).val().trim() !== '';
        }).length;

        var progress = (filledFields / totalFields) * 100;

        $('.sk-cfe-progress-bar').css('width', progress + '%');
    }

    // Update progress on field change
    $(document).on('input change', '.sk-cfe-custom-field input, .sk-cfe-custom-field textarea, .sk-cfe-custom-field select', function () {
        updateProgress();
    });

    // Initialize progress
    updateProgress();
});
