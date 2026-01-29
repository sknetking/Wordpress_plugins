jQuery(document).ready(function ($) {
    'use strict';

    // Copy shortcode functionality
    $('.wc-copy-btn').on('click', function (e) {
        e.preventDefault();

        var $button = $(this);
        var textToCopy = $button.data('text');
        var $originalText = $button.text();

        // Create temporary textarea to copy text
        var $tempTextarea = $('<textarea>').val(textToCopy).css({
            position: 'fixed',
            opacity: 0,
            pointerEvents: 'none'
        }).appendTo('body');

        $tempTextarea[0].select();

        try {
            document.execCommand('copy');

            // Update button state
            $button.addClass('copied').text('Copied!');

            // Show notification
            showCopyNotification('Shortcode copied to clipboard!');

            // Reset button after 2 seconds
            setTimeout(function () {
                $button.removeClass('copied').text($originalText);
            }, 2000);

        } catch (err) {
            console.error('Failed to copy text:', err);
            showCopyNotification('Failed to copy shortcode', 'error');
        }

        $tempTextarea.remove();
    });

    // Show copy notification
    function showCopyNotification(message, type) {
        type = type || 'success';

        var $notification = $('<div>')
            .addClass('wc-copy-notification')
            .text(message)
            .css('background', type === 'error' ? '#dc3545' : '#28a745')
            .appendTo('body');

        // Show notification
        setTimeout(function () {
            $notification.addClass('show');
        }, 100);

        // Hide and remove after 3 seconds
        setTimeout(function () {
            $notification.removeClass('show');
            setTimeout(function () {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    // Smooth scroll to sections
    $('.wc-filter-card h2').on('click', function () {
        var $card = $(this).closest('.wc-filter-card');
        $('html, body').animate({
            scrollTop: $card.offset().top - 20
        }, 500);
    });

    // Add hover effects to cards
    $('.wc-filter-card').hover(
        function () {
            $(this).find('.dashicons').addClass('animated');
        },
        function () {
            $(this).find('.dashicons').removeClass('animated');
        }
    );

    // Initialize tooltips
    $('.wc-shortcode-example code').attr('title', 'Click the Copy button to copy this shortcode');

    // Add keyboard shortcuts
    $(document).on('keydown', function (e) {
        // Ctrl/Cmd + C on shortcode examples
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
            var $focusedElement = $(document.activeElement);
            if ($focusedElement.hasClass('wc-shortcode-example') || $focusedElement.closest('.wc-shortcode-example').length) {
                e.preventDefault();
                $focusedElement.closest('.wc-shortcode-example').find('.wc-copy-btn').click();
            }
        }
    });

    // Animate elements on page load
    $('.wc-filter-card').each(function (index) {
        var $card = $(this);
        $card.css('opacity', '0').css('transform', 'translateY(20px)');

        setTimeout(function () {
            $card.css({
                'opacity': '1',
                'transform': 'translateY(0)',
                'transition': 'all 0.5s ease'
            });
        }, index * 100);
    });

    // Add interactive highlighting to shortcode examples
    $('.wc-shortcode-example').hover(
        function () {
            $(this).find('code').css('background', 'rgba(0, 115, 170, 0.05)');
        },
        function () {
            $(this).find('code').css('background', 'transparent');
        }
    );

    // Quick link interactions
    $('.wc-link-card').on('click', function (e) {
        // Add ripple effect
        var $card = $(this);
        var ripple = $('<span>').addClass('ripple');

        $card.append(ripple);

        setTimeout(function () {
            ripple.remove();
        }, 600);
    });

    // Add ripple effect styles dynamically
    $('<style>')
        .text(`
            .wc-link-card {
                position: relative;
                overflow: hidden;
            }
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(0, 115, 170, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            .dashicons.animated {
                animation: bounce 0.5s ease;
            }
            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% {
                    transform: translateY(0);
                }
                40% {
                    transform: translateY(-10px);
                }
                60% {
                    transform: translateY(-5px);
                }
            }
        `)
        .appendTo('head');

});
