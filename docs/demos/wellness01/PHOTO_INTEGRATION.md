# Wellness 01 — AI imagery / native pattern integration

**Date:** 2026-10-29  
**Owner approval:** user approved the Wellness 01 visual concept and requested its integration into `truongdinhnamaz/aznet-theme`.  
**Scope:** reusable WordPress-native presentation; no booking engine, CRM, commerce, RootProfile identity or provider contract change.

## Image assets and editorial safety

The four source visuals were generated for this design, not captured at an actual studio. They are **AI illustrations**, not documentary pictures of instructors, clients or facilities.

| File | Purpose | SHA-256 |
| --- | --- | --- |
| `assets/starter/wellness01/group.webp` | Hero / general classes / illustrative team | `0fcb687195a2e9ba94895f80350371186fb8741e73ae1afa718e515bafd063fa` |
| `assets/starter/wellness01/mobility.webp` | Pilates and movement program | `2ed62df3fe02382d87c00b62b40455376a2ebe24dd07c2620988d12491a19bf4` |
| `assets/starter/wellness01/guide.webp` | One-to-one guidance, studio, illustrative profile | `2dfa4b3025fe01bde1d03bf6bdcebe382ad6f1bcc0ba201c9d00ec0fc9ba4f8f` |
| `assets/starter/wellness01/meditation.webp` | Mindfulness / breathing program | `6a07ea83ae2eb780278c22033a7e104e1c0dcfbdfde51e452e21daede449f2bc` |

Total file weight: **390,328 bytes**. Seven Core Image blocks display these four reusable WebP illustrations in the editable native pattern.

The Theme starter image URLs are resolved through public `get_theme_file_uri()` and escaped with `esc_url()`. No external hotlinks and no pre-existing plugin/private storage. The content is standard WordPress Core blocks. **Before publication of any actual client website, copy authorized imagery into that site's WordPress Media Library and replace these starter references**, so editorial content remains portable when the Theme changes. Never assign fictitious staff names, credentials, testimonials or case studies to the people pictured. The pattern includes a visible AI illustration disclosure.

## Separate deliverables

- `patterns/homepage-wellness-01.php`: Theme-owned Core-block homepage structure with reusable AI example photos, text editable in Gutenberg. This does not own booking, actual schedules, customer profiles, or transaction state.
- `docs/demos/wellness01/preview.html`: **reference-only** interactive showcase; photos sourced locally from Theme starter assets; the schedule and CTA are simulated, not live.
- `tests/offline/r2-wellness01-pattern-contract.php`: 21 offline source/asset safety checks, including no private domain reads, valid Core block syntax, valid WebP assets and correct anchors.
- `tests/browser/wellness01-preview-l4.mjs`: standalone preview browser suite (not a WordPress L4 substitute).
- `tests/browser/wellness01-native-l4.mjs` and `.github/workflows/wellness01-native-runtime-browser.yml`: WordPress 6.9/PHP 8.1 clean runtime + real Gutenberg save/reopen + browser/axe verification required for native Theme L3/L4 closure.

## Verification / gate status

**Local PASS (L1/L2):** `php -l` on pattern and offline contract; source/asset tests **21/21**. The browser reference **30/30** cases (10 widths x 3 variants) has decoded all seven illustrations without horizontal overflow or page JavaScript errors, using the same WebP bytes embedded into an offline browser test; a separate check verifies the relative paths exist in the proposed Theme tree. These are **fixture-only** browser results, not WordPress UI certification.

**BLOCKED/UNKNOWN:** GitHub Actions was returning `failure` with zero executed steps on PR #544 *and on canonical main* as of this checkpoint. A concrete GitHub runner/account failure cause has not been authenticated. WordPress L3, Gutenberg real editor save/reopen, WordPress L4/axe, optional integrations and L6 package/release are not proven. A ready-to-run isolated test workflow is included but does not count as PASS by its presence alone.

**Rollback:** Remove the four starter WebPs and revert the pattern/preview/test/workflow additions in the draft feature branch; no production WordPress content is changed by the branch. The independent design demo is separately preserved.

**EXACT NEXT:** Obtain fresh WordPress-clean L3 and native Gutenberg L4 evidence on the exact photo-integrated PR head (restore GitHub Actions execution or run an equivalent isolated WP environment), repair any RED findings, then use the already-given owner approval to merge source. GitHub release tagging and real-site deployment remain separate operations.
