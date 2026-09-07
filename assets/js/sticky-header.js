(() => {
    const header = document.querySelector('[data-aznet-theme-site-header].aznet-theme-site-header--sticky-compact');

    if (!(header instanceof HTMLElement)) {
        return;
    }

    const threshold = 24;
    let ticking = false;

    const update = () => {
        header.classList.toggle('is-aznet-theme-header-compact', window.scrollY > threshold);
        ticking = false;
    };

    const requestUpdate = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        requestAnimationFrame(update);
    };

    window.addEventListener('scroll', requestUpdate, { passive: true });
    update();
})();
