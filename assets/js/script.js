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
            this.initProductSearch();
            
            // Initialize product search handlers immediately
            this.initProductSearchHandlers();
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
            
            // Product search input
            $(document).on('input', '#sps-product-search', this.handleProductSearchInput);
            $(document).on('keydown', '#sps-product-search', this.handleProductSearchKeydown);
            $(document).on('click', '.sps-autocomplete-item', this.handleAutocompleteClick);
            
            // Search form submit handler
            $(document).on('submit', '#sps-search-form', this.handleSearchSubmit);
            
            // Close autocomplete when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.sps-search-wrapper').length) {
                    $('#sps-autocomplete-results').removeClass('show');
                }
            });
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
        },
        
        /**
         * Initialize product search autocomplete
         */
        initProductSearch: function() {
            var searchInput = $('#sps-product-search');
            if (!searchInput.length) {
                return;
            }
            
            // Auto-focus on search input if query parameter exists
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('query')) {
                searchInput.focus();
            }
        },
        
        /**
         * Initialize product search handlers (ensure they're attached)
         */
        initProductSearchHandlers: function() {
            // Wait a bit to ensure DOM is ready
            setTimeout(function() {
                var $searchInput = $('#sps-product-search');
                if ($searchInput.length) {
                    // Check if sps_ajax is available
                    if (typeof sps_ajax === 'undefined') {
                        console.error('sps_ajax is not defined. Make sure script is localized correctly.');
                        // Try to reload the page after a delay
                        setTimeout(function() {
                            if (typeof sps_ajax === 'undefined') {
                                console.warn('sps_ajax still not available. Script may need to be reloaded.');
                            }
                        }, 1000);
                        return;
                    }
                    
                    console.log('Product search input found, sps_ajax available:', {
                        ajax_url: sps_ajax.ajax_url,
                        has_nonce: !!sps_ajax.nonce
                    });
                } else {
                    console.log('Product search input not found on page');
                }
            }, 100);
        },
        
        /**
         * Handle product search input with autocomplete
         */
        handleProductSearchInput: function(e) {
            var $input = $(this);
            var searchTerm = $input.val().trim();
            var category = $input.data('category');
            var subCategory = $input.data('sub-category') || '';
            var $results = $('#sps-autocomplete-results');
            
            // Clear previous timeout
            clearTimeout($input.data('searchTimeout'));
            
            // Hide results if search term is too short
            if (searchTerm.length < 2) {
                $results.removeClass('show').empty();
                return;
            }
            
            // Show loading state
            $results.addClass('show').html('<div class="sps-autocomplete-loading">Mencari...</div>');
            
            // Check if sps_ajax is available
            if (typeof sps_ajax === 'undefined') {
                console.error('sps_ajax is not defined. Make sure script is localized correctly.');
                $results.html('<div class="sps-autocomplete-no-results">Error: AJAX tidak tersedia</div>').addClass('show');
                return;
            }
            
            // Make AJAX request for autocomplete with debounce
            var searchTimeout = setTimeout(function() {
                $.ajax({
                    url: sps_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'sps_search_products',
                        search_term: searchTerm,
                        category: category,
                        sub_category: subCategory,
                        nonce: sps_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.products && response.data.products.length > 0) {
                            var html = '';
                            $.each(response.data.products, function(index, product) {
                                var image = product.image || '';
                                var imageHtml = image ? '<img src="' + image + '" alt="' + product.title + '" class="sps-autocomplete-item-image">' : '<div class="sps-autocomplete-item-image" style="background: #f0f0f0; width: 40px; height: 40px;"></div>';
                                html += '<div class="sps-autocomplete-item" data-url="' + (product.url || '#') + '">';
                                html += imageHtml;
                                html += '<div class="sps-autocomplete-item-info">';
                                html += '<h4 class="sps-autocomplete-item-title">' + (product.title || '') + '</h4>';
                                if (product.category) {
                                    html += '<p class="sps-autocomplete-item-category">' + product.category + '</p>';
                                }
                                html += '</div>';
                                html += '</div>';
                            });
                            $results.html(html).addClass('show');
                        } else {
                            $results.html('<div class="sps-autocomplete-no-results">Produk tidak ditemukan</div>').addClass('show');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                        $results.html('<div class="sps-autocomplete-no-results">Error saat mencari: ' + error + '</div>').addClass('show');
                    }
                });
            }, 300);
            
            $input.data('searchTimeout', searchTimeout);
        },
        
        /**
         * Handle product search keydown (Enter key)
         */
        handleProductSearchKeydown: function(e) {
            var $input = $(this);
            var $results = $('#sps-autocomplete-results');
            
            // Handle Enter key
            if (e.keyCode === 13 || e.which === 13) {
                e.preventDefault();
                e.stopPropagation();
                
                // Check if there's an active autocomplete item
                var $activeItem = $results.find('.sps-autocomplete-item.active');
                if ($activeItem.length) {
                    // Click the active item
                    var url = $activeItem.data('url');
                    if (url && url !== '#') {
                        window.location.href = url;
                        return;
                    }
                }
                
                // Otherwise, trigger form submit
                var $form = $input.closest('#sps-search-form');
                if ($form.length) {
                    $form.trigger('submit');
                } else {
                    // Fallback: submit search directly
                    this.submitSearch($input);
                }
            }
            
            // Handle Escape key
            if (e.keyCode === 27) {
                $results.removeClass('show');
                $input.blur();
            }
            
            // Handle Arrow Down
            if (e.keyCode === 40) {
                e.preventDefault();
                var $items = $results.find('.sps-autocomplete-item');
                var $active = $results.find('.sps-autocomplete-item.active');
                
                if ($active.length) {
                    $active.removeClass('active');
                    var $next = $active.next('.sps-autocomplete-item');
                    if ($next.length) {
                        $next.addClass('active');
                        $next[0].scrollIntoView({ block: 'nearest' });
                    } else {
                        $items.first().addClass('active');
                    }
                } else {
                    $items.first().addClass('active');
                }
            }
            
            // Handle Arrow Up
            if (e.keyCode === 38) {
                e.preventDefault();
                var $items = $results.find('.sps-autocomplete-item');
                var $active = $results.find('.sps-autocomplete-item.active');
                
                if ($active.length) {
                    $active.removeClass('active');
                    var $prev = $active.prev('.sps-autocomplete-item');
                    if ($prev.length) {
                        $prev.addClass('active');
                        $prev[0].scrollIntoView({ block: 'nearest' });
                    } else {
                        $items.last().addClass('active');
                    }
                } else {
                    $items.last().addClass('active');
                }
            }
        },
        
        /**
         * Handle autocomplete item click
         */
        handleAutocompleteClick: function(e) {
            e.preventDefault();
            e.stopPropagation();
            var url = $(this).data('url');
            if (url && url !== '#') {
                window.location.href = url;
            }
        },
        
        /**
         * Handle search form submit
         */
        handleSearchSubmit: function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $form = $(this);
            var $input = $form.find('#sps-product-search');
            
            if ($input.length) {
                SPS.submitSearch($input);
            }
            
            return false;
        },
        
        /**
         * Submit search - update URL with query parameter
         */
        submitSearch: function($input) {
            var searchTerm = $input.val().trim();
            
            if (searchTerm.length < 2) {
                alert('Masukkan minimal 2 karakter untuk mencari');
                return;
            }
            
            // Hide autocomplete
            $('#sps-autocomplete-results').removeClass('show');
            
            // 1. Dapatkan URL saat ini secara penuh
            var currentUrl = window.location.href;
            
            // 2. Encode kalimat yang sudah diketik di search bar
            var encodedQuery = encodeURIComponent(searchTerm);
            
            // 3. Cek apakah sudah ada parameter yang diset saat ini
            var hasParams = currentUrl.indexOf('?') !== -1;
            var newUrl;
            
            if (hasParams) {
                // Jika sudah ada parameter, tambahkan &query=
                // Cek apakah query sudah ada, jika ya replace, jika tidak tambahkan
                if (currentUrl.indexOf('query=') !== -1) {
                    // Replace existing query parameter
                    newUrl = currentUrl.replace(/([&?])query=[^&]*/, '$1query=' + encodedQuery);
                } else {
                    // Tambahkan &query= di akhir
                    newUrl = currentUrl + '&query=' + encodedQuery;
                }
            } else {
                // Jika belum ada parameter, tambahkan ?query=
                newUrl = currentUrl + '?query=' + encodedQuery;
            }
            
            // 4. Redirect langsung ke URL baru yang sudah mengandung param query
            window.location.href = newUrl;
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
