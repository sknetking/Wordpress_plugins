jQuery(document).ready(function ($) {
    'use strict';

    // Global variables
    let currentPage = 1;
    let isLoading = false;
    let filterTimeout;

    // Initialize filter functionality
    function initProductFilter() {
        bindEvents();
        loadInitialProducts();
    }

    // Bind all events
    function bindEvents() {
        // Filter form changes
        $('#wc-product-filter-form input, #wc-product-filter-form select').on('change', function () {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                currentPage = 1;
                filterProducts();
            }, 300);
        });

        // Price slider
        $('#price_range').on('input', function () {
            const value = $(this).val();
            $('#max_price').val(value);
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function () {
                currentPage = 1;
                filterProducts();
            }, 300);
        });

        // Apply filters button
        $('#wc-apply-filters').on('click', function () {
            currentPage = 1;
            filterProducts();
        });

        // Clear filters button
        $('#wc-clear-filters').on('click', function () {
            clearAllFilters();
        });

        // Sort change
        $('#wc-sort-products').on('change', function () {
            currentPage = 1;
            filterProducts();
        });

        // Pagination clicks
        $(document).on('click', '.wc-pagination-btn', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('wc-active') && !$(this).is(':disabled')) {
                currentPage = $(this).data('page');
                filterProducts();
                // Scroll to top of products
                $('html, body').animate({
                    scrollTop: $('.wc-filter-content').offset().top - 100
                }, 500);
            }
        });

        // Mobile filter toggle
        $('.wc-filter-toggle').on('click', function () {
            $('.wc-filter-sidebar').toggleClass('wc-collapsed');
        });

        // Price input validation
        $('#min_price, #max_price').on('input', function () {
            const min = parseFloat($('#min_price').val()) || 0;
            const max = parseFloat($('#max_price').val()) || 0;

            if (min > max && max > 0) {
                $('#min_price').val(max);
            }
        });
    }

    // Load initial products
    function loadInitialProducts() {
        // Set responsive columns based on screen size
        updateResponsiveColumns();
        filterProducts();
    }

    // Update responsive columns
    function updateResponsiveColumns() {
        const $productsGrid = $('.wc-products-grid ul.products');
        if (!$productsGrid.length) return;

        let columns = wc_filter_params.desktop_columns || 4;

        if (window.innerWidth <= 480) {
            columns = 1;
        } else if (window.innerWidth <= 768) {
            columns = wc_filter_params.mobile_columns || 2;
        } else if (window.innerWidth <= 1024) {
            columns = wc_filter_params.tablet_columns || 3;
        }

        $productsGrid.css('grid-template-columns', `repeat(${columns}, 1fr)`);
    }

    // Handle window resize
    $(window).on('resize', function () {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function () {
            updateResponsiveColumns();
        }, 250);
    });

    // Filter products via AJAX
    function filterProducts() {
        if (isLoading) return;

        isLoading = true;
        showLoading();

        const formData = $('#wc-product-filter-form').serialize();
        const sort = $('#wc-sort-products').val();

        $.ajax({
            url: wc_filter_params.ajax_url,
            type: 'POST',
            data: formData + '&action=wc_filter_products&sort=' + sort + '&page=' + currentPage + '&posts_per_page=12&filter_nonce=' + wc_filter_params.nonce,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#wc-filtered-products').html(response.data.products);
                    updateResultsCount(response.data.count);
                    updateClearButton();
                    updateURL();
                } else {
                    showError(response.data.message || wc_filter_params.error_text);
                }
            },
            error: function (xhr, status, error) {
                showError(wc_filter_params.error_text);
            },
            complete: function () {
                isLoading = false;
            }
        });
    }

    // Show loading state
    function showLoading() {
        const loadingHtml = `
            <div class="wc-loading">
                <div class="wc-spinner"></div>
                <p>${wc_filter_params.loading_text}</p>
            </div>
        `;
        $('#wc-filtered-products').html(loadingHtml);
    }

    // Show error message
    function showError(message) {
        const errorHtml = `
            <div class="wc-no-products">
                <p>${message}</p>
            </div>
        `;
        $('#wc-filtered-products').html(errorHtml);
    }

    // Update results count
    function updateResultsCount(count) {
        const countText = count === 1 ?
            wc_filter_params.single_product_text || '1 product found' :
            `${count} ${wc_filter_params.products_text || 'products found'}`;
        $('.wc-results-count').text(countText);
    }

    // Update clear button visibility
    function updateClearButton() {
        const hasActiveFilters = $('#wc-product-filter-form input[type="checkbox"]:checked, #wc-product-filter-form input[type="number"]').filter(function () {
            return $(this).val() !== '';
        }).length > 0;

        if (hasActiveFilters) {
            $('#wc-clear-filters').show();
        } else {
            $('#wc-clear-filters').hide();
        }
    }

    // Clear all filters
    function clearAllFilters() {
        $('#wc-product-filter-form')[0].reset();
        $('#wc-clear-filters').hide();
        currentPage = 1;
        filterProducts();
    }

    // Update browser URL with filter parameters
    function updateURL() {
        const formData = $('#wc-product-filter-form').serialize();
        const sort = $('#wc-sort-products').val();
        let url = window.location.pathname + '?';

        if (formData) {
            url += formData + '&';
        }

        if (sort !== 'default') {
            url += 'sort=' + sort + '&';
        }

        if (currentPage > 1) {
            url += 'page=' + currentPage + '&';
        }

        url = url.slice(0, -1); // Remove trailing &

        window.history.pushState({ path: url }, '', url);
    }

    // Initialize on page load
    initProductFilter();

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function (event) {
        if (event.state && event.state.path) {
            // Parse URL parameters and apply filters
            const urlParams = new URLSearchParams(window.location.search);

            // Reset form
            $('#wc-product-filter-form')[0].reset();

            // Apply URL parameters to form
            urlParams.forEach(function (value, key) {
                const input = $(`[name="${key}"]`);
                if (input.length) {
                    if (input.attr('type') === 'checkbox') {
                        // Handle multiple checkbox values
                        if (value.includes(',')) {
                            const values = value.split(',');
                            values.forEach(function (val) {
                                $(`[name="${key}"][value="${val}"]`).prop('checked', true);
                            });
                        } else {
                            $(`[name="${key}"][value="${value}"]`).prop('checked', true);
                        }
                    } else {
                        input.val(value);
                    }
                }
            });

            // Set sort and page
            const sort = urlParams.get('sort');
            if (sort) {
                $('#wc-sort-products').val(sort);
            }

            const page = urlParams.get('page');
            currentPage = page ? parseInt(page) : 1;

            // Reload products
            filterProducts();
        }
    });

    // Keyboard navigation support
    $(document).on('keydown', function (e) {
        // Escape key to close mobile filters
        if (e.key === 'Escape') {
            $('.wc-filter-sidebar').addClass('wc-collapsed');
        }

        // Enter key on filter inputs
        if (e.key === 'Enter') {
            const $target = $(e.target);
            if ($target.closest('#wc-product-filter-form').length) {
                e.preventDefault();
                currentPage = 1;
                filterProducts();
            }
        }
    });

    // Accessibility improvements
    $('.wc-filter-checkbox').each(function () {
        const $checkbox = $(this);
        const $label = $checkbox.closest('.wc-attribute-label');

        $label.attr('role', 'checkbox');
        $label.attr('aria-checked', $checkbox.prop('checked'));
        $label.attr('tabindex', '0');

        // Keyboard support for custom checkboxes
        $label.on('keypress', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                $checkbox.prop('checked', !$checkbox.prop('checked'));
                $label.attr('aria-checked', $checkbox.prop('checked'));
                $checkbox.trigger('change');
            }
        });
    });

    // Update aria-checked when checkbox changes
    $('.wc-filter-checkbox').on('change', function () {
        const $checkbox = $(this);
        const $label = $checkbox.closest('.wc-attribute-label');
        $label.attr('aria-checked', $checkbox.prop('checked'));
    });

});