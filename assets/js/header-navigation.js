(() => {
    const trigger = document.querySelector('[data-aznet-theme-nav-trigger]');
    const panel = document.querySelector('[data-aznet-theme-nav-panel]');

    if (!(trigger instanceof HTMLButtonElement) || !(panel instanceof HTMLElement)) {
        return;
    }

    const header = trigger.closest('[data-aznet-theme-site-header]');
    const drawer = header instanceof HTMLElement && header.classList.contains('aznet-theme-site-header--mobile-drawer');
    const backdrop = header?.querySelector('[data-aznet-theme-nav-backdrop]');
    const closeButton = panel.querySelector('[data-aznet-theme-nav-close]');
    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    let previousBodyOverflow = '';

    const focusableItems = () => Array.from(panel.querySelectorAll(focusableSelector)).filter((element) => {
        if (!(element instanceof HTMLElement)) {
            return false;
        }
        const rect = element.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    });

    // WordPress remains the sole source of navigation URLs and hierarchy.
    // Disclosures are transient DOM presentation enhancements. Without JS,
    // the original nested links are left expanded and accessible.
    const setupSubmenus = () => {
        const menu = panel.querySelector('#aznet-theme-mobile-menu');
        if (!(menu instanceof HTMLElement)) {
            return;
        }
        const expandLabel = panel.dataset.aznetThemeExpandLabel || 'Expand submenu:';
        const collapseLabel = panel.dataset.aznetThemeCollapseLabel || 'Collapse submenu:';

        menu.querySelectorAll('li.menu-item-has-children').forEach((item, index) => {
            const link = Array.from(item.children).find((node) => node instanceof HTMLAnchorElement);
            const submenu = Array.from(item.children).find((node) =>
                node instanceof HTMLUListElement && node.classList.contains('sub-menu')
            );
            if (!(link instanceof HTMLAnchorElement) || !(submenu instanceof HTMLUListElement)) {
                return;
            }

            let id = 'aznet-theme-mobile-submenu-' + (index + 1);
            while (document.getElementById(id)) {
                id += '-menu';
            }
            submenu.id = id;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'aznet-theme-site-header__submenu-toggle';
            button.setAttribute('aria-controls', submenu.id);
            const itemName = (link.textContent || '').trim();

            const update = (expanded) => {
                submenu.hidden = !expanded;
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                button.setAttribute('aria-label', (expanded ? collapseLabel : expandLabel) + ' ' + itemName);
                item.classList.toggle('is-aznet-theme-submenu-open', expanded);
            };
            item.classList.add('is-aznet-theme-submenu-enhanced');
            item.insertBefore(button, submenu);
            // Keep the current WordPress branch discoverable when its child is active.
            update(item.classList.contains('current-menu-ancestor') || item.classList.contains('current-menu-parent'));
            button.addEventListener('click', () => {
                update(button.getAttribute('aria-expanded') !== 'true');
            });
        });
    };

    const close = (returnFocus = true) => {
        if (panel.hidden) {
            return;
        }
        panel.hidden = true;
        if (backdrop instanceof HTMLElement) {
            backdrop.hidden = true;
        }
        panel.removeAttribute('role');
        panel.removeAttribute('aria-modal');
        panel.removeAttribute('aria-labelledby');
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
        if (drawer) {
            panel.setAttribute('role', 'dialog');
            panel.setAttribute('aria-modal', 'true');
            panel.setAttribute('aria-labelledby', 'aznet-theme-mobile-panel-title');
            if (backdrop instanceof HTMLElement) {
                backdrop.hidden = false;
            }
        }

        const items = focusableItems();
        if (items.length > 0) {
            items[0].focus();
        } else {
            panel.focus();
        }
    };

    setupSubmenus();
    panel.hidden = true;
    if (backdrop instanceof HTMLElement) {
        backdrop.hidden = true;
    }
    trigger.setAttribute('aria-expanded', 'false');
    // The drawer style applies only after JS is ready; no-JS remains a native
    // inline disclosure with all WordPress menu links visible.
    if (header instanceof HTMLElement) {
        header.classList.add('is-aznet-theme-nav-enhanced');
    }

    trigger.addEventListener('click', () => {
        if (panel.hidden) {
            open();
        } else {
            close();
        }
    });

    if (closeButton instanceof HTMLButtonElement) {
        closeButton.addEventListener('click', () => close());
    }
    if (backdrop instanceof HTMLElement) {
        backdrop.addEventListener('click', () => close());
    }

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

    document.addEventListener('focusin', (event) => {
        if (!drawer || panel.hidden || panel.contains(event.target)) {
            return;
        }
        const items = focusableItems();
        (items[0] || panel).focus();
    });

    // Close when a visitor interacts outside the popover or drawer. Preserve
    // focus only when it was inside the panel; never steal an outside click.
    document.addEventListener('pointerdown', (event) => {
        if (panel.hidden || !(event.target instanceof Node)) {
            return;
        }
        if (!panel.contains(event.target) && !trigger.contains(event.target)) {
            close(panel.contains(document.activeElement));
        }
    });

    // A breakpoint switch must not leave the desktop page scroll-locked.
    window.addEventListener('resize', () => {
        if (!panel.hidden && trigger.getClientRects().length === 0) {
            close(false);
        }
    });

    window.addEventListener('pagehide', () => {
        close(false);
    });
})();
