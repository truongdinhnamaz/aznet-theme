# G5 — Core Browser / Responsive / A11y / Performance — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Workflow: `.github/workflows/g-core-browser.yml`

## RED reproduction

Initial run: `34105630308`  
Job: `101689863613`  
Head: `16afbe7a613909f02a4531d4d5b08fbd6ff1afa7`

Result: **FAIL** with 15/18 cases passing. The only failures were the expected 404 route at all three viewports because Chromium emits the generic console message `Failed to load resource: the server responded with a status of 404 (Not Found)` for the top-level 404 document itself.

G4 had already proven `/definitely-missing/` correctly returns HTTP 404 with the Theme Header/Main/Footer and no Theme-caused PHP error. Therefore the failure was reduced to the browser harness: it treated the expected top-level 404 document status as a failed Theme subresource.

## Minimal GREEN

Harness-only commit: `bda75e5a8bb1a75e165a876df0fa254eb72822d8`

The test now:

- allows only the generic console noise matching the **expected top-level document status** for an intentional 4xx route;
- independently records every non-document response with HTTP status >= 400;
- still fails on missing CSS/JS/image/font/other subresources, unexpected console errors, page errors or wrong document status.

No production Theme PHP/CSS/JS/template behavior changed.

## Fresh GREEN evidence

GitHub Actions run: `34106353494`  
Job: `101692167519` (`core-browser`)  
Result: **SUCCESS**  
WordPress: `6.9`  
PHP: `8.1.34`  
Viewport matrix: `1440x1000`, `1024x900`, `390x844`

All **18/18** route/viewport cases passed:

- Home
- Page
- Post
- Archive
- Search
- 404

at all three viewports.

## Assertions per case

- exactly one `main#main` landmark;
- no duplicate DOM IDs;
- horizontal overflow <= 1px;
- skip link is first visible keyboard focus with a visible indicator;
- primary navigation is keyboard reachable on desktop/tablet;
- mobile disclosure navigation opens and remains usable by keyboard;
- reduced-motion preference is active in the reduced-motion mobile context;
- Woo component styles are absent on WordPress-clean routes;
- axe critical/serious violations = 0;
- unexpected console errors = 0;
- page errors = 0;
- failed non-document subresources = 0;
- no Theme-caused PHP Fatal/Warning/Parse/Uncaught marker after browser verification.

Artifact: `g5-core-browser-a11y-performance`  
Artifact ID: `10012570992`  
Artifact ZIP SHA-256: `5f7817a656c6ab993a0dfb44a356a66558e99403ceca36dce9eec3e9e06acfb3`

## PASS

**G5 / L4 PASS.** The WordPress-clean core presentation passed the fresh desktop/tablet/mobile browser, keyboard, a11y and asset-scope matrix.

## Boundary

This does not prove update/theme-switch/rollback, deterministic release packaging, optional provider certification or final version promotion.

## NEXT

**G6 — prove in-place update continuity, theme-switch safety and rollback using WordPress-native content/configuration plus an opaque external-data sentinel; do not invent a provider schema or Theme settings subsystem.**