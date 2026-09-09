from pathlib import Path

MAIN = "04edd8b312de70e6ee8339c461ae8f16f93e5859"
TREE = "28c30353e9f01bc51f4474347069738d410b5256"
RUN = "34414878827"
STATIC_ARTIFACT = "10128678294"
BROWSER_ARTIFACT = "10128707347"


def read(path: str) -> str:
    return Path(path).read_text()


def write(path: str, text: str) -> None:
    Path(path).write_text(text)


def replace_once(path: str, old: str, new: str) -> None:
    text = read(path)
    if old not in text:
        raise SystemExit(f"missing expected text in {path}: {old[:120]!r}")
    write(path, text.replace(old, new, 1))


def replace_section(path: str, start: str, end: str, body: str) -> None:
    text = read(path)
    a = text.find(start)
    if a < 0:
        raise SystemExit(f"missing section start in {path}: {start!r}")
    b = text.find(end, a)
    if b < 0:
        raise SystemExit(f"missing section end in {path}: {end!r}")
    write(path, text[:a] + body.rstrip() + "\n\n" + text[b:])


def replace_tail(path: str, marker: str, body: str) -> None:
    text = read(path)
    p = text.find(marker)
    if p < 0:
        raise SystemExit(f"missing tail marker in {path}: {marker!r}")
    write(path, text[:p] + body.rstrip() + "\n")


# AZT-03 — canonical implementation baseline/provenance owner.
replace_once("docs/source/AZT-03-baseline-provenance.md", "**Version:** v0.29  ", "**Version:** v0.30  ")
replace_once(
    "docs/source/AZT-03-baseline-provenance.md",
    "- Current canonical repository/implementation head after PR #57: `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`.\n- Current canonical implementation tree: `32ebca25a63d511e74250646d333202994c65328`.",
    f"- Current canonical repository/implementation head after owner-approved PR #58 merge: `main@{MAIN}`.\n- Current canonical implementation tree: `{TREE}`.",
)
replace_once(
    "docs/source/AZT-03-baseline-provenance.md",
    "- P3 Editorial Listing/Search Hardening: native featured image/date scan presentation and resilient long-title/excerpt styling while preserving WordPress main-query ownership.",
    "- P3 Editorial Listing/Search Hardening: native featured image/date scan presentation and resilient long-title/excerpt styling while preserving WordPress main-query ownership.\n- P4-A Homepage Composer + Law 01 Premium presentation with typed WordPress Content Map, exactly one native `the_content()` boundary and Theme-owned presentation only.\n- P4-B D-025/D-026 Law Site Provisioning with mutation-free activation, proposal-only recommendations, WordPress-owned starter content/media and index-safe publication boundaries.",
)
replace_section(
    "docs/source/AZT-03-baseline-provenance.md",
    "### Homepage Law 01 + Law Site Provisioning integration candidate — PR #58, not canonical main",
    "## 3. Current release-path classification",
    f'''### Homepage Law 01 + Law Site Provisioning — canonical main integration

The D-024/D-025/D-026 stack is canonical Theme implementation after owner-approved PR #58 merge. The previously recorded stacked `1.1.1` package remains historical candidate evidence only; the integration/release metadata baseline is the source-approved Theme `1.1.0`.

PR #58 merged to canonical `main@{MAIN}` with tree `{TREE}`. The merge tree is identical to final PR head `992cf0274a9bdec93131fbb69401516b5c602ed8`, so no conflict-resolution byte delta was introduced by the canonical merge.

Fresh D-022 `V1 Exact Main Verification` run `{RUN}` completed SUCCESS on exact `main@{MAIN}`. `static-contracts` passed the reusable v1 core/static contract gate on PHP 8.1; `clean-runtime-browser` passed WordPress 6.9 clean runtime routes plus core browser/a11y verification. Exact-main evidence artifacts: static `{STATIC_ARTIFACT}` and runtime/browser `{BROWSER_ARTIFACT}`.

Pre-merge integration package evidence remains retained: `Law Site Provisioning Candidate Package` run `34413505990`, Theme `1.1.0`, 121 packaged files, SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`; browser/runtime run `34413506017`, artifact `10128198277`.

The separately known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and is not a blanket PHP-log-clean claim. No source statement here authorizes a Git tag, GitHub Release, L5 provider certification, destructive pilot cleanup or final production deployment. Detailed integration evidence remains `docs/evidence/HOMEPAGE_LAW01_PROVISIONING_PR58_INTEGRATION.md`.''',
)
replace_once(
    "docs/source/AZT-03-baseline-provenance.md",
    "Current product-development next is governed by AZT-04. P2/P3 are closed; the exact next is P4 Real Pilot QA. Duplicate-theme cleanup on the pilot remains a separate destructive site-operations gate until completed.",
    f"Current product-development next is governed by AZT-04. P2/P3 and P4-A/P4-B canonical integration are closed through exact-main verification on `main@{MAIN}`; the exact next is P4 Real Pilot QA. Duplicate-theme cleanup on the pilot remains a separate destructive site-operations gate until completed.",
)
replace_tail(
    "docs/source/AZT-03-baseline-provenance.md",
    "## 11. Exact next",
    f'''## 11. Exact next

**P4 — Real Pilot QA from canonical `main@{MAIN}` after owner-approved PR #58 merge and successful D-022 exact-main verification run `{RUN}`. Prove current-stack routes/responsive/a11y/Rank Math/RootProfile compatibility and measured actual-site behavior. P1 duplicate-theme deletion remains a separate destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**''',
)

