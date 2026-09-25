(function () {
    'use strict';

    function formFor(element) {
        return element.closest('form');
    }

    document.addEventListener('click', function (event) {
        var selectButton = event.target.closest('.aznet-theme-homepage-media-select');
        if (selectButton) {
            event.preventDefault();
            var form = formFor(selectButton);
            if (!form || !window.wp || !wp.media) { return; }

            var frame = wp.media({
                title: 'Chọn ảnh',
                button: { text: 'Dùng ảnh này' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first();
                if (!attachment) { return; }
                var data = attachment.toJSON();
                var input = form.querySelector('input[name="homepage_featured_image_id"]');
                var preview = form.querySelector('.aznet-theme-homepage-media-preview');
                if (input) { input.value = data.id || 0; }
                if (preview) {
                    var url = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
                    preview.innerHTML = url ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">' : '';
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
            var clearInput = clearForm.querySelector('input[name="homepage_featured_image_id"]');
            var clearPreview = clearForm.querySelector('.aznet-theme-homepage-media-preview');
            if (clearInput) { clearInput.value = '0'; }
            if (clearPreview) { clearPreview.innerHTML = ''; }
        }
    });
}());
