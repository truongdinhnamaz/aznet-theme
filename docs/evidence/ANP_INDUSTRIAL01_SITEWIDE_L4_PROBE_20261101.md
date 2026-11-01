# ANP Industrial 01 — bounded sitewide visual/a11y audit probe (2026-11-01)

## Authority, status and scope

Owner requested: **review and fix the visual interface of all published WordPress Pages before Theme release**. AZnet Theme only owns presentation. WordPress owns Page contents; WooCommerce, ConvertFlow and RootProfile own their respective domain state. SMTP verification is deliberately excluded by owner instruction.

The public, **read-only** `tests/browser/industrial01-sitewide-audit.mjs` is a diagnostic harness, not production code and not a claim of completed visual testing. It uses the existing Playwright and `@axe-core/playwright` dependencies; no new build system or plugin is introduced. It never writes to WordPress, submits forms, clicks purchase buttons, or invents business data. The explicit ANP route manifest is `docs/evidence/anp-industrial01-sitewide-routes.json` (14 public routes: all nine published Pages + representative product, category, archive and article). Do not use those URLs to infer domain identity/routing in Theme production code.

## Local and authorized CI invocation

Run in a trusted checkout where the existing browser dependencies and Internet/public-site access are available:

```bash
node --check tests/browser/industrial01-sitewide-audit.mjs
node tests/browser/industrial01-sitewide-audit.mjs --self-test
# First reproduce the two Lighthouse findings only on the Komatsu product:
AZT_AUDIT_BASE_URL=https://anpvietnam.vn/ \
AZT_AUDIT_ROUTES_FILE=docs/evidence/anp-industrial01-sitewide-routes.json \
AZT_AUDIT_ONLY_ROUTE=komatsu-product \
AZT_AUDIT_DIR=/tmp/anp-industrial01-komatsu \
node tests/browser/industrial01-sitewide-audit.mjs

# After triage, run the complete 14-route, 42-viewport matrix:
AZT_AUDIT_BASE_URL=https://anpvietnam.vn/ \
AZT_AUDIT_ROUTES_FILE=docs/evidence/anp-industrial01-sitewide-routes.json \
AZT_AUDIT_DIR=/tmp/anp-industrial01-sitewide \
node tests/browser/industrial01-sitewide-audit.mjs
```


The optional `AZT_AUDIT_ONLY_ROUTE` is an **exact reviewed manifest ID**. Unknown IDs and unsafe values fail closed. Begin with `komatsu-product` (3 read-only views) to obtain the precise `color-contrast` and `label-content-name-mismatch` targets without first auditing 39 unrelated page-views; omit the filter only when performing the complete sitewide gate. Neither mode is a deployment or WooCommerce mutation.

**False-PASS prevention:** An HTTP 200 is not sufficient when WordPress redirects an audited Page to an unrelated path (notably Homepage); final origin **and route path** must match the reviewed target, allowing only trailing-slash normalization. A page with **zero H1, blank H1 or multiple H1s** also fails the semantic gate; merely recording the headings is not enough for PASS. The probe **captures the full page before inspecting H1/main/overflow and before axe-core analysis** because that capture can trigger lazy-loaded/offscreen content. A self-test guards the `capture → DOM → axe` order, preventing semantic measurements from representing a different state than the accessibility audit. The probe also examines **all image elements after full-page capture**; unloaded images and offscreen broken images cause `review`, never `PASS`. A `review` due to pending lazy imagery is not automatically a published-image defect; use DOM/screenshot evidence and source ownership to triage.

**Accessibility completeness:** Axe-core may return `incomplete` results (including color contrast) that need manual review rather than a confirmed violation. The probe saves each incomplete rule, affected targets, HTML, failure summaries **and computed foreground/background/ancestor styles where the selector is inspectable** into `axeIncomplete`; the same evidence collector is used for confirmed violations. Any such result is **REVIEW, never PASS**, without incorrectly calling it a defect. Self-test covers no findings, confirmed violation, incomplete result and missing incomplete data.\n\nOutput: checkpointed `report.json` plus full-page screenshots for each of **390 / 768 / 1440 CSS px**. It checks HTTP/final origin, genuine AZnet Theme + Industrial 01 body classes, main landmark, H1s, horizontal overflow, initially visible broken images, CF7 presence, axe WCAG 2.0/2.1 A/AA violations with actual failing DOM selectors, clipped HTML and failure reason, and computed text/background colors with up to four ancestor styles. It returns nonzero on any non-PASS surface. Theme vs provider ownership is classified **only after** inspecting the violating DOM and authoritative source. Screenshots/HTML may include public client content: inspect privacy before posting artifacts publicly.

## Existing WordPress and lab evidence, not invalidated

- Homepage Page #2; About #15; Contact #17 have native content, previously verified at stored-content scope.
- Toyota #9522: Flatsome source backup draft #15817, Gutenberg stage #15818, published Core Blocks revision #15819, exact post-update WordPress readback. Mitsubishi #9549: backup #15820, stage #15821, revision #15822. Both Woo shortcodes render real product/category cards in targeted WPVibe section output; WPVibe full preview uses the **old Flatsome/Salecar draft shell**, and is not reliable L4 production evidence.
- Lighthouse lab sample: Toyota mobile Performance 99/A11y100/Best100; Mitsubishi desktop Performance98/A11y100/Best100. Representative Woo Shop/Category/Article/Privacy/Sitemap A11y100; one Komatsu product desktop A11y97 with **color-contrast** and **label-content-name-mismatch**. PSI summary alone lacks offending elements, hence exact root cause and Theme/provider ownership **UNKNOWN**.
- Existing Header fallback Draft PR #548 and Industrial gallery placeholder Draft PR #547 stay **unmerged/undeployed**. CI has experienced startup failure before steps/logs; not evidence of a test regression or success.
- Published empty secondary Page #12758 is not the active homepage (Front Page #2) and has an indexing warning; noindex/redirect/deletion is out of scope without owner-level evidence and authorization.