# AZT-04 — roadmap / QA / gate owner.
replace_once("docs/source/AZT-04-roadmap-qa-decisions.md", "**Version:** v0.33  ", "**Version:** v0.34  ")
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "| P4 | Real law-site pilot QA | READY / AFTER MAIN INTEGRATION | Owner-approved pilot QA remains pending; PR #58 integration must clear its explicit main gate first |\n| P4-A | Homepage Composer + Law 01 | PR HEAD PASS / MAIN GATE | Premium Law01 + Composer integrated in PR #58; exact PR-head matrix is GREEN; canonical-main merge remains owner-gated |\n| P4-B | Law Site Provisioning v1.1 | PR HEAD PASS / MAIN GATE | D-026 smart setup integrated in PR #58 with Theme metadata restored to `1.1.0`; exact PR-head package/browser matrix is GREEN |",
    "| P4 | Real law-site pilot QA | READY / EXACT NEXT | Owner-approved pilot QA is now the exact next after canonical PR #58 integration and D-022 exact-main PASS |\n| P4-A | Homepage Composer + Law 01 | PASS / MERGED | Premium Law01 + Composer merged through PR #58; exact-main static/runtime/browser core verification PASS |\n| P4-B | Law Site Provisioning v1.1 | PASS / MERGED | D-026 smart setup merged through PR #58 with Theme metadata `1.1.0`; deterministic package/browser evidence retained and exact-main verification PASS |",
)
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "Current canonical implementation remains `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, tree `32ebca25a63d511e74250646d333202994c65328`, Theme metadata `1.1.0`. PR #58 is a verified integration candidate only until an explicit owner-approved merge; no PR-head PASS is promoted to exact-main evidence.",
    f"Current canonical implementation is `main@{MAIN}`, tree `{TREE}`, Theme metadata `1.1.0`. Owner-approved PR #58 merge is complete. Fresh `V1 Exact Main Verification` run `{RUN}` succeeded on that exact merge SHA; PR-head evidence is retained separately and is not substituted for exact-main evidence.",
)
replace_section(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "### P4-A — Homepage Composer + Law 01",
    "### P5 — Publication and deployment",
    f'''### P4-A — Homepage Composer + Law 01

**State:** **PASS / MERGED TO CANONICAL MAIN.**

Homepage Composer Core + `law-01` Premium presentation merged through PR #58 to `main@{MAIN}`. Content Map remains typed WordPress references only; preset switching does not mutate WordPress content; native Post/Page Classic Editor policy remains; `front-page.php` retains exactly one `the_content()` boundary.

Fresh D-022 exact-main run `{RUN}` passed reusable static/contracts plus WordPress 6.9 clean runtime/browser/a11y on the exact merge SHA. The integration-only `1.1.1` metadata attempt remains rejected historical evidence; canonical Theme metadata is `1.1.0`.

### P4-B — Law Site Provisioning v1.1

**State:** **PASS / MERGED TO CANONICAL MAIN.**

D-026 `law01-v1-1` remains an additive WordPress-native setup workflow. Recommendations are proposal-only until explicit change-plan confirmation; activation remains mutation-free; existing-site overwrite and whole-site noindex takeover remain forbidden. Starter Pages/Posts/Categories/Attachments become ordinary WordPress-owned content after successful provisioning. New/mostly-empty-site starter publication is coupled to explicit native search-visibility consent; active-site starter Posts fail safe to Draft without a public per-Post SEO contract.

Pre-merge integrated QA remains retained: package run `34413505990` produced Theme `1.1.0` deterministic candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; browser/runtime run `34413506017` passed WordPress 6.9 / PHP 8.1 / MySQL 8 provisioning scenarios. Canonical integration is separately proven by exact-main run `{RUN}` SUCCESS on `main@{MAIN}`.

**Known observation:** the separately recorded WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and does not support a blanket PHP-log-clean claim.

**Next:** P4 Real Pilot QA under the already-approved pilot execution scope. No tag/GitHub Release, L5 provider certification, destructive duplicate-theme deletion or final production deployment is authorized by this merge.''',
)
replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. P4 pilot QA execution is now approved. None of these approvals include Git tag/GitHub Release or final production deployment.",
    f"Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration is cleared at `main@{MAIN}` and D-022 exact-main run `{RUN}` is PASS. P4 pilot QA execution remains approved. None of these approvals include Git tag/GitHub Release, destructive duplicate-theme cleanup, L5 provider certification or final production deployment.",
)
replace_tail(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "## 14. Exact next",
    f'''## 14. Exact next

**P4 — Real law-site pilot QA on canonical `main@{MAIN}`. D-022 exact-main verification run `{RUN}` is PASS. Prove the real current-stack route/responsive/a11y/Rank Math/RootProfile matrix and measured site behavior. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**''',
)

