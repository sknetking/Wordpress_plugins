jQuery(document).ready(function ($) {
    // Enhanced variation gallery functionality
    var variationForm = $('form.variations_form');

    // Handle swatch clicks for all types
    $(document).on('click', '.wcvg-swatch', function (e) {
        e.preventDefault();

        var $swatch = $(this);
        var value = $swatch.data('value');
        var $container = $swatch.closest('.wcvg-attribute-group');
        var attributeName = $container.data('attribute');

        // Find the correct select field (try multiple formats)
        var $select = $('#' + attributeName);
        if ($select.length === 0) {
            $select = $('#pa_' + attributeName);
        }
        if ($select.length === 0) {
            $select = $('[name="attribute_' + attributeName + '"]');
        }

        // Remove active class from all swatches in this container
        $container.find('.wcvg-swatch').removeClass('wcvg-active');

        // Add active class to clicked swatch
        $swatch.addClass('wcvg-active');

        // Update the hidden select field
        $select.val(value).trigger('change');

        console.log('Selected:', attributeName, value, 'Select field:', $select.attr('id'));

        // Trigger WooCommerce variation update
        setTimeout(function () {
            checkAndEnableAddToCart();
        }, 100);
    });

    // Handle radio button changes
    $(document).on('change', '.wcvg-radio-swatch input[type="radio"]', function () {
        var $radio = $(this);
        var value = $radio.val();
        var $container = $radio.closest('.wcvg-attribute-group');
        var attributeName = $container.data('attribute');

        // Find the correct select field
        var $select = $('#' + attributeName);
        if ($select.length === 0) {
            $select = $('#pa_' + attributeName);
        }
        if ($select.length === 0) {
            $select = $('[name="attribute_' + attributeName + '"]');
        }

        // Remove active class from all swatches in this container
        $container.find('.wcvg-swatch').removeClass('wcvg-active');

        // Add active class to parent label
        $radio.closest('.wcvg-radio-swatch').addClass('wcvg-active');

        // Update the hidden select field
        $select.val(value).trigger('change');

        console.log('Radio selected:', attributeName, value);

        // Trigger WooCommerce variation update
        setTimeout(function () {
            checkAndEnableAddToCart();
        }, 100);
    });

    // Reset variations functionality
    $(document).on('click', '.wcvg-reset', function (e) {
        e.preventDefault();

        // Remove all active classes
        $('.wcvg-swatch').removeClass('wcvg-active');

        // Reset all select fields
        $('.variations select').val('').trigger('change');

        // Reset variation form
        variationForm.trigger('reset_data');

        // Disable add to cart button
        $('.single_add_to_cart_button').addClass('disabled').prop('disabled', true);

        console.log('Variations reset');
    });

    // Function to check and enable Add to Cart button
    function checkAndEnableAddToCart() {
        var allSelected = true;
        var selectedValues = {};

        // Check all variation select fields
        $('.variations select').each(function () {
            var $select = $(this);
            var name = $select.attr('name');
            var value = $select.val();

            if (!value) {
                allSelected = false;
                return false; // break the loop
            }

            selectedValues[name] = value;
        });

        console.log('All selected:', allSelected, 'Selected values:', selectedValues);

        if (allSelected && typeof wcvg_variation_data !== 'undefined') {
            // Find matching variation
            var matchingVariation = null;

            for (var i = 0; i < wcvg_variation_data.length; i++) {
                var variation = wcvg_variation_data[i];
                var match = true;

                for (var attr in selectedValues) {
                    if (variation.attributes[attr] !== selectedValues[attr]) {
                        match = false;
                        break;
                    }
                }

                if (match) {
                    matchingVariation = variation;
                    break;
                }
            }

            if (matchingVariation) {
                console.log('Found matching variation:', matchingVariation);

                // Update price
                if (matchingVariation.price_html) {
                    $('.woocommerce-variation-price').html(matchingVariation.price_html);
                }

                // Update description
                if (matchingVariation.variation_description) {
                    $('.woocommerce-variation-description').html(matchingVariation.variation_description);
                }

                // Enable add to cart button if variation is purchasable and in stock
                if (matchingVariation.is_purchasable && matchingVariation.is_in_stock) {
                    $('.single_add_to_cart_button').removeClass('disabled').prop('disabled', false);
                    console.log('Add to cart button ENABLED');
                } else {
                    $('.single_add_to_cart_button').addClass('disabled').prop('disabled', true);
                    console.log('Add to cart button DISABLED - not purchasable or out of stock');
                }

                // Trigger WooCommerce events
                variationForm.trigger('found_variation', [matchingVariation]);
            } else {
                console.log('No matching variation found');
                $('.woocommerce-variation-price').html('');
                $('.woocommerce-variation-description').html('');
                $('.single_add_to_cart_button').addClass('disabled').prop('disabled', true);
                variationForm.trigger('variation_not_found');
            }
        } else {
            console.log('Not all attributes selected or variation data not available');
            $('.single_add_to_cart_button').addClass('disabled').prop('disabled', true);
        }
    }

    // Handle WooCommerce variation found event
    variationForm.on('found_variation', function (event, variation) {
        console.log('WooCommerce found_variation triggered:', variation);
    });

    // Handle variation not found
    variationForm.on('variation_not_found', function () {
        console.log('WooCommerce variation_not_found triggered');
        $('.woocommerce-variation-price').html('');
        $('.woocommerce-variation-description').html('');
        $('.single_add_to_cart_button').addClass('disabled').prop('disabled', true);
    });

    // Initialize on page load
    function initializeVariations() {
        // Check if any variations are already selected
        setTimeout(function () {
            checkAndEnableAddToCart();
        }, 500);
    }

    // Initialize after WooCommerce variations are loaded
    $(document).on('wc_variation_form', function () {
        initializeVariations();
    });

    // Also initialize immediately if variations are already loaded
    if (variationForm.length && variationForm.data('product_variations')) {
        initializeVariations();
    }
});