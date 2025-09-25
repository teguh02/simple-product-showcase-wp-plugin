/**
 * Simple Product Showcase - Frontend JavaScript
 * 
 * JavaScript untuk interaksi frontend plugin Simple Product Showcase
 */

(function($) {
    'use strict';
    
    // Plugin object
    var SPS = {
        
        /**
         * Initialize plugin
         */
        init: function() {
            this.bindEvents();
            this.initFilters();
            this.initLazyLoading();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // WhatsApp button click tracking
            $(document).on('click', '.sps-whatsapp-button', this.trackWhatsAppClick);
            
            // Product image click tracking
            $(document).on('click', '.sps-product-image a', this.trackProductView);
            
            // Search form submission
            $(document).on('submit', '.sps-search-form', this.handleSearch);
            
            // Category filter change
            $(document).on('change', '.sps-category-select', this.handleCategoryFilter);
            
            // Smooth scroll for anchor links
            $(document).on('click', 'a[href^="#"]', this.smoothScroll);
        },
        
        /**
         * Initialize filters
         */
        initFilters: function() {
            // Auto-submit search form on input change (with debounce)
            var searchTimeout;
            $(document).on('input', '.sps-search-input', function() {
                clearTimeout(searchTimeout);
                var $this = $(this);
                searchTimeout = setTimeout(function() {
                    if ($this.val().length >= 3 || $this.val().length === 0) {
                        $this.closest('form').submit();
                    }
                }, 500);
            });
        },
        
        /**
         * Initialize lazy loading for images
         */
        initLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(function(img) {
                    imageObserver.observe(img);
                });
            }
        },
        
        /**
         * Track WhatsApp button clicks
         */
        trackWhatsAppClick: function(e) {
            var $button = $(this);
            var productTitle = $button.closest('.sps-product-item, .sps-single-product').find('.sps-product-title').text().trim();
            
            // Send analytics event if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'whatsapp_click', {
                    'event_category': 'engagement',
                    'event_label': productTitle
                });
            }
            
            // Track with custom event
            $(document).trigger('sps:whatsapp:click', {
                product: productTitle,
                button: $button
            });
        },
        
        /**
         * Track product view clicks
         */
        trackProductView: function(e) {
            var $link = $(this);
            var productTitle = $link.closest('.sps-product-item').find('.sps-product-title').text().trim();
            
            // Send analytics event if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'product_view', {
                    'event_category': 'engagement',
                    'event_label': productTitle
                });
            }
            
            // Track with custom event
            $(document).trigger('sps:product:view', {
                product: productTitle,
                link: $link
            });
        },
        
        /**
         * Handle search form submission
         */
        handleSearch: function(e) {
            var $form = $(this);
            var searchTerm = $form.find('.sps-search-input').val().trim();
            
            if (searchTerm.length < 3 && searchTerm.length > 0) {
                e.preventDefault();
                SPS.showMessage('Please enter at least 3 characters to search.', 'warning');
                return false;
            }
            
            // Add loading state
            $form.addClass('loading');
            $form.find('.sps-search-button').prop('disabled', true);
            
            // Track search event
            if (typeof gtag !== 'undefined') {
                gtag('event', 'search', {
                    'search_term': searchTerm
                });
            }
        },
        
        /**
         * Handle category filter change
         */
        handleCategoryFilter: function(e) {
            var $select = $(this);
            var category = $select.val();
            var $container = $select.closest('.sps-archive-products, .sps-products-container');
            
            // Show loading state
            $container.addClass('loading');
            
            // AJAX filter products
            $.ajax({
                url: sps_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'sps_filter_products',
                    category: category,
                    nonce: sps_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.sps-products-grid').html(response.data);
                        SPS.updateURL(category);
                    } else {
                        SPS.showMessage('Error filtering products. Please try again.', 'error');
                    }
                },
                error: function() {
                    SPS.showMessage('Error filtering products. Please try again.', 'error');
                },
                complete: function() {
                    $container.removeClass('loading');
                }
            });
        },
        
        /**
         * Update URL without page reload
         */
        updateURL: function(category) {
            var url = new URL(window.location);
            if (category) {
                url.searchParams.set('category', category);
            } else {
                url.searchParams.delete('category');
            }
            window.history.pushState({}, '', url);
        },
        
        /**
         * Smooth scroll for anchor links
         */
        smoothScroll: function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        },
        
        /**
         * Show message to user
         */
        showMessage: function(message, type) {
            type = type || 'info';
            var $message = $('<div class="sps-message sps-message-' + type + '">' + message + '</div>');
            
            // Remove existing messages
            $('.sps-message').remove();
            
            // Add new message
            $('body').prepend($message);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        /**
         * Format price
         */
        formatPrice: function(price) {
            // Remove any existing formatting
            price = price.toString().replace(/[^\d]/g, '');
            
            // Add thousand separators
            return price.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        /**
         * Validate WhatsApp number
         */
        validateWhatsAppNumber: function(number) {
            // Remove all non-numeric characters except +
            number = number.replace(/[^\d+]/g, '');
            
            // Check if it's a valid format
            var regex = /^\+?[1-9]\d{1,14}$/;
            return regex.test(number);
        },
        
        /**
         * Generate WhatsApp URL
         */
        generateWhatsAppURL: function(number, message) {
            // Clean number
            number = number.replace(/[^\d+]/g, '');
            
            // Encode message
            message = encodeURIComponent(message);
            
            return 'https://wa.me/' + number + '?text=' + message;
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        SPS.init();
    });
    
    // Expose SPS to global scope for external use
    window.SPS = SPS;
    
})(jQuery);

/**
 * Additional utility functions
 */

// Debounce function
function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Throttle function
function throttle(func, limit) {
    var inThrottle;
    return function() {
        var args = arguments;
        var context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(function() {
                inThrottle = false;
            }, limit);
        }
    };
}

// Check if element is in viewport
function isInViewport(element) {
    var rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Smooth scroll to element
function smoothScrollTo(element, offset) {
    offset = offset || 0;
    var targetPosition = element.offsetTop - offset;
    
    window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
    });
}
