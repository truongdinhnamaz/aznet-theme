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

Output: checkpointed `report.json` plus full-page screenshots for each of **390 / 768 / 1440 CSS px**. It checks HTTP/final origin, genuine AZnet Theme + Industrial 01 body classes, main landmark, H1s, horizontal overflow, initially visible broken images, CF7 presence, axe WCAG 2.0/2.1 A/AA violations with actual failing DOM selectors, clipped HTML and failure reason, and computed text/background colors with up to four ancestor styles. It returns nonzero on any non-PASS surface. Theme vs provider ownership is classified **only after** inspecting the violating DOM and authoritative source. Screenshots/HTML may include public client content: inspect privacy before posting artifacts publicly.

## Existing WordPress and lab evidence, not invalidated

- Homepage Page #2; About #15; Contact #17 have native content, previously verified at stored-content scope.
- Toyota #9522: Flatsome source backup draft #15817, Gutenberg stage #15818, published Core Blocks revision #15819, exact post-update WordPress readback. Mitsubishi #9549: backup #15820, stage #15821, revision #15822. Both Woo shortcodes render real product/category cards in targeted WPVibe section output; WPVibe full preview uses the **old Flatsome/Salecar draft shell**, and is not reliable L4 production evidence.
- Lighthouse lab sample: Toyota mobile Performance 99/A11y100/Best100; Mitsubishi desktop Performance98/A11y100/Best100. Representative Woo Shop/Category/Article/Privacy/Sitemap A11y100; one Komatsu product desktop A11y97 with **color-contrast** and **label-content-name-mismatch**. PSI summary alone lacks offending elements, hence exact root cause and Theme/provider ownership **UNKNOWN**.
- Existing Header fallback Draft PR #548 and Industrial gallery placeholder Draft PR #547 stay **unmerged/undeployed**. CI has experienced startup failure before steps/logs; not evidence of a test regression or success.
- Published empty secondary Page #12758 is not the active homepage (Front Page #2) and has an indexing warning; noindex/redirect/deletion is out of scope without owner-level evidence and authorization.

## Verification boundary and rollback

**PASS: tooling authored on isolated work branch**. Offline syntax and self-tests of a corresponding local implementation were run under Node 22.16.0; the exact GitHub script still needs an independently executable runner check. **BLOCKED: live L4**: Chat browser lists no ANP tab, authorized WPVibe accounts exhausted rolling quota; public HTML endpoints are unavailable via current container/web (DNS/cache miss). The probe has **not run against production**, no real 390/768/1440 screenshots or failing-product selectors have been obtained. The live promoted NWQS policy endpoint was unavailable; use provisional baseline only.

**EXACT NEXT:** execute the exact-head read-only probe in an authorized online browser/runner; identify actual Komatsu violation targets and classify ownership; for a verified Theme-owned defect create focused RED→minimal GREEN→regression, then repeat impacted breakpoints. Do not merge/release PR #547/#548 without fresh official regression, actual visual QA and owner approval. Rollback for this slice is simply reverting the test/route/docs branch; no production data were changed.
