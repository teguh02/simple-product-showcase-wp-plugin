/**
 * Simple Product Showcase - Admin JavaScript
 * 
 * JavaScript untuk admin panel plugin Simple Product Showcase
 */

(function($) {
    'use strict';
    
    // Admin object
    var SPSAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initWhatsAppPreview();
            this.initFormValidation();
            this.initTooltips();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            // Settings form submission
            $(document).on('submit', '#sps-settings-form', this.handleSettingsSubmit);
            
            // WhatsApp number input validation
            $(document).on('input', '#sps_whatsapp_number', this.validateWhatsAppNumber);
            
            // WhatsApp message preview
            $(document).on('input', '#sps_whatsapp_message', this.updateWhatsAppPreview);
            
            // Product price formatting
            $(document).on('input', '#sps_product_price', this.formatProductPrice);
            
            // Meta box toggle
            $(document).on('click', '.sps-meta-box-toggle', this.toggleMetaBox);
            
            // Bulk actions
            $(document).on('change', '#bulk-action-selector-top, #bulk-action-selector-bottom', this.handleBulkActions);
        },
        
        /**
         * Initialize WhatsApp preview
         */
        initWhatsAppPreview: function() {
            this.updateWhatsAppPreview();
        },
        
        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            // Add real-time validation to required fields
            $('input[required], textarea[required]').on('blur', function() {
                SPSAdmin.validateField($(this));
            });
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltips to help text
            $('.sps-help-text').each(function() {
                var $help = $(this);
                var $trigger = $help.prev('label, .sps-meta-box h3');
                
                if ($trigger.length) {
                    $trigger.attr('title', $help.text().trim());
                }
            });
        },
        
        /**
         * Handle settings form submission
         */
        handleSettingsSubmit: function(e) {
            var $form = $(this);
            var isValid = true;
            
            // Validate required fields
            $form.find('input[required], textarea[required]').each(function() {
                if (!SPSAdmin.validateField($(this))) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                SPSAdmin.showMessage('Please fix the errors before saving.', 'error');
                return false;
            }
            
            // Show loading state
            $form.addClass('loading');
            $form.find('input[type="submit"]').prop('disabled', true).val('Saving...');
        },
        
        /**
         * Validate individual field
         */
        validateField: function($field) {
            var value = $field.val().trim();
            var isValid = true;
            var errorMessage = '';
            
            // Remove existing error styling
            $field.removeClass('error');
            $field.next('.sps-field-error').remove();
            
            // Check if required field is empty
            if ($field.prop('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required.';
            }
            
            // Validate WhatsApp number
            if ($field.attr('id') === 'sps_whatsapp_number' && value) {
                if (!SPSAdmin.isValidWhatsAppNumber(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid WhatsApp number with country code (e.g., +6281234567890).';
                }
            }
            
            // Validate email if needed
            if ($field.attr('type') === 'email' && value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address.';
                }
            }
            
            // Show error if invalid
            if (!isValid) {
                $field.addClass('error');
                $field.after('<div class="sps-field-error">' + errorMessage + '</div>');
            }
            
            return isValid;
        },
        
        /**
         * Validate WhatsApp number
         */
        validateWhatsAppNumber: function() {
            var $input = $(this);
            var number = $input.val().trim();
            
            if (number) {
                if (SPSAdmin.isValidWhatsAppNumber(number)) {
                    $input.removeClass('error').addClass('valid');
                    SPSAdmin.showFieldMessage($input, 'Valid WhatsApp number', 'success');
                } else {
                    $input.removeClass('valid').addClass('error');
                    SPSAdmin.showFieldMessage($input, 'Invalid WhatsApp number format', 'error');
                }
            } else {
                $input.removeClass('error valid');
                SPSAdmin.hideFieldMessage($input);
            }
        },
        
        /**
         * Check if WhatsApp number is valid
         */
        isValidWhatsAppNumber: function(number) {
            // Remove all non-numeric characters except +
            number = number.replace(/[^\d+]/g, '');
            
            // Check if it starts with + and has at least 10 digits
            var regex = /^\+[1-9]\d{9,14}$/;
            return regex.test(number);
        },
        
        /**
         * Update WhatsApp preview
         */
        updateWhatsAppPreview: function() {
            var $messageField = $('#sps_whatsapp_message');
            var $numberField = $('#sps_whatsapp_number');
            var $preview = $('.sps-whatsapp-preview');
            
            if ($preview.length === 0) {
                $messageField.after('<div class="sps-whatsapp-preview"><h4>Preview:</h4><p class="sps-preview-message"></p></div>');
                $preview = $('.sps-whatsapp-preview');
            }
            
            var message = $messageField.val() || 'Hai kak, saya mau tanya tanya tentang produk ini yaa: {product_link}';
            var number = $numberField.val() || '+6281234567890';
            
            // Replace placeholders
            message = message.replace('{product_link}', 'https://example.com/product/sample-product/');
            message = message.replace('{product_title}', 'Sample Product');
            
            $preview.find('.sps-preview-message').text(message);
            
            // Show/hide preview based on number validity
            if (SPSAdmin.isValidWhatsAppNumber(number)) {
                $preview.show();
            } else {
                $preview.hide();
            }
        },
        
        /**
         * Format product price
         */
        formatProductPrice: function() {
            var $input = $(this);
            var value = $input.val();
            
            // Remove any existing formatting
            value = value.replace(/[^\d]/g, '');
            
            // Add thousand separators
            if (value) {
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                $input.val(value);
            }
        },
        
        /**
         * Toggle meta box
         */
        toggleMetaBox: function(e) {
            e.preventDefault();
            var $button = $(this);
            var $metaBox = $button.closest('.sps-meta-box');
            var $content = $metaBox.find('.sps-meta-box-content');
            
            $content.slideToggle();
            $button.toggleClass('expanded');
        },
        
        /**
         * Handle bulk actions
         */
        handleBulkActions: function() {
            var $select = $(this);
            var action = $select.val();
            
            if (action && action !== '-1') {
                var $form = $select.closest('form');
                var $submitButton = $form.find('#doaction, #doaction2');
                
                if ($submitButton.length) {
                    $submitButton.prop('disabled', false);
                }
            }
        },
        
        /**
         * Show field message
         */
        showFieldMessage: function($field, message, type) {
            type = type || 'info';
            var $message = $field.next('.sps-field-message');
            
            if ($message.length === 0) {
                $message = $('<div class="sps-field-message"></div>');
                $field.after($message);
            }
            
            $message.removeClass('success error info warning').addClass(type).text(message).show();
        },
        
        /**
         * Hide field message
         */
        hideFieldMessage: function($field) {
            $field.next('.sps-field-message').hide();
        },
        
        /**
         * Show admin message
         */
        showMessage: function(message, type) {
            type = type || 'info';
            var $message = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            // Remove existing messages
            $('.notice').remove();
            
            // Add new message
            $('.wrap h1').after($message);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        /**
         * Confirm action
         */
        confirmAction: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },
        
        /**
         * Generate sample data
         */
        generateSampleData: function() {
            SPSAdmin.confirmAction('This will create sample products and categories. Continue?', function() {
                // AJAX call to generate sample data
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'sps_generate_sample_data',
                        nonce: $('#sps_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            SPSAdmin.showMessage('Sample data generated successfully!', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            SPSAdmin.showMessage('Error generating sample data: ' + response.data, 'error');
                        }
                    },
                    error: function() {
                        SPSAdmin.showMessage('Error generating sample data. Please try again.', 'error');
                    }
                });
            });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        SPSAdmin.init();
    });
    
    // Expose SPSAdmin to global scope
    window.SPSAdmin = SPSAdmin;
    
})(jQuery);

/**
 * Additional admin utility functions
 */

// Format currency input
function formatCurrency(input) {
    var value = input.value.replace(/[^\d]/g, '');
    if (value) {
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        input.value = value;
    }
}

// Validate form before submission
function validateForm(form) {
    var isValid = true;
    var requiredFields = form.querySelectorAll('input[required], textarea[required]');
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Copy to clipboard
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Copied to clipboard!');
        });
    } else {
        // Fallback for older browsers
        var textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Copied to clipboard!');
    }
}

// Generate shortcode examples
function generateShortcodeExamples() {
    var examples = [
        '[sps_products]',
        '[sps_products columns="2"]',
        '[sps_products category="shoes"]',
        '[sps_products limit="6" columns="3"]',
        '[sps_products show_price="false"]',
        '[sps_products show_whatsapp="false"]'
    ];
    
    var html = '<h4>Shortcode Examples:</h4><ul>';
    examples.forEach(function(example) {
        html += '<li><code>' + example + '</code></li>';
    });
    html += '</ul>';
    
    return html;
}