# AZT-EXEC-MAP — derived execution state. Use stable section markers for P4-B.
replace_once("docs/source/AZT-EXEC-MAP.md", "**Version:** v0.25  ", "**Version:** v0.26  ")
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "> **PR #58 integration checkpoint:** Homepage Composer + Law 01 Premium + D-025/D-026 provisioning are integrated on `work/homepage-composer-law01`. Theme metadata is restored to source-approved `1.1.0`; proposal-only recommendations, WordPress ownership and index-safety boundaries are retained.\n\n**Completed execution:** `stack integration -> G8 RED on unapproved 1.1.1 metadata -> minimal 1.1.0 GREEN -> R2 harness RED -> test-only server-restart GREEN -> 21/21 PR workflows + deterministic package/browser PASS on 356ef8c...`.",
    f"> **Canonical-main checkpoint:** Homepage Composer + Law 01 Premium + D-025/D-026 provisioning merged through PR #58 to `main@{MAIN}`. Theme metadata remains source-approved `1.1.0`; proposal-only recommendations, WordPress ownership and index-safety boundaries are retained.\n\n**Completed execution:** `stack integration -> G8 RED on unapproved 1.1.1 metadata -> minimal 1.1.0 GREEN -> R2 harness RED -> test-only server-restart GREEN -> 21/21 PR workflows -> owner-approved PR #58 merge -> D-022 exact-main PASS run {RUN}`.",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "| P4 Real Pilot QA | READY / AFTER MAIN INTEGRATION | Pilot QA remains approved; run after PR #58 canonical-main disposition |\n| P4-A Homepage Composer + Law 01 | PR HEAD PASS / MAIN GATE | Integrated in PR #58; exact head `356ef8c...` has full PR matrix GREEN |\n| P4-B Law Site Provisioning | PR HEAD PASS / MAIN GATE | D-026 integrated in PR #58; Theme `1.1.0`; deterministic package/browser PASS; activation mutation-free and active-site overwrite/noindex takeover forbidden |",
    "| P4 Real Pilot QA | READY / EXACT NEXT | Pilot QA remains approved; canonical-main integration and D-022 exact-main verification are complete |\n| P4-A Homepage Composer + Law 01 | PASS / MERGED | PR #58 merged; exact-main static/runtime/browser core verification PASS |\n| P4-B Law Site Provisioning | PASS / MERGED | D-026 merged; Theme `1.1.0`; deterministic package/browser evidence retained; activation mutation-free and active-site overwrite/noindex takeover forbidden |",
)
replace_once(
    "docs/source/AZT-EXEC-MAP.md",
    "Current canonical implementation baseline remains `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, Theme `1.1.0`, tree `32ebca25a63d511e74250646d333202994c65328`. PR #58 remains a verified candidate until owner-approved main integration and subsequent exact-main verification.",
    f"Current canonical implementation baseline is `main@{MAIN}`, Theme `1.1.0`, tree `{TREE}`. PR #58 is merged and D-022 exact-main verification run `{RUN}` succeeded on that exact SHA.",
)
replace_section(
    "docs/source/AZT-EXEC-MAP.md",
    "### P4-B — Law Site Provisioning v1.1",
    "### P5 — Final Candidate / Publication / Deployment",
    f'''### P4-B — Law Site Provisioning v1.1

**State:** PASS / MERGED. D-026 implementation integrated upward through PR #61 -> #60 -> #59 -> #58 and is canonical at `main@{MAIN}`.

**Goal/boundary:** provision or explicitly map the bounded Law 01 starter structure with proposal-only recommendation, starter editorial/media coverage and index-safe publication without transferring ongoing WordPress content ownership to Theme.

**Retained verification:** exact PR production/test head `356ef8c00991c63e294d619d42c7094d1074226d` completed 21/21 triggered PR workflows. Package run `34413505990`; browser/runtime run `34413506017`; Theme `1.1.0` candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; package artifact `10128165973`; browser artifact `10128198277`.

**Canonical verification:** owner-approved PR #58 merged to `main@{MAIN}`, tree `{TREE}`. D-022 exact-main run `{RUN}` passed static/contracts and clean WordPress 6.9 runtime/browser/a11y; artifacts `{STATIC_ARTIFACT}` and `{BROWSER_ARTIFACT}`.

**Regression closure:** G8 rejected candidate-only metadata `1.1.1` and the minimal fix restored `1.1.0`; R2 portability failure was reduced to a disposable CI-server lifecycle issue and fixed only in the harness. No ownership/domain workaround was introduced.

**Rollback:** before successful provisioning handoff, restore captured settings/Front Page/menu assignment/indexing and remove only current-run creations. After success, presentation rollback is `homepage_preset=off` or remapping; WordPress-owned content persists.

**Exit:** PASS through canonical-main integration plus D-022 exact-main verification. P4 real-site pilot and P5 publication/deployment remain separate gates.

**Next:** P4 Real Pilot QA under the existing owner approval; stop before P5 publication/deployment gates.''',
)
replace_tail(
    "docs/source/AZT-EXEC-MAP.md",
    "## 15. Exact next",
    f'''## 15. Exact next

**P4 — Real Pilot QA from canonical `main@{MAIN}` after D-022 exact-main PASS run `{RUN}`. Verify the current-stack route/responsive/a11y/Rank Math/RootProfile matrix, record defects/evidence, and keep P1 duplicate-theme deletion as a separate destructive site-ops gate. Stop before tag/GitHub Release or final production deployment.**''',
)

