(() => {
    // Progressive enhancement of native wp_nav_menu output only.
    // No catalog fetch, WordPress data mutations, or plugin semantics.
    const labels = window.aznetThemeCatalogNav || {};
    const expandText = String(labels.expand || 'Mở danh mục con');
    const collapseText = String(labels.collapse || 'Thu gọn danh mục con');

    document.querySelectorAll('.aznet-theme-catalog-navigation__menu').forEach((menu, menuIndex) => {
        menu.querySelectorAll('li').forEach((item, itemIndex) => {
            const link = Array.from(item.children).find((element) => element.tagName === 'A');
            const submenu = Array.from(item.children).find((element) => element.matches('ul.sub-menu'));
            if (!link || !submenu) return;

            const button = document.createElement('button');
            const submenuId = submenu.id || 'aznet-theme-catalog-submenu-' + menuIndex + '-' + itemIndex;
            submenu.id = submenuId;
            const itemLabel = (link.textContent || '').trim();
            button.type = 'button';
            button.className = 'aznet-theme-catalog-navigation__submenu-toggle';
            button.setAttribute('aria-controls', submenuId);
            button.setAttribute('aria-expanded', 'false');
            button.setAttribute('aria-label', expandText + ': ' + itemLabel);
            button.textContent = '+';
            submenu.hidden = true;
            item.insertBefore(button, submenu);

            const setExpanded = (expanded) => {
                submenu.hidden = !expanded;
                button.setAttribute('aria-expanded', String(expanded));
                button.setAttribute('aria-label', (expanded ? collapseText : expandText) + ': ' + itemLabel);
                button.textContent = expanded ? '−' : '+';
            };
            button.addEventListener('click', () => {
                setExpanded(submenu.hidden);
            });
            item.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !submenu.hidden) {
                    event.preventDefault();
                    event.stopPropagation();
                    setExpanded(false);
                    button.focus();
                }
            });
        });
    });
})();
