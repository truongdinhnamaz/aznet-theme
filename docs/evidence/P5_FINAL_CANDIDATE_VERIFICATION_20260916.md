# P5 Final Candidate Technical Verification — 2026-09-16

## Scope

Technical final-candidate verification only for canonical AZnet Theme bytes after P1/P4 closure. This evidence does **not** authorize or imply a Git tag, GitHub Release, production deployment, optional-provider L5 certification, or any production-site mutation.

Canonical source under test:

- repository: `truongdinhnamaz/aznet-theme`
- canonical commit: `f0b2d581b16a663e4b422234e6ac15568bcc7985`
- canonical tree: `f21884f5989239858261721c5002c5158d6d9be0`
- Theme metadata: `1.1.0`
- WordPress support-floor verification: `6.9`
- PHP verification floor: `8.1`

A temporary P5 GitHub Actions runner was committed only on `work/p5-final-candidate-verification`, but both verification jobs explicitly checked out and asserted the canonical commit above before executing. Therefore the plan/runner branch itself was not treated as candidate Theme source.

## Fresh run

Workflow run: `35051428372` — **SUCCESS**.

Jobs:

- `exact-main` — job `104652463440` — **SUCCESS**.
- `final-package` — job `104652463273` — **SUCCESS**.

### Exact-main artifact

- artifact: `p5-exact-main`
- artifact ID: `10429087315`
- artifact digest: `sha256:f95f6281d1d5daf729896cc24d42b786e7cb030ed9588b80600ce15287c7cb4d`
- recorded canonical SHA: `f0b2d581b16a663e4b422234e6ac15568bcc7985`
- recorded canonical tree: `f21884f5989239858261721c5002c5158d6d9be0`
- WordPress: `6.9`
- active third-party plugins: none

The reusable v1 core verification completed successfully. The clean WordPress runtime route smoke then passed Homepage, Page, Post, archive/category, Search and 404. The retained core browser/a11y harness passed **18/18** route/viewport cases across 1440x1000, 1024x900 and 390x844, with:

- maximum horizontal overflow: `0`;
- blocking axe critical/serious violations: `0`;
- failed subresources: `0`;
- console errors: `0`;
- page errors: `0`;
- exactly one main landmark in every case;
- no PHP `Fatal error`, `Warning`, `Parse error` or `Uncaught` marker in the verified runtime log.

## Deterministic final package

Final technical candidate file:

- name: `aznet-theme-1.1.0.zip`
- SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`
- package artifact: `p5-final-candidate-1.1.0`
- artifact ID: `10428893463`
- artifact digest: `sha256:12e48cd836e4ab1a4e347749cc275ff555176930fad3987b702fc0d4192df61e`

Package gates:

- `style.css` and `AZNET_THEME_VERSION` both resolved exactly to `1.1.0`;
- two independent builds were byte-identical (`DETERMINISTIC_DOUBLE_BUILD = PASS`);
- ZIP integrity check passed;
- exactly one top-level `aznet-theme/` directory;
- `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md` and `aznet-preview.png` excluded from distributable bytes;
- exact package/source comparison passed for **121 production files**;
- packaged PHP lint passed for **93 PHP files**;
- no production byte mutation was introduced by the P5 runner.

The final package SHA matches the earlier D-027 deterministic package SHA because subsequent P4/P1 work and closure were test/workflow/docs/site-operations changes only; the production Theme byte set remained unchanged. P5 independently rebuilt and reverified those current canonical production bytes rather than inferring package validity from the historical D-027 run.

## Exact-package clean WordPress / lifecycle evidence

The exact final ZIP was installed on a disposable clean WordPress `6.9` site with **zero active third-party plugins**.

Initial state:

- Theme: `AZnet Theme`
- folder: `aznet-theme`
- version: `1.1.0`
- status: Active

The exact-package browser/a11y harness passed **18/18** cases before theme switching with the same core quality boundary as exact-main:

- maximum horizontal overflow `0`;
- blocking axe violations `0`;
- failed subresources `0`;
- console errors `0`;
- page errors `0`;
- one main landmark per case.

WordPress-owned Page, Post, Category, Menu and explicit native meta continuity sentinels were recorded. The site then switched through WordPress core lifecycle tooling to bundled `twentytwentyfive`; all recorded WordPress-owned data remained present and unchanged (`SWITCH_AWAY_CONTINUITY = PASS`).

The site then switched back to the exact AZnet Theme package:

- `aznet-theme` returned Active at version `1.1.0`;
- native continuity sentinel remained intact;
- Homepage header/footer runtime smoke passed;
- `SWITCH_BACK_RUNTIME = PASS`;
- the core browser/a11y matrix passed a second time **18/18** after switch-back with overflow `0`, blocking axe `0`, failed subresources `0`, console errors `0`, and page errors `0`;
- runtime logs contained no PHP fatal/warning/parse/uncaught marker in the tested boundary.

## Ownership and claim boundary

P5 technical verification proves the standalone Theme package at the support floor and its WordPress-native switch/rollback reference. It does not transfer ownership of WordPress content, menus, media or provider data to the Theme. Optional WooCommerce, RootProfile, ConvertFlow and SEO integrations remain separate additive compatibility tracks under their existing public-contract boundaries.

This evidence does **not** claim:

- a Git tag exists;
- a GitHub Release exists;
- the final package has been deployed to production;
- RootProfile/ConvertFlow/WooCommerce/SEO L5 certification;
- that any provider-owned defect is fixed by the Theme.

## Exit

**P5 technical final-candidate gates: PASS.**

The correct resulting state is **TECHNICAL CANDIDATE PASS / PUBLICATION GATED**. The next action is an owner decision on publication semantics. Git tag, GitHub Release and final production deployment remain separate explicit hard gates.
