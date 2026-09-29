(() => {
    'use strict';

    const gallery = document.querySelector('[data-footer-template-gallery]');
    if (!gallery) {
        return;
    }

    const preview = gallery.querySelector('[data-footer-live-preview]');
    const previewLabel = gallery.querySelector('[data-footer-preview-label]');
    const footerPresetFallback = gallery.querySelector('[data-footer-preset-fallback]');
    const cards = Array.from(gallery.querySelectorAll('[data-footer-template]'));
    const fieldGroups = Array.from(document.querySelectorAll('[data-footer-template-fields]'));
    const emptyState = document.querySelector('[data-footer-template-fields-empty]');

    const applyPreview = (preset, label) => {
        if (preview) {
            preview.dataset.preset = preset;
            preview.className = 'aznet-theme-footer-live-preview aznet-theme-footer-live-preview--' + preset;
            if (previewLabel) {
                previewLabel.textContent = label;
            }
        }

        fieldGroups.forEach((group) => {
            group.hidden = group.dataset.footerTemplateFields !== preset;
        });
        if (emptyState) {
            emptyState.hidden = true;
        }
    };

    cards.forEach((card) => {
        const input = card.querySelector('input[type="radio"]');
        const label = card.querySelector('strong');

        if (!input) {
            return;
        }

        input.addEventListener('change', () => {
            if (!input.checked) {
                return;
            }
            if (footerPresetFallback) {
                const fallback = footerPresetFallback;
                fallback.disabled = true;
            }
            applyPreview(card.dataset.footerTemplate || input.value, label ? label.textContent : input.value);
        });
    });
})();
