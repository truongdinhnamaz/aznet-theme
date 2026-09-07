(() => {
    const trigger = document.querySelector('[data-aznet-theme-nav-trigger]');
    const panel = document.querySelector('[data-aznet-theme-nav-panel]');

    if (!(trigger instanceof HTMLButtonElement) || !(panel instanceof HTMLElement)) {
        return;
    }

    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    let previousBodyOverflow = '';

    const focusableItems = () => Array.from(panel.querySelectorAll(focusableSelector)).filter((element) => {
        if (!(element instanceof HTMLElement)) {
            return false;
        }
        const rect = element.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    });

    const close = (returnFocus = true) => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = previousBodyOverflow;

        if (returnFocus) {
            trigger.focus();
        }
    };

    const open = () => {
        previousBodyOverflow = document.body.style.overflow;
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';

        const items = focusableItems();
        if (items.length > 0) {
            items[0].focus();
        } else {
            panel.focus();
        }
    };

    panel.hidden = true;
    trigger.setAttribute('aria-expanded', 'false');

    trigger.addEventListener('click', () => {
        if (panel.hidden) {
            open();
        } else {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (panel.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }

        if (event.key === 'Tab') {
            const items = focusableItems();
            if (items.length === 0) {
                event.preventDefault();
                panel.focus();
                return;
            }

            const first = items[0];
            const last = items[items.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.addEventListener('pagehide', () => {
        close(false);
    });
})();