# SOURCE_MANIFEST — short current-state index; rewrite atomically to avoid brittle historical sentence matching.
manifest = f'''# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate, merged P2/P3 editorial hardening, and owner-approved PR #58 Homepage/Law01/Provisioning canonical integration with D-022 exact-main verification.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership and v1.1 objective/non-goals |
| `AZT-02-architecture.md` | v0.8 | Theme architecture, public integration contracts and D-026 boundary |
| `AZT-03-baseline-provenance.md` | v0.30 | Canonical implementation/provenance including PR #58 merge + exact-main evidence |
| `AZT-04-roadmap-qa-decisions.md` | v0.34 | R0-R6/P2-P3/P4-A/P4-B PASS, P4 real-pilot exact next, P5 gates and D-001..D-026 |
| `AZT-EXEC-MAP.md` | v0.26 | Derived execution map with canonical PR #58 checkpoint and P4 exact next |

Current canonical implementation baseline: `main@{MAIN}`, tree `{TREE}`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Historical D-026 stacked evidence remains preserved at verified head `d564c6e8a43f458a493fe816c4fc768d4917065f`, package run `34362997863` and browser run `34362997879`. Its `1.1.1` candidate label is historical only and is not the current integration/release metadata baseline.

PR #58 integration verification caught and closed two issues before canonical merge: G8 run `34412609546` rejected unapproved Theme metadata `1.1.1`, fixed by `ec1e86f631a1b341736422296ae4689830a505c6` restoring `1.1.0`; R2 run `34413075882` exposed a disposable PHP-server lifecycle failure, fixed only in the CI harness at `356ef8c00991c63e294d619d42c7094d1074226d`.

Exact PR production/test head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced Theme `1.1.0` candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` succeeded with artifact `10128198277`.

Owner-approved PR #58 merged to canonical `main@{MAIN}` with final tree `{TREE}`. Fresh D-022 `V1 Exact Main Verification` run `{RUN}` completed SUCCESS on that exact SHA; exact-main artifacts: static `{STATIC_ARTIFACT}` and clean runtime/browser `{BROWSER_ARTIFACT}`.

The known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made. RootProfile E5-C/E5-D and ConvertFlow F8 remain separate optional compatibility gates under their existing ownership rules.

**Exact next:** P4 Real Pilot QA on canonical `main@{MAIN}` under the existing owner approval. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Git tag/GitHub Release and final production deployment remain separate P5 owner gates.

Derived DOCX source material remains archival/export evidence only. Canonical source changes occur in `docs/source/` through reviewed Git history.
'''
write("docs/source/SOURCE_MANIFEST.md", manifest)

# Append immutable post-merge evidence rather than rewriting historical sections.
evidence = "docs/evidence/HOMEPAGE_LAW01_PROVISIONING_PR58_INTEGRATION.md"
text = read(evidence)
heading = "## Post-merge canonical-main verification"
if heading not in text:
    text = text.rstrip() + f'''\n\n{heading}\n\n- Owner-approved PR #58 merged to `main@{MAIN}`.\n- Merge tree: `{TREE}`; identical to final PR-head tree, so no conflict-resolution byte delta.\n- D-022 `V1 Exact Main Verification` run `{RUN}`: **SUCCESS** on exact merge SHA.\n- `static-contracts`: SUCCESS; artifact `{STATIC_ARTIFACT}`.\n- `clean-runtime-browser`: SUCCESS on WordPress 6.9 / PHP 8.1; artifact `{BROWSER_ARTIFACT}`.\n- Theme metadata remains `1.1.0`.\n- No tag, GitHub Release, L5 provider certification, destructive pilot cleanup or final production deployment is implied.\n'''
    write(evidence, text)
