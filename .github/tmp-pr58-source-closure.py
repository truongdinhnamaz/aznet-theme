from pathlib import Path


def replace_once(path: str, old: str, new: str) -> None:
    p = Path(path)
    text = p.read_text()
    if old not in text:
        raise SystemExit(f"missing expected text in {path}: {old[:100]!r}")
    p.write_text(text.replace(old, new, 1))


def replace_section(path: str, start: str, end: str, new_body: str) -> None:
    p = Path(path)
    text = p.read_text()
    a = text.find(start)
    if a < 0:
        raise SystemExit(f"missing section start in {path}: {start!r}")
    b = text.find(end, a)
    if b < 0:
        raise SystemExit(f"missing section end in {path}: {end!r}")
    p.write_text(text[:a] + new_body.rstrip() + "\n\n" + text[b:])


# AZT-03 owns current baseline/provenance.
replace_once(
    "docs/source/AZT-03-baseline-provenance.md",
    "**Version:** v0.28  ",
    "**Version:** v0.29  ",
)
replace_once(
    "docs/source/AZT-03-baseline-provenance.md",
    "**Date:** 09/09/2026  ",
    "**Date:** 10/09/2026  ",
)
replace_section(
    "docs/source/AZT-03-baseline-provenance.md",
    "### Law Site Provisioning v1.1 candidate provenance — stacked, not canonical main",
    "## 3. Current release-path classification",
    """### Homepage Law 01 + Law Site Provisioning integration candidate — PR #58, not canonical main

The previously recorded D-026 stacked candidate at `d564c6e8a43f458a493fe816c4fc768d4917065f` remains historical evidence for the v1.1 implementation slice. Its `1.1.1` package label is **not** the integration/release metadata baseline: fresh G8 verification later proved that Theme metadata must remain the source-approved `1.1.0` until a separately approved release-governance change occurs.

The approved stack was integrated upward without conflict-resolution byte deltas: PR #61 -> PR #60 at `f10751b5fadd19107bbec7bcd1fafa2bd92d409b`; PR #60 -> PR #59 at `bb2155d114922f585a1650b6bbdf61b3ce6414a3`; PR #59 -> PR #58 at `ed087860364b8f8ad542226866d604013aee8ff5`.

Fresh PR #58 verification caught two integration-only issues before any canonical-main merge. G8 run `34412609546` failed exactly because candidate-only commit `97110c42af945837a29520585d183b2f7e58ceae` had changed `style.css` and `AZNET_THEME_VERSION` from `1.1.0` to `1.1.1`. Minimal production fix `ec1e86f631a1b341736422296ae4689830a505c6` restored the approved metadata bytes to `1.1.0`; the exact G8 contract then passed. R2 Pattern Library Browser run `34413075882` separately exposed a disposable PHP-development-server death during theme-switch portability verification; artifact/log evidence showed no Theme PHP error at the failure boundary. Test-harness-only commit `356ef8c00991c63e294d619d42c7094d1074226d` restarts only that disposable runtime before portability rendering, matching the already-proven R3 harness pattern and changing no packaged Theme bytes.

Exact PR-head `356ef8c00991c63e294d619d42c7094d1074226d` then completed all 21 triggered pull-request workflows successfully, including reusable core, lifecycle, R1-R6 static/browser, Homepage/Law01, Law Site Provisioning browser/runtime and deterministic package gates.

Fresh `Law Site Provisioning Candidate Package` run `34413505990` checked out exact head `356ef8c00991c63e294d619d42c7094d1074226d`, passed PHP 8.1.34 + mbstring/mysqli reusable core verification, linted 93 production PHP files, built twice deterministically, passed `unzip -t` and exact-byte unpacked/source comparison for 121 packaged files. Integration candidate: `aznet-theme-1.1.0-law-site-provisioning-v1-1-candidate.zip`; SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; artifact ID `10128165973`.

Fresh `Law Site Provisioning Browser Quality` run `34413506017` passed WordPress 6.9 / PHP 8.1 / MySQL 8 recommendation-first setup, new/active/rerun/rollback/user-edit/index-restore runtime scenarios and Law 01 Homepage responsive/a11y checks; browser artifact ID `10128198277`. The separately known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and is not a blanket clean-log claim.

PR #58 remains an open owner-gated integration candidate against canonical `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`. The verified PR-head candidate does not itself make `main` canonical to these bytes and does not authorize a tag, GitHub Release, L5 provider certification or production deployment. Detailed integration evidence: `docs/evidence/HOMEPAGE_LAW01_PROVISIONING_PR58_INTEGRATION.md`.""",
)

