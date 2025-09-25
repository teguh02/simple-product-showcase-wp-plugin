/**
 * Gallery Metabox JavaScript
 * JavaScript untuk Product Gallery meta box
 */

jQuery(document).ready(function($) {
    'use strict';
    
    var frame;
    
    // Image upload functionality
    $('.sps-upload').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInput = $('#' + button.data('target'));
        var targetPreview = $('#' + button.data('preview'));
        var galleryItem = button.closest('.sps-gallery-item');
        
        // Add loading state
        galleryItem.addClass('loading');
        
        // Create media frame
        frame = wp.media({
            title: spsGalleryMetabox.selectImage,
            button: {
                text: spsGalleryMetabox.useImage
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
            targetInput.val(attachment.id);
            
            // Update preview
            var imageUrl = attachment.sizes && attachment.sizes.medium ? 
                attachment.sizes.medium.url : 
                attachment.url;
            
            targetPreview.html(
                '<img src="' + imageUrl + '" alt="' + attachment.alt + '" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; display: block;" />'
            );
            
            // Update button text
            button.text(spsGalleryMetabox.changeImage);
            
            // Show remove button
            if (!button.siblings('.sps-remove').length) {
                button.after(
                    '<button type="button" class="button sps-remove" data-target="' + button.data('target') + '" data-preview="' + button.data('preview') + '" style="margin-left: 10px; color: #a00;">' + 
                    spsGalleryMetabox.removeImage + 
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
    $(document).on('click', '.sps-remove', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var targetInput = $('#' + button.data('target'));
        var targetPreview = $('#' + button.data('preview'));
        var uploadButton = button.siblings('.sps-upload');
        var galleryItem = button.closest('.sps-gallery-item');
        
        // Add loading state
        galleryItem.addClass('loading');
        
        // Clear hidden input
        targetInput.val('');
        
        // Update preview
        targetPreview.html(
            '<div class="no-image-placeholder">' + spsGalleryMetabox.noImageSelected + '</div>'
        );
        
        // Update button text
        uploadButton.text(spsGalleryMetabox.selectImage);
        
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
