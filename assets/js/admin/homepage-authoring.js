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

    function heroPreviewFor(element) {
        var form = formFor(element);
        return form ? form.querySelector('[data-hero-live-preview]') : null;
    }

    function updateHeroVariant(radio) {
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
        if (!preview) { return; }

        var key = input.getAttribute('data-preview-key') || '';
        if (key) {
            var targetByKey = preview.querySelector('[data-preview-key-target="' + key + '"]');
            if (targetByKey) { targetByKey.textContent = input.value || ''; }
        }

        if (input.name) {
            var targetByName = preview.querySelector('[data-preview-field="' + input.name + '"]');
            if (targetByName) { targetByName.textContent = input.value || ''; }
        }
    }

    function updateHeroPreviewMedia(form, key, url) {
        if (!form) { return; }
        var preview = form.querySelector('[data-hero-live-preview]');
        if (!preview) { return; }

        var target = key
            ? preview.querySelector('[data-preview-media-key="' + key + '"]')
            : preview.querySelector('[data-hero-preview-media]');

        if (!target) { return; }
        target.innerHTML = url ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">' : '';
    }

    function moveCategoryRow(button) {
        var row = button.closest('[data-category-order-row]');
        var list = row ? row.closest('[data-category-order-list]') : null;
        if (!row || !list) { return; }

        var direction = button.getAttribute('data-category-move');
        if ('up' === direction) {
            var previous = row.previousElementSibling;
            if (previous && previous.matches('[data-category-order-row]')) {
                list.insertBefore(row, previous);
            }
            return;
        }

        if ('down' === direction) {
            var next = row.nextElementSibling;
            if (next && next.matches('[data-category-order-row]')) {
                list.insertBefore(next, row);
            }
        }
    }

    document.addEventListener('click', function (event) {
        var categoryMove = event.target.closest('[data-category-move]');
        if (categoryMove) {
            event.preventDefault();
            moveCategoryRow(categoryMove);
            return;
        }

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
                var key = selectButton.getAttribute('data-preview-key') || (target.input ? target.input.getAttribute('data-preview-key') || '' : '');

                if (target.input) {
                    target.input.value = data.id || 0;
                }

                var url = data.sizes && data.sizes.medium_large
                    ? data.sizes.medium_large.url
                    : (data.sizes && data.sizes.medium
                        ? data.sizes.medium.url
                        : (data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url));

                if (target.preview) {
                    target.preview.innerHTML = url
                        ? '<img src="' + String(url).replace(/"/g, '&quot;') + '" alt="">'
                        : '';
                }

                updateHeroPreviewMedia(form, key, url);
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
            var key = clearButton.getAttribute('data-preview-key') || (target.input ? target.input.getAttribute('data-preview-key') || '' : '');

            if (target.input) { target.input.value = '0'; }
            if (target.preview) { target.preview.innerHTML = ''; }
            updateHeroPreviewMedia(clearForm, key, '');
        }
    });

    document.addEventListener('change', function (event) {
        var variant = event.target.closest('input[name="homepage_hero_variant"]');
        if (variant) { updateHeroVariant(variant); }
    });

    document.addEventListener('input', function (event) {
        var field = event.target.closest('[data-preview-key], input[name^="homepage_hero_"], textarea[name^="homepage_hero_"]');
        if (field && 'homepage_hero_variant' !== field.name) {
            updateHeroPreviewField(field);
        }
    });

    document.querySelectorAll('[data-hero-template-gallery] input[name="homepage_hero_variant"]:checked').forEach(updateHeroVariant);
}());