# AZT-04 owns roadmap / QA / accepted decisions.
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "**Version:** v0.32  ",
    "**Version:** v0.33  ",
)
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "**Date:** 09/09/2026",
    "**Date:** 10/09/2026",
)
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "| P4 | Real law-site pilot QA | READY / EXACT NEXT | Owner approved pilot QA execution; prove current-stack route/responsive/a11y/Rank Math/RootProfile compatibility without implying final production deployment |",
    "| P4 | Real law-site pilot QA | READY / AFTER MAIN INTEGRATION | Owner-approved pilot QA remains pending; PR #58 integration must clear its explicit main gate first |\n| P4-A | Homepage Composer + Law 01 | PR HEAD PASS / MAIN GATE | Premium Law01 + Composer integrated in PR #58; exact PR-head matrix is GREEN; canonical-main merge remains owner-gated |\n| P4-B | Law Site Provisioning v1.1 | PR HEAD PASS / MAIN GATE | D-026 smart setup integrated in PR #58 with Theme metadata restored to `1.1.0`; exact PR-head package/browser matrix is GREEN |",
)
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "Current canonical post-hardening implementation: `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`, tree `8fc309ea8e01bfe727da943754c98164b3f92a58`, Theme metadata `1.1.0`. Fresh `V1 Exact Main Verification` run `34195248467` succeeded on that exact SHA after PR #52.",
    "Current canonical implementation remains `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, tree `32ebca25a63d511e74250646d333202994c65328`, Theme metadata `1.1.0`. PR #58 is a verified integration candidate only until an explicit owner-approved merge; no PR-head PASS is promoted to exact-main evidence.",
)
replace_section(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "### P4-A — Homepage Composer + Law 01",
    "### P5 — Publication and deployment",
    """### P4-A — Homepage Composer + Law 01

**State:** **PR HEAD PASS / OWNER MAIN GATE.**

Homepage Composer Core + `law-01` Premium presentation are integrated in PR #58. Content Map remains typed WordPress references only; preset switching does not mutate WordPress content; native Post/Page Classic Editor policy remains; `front-page.php` retains exactly one `the_content()` boundary.

Fresh exact PR-head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully after integration debugging. The previously observed candidate-only metadata bump was rejected by G8 and restored to source-approved Theme metadata `1.1.0` before this final PR-head matrix.

**Gate:** merge PR #58 into canonical `main` requires explicit owner approval. After merge, D-022 requires fresh exact-main verification; PR-head evidence cannot substitute for it.

### P4-B — Law Site Provisioning v1.1

**State:** **PR HEAD PASS / INTEGRATED IN PR #58; OWNER MAIN GATE.**

D-026 `law01-v1-1` remains an additive WordPress-native setup workflow. Recommendations are proposal-only until explicit change-plan confirmation; activation remains mutation-free; existing-site overwrite and whole-site noindex takeover remain forbidden. Starter Pages/Posts/Categories/Attachments become ordinary WordPress-owned content after successful provisioning. New/mostly-empty-site starter publication is coupled to explicit native search-visibility consent; active-site starter Posts fail safe to Draft without a public per-Post SEO contract.

**Fresh integrated QA:** exact head `356ef8c00991c63e294d619d42c7094d1074226d` completed 21/21 triggered PR workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced deterministic Theme `1.1.0` candidate `aznet-theme-1.1.0-law-site-provisioning-v1-1-candidate.zip`, 121 files, SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` passed the WP 6.9 / PHP 8.1 / MySQL 8 runtime/browser/a11y matrix; artifact `10128198277`.

**Integration debugging evidence:** G8 run `34412609546` correctly rejected unapproved Theme metadata `1.1.1`; commit `ec1e86f631a1b341736422296ae4689830a505c6` restored both metadata locations to `1.1.0`. R2 browser run `34413075882` exposed a disposable CI-server lifecycle failure, not a Theme runtime defect; test-only commit `356ef8c00991c63e294d619d42c7094d1074226d` made the portability harness restart that disposable server and the rerun passed.

**Known observation:** the separately recorded WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and does not support a blanket PHP-log-clean claim.

**Gate:** PR #61, #60 and #59 have been merged upward through the stack; PR #58 is now the sole canonical-main integration gate. No tag/GitHub Release, L5 provider certification or production deployment is authorized by this state.

**Next:** explicit owner disposition for PR #58 -> `main`; after merge, run D-022 exact-main verification before any release-candidate promotion.""",
)
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "**P4 — Real law-site pilot QA on the post-P3 candidate from canonical `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Prove the real current-stack route/responsive/a11y/Rank Math/RootProfile matrix and measured site behavior. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**",
    "**Owner-gated PR #58 -> `main` integration is the exact next action. If approved and merged, run D-022 fresh exact-main verification on the resulting merge SHA before promoting any release candidate; then continue P4 real-pilot QA. P1 duplicate-theme cleanup remains a separate destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**",
)

