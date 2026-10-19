# D-049 Minh Nguyên Header / Hero catalog visual parity — 2026-10-19

## Root cause and user reference
- In the real owner-provided desktop homepage screenshot, the current Theme 1.3.88 renders the WordPress 19-item menu in a white card *inside* the industrial hero image, but the desktop Header's left menu position is blank and the primary navigation occupies the whole lower row, aligned right. The category card is detached by its own top margin and duplicates the catalog title inside the Hero.
- The provided second image is a schematic, not actual WordPress data. Desired UX: logo and utilities on the first Header row, category heading on the left of the second Header row with main navigation next to it, one white vertical menu column connected directly beneath the heading and Hero copy in the right column. Preserve Industrial 01 navy/gold visual identity; do not import fictitious labels from the wireframe.
- Source root cause: `catalog-navigation.css` intentionally set `.aznet-theme-site-header--catalog-home-rail .aznet-theme-site-header__nav { grid-column: 1 / 4; }` and omitted all Header category headings on homepage; category Card carried `margin-block:1.25rem`. This passed duplicate-menu QA but not visual alignment.

## RED evidence
- PR #470, test commit `a9adf357dd5856e8ed4f29f19f53510fb18ac02a`, V1 script wired by `7de68f7e4095601f7b2044d795819444848d13bc`.
- Real CI RED: V1 Core PR run 39238528512, job 118013410217, fails for intended missing Core Header category label; retained D-027 run 39238529016 fails at the same intended contract.

## Bounded implementation and acceptance
- Scoped header category **label only** (not new toggle or duplicated WordPress menu), rendered solely if `catalog_home_rail` is true.
- Common desktop Header/Hero first-column width and gap; Header menu starts in adjacent column; no blank left row. The Header/aside labels are not both visually duplicated; Hero rail touches lower Header band at the top.
- Industrial 01 existing Hero retains WordPress/site-owned image, editable headline/lead/CTA. Typography/spacing constrained to active catalog mode. No CSS changes to independent Law 01/Curtain 01 layouts.
- Tablet/mobile keep existing WordPress-native menu panel (single source), no Hero rail or duplicate Header label, keyboard/Escape/no-JS support.
- Required GREEN: new offline contract, WordPress-runtime/Playwright computed geometry, 1440/1024/390/320, keyboard/axe, retained CI and 3-pilot scope.
- Production: pilot already runs 1.3.88 with WordPress catalog assignment; no live Theme overwrite or pre-existing WPVibe Draft mutation in PR #470. Publish new exact package and install only after gates; feature off is immediate reversible rollback.

## Current state
- Implementation candidate; do not call the new visual parity production PASS until fresh real non-preview production verification.
