# ANP PR #552 — isolated WordPress pretty permalink/canonical investigation V9 (2026-11-01)

## Provenance, scope, authority
- Canonical repository `truongdinhnamaz/aznet-theme`, Draft PR #552 open/unmerged at `fc6725654ab20767fecd17412416b2ad12a6c2bd`; canonical main `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405` (fresh checked).
- Accepted AZT source-of-truth governance and AZT-05 v1.0 apply; this file is a **bounded evidence record**, not a policy/release decision.
- Execution: disposable WordPress Playground / SQLite, exact PR #552 pinned 242-file package (ZIP SHA-256 `44e90157bdf235d9f75491a8dfe5793a0ad33a3e2ff8d1bb7f7e12ac62e10d0f`), with an isolated test-only mu-plugin that can suppress canonical redirect on one marked URL.
- [Browser automation evidence](https://agent.tinyfish.ai/runs/54f220da-57ff-4129-b124-75c03e7f6ca6) — V9. Test Blueprint source: [`AZT_PR552_WORDPRESS_ROUTE_IDENTITY_V9_20261101.json`](AZT_PR552_WORDPRESS_ROUTE_IDENTITY_V9_20261101.json), pinned to evidence commit `47597f4c592f98c241bd85fccc25e60ad2b9a2e3`.
- No ANP hosting/production/WordPress data, Wellness 01 files or mixed WPVibe draft touched. No main/PR source edit, merge or publish.

## Actual V9 HTTP observations
| Route | Observed HTTP | Redirected | Content | Verdict |
| --- | ---: | --- | --- | --- |
| `/` | 200 | no | H1 `AZT QA Home` | PASS |
| `/azt-qa-contact/` | 200 | no | H1 `AZT QA Contact` | PASS |
| `/azt-qa-article/` | 200 | yes → homepage | H1 `AZT QA Home` | FAIL |
| `?p=6` | 200 | yes → homepage | H1 `AZT QA Home` | FAIL |
| `?p=6&azt_pr552_nocanonical=1` | 200 | no | H1 `AZT QA Article` | PASS |
| `?s=azt` | 200 | no | correct search heading | PASS |
| nonexistent pretty URL | 200 | yes → homepage | H1 `AZT QA Home` | FAIL |
| `?p=987654321` | 404 | no | H1 `Page not found` | PASS |

**Never equate HTTP 200 to route PASS** without verifying expected content/identity.

## WordPress core state and scoped log
- Article object ID **6**, `post_status=publish`, `WP_Query(['p'=>6,'post_type'=>'post','post_status'=>'publish'])` finds the article.
- On `?p=6&azt_pr552_trace=1`, `get_queried_object_id=6`, `is_single=true`, `is_home=false`, `is_front_page=false`, `is_404=false`.
- `permalink_structure=/%postname%/`; canonical pretty permalink points to `/azt-qa-article/`.
- With test-only `redirect_canonical` suppressed for the query, the same article serves its correct HTML/H1. This proves the published Post and candidate's article rendering work in the isolated environment.
- `debug.log` exists, sentinel recorded, 1 log line, zero Theme-scoped error lines in that bounded session. Not a sitewide log certification.
- V9 harness summary: `base_pass=true`, `article_pass=true` **only via marked noncanonical query**, `definitive_pass=true` per narrowly scripted criteria, `pretty_routes_both_pass=false`, hence **pretty permalink gate FAIL**. No full routing PASS.

## Technical interpretation / ownership decision
- The evidence demonstrates divergence in **WordPress canonical redirect and pretty permalink resolution on the disposable Playground**. It does not conclusively identify Apache/nginx rewrite configuration vs Playground URL scoping vs any other rewrite hook.
- Fresh canonical PR diff against main contains **only five modified production paths**: `assets/css/components/catalog-navigation.css`, `assets/css/components/homepage-industrial-01.css`, `assets/js/catalog-navigation.js`, `inc/theme/header.php` (WooCommerce page URL fallback behavior), `template-parts/homepage/industrial-01/categories.php`. **No files named/related to canonical, redirect, permalink, or rewrite were changed in PR #552**. This is supporting evidence against a PR-specific routing regression, **not proof** that runtime routing can never be influenced indirectly.
- No production Theme bug has been reproduced at the correct shallow layer. Do not add speculative redirect/rewrite patches or disable `redirect_canonical` in production Theme to make Playground pass.
- Previous PASS retains validity: L0–L2 exact ZIP/contracts; L3 WP exact-package install and Theme switch/content continuity; scoped L4 WordPress responsive 390/768/1440 and native catalog ARIA/focus/escape.
- Still UNKNOWN/BLOCKED: pretty-permalink full HTTP identity on a **standard disposable WordPress rewrite-capable host**, real provider-present L5, live ANP release/backup/restore L6. Current ANP has Wellness 01 files outside clean ZIP; never do full live-theme overwrite.

## EXACT NEXT
Use a **separate disposable WordPress installation with a real rewrite-capable server and database** (not ANP hosting and not another blind Playground run) to test published Post pretty permalink, canonical query redirect and missing pretty 404 against the exact pinned package. Save the HTTP status, final URL and expected H1 receipts. If this capability cannot be accessed, mark pretty-permalink gate **BLOCKED/UNKNOWN**, preserve all valid PASS and avoid extra paid probes. Release/merge remain approval-gated.

Engineering Guardian shared authenticated registry not verified in this chat; cross-chat collision protection **UNKNOWN / NOT ENFORCED**. Evidence work confined to docs-only branch.
