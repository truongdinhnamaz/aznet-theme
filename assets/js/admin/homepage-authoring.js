(function () {
    'use strict';

    function formFor(element) {
        return element.closest('form');
    }

    function mediaTarget(button, form, fallbackName, fallbackPreviewSelector) {
        var inputId = button.getAttribute('data-media-input') || '';
        var previewId = button.getAttribute('data-media-preview') || '';

        return {
            input: inputId ? document.getElementById(inputId) : form.querySelector('input[name="' + fallbackName + '"]'),
            preview: previewId ? document.getElementById(previewId) : form.querySelector(fallbackPreviewSelector)
        };
    }

    document.addEventListener('click', function (event) {
        var selectButton = event.target.closest('.aznet-theme-homepage-media-select');
        if (selectButton) {
            event.preventDefault();
            var form = formFor(selectButton);
            if (!form || !window.wp || !wp.media) { return; }

            var frame = wp.media({
                title: selectButton.getAttribute('data-title') || 'Chọn ảnh',
                button: { text: selectButton.getAttribute('data-button') || 'Dùng ảnh này' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first();
                if (!attachment) { return; }

                var data = attachment.toJSON();
                var target = mediaTarget(selectButton, form, 'homepage_featured_image_id', '.aznet-theme-homepage-media-preview');

                if (target.input) {
                    target.input.value = data.id || 0;
                }

                if (target.preview) {
                    var url = data.sizes && data.sizes.medium
                        ? data.sizes.medium.url
                        : (data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url);
                    target.preview.innerHTML = url
                        ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">'
                        : '';
                }
            });

            frame.open();
            return;
        }

        var clearButton = event.target.closest('.aznet-theme-homepage-media-clear');
        if (clearButton) {
            event.preventDefault();
            var clearForm = formFor(clearButton);
            if (!clearForm) { return; }

            var target = mediaTarget(clearButton, clearForm, 'homepage_featured_image_id', '.aznet-theme-homepage-media-preview');
            if (target.input) { target.input.value = '0'; }
            if (target.preview) { target.preview.innerHTML = ''; }
        }
    });
}());
