(function () {
    'use strict';

    function formFor(element) {
        return element.closest('form');
    }

    function heroPreviewFor(element) {
        var form = formFor(element);
        return form ? form.querySelector('[data-hero-live-preview]') : null;
    }

    function updateHeroPreview(radio) {
        var preview = heroPreviewFor(radio);
        if (!preview) { return; }
        var variant = radio.value || 'split';
        preview.className = preview.className.replace(/aznet-theme-homepage-hero-live-preview--[a-z-]+/g, '').trim();
        preview.classList.add('aznet-theme-homepage-hero-live-preview--' + variant);
        preview.setAttribute('data-variant', variant);
        var card = radio.closest('.aznet-theme-homepage-hero-library__card');
        var label = card ? card.querySelector('.aznet-theme-homepage-hero-library__card__meta strong') : null;
        var target = preview.querySelector('[data-hero-preview-label]');
        if (target && label) { target.textContent = label.textContent; }
    }

    function updateHeroPreviewField(input) {
        var preview = heroPreviewFor(input);
        if (!preview || !input.name) { return; }
        var target = preview.querySelector('[data-preview-field="' + input.name + '"]');
        if (target) { target.textContent = input.value || ''; }
    }

    function updateHeroPreviewMedia(form, url) {
        if (!form) { return; }
        var preview = form.querySelector('[data-hero-preview-media]');
        if (!preview) { return; }
        preview.innerHTML = url ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">' : '';
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
                var url = data.sizes && data.sizes.medium_large ? data.sizes.medium_large.url : (data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url);
                if (preview) {
                    preview.innerHTML = url ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">' : '';
                }
                updateHeroPreviewMedia(form, url);
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
            updateHeroPreviewMedia(clearForm, '');
        }
    });

    document.addEventListener('change', function (event) {
        var variant = event.target.closest('input[name="homepage_hero_variant"]');
        if (variant) { updateHeroPreview(variant); }
    });

    document.addEventListener('input', function (event) {
        var field = event.target.closest('input[name^="homepage_hero_"], textarea[name^="homepage_hero_"]');
        if (field && 'homepage_hero_variant' !== field.name) { updateHeroPreviewField(field); }
    });

    document.querySelectorAll('[data-hero-template-gallery] input[name="homepage_hero_variant"]:checked').forEach(updateHeroPreview);
}());