# Derived execution map.
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "**Version:** v0.24  ",
    "**Version:** v0.25  ",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "**Date:** 09/09/2026",
    "**Date:** 10/09/2026",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "> **D-026 candidate checkpoint:** Law Site Provisioning v1.1 (`law01-v1-1`) completed the approved stacked implementation path with proposal-only smart recommendations, bounded starter editorial/media coverage and index-safe publication. Active sites never receive whole-site noindex for starter content.\n\n**Completed execution:** `P4-B v1.1 source gate → recommendation/classification → starter editorial → plan/admin UX → media/runner → index/readiness → L3/L4 → candidate/evidence = PASS on verified head d564c6e8...`.",
    "> **PR #58 integration checkpoint:** Homepage Composer + Law 01 Premium + D-025/D-026 provisioning are integrated on `work/homepage-composer-law01`. Theme metadata is restored to source-approved `1.1.0`; proposal-only recommendations, WordPress ownership and index-safety boundaries are retained.\n\n**Completed execution:** `stack integration -> G8 RED on unapproved 1.1.1 metadata -> minimal 1.1.0 GREEN -> R2 harness RED -> test-only server-restart GREEN -> 21/21 PR workflows + deterministic package/browser PASS on 356ef8c...`.",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "| P4 Real Pilot QA | READY / EXACT NEXT | Pilot QA execution approved; current-stack law-site route/responsive/a11y/Rank Math/RootProfile evidence required |\n| P4-B Law Site Provisioning | V1.1 CANDIDATE PASS / STACKED | `law01-v1-1`: L1-L4 + deterministic candidate PASS on `d564c6e8...`; activation remains mutation-free, existing-site overwrite remains forbidden, PR #61 integration remains owner-gated |",
    "| P4 Real Pilot QA | READY / AFTER MAIN INTEGRATION | Pilot QA remains approved; run after PR #58 canonical-main disposition |\n| P4-A Homepage Composer + Law 01 | PR HEAD PASS / MAIN GATE | Integrated in PR #58; exact head `356ef8c...` has full PR matrix GREEN |\n| P4-B Law Site Provisioning | PR HEAD PASS / MAIN GATE | D-026 integrated in PR #58; Theme `1.1.0`; deterministic package/browser PASS; activation mutation-free and active-site overwrite/noindex takeover forbidden |",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "Current post-hardening implementation baseline: `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`, Theme `1.1.0`, tree `8fc309ea8e01bfe727da943754c98164b3f92a58`. Fresh `V1 Exact Main Verification` run `34195248467` succeeded on that exact SHA.",
    "Current canonical implementation baseline remains `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, Theme `1.1.0`, tree `32ebca25a63d511e74250646d333202994c65328`. PR #58 remains a verified candidate until owner-approved main integration and subsequent exact-main verification.",
)
replace_section(
    "docs/source/AZT-EXEC-MAP.md",
    "### P4-B — Law Site Provisioning v1.1",
    "### P5 — Final Candidate / Publication / Deployment",
    """### P4-B — Law Site Provisioning v1.1

**State:** PR HEAD PASS / MAIN GATE. D-026 implementation has been integrated upward through PR #61 -> #60 -> #59 into PR #58.

**Goal/boundary:** provision or explicitly map the bounded Law 01 starter structure with proposal-only recommendation, starter editorial/media coverage and index-safe publication without transferring ongoing WordPress content ownership to Theme.

**Fresh verification:** exact PR-head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered PR workflows successfully. Package run `34413505990`; browser/runtime run `34413506017`; candidate `aznet-theme-1.1.0-law-site-provisioning-v1-1-candidate.zip`; SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; package artifact `10128165973`; browser artifact `10128198277`.

**Regression closure:** G8 rejected candidate-only metadata `1.1.1` and the minimal fix restored `1.1.0`; R2 portability failure was reduced to a disposable CI-server lifecycle issue and fixed only in the harness. No ownership/domain workaround was introduced.

**Rollback:** before successful provisioning handoff, restore captured settings/Front Page/menu assignment/indexing and remove only current-run creations. After success, presentation rollback is `homepage_preset=off` or remapping; WordPress-owned content persists.

**Exit:** PASS through integrated PR-head L1-L4 + deterministic candidate. No exact-main, L5 provider certification, tag/GitHub Release or production deployment is implied.

**Next:** explicit owner gate for PR #58 -> `main`; if merged, D-022 exact-main verification precedes release-candidate promotion.""",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "**P4 — Real Pilot QA from canonical post-P3 `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Prepare an exact candidate for the approved pilot, verify the current-stack route/responsive/a11y/Rank Math/RootProfile matrix, record defects/evidence, and keep P1 duplicate-theme deletion as a separate destructive site-ops gate. Stop before tag/GitHub Release or final production deployment.**",
    "**Owner-gated PR #58 -> `main` integration is exact next. After an approved merge, run D-022 exact-main verification on the merge SHA, then continue the approved P4 real-pilot route/responsive/a11y/Rank Math/RootProfile matrix. P1 duplicate-theme deletion remains a separate destructive site-ops gate. Stop before tag/GitHub Release or final production deployment.**",
)

