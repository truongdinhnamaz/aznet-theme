# PR #552 — Exact candidate ZIP reconstructed; disposable WordPress gate remains blocked (2026-11-01)

## Scope and authority
- Canonical repository `truongdinhnamaz/aznet-theme`; Draft PR #552 source head `fc6725654ab20767fecd17412416b2ad12a6c2bd`; `main` at `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`.
- Prior evidence checkpoint `2fbdaba4bfe7048acf40da552facb2fd25776cd6`, PR comment `6558332122`; earlier source-Git runtime smoke PR comment `6558507103`.
- Source governance `docs/SOURCE_GOVERNANCE.md` and `docs/source/AZT-05-product-constitution.md` v1.0 consulted on canonical main. This report is **evidence**, not a change of constitution, architecture, release gate or product ownership.
- No change to candidate PR source branch, canonical main, ANP hosting, ANP WordPress, the existing WPVibe mixed draft, Wellness 01 files, or external domain-owner data.

## Fresh local reproduction — exact pinned ZIP restored

A pre-existing successful X6 Actions artifact (run [40628520518](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40628520518), artifact `12790642034`) supplied exactly 242 known baseline production files from checkout `431af3026372259fd6ac8b89ce830effdee815f7`. Six production files are different at PR #552 head, and their contents were reconstructed from canonical GitHub patches without any Theme production code invention.

All six resulting **Git blob hashes** matched the target PR #552 Git tree exactly:
- `assets/css/components/catalog-navigation.css` `dc005e7ebdd7f0ce4e874fa0362708941c3a678b`
- `assets/css/components/homepage-industrial-01.css` `7c9e4e9142cc315cb1ce16740efbf0e8a3e75459`
- `assets/css/presets/digital-01.css` `94c04aac36a3398e73c9e5d030a802fe5cf4082c`
- `assets/js/catalog-navigation.js` `01c47765f7484b0f7338088c63f22e1852b43314`
- `inc/theme/header.php` `87935d873678998ffff2bb9dabce65a38e65c46e`
- `template-parts/homepage/industrial-01/categories.php` `7da1dc5e0c8b422f1aba5afdfb4dd0fb16b6c677`

The deterministic ZIP was rebuilt locally with the accepted package layout: top-level `aznet-theme/`, fixed `2020-01-01` timestamps, `0644` files, sorted production paths and ZIP_DEFLATED level 9. Independent artifact verification:

| Metric | Observed | Expected |
|---|---:|---:|
| Production paths | **242** | 242 |
| ZIP byte length | **656000** | 656000 |
| ZIP integrity | `ZipFile.testzip() = None` | clean |
| SHA-256 | `44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f` | same (previous PASS) |
| Wellness 01 extra paths | 0 | 0 in clean candidate |

The reconstructed source's serialized `relative_path<TAB>sha256(file)<LF>` payload SHA-256 is `08b23a84281259d5c0c5b593eee197051865451e920253a18aa8ea05ff5d337c`. This is a convenient expected payload fingerprint for *future isolated installation verification*. No fresh full lint/mock-DOM/fixture rerun was performed because L0-L2 tests remain PASS from checkpoint.

**Attachment:** `aznet-theme-pr552-exact-242.zip` (same exact ZIP SHA) is available as a conversation artifact; no GitHub Release, public production asset, or merge was made.

## Disposable WordPress continuation attempts (strict gate separation)

1. Earlier independent [WordPress Playground run](https://agent.tinyfish.ai/runs/b886f7c9-1480-417d-8b10-09fe2159a943) succeeded at WordPress 6.9 / PHP 8.1 / SQLite **Git-source activation only**. It did not install the ZIP, verify 242 production bytes inside WordPress or perform full routes/rollback.
2. A bounded Blueprint `docs/evidence/anp-pr552-clean-package-preflight-20261101/ANP_PR552_ISOLATED_WORDPRESS_PLAYGROUND_BLUEPRINT_20261101.json` was added **on the evidence branch only** at commit `a8195cc4c2627f45cbab33572f6cb4c140f75d7e`. It targets WordPress 6.9/PHP 8.1/SQLite and would validate the 242-file payload digest after `git:directory` installation, create three synthetic native content fixtures and report routes, without touching customer data.
3. [Playground run 6f25045a](https://agent.tinyfish.ai/runs/6f25045a-088b-479d-8300-4f9081c18a61) failed before WordPress boot: `BlueprintFetchError`, public GitHub raw Blueprint URL could not be downloaded; **transport failure**, not a Theme defect.
4. [Playground run 9822d51e](https://agent.tinyfish.ai/runs/9822d51e-306d-4ac7-8d12-05e6443d6cdf) retried with the same Blueprint embedded directly in the URL. It ended with generic `Ooops! WordPress Playground had a hiccup!`; the agent could not capture a PHP/SQLite stack trace or the QA report. Theme activation, WordPress versions, 242-file manifest and routes were **not independently verified in this run**. This evidence cannot establish a Theme regression or claim a deeper PASS.
5. Local execution container lacks WordPress Core, WP-CLI, Docker/Podman, and MySQL/SQLite PHP database driver; official WordPress ZIP retrieval fails DNS. No authorized independent local WordPress+DB execution has been established.

## QA disposition / safety

- **PASS (preserved):** L0-L2 source/package integrity and focused contracts; synthetic Chromium 390/768/1440 and 8/8 AX category names; genuine ANP preview Contact H1, all previously scoped.
- **NEW PASS:** reconstructed exact **same** ZIP checksum after independent local rebuild; six Git production blobs exact.
- **PARTIAL:** Git-source WP Playground activation from earlier run; not exact ZIP installation.
- **BLOCKED/UNKNOWN:** WordPress 6.9+/PHP 8.1+ isolated **exact ZIP installer/runtime** gate, full L4/axe, L5 provider integration, production preimage/rollback and L6 release.
- **Safety:** ANP active installation contains Wellness-only paths not in clean ZIP. **Do not install this whole ZIP on live ANP**; no hosting/cPanel, destructive draft deletion, PR merge, release tag or publish approval is implied.

**EXACT NEXT (one action):** acquire/use an authorized disposable WordPress runtime with DB that accepts local conversation artifact ZIP bytes, prove SHA-256 before import and perform exact-ZIP activation + representative route/PHP-log smoke. If unavailable, retain BLOCKED and do not retry synthetic fixtures or rerun already-PASS lint. Follow with bounded rollback rehearsal only after L3 succeeds.
