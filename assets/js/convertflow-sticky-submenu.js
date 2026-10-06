(() => {
    const submenu = document.querySelector('[data-choiceguide-sticky-submenu]');
    const journey = submenu instanceof HTMLElement
        ? submenu.closest('[data-choiceguide-product-journey]')
        : null;
    const header = document.querySelector('[data-aznet-theme-site-header]');

    if (!(submenu instanceof HTMLElement) ||
        !(journey instanceof HTMLElement) ||
        !(header instanceof HTMLElement)) {
        return;
    }

    const body = document.body;
    let ticking = false;

    const adminBarHeight = () => {
        const adminBar = document.getElementById('wpadminbar');
        if (!(adminBar instanceof HTMLElement)) {
            return 0;
        }

        const style = window.getComputedStyle(adminBar);
        if ('fixed' !== style.position) {
            return 0;
        }

        return adminBar.getBoundingClientRect().height;
    };

    const update = () => {
        const journeyTop = journey.getBoundingClientRect().top + window.scrollY;
        const headerHeight = header.getBoundingClientRect().height;
        const threshold = Math.max(0, journeyTop - adminBarHeight() - headerHeight);

        body.classList.toggle(
            'is-aznet-theme-submenu-replacing-header',
            window.scrollY >= threshold
        );

        ticking = false;
    };

    const requestUpdate = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(update);
    };

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    update();
})();