# Compact manifest rewrite eliminates stale stacked-state wording.
Path("docs/source/SOURCE_MANIFEST.md").write_text(
    """# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate, merged P2/P3 editorial hardening, and the PR #58 Homepage/Law01/Provisioning integration candidate. Canonical `main` is unchanged until the explicit PR #58 owner gate clears.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership and v1.1 objective/non-goals |
| `AZT-02-architecture.md` | v0.8 | Theme architecture, public integration contracts and D-026 boundary |
| `AZT-03-baseline-provenance.md` | v0.29 | Canonical implementation/provenance plus PR #58 integrated candidate evidence |
| `AZT-04-roadmap-qa-decisions.md` | v0.33 | R0-R6/P2-P3 retained PASS, P4-A/P4-B PR-head PASS, PR #58 main gate, P5 gates and D-001..D-026 |
| `AZT-EXEC-MAP.md` | v0.25 | Derived execution map with PR #58 integration checkpoint and exact next |

Current canonical implementation baseline: `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, tree `32ebca25a63d511e74250646d333202994c65328`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Historical D-026 stacked evidence remains preserved at verified head `d564c6e8a43f458a493fe816c4fc768d4917065f`, package run `34362997863` and browser run `34362997879`. Its `1.1.1` candidate label is historical only and is not the current integration/release metadata baseline.

The stack is now integrated upward: PR #61 -> #60 at `f10751b5fadd19107bbec7bcd1fafa2bd92d409b`; PR #60 -> #59 at `bb2155d114922f585a1650b6bbdf61b3ce6414a3`; PR #59 -> #58 at `ed087860364b8f8ad542226866d604013aee8ff5`. Each recorded merge introduced no conflict-resolution file delta against its approved head.

PR #58 integration verification caught and closed two issues before main: G8 run `34412609546` rejected unapproved Theme metadata `1.1.1`, fixed by `ec1e86f631a1b341736422296ae4689830a505c6` restoring `1.1.0`; R2 run `34413075882` exposed a disposable PHP-server lifecycle failure, fixed only in the CI harness at `356ef8c00991c63e294d619d42c7094d1074226d`.

Exact PR-head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced `aznet-theme-1.1.0-law-site-provisioning-v1-1-candidate.zip`, 121 files, SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` succeeded with artifact `10128198277`.

The known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made. Provider L5 certification, Git tag/GitHub Release and production deployment remain separate gates.

**Exact next:** explicit owner disposition for PR #58 -> canonical `main`. If merged, D-022 requires fresh exact-main verification on the merge SHA before any release-candidate promotion; P4 real-pilot QA then continues under its existing approval.

Derived DOCX source material remains archival/export evidence only. Canonical source changes occur in `docs/source/` through reviewed Git history.
"""
)