## Verification boundary and rollback

**PASS: tooling authored on isolated work branch**. Offline syntax and self-tests of a corresponding local implementation were run under Node 22.16.0; the exact GitHub script still needs an independently executable runner check. **BLOCKED: live L4**: Chat browser lists no ANP tab, authorized WPVibe accounts exhausted rolling quota; public HTML endpoints are unavailable via current container/web (DNS/cache miss). The probe has **not run against production**, no real 390/768/1440 screenshots or failing-product selectors have been obtained. The live promoted NWQS policy endpoint was unavailable; use provisional baseline only.

**EXACT NEXT:** execute the exact-head read-only probe in an authorized online browser/runner; identify actual Komatsu violation targets and classify ownership; for a verified Theme-owned defect create focused RED→minimal GREEN→regression, then repeat impacted breakpoints. Do not merge/release PR #547/#548 without fresh official regression, actual visual QA and owner approval. Rollback for this slice is simply reverting the test/route/docs branch; no production data were changed.

## Additive checkpoint — 2026-11-01: Contact false-PASS guard

Fresh public Lighthouse lab on `/ac-quy-xe-nang-dien-komatsu-3-tan-chinh-hang-gs` (mobile): Accessibility 97/100 and Best Practices 100/100, with `color-contrast` and `label-content-name-mismatch` findings. The summary exposes **no exact failing DOM target**; component responsibility remains UNKNOWN. Do not apply speculative token/CSS changes or claim those findings are fixed by Draft #551.

The authorized WPVibe `get_page_html` tool was given the explicit ANP public homepage URL, yet again returned a body tagged `wp-theme-flatsome wp-child-theme-salecar-wpvibe-draft`. It is therefore **preview/draft evidence only**, not independent evidence that public ANP has switched to Flatsome. Public route and real served theme remain UNKNOWN until the isolated browser probe runs. The main WPVibe account is over its rolling call allowance; an alternate authorized ANP account has limited remaining calls. GitHub V1 PR-run 41261898740 has failed jobs with `steps: null` and no job logs: official CI cannot be counted as a code regression or QA PASS.

Focused QA-harness defect: the Contact form count was merely recorded, so the prior pass predicate could accept an otherwise clean Contact Page with **zero rendered forms in main**. An isolated Node 22 reproduction of that predicate **RED** reproduced `pass` instead of expected `review` (exit 1). On the isolated PR #550 branch, minimal test-only change `3794dfb8a59e19ac6aca59cf8f14b3791bbf436a`:

- Counts `.wpcf7 form` **inside `main#main`** rather than anywhere in the document (a Header modal cannot satisfy the Contact gate).
- Requires at least one such form for manifest route ID `contact` before visual PASS; other pages are unaffected. This checks **presence only**, not form validation, submission or email delivery.
- Adds route-gate `--self-test` assertions for missing, present and irrelevant forms. Separate Node 22 focused GREEN reproduction passed **6/6** assertions (zero/present/undefined, homepage unaffected, console violation, axe violation). GitHub fetched-blob readback confirmed gate, selector and self-test source markers at commit `3794dfb8`.

**Evidence depth:** focused logic RED/GREEN + GitHub source readback PASS; the exact PR-head full `--self-test`, actual browser/visual/a11y L4, independent production inspection, CF7 delivery and release remain **BLOCKED/UNKNOWN**. No live WordPress mutation, no theme/plugin domain changes, no merge/release.

**EXACT NEXT:** execute exact PR #550 head `--self-test` then the three-viewport `komatsu-product` probe in a trusted independent online browser runner; inspect failing selectors and identify their source owner before any UI fix. When routes are reliable, inspect Contact's rendered form and remaining 14-route matrix. No automatic PR merge or production deployment.

## Follow-up exact-head verification — 2026-11-01

The exact PR-branch JavaScript source `tests/browser/industrial01-sitewide-audit.mjs` was copied into an isolated Node 22.16.0 container without modifying the repository or target WordPress site. `git hash-object` of the local 17,258-byte file was **`37f75ba53abba9db7e2fd2cb181043a50ef708c1`**, matching GitHub's fetched blob SHA exactly.

On these **same bytes**, `node --check` **PASS** and `node industrial01-sitewide-audit.mjs --self-test` **PASS**, covering URL/manifest fail-closed behavior, redirect/H1 gates, Contact form requirement, screenshot→DOM→axe order, diagnostic sanitization and bounded console collection. This replaces the older UNKNOWN exact-head self-test statement above; it does **not** establish runtime against ANP, visual parity, actual WCAG L4 or form submission.

**Remaining blocker:** no independent network-enabled browser runner or installed Playwright/Axe toolchain in the available container. Container DNS lookup of `anpvietnam.vn` and `api.github.com` fails; the connected WordPress HTML renderer returns a Flatsome draft shell, not a reliable public rendering. GitHub Actions PR runs fail before job steps. **Exact NEXT:** execute the single reviewed Komatsu route on an independent online Chromium+Playwright/Axe runner and attribute actual violating selectors. Remain Draft; never infer production QA PASS from self-tests.
