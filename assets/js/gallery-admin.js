/**
 * Gallery Admin JavaScript
 * JavaScript untuk Product Gallery meta box di WordPress admin
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Image upload functionality
    $('.sps-upload-image').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInput = button.data('target');
        var previewContainer = button.closest('.sps-gallery-item').find('.sps-image-preview');
        var galleryItem = button.closest('.sps-gallery-item');
        
        // Add loading state
        galleryItem.addClass('loading');
        
        // Create media frame
        var frame = wp.media({
            title: spsGalleryAdmin.selectImage,
            button: {
                text: spsGalleryAdmin.useImage
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // When image is selected
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            // Update hidden input
            $('#' + targetInput).val(attachment.id);
            
            // Update preview
            var imageUrl = attachment.sizes && attachment.sizes.medium ? 
                attachment.sizes.medium.url : 
                attachment.url;
            
            previewContainer.html(
                '<img src="' + imageUrl + '" alt="' + attachment.alt + '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;" />'
            );
            
            // Update button text
            button.text(spsGalleryAdmin.changeImage);
            
            // Show remove button
            if (!button.siblings('.sps-remove-image').length) {
                button.after(
                    '<button type="button" class="button sps-remove-image" data-target="' + targetInput + '" style="margin-left: 10px; color: #a00;">' + 
                    spsGalleryAdmin.removeImage + 
                    '</button>'
                );
            }
            
            // Add success state
            galleryItem.removeClass('loading').addClass('success');
            setTimeout(function() {
                galleryItem.removeClass('success');
            }, 2000);
        });
        
        // Handle frame close without selection
        frame.on('close', function() {
            galleryItem.removeClass('loading');
        });
        
        // Open frame
        frame.open();
    });
    
    // Remove image functionality
    $(document).on('click', '.sps-remove-image', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInput = button.data('target');
        var previewContainer = button.closest('.sps-gallery-item').find('.sps-image-preview');
        var uploadButton = button.siblings('.sps-upload-image');
        var galleryItem = button.closest('.sps-gallery-item');
        
        // Add loading state
        galleryItem.addClass('loading');
        
        // Clear hidden input
        $('#' + targetInput).val('');
        
        // Update preview
        previewContainer.html(
            '<div class="no-image-placeholder">' + spsGalleryAdmin.noImageSelected + '</div>'
        );
        
        // Update button text
        uploadButton.text(spsGalleryAdmin.selectImage);
        
        // Remove remove button
        button.remove();
        
        // Remove loading state
        galleryItem.removeClass('loading');
    });
    
    // Drag and drop functionality (optional enhancement)
    $('.sps-image-preview').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('drag-over');
    });
    
    $('.sps-image-preview').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
    });
    
    $('.sps-image-preview').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        
        // Handle file drop if needed
        // This is a placeholder for future drag-and-drop functionality
    });
    
    // Validation before save
    $('#post').on('submit', function(e) {
        var hasImages = false;
        $('.sps-gallery-item input[type="hidden"]').each(function() {
            if ($(this).val() !== '') {
                hasImages = true;
                return false;
            }
        });
        
        // Optional: Add validation logic here
        // For example, require at least one image
        // if (!hasImages) {
        //     alert('Please select at least one gallery image.');
        //     e.preventDefault();
        //     return false;
        // }
    });
    
    // Auto-save functionality (optional)
    $('.sps-gallery-item input[type="hidden"]').on('change', function() {
        // Optional: Auto-save when images change
        // This could trigger an AJAX save
    });
});