Path("docs/evidence/HOMEPAGE_LAW01_PROVISIONING_PR58_INTEGRATION.md").write_text(
    """# Homepage Law 01 + Provisioning — PR #58 Integration Evidence

**Date:** 2026-09-10  
**Repository:** `truongdinhnamaz/aznet-theme`  
**PR:** #58 `work/homepage-composer-law01` -> `main`  
**Verified production/test head:** `356ef8c00991c63e294d619d42c7094d1074226d`  
**Theme metadata:** `1.1.0`

## PASS

- Approved stacked work was integrated upward: PR #61 -> #60 (`f10751b5...`), PR #60 -> #59 (`bb2155d1...`), PR #59 -> #58 (`ed087860...`), with no conflict-resolution file delta at the recorded merges.
- G8 run `34412609546` provided intended RED: candidate-only commit `97110c42...` had incorrectly promoted Theme metadata to `1.1.1`. Minimal production fix `ec1e86f...` restored `style.css` and `AZNET_THEME_VERSION` to source-approved `1.1.0`; the exact G8 contract passed and the reusable core/package gates are GREEN.
- R2 Pattern Library Browser run `34413075882` provided a second RED at theme-switch portability because the disposable single-process PHP development server was no longer listening. The uploaded server log stopped without a Theme PHP fatal/warning at that boundary. Test-only commit `356ef8c...` restarts only the disposable CI runtime before portability rendering; the same assertions then passed in run `34413505952`.
- Exact head `356ef8c...` completed all 21 triggered pull-request workflows successfully: reusable core, lifecycle, R1-R6 static/browser/performance, native Homepage, P4 blueprint, Homepage Composer/Law01, Provisioning package/browser and R2 portability.

## Deterministic candidate

`Law Site Provisioning Candidate Package` run `34413505990`:

- checkout: exact `356ef8c00991c63e294d619d42c7094d1074226d`;
- PHP `8.1.34`, mbstring + mysqli;
- production PHP lint: 93 files PASS;
- reusable v1 core verification PASS;
- deterministic double build PASS;
- `unzip -t` PASS;
- exact-byte source/package comparison: 121 files PASS;
- candidate: `aznet-theme-1.1.0-law-site-provisioning-v1-1-candidate.zip`;
- inner SHA-256: `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`;
- artifact ID: `10128165973`.

## Runtime / browser / accessibility

`Law Site Provisioning Browser Quality` run `34413506017` PASS on WordPress 6.9 / PHP 8.1 / MySQL 8:

- recommendation-first wizard;
- new / active / rerun / rollback / user-edit / index-restore scenarios;
- starter Hero from WordPress uploads and starter editorial coverage;
- Law 01 responsive Homepage coverage at 1440 / 1024 / 390 / 320;
- full-width Hero / Services / Final CTA with constrained inner content;
- zero horizontal overflow in covered assertions;
- covered axe critical/serious blockers = 0;
- browser artifact ID `10128198277`.

## Ownership / safety retained

- activation is mutation-free;
- recommendations remain proposal-only until explicit confirmation;
- no authoritative slug/URL/fuzzy/synonym mapping;
- WordPress owns created/reused Page/Post/Category/Attachment and publication state after handoff;
- active sites do not receive whole-site noindex from starter provisioning;
- no Rank Math/Yoast/private SEO storage writes;
- no RootProfile/ConvertFlow/Woo private data or copied domain workflow;
- no fake lawyer/person/credential/current-news claims.

## UNKNOWN / not implied

The separately known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking. This evidence does not claim blanket PHP-log cleanliness, L5 provider certification, canonical-main PASS, tag/GitHub Release or production deployment.

## NEXT

PR #58 -> `main` remains an explicit owner merge gate. If merged, run D-022 fresh exact-main verification on the resulting merge SHA before promoting any release candidate. P4 real-pilot QA remains a subsequent approved execution step.
"""
)
