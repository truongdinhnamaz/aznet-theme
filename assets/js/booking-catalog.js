/* Restore the exact service name from a booking link in Fluent Forms #3.
 * Some server-side query prefill sanitizers strip Vietnamese spacing/diacritics.
 * This changes only the text input's value; it never submits the form. */
(function () {
    'use strict';

    function prefillService() {
        var field = document.querySelector('#fluentform_3 input[name="service_name"]');
        if (!field || !window.URLSearchParams) {
            return;
        }

        var service = new URLSearchParams(window.location.search).get('service');
        if (!service) {
            return;
        }

        service = service.replace(/\s+/g, ' ').trim().slice(0, 180);
        if (!service || field.value === service) {
            return;
        }

        field.value = service;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', prefillService, { once: true });
    } else {
        prefillService();
    }
})();
