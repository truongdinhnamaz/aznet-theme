from pathlib import Path


def replace_once(path: str, old: str, new: str) -> None:
    p = Path(path)
    text = p.read_text()
    if old not in text:
        raise SystemExit(f'missing marker in {path}: {old[:120]!r}')
    p.write_text(text.replace(old, new, 1))


def append_once(path: str, marker: str, block: str) -> None:
    p = Path(path)
    text = p.read_text()
    if block.strip() in text:
        return
    if marker not in text:
        raise SystemExit(f'missing append marker in {path}: {marker!r}')
    p.write_text(text.replace(marker, marker + block, 1))

# AZT-02 — architecture owner.
replace_once('docs/source/AZT-02-architecture.md', '| **Mã tài liệu** | AZT-02 | **Phiên bản** | v0.8 |', '| **Mã tài liệu** | AZT-02 | **Phiên bản** | v0.9 |')
replace_once('docs/source/AZT-02-architecture.md', '| **Trạng thái** | Working Source | **Ngày** | 09/09/2026 |', '| **Trạng thái** | Working Source | **Ngày** | 16/09/2026 |')
append_once(
    'docs/source/AZT-02-architecture.md',
    '- Current explicit Content Map and WordPress publication/media state remain authoritative over recommendations and provisioning provenance.\n',
    '''\n\n# 16. Standalone Core runtime independence — D-027\n\nAZnet Theme has **zero mandatory third-party runtime dependency**. WordPress + AZnet Theme alone must provide a complete Core product on the support floor. Optional providers may add presentation capabilities through approved public/versioned contracts but may not complete an otherwise partial Core.\n\n## 16.1 Core runtime invariant\n\n- Core install/activate/setup/author/render must work with zero active third-party plugins.\n- Core includes Design System, Header/Footer, native Logo/Menu/Search presentation, Homepage Composer, Theme-owned presets including `law-01`, Quick Setup/Law Site Provisioning, native Post/Page/Archive/Search/404 presentation, native author fallback, Control Center, System Health core readiness and Theme-owned settings portability.\n- Provider absence is a normal state, not a Core error. Core admin must not require, nag for or redirect users to install optional plugins.\n- Optional-provider UI is capability-driven: hide provider-specific workflows when the public capability is absent rather than rendering a disabled required-plugin surface.\n- System Health must separate **Standalone Core** readiness from **Optional Integrations** availability. Optional `Not present` must not downgrade Core readiness.\n\n## 16.2 Provisioning independence\n\nLaw01 provisioning must complete from resources shipped with the Theme package plus WordPress public APIs. The supported setup path must not require an AZnet API, mandatory CDN, remote template repository, license server, AI service, plugin marketplace or third-party demo importer. Starter media may be bundled and imported into WordPress Media Library; created content remains WordPress-owned after successful provisioning.\n\n## 16.3 Optional provider boundary\n\n- WooCommerce absent => complete non-commerce Theme; present => additive commerce presentation only, with Woo retaining commerce truth.\n- RootProfile absent => WordPress-native author/site presentation; present => additive identity/Profile presentation through public contracts only.\n- ConvertFlow absent => native Homepage Composer remains complete; present => additive Journey projection only, with ConvertFlow retaining Journey/conversion semantics.\n- SEO plugins remain optional coexistence targets; Theme must not write private SEO-plugin storage to provide standalone behavior.\n\n## 16.4 QA/release invariant\n\nThe exact final package must pass a zero-plugin standalone path before Core Ready/publication: install -> activate -> setup -> provision -> representative runtime/browser/a11y -> update/theme-switch continuity. Optional L5 compatibility certification is additive and cannot substitute for Standalone Core PASS. Development/QA tools such as GitHub Actions, WP-CLI, Playwright and axe are allowed because they are not website runtime dependencies.\n\nA QA runner's inability to resolve/authenticate to a pilot site is recorded as `P4 PILOT ACCESS BLOCKED`; it is not evidence that the Theme requires a connector/plugin.\n'''
)

# AZT-04 — roadmap / QA / decision owner.
replace_once('docs/source/AZT-04-roadmap-qa-decisions.md', '**Version:** v0.34  ', '**Version:** v0.35  ')
replace_once('docs/source/AZT-04-roadmap-qa-decisions.md', '**Date:** 10/09/2026', '**Date:** 16/09/2026')
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '| P4 | Real law-site pilot QA | READY / EXACT NEXT | Owner-approved pilot QA is now the exact next after canonical PR #58 integration and D-022 exact-main PASS |',
    '| P4 | Real law-site pilot QA | PILOT ACCESS BLOCKED / APPROVED | Execution remains approved, but the current QA environment cannot reach the pilot; this is an environment/access blocker, not a Theme runtime dependency |'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '| P4-B | Law Site Provisioning v1.1 | PASS / MERGED | D-026 smart setup merged through PR #58 with Theme metadata `1.1.0`; deterministic package/browser evidence retained and exact-main verification PASS |\n| P5 | Publication & production deployment | GATED | Tag/GitHub Release and final production deployment remain separate explicit owner gates |',
    '| P4-B | Law Site Provisioning v1.1 | PASS / MERGED | D-026 smart setup merged through PR #58 with Theme metadata `1.1.0`; deterministic package/browser evidence retained and exact-main verification PASS |\n| P4-C | Standalone Core independence | READY / EXACT NEXT | D-027 accepted: zero mandatory third-party runtime dependencies; implementation must turn clean-WP independence into executable L1-L4 and final-package gates |\n| P5 | Publication & production deployment | GATED | Tag/GitHub Release and final production deployment remain separate explicit owner gates |'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '| **D-026** | **Law Site Provisioning v1.1 may preselect non-authoritative exact-label recommendations, seed bounded WordPress-native starter editorial/media content, and temporarily discourage indexing only on a confirmed new/mostly-empty-site plan; active-site starter content fails safe to Draft without a public per-Post SEO contract, and all authoritative mapping/mutation remains explicit in the confirmed provisioning plan.** | **Accepted** |',
    '| **D-026** | **Law Site Provisioning v1.1 may preselect non-authoritative exact-label recommendations, seed bounded WordPress-native starter editorial/media content, and temporarily discourage indexing only on a confirmed new/mostly-empty-site plan; active-site starter content fails safe to Draft without a public per-Post SEO contract, and all authoritative mapping/mutation remains explicit in the confirmed provisioning plan.** | **Accepted** |\n| **D-027** | **AZnet Theme Core has zero mandatory third-party runtime dependency. WordPress + Theme alone must complete install/activate/setup/provision/author/render on the support floor; optional providers are additive capability tracks, provider absence is not Core failure, and the exact final package must pass a zero-plugin standalone release path before Core Ready/publication. External development/QA tooling is allowed only outside deployed runtime.** | **Accepted** |'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    'Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration is cleared at `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` and D-022 exact-main run `34414878827` is PASS. P4 pilot QA execution remains approved. None of these approvals include Git tag/GitHub Release, destructive duplicate-theme cleanup, L5 provider certification or final production deployment.',
    'Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration is cleared at `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` and D-022 exact-main run `34414878827` is PASS. P4 pilot QA execution remains approved but is currently access-blocked from the available QA environment. D-027 Standalone Core architecture was explicitly approved on 16/09/2026 and its implementation may proceed on a bounded branch. None of these approvals include Git tag/GitHub Release, destructive duplicate-theme cleanup, L5 provider certification or final production deployment.'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '**P4 — Real law-site pilot QA on canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`. D-022 exact-main verification run `34414878827` is PASS. Prove the real current-stack route/responsive/a11y/Rank Math/RootProfile matrix and measured site behavior. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**',
    '**P4-C / D-027 — implement the Standalone Core contract on a bounded work branch. Start from fresh L0 state, add an explicit RED for the currently missing standalone guarantee, preserve already-PASS zero-plugin behavior, and prove L1-L4 plus an exact-package zero-plugin path before P5. P4 real-pilot execution remains approved but `P4 PILOT ACCESS BLOCKED` until the QA environment can reach the pilot. Stop before tag/GitHub Release or final production deployment.**'
)

# EXEC-MAP — derived execution state.
replace_once('docs/source/AZT-EXEC-MAP.md', '**Version:** v0.26  ', '**Version:** v0.27  ')
replace_once('docs/source/AZT-EXEC-MAP.md', '**Date:** 10/09/2026', '**Date:** 16/09/2026')
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '| P4 Real Pilot QA | READY / EXACT NEXT | Pilot QA remains approved; canonical-main integration and D-022 exact-main verification are complete |',
    '| P4 Real Pilot QA | PILOT ACCESS BLOCKED / APPROVED | Pilot QA remains approved; current QA environment cannot reach the pilot; no product dependency is inferred |'
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '| P4-B Law Site Provisioning | PASS / MERGED | D-026 merged; Theme `1.1.0`; deterministic package/browser evidence retained; activation mutation-free and active-site overwrite/noindex takeover forbidden |\n| P5 Publication/Deployment | GATED | Tag/GitHub Release and final production deploy remain explicit owner actions |',
    '| P4-B Law Site Provisioning | PASS / MERGED | D-026 merged; Theme `1.1.0`; deterministic package/browser evidence retained; activation mutation-free and active-site overwrite/noindex takeover forbidden |\n| P4-C Standalone Core independence | READY / EXACT NEXT | D-027 accepted; zero-plugin WordPress + Theme path becomes an executable release invariant; optional integrations remain additive |\n| P5 Publication/Deployment | GATED | Tag/GitHub Release and final production deploy remain explicit owner actions |'
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '**Next:** P4 Real Pilot QA under the existing owner approval; stop before P5 publication/deployment gates.\n\n### P5 — Final Candidate / Publication / Deployment',
    '''**Next:** P4-C / D-027 Standalone Core implementation; pilot QA remains access-blocked in parallel.\n\n### P4-C — Standalone Core Independence\n\n**State:** READY / EXACT NEXT. D-027 architecture approved 16/09/2026.\n\n**Goal:** turn the existing WordPress-clean principle into an executable product/release contract: WordPress + AZnet Theme only must complete install/activate/setup/provision/author/render without mandatory third-party plugins or external runtime services.\n\n**Core boundary:** retain Theme-owned Design System, Header/Footer, native Homepage Composer, `law-01`, Quick Setup/Provisioning, native editorial templates, Control Center/System Health and settings portability as zero-plugin Core. Optional WooCommerce/RootProfile/ConvertFlow/SEO integrations stay additive and public-contract-based.\n\n**QA:** L1 dependency/nag scan; L2 provider-absent contracts; L3 full clean-WP provisioning/runtime; L4 setup/frontend responsive+a11y; final P5 package must repeat the zero-plugin path on exact package bytes.\n\n**UX:** System Health separates Standalone Core from Optional Integrations; optional `Not present` does not downgrade Core. No required-plugin nags, marketplace redirects or setup blockers for absent providers.\n\n**Rollback:** bounded tests/admin presentation only; preserve existing provider adapters and WordPress-owned provisioning outputs.\n\n**Exit:** Standalone Core matrix PASS through L1-L4 with exact-package gate wired for P5.\n\n**Next:** resume P4 pilot when access exists; otherwise continue P5 only after D-027 package gate is satisfied and separately approved.\n\n### P5 — Final Candidate / Publication / Deployment'''
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '**P4 — Real Pilot QA from canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` after D-022 exact-main PASS run `34414878827`. Verify the current-stack route/responsive/a11y/Rank Math/RootProfile matrix, record defects/evidence, and keep P1 duplicate-theme deletion as a separate destructive site-ops gate. Stop before tag/GitHub Release or final production deployment.**',
    '**P4-C / D-027 — implement Standalone Core independence from current canonical production bytes, beginning with a focused RED that proves the missing guarantee. Preserve existing PASS behavior, keep P4 pilot as `PILOT ACCESS BLOCKED / APPROVED`, and stop before tag/GitHub Release or final production deployment.**'
)

# Source manifest.
replace_once('docs/source/SOURCE_MANIFEST.md', '| `AZT-02-architecture.md` | v0.8 | Theme architecture, public integration contracts and D-026 boundary |', '| `AZT-02-architecture.md` | v0.9 | Theme architecture, public integration contracts and D-027 Standalone Core runtime-independence boundary |')
replace_once('docs/source/SOURCE_MANIFEST.md', '| `AZT-04-roadmap-qa-decisions.md` | v0.34 | R0-R6/P2-P3/P4-A/P4-B PASS, P4 real-pilot exact next, P5 gates and D-001..D-026 |', '| `AZT-04-roadmap-qa-decisions.md` | v0.35 | R0-R6/P2-P3/P4-A/P4-B PASS, D-027/P4-C Standalone Core exact next, P4 pilot access-blocked, P5 gates and D-001..D-027 |')
replace_once('docs/source/SOURCE_MANIFEST.md', '| `AZT-EXEC-MAP.md` | v0.26 | Derived execution map with canonical PR #58 checkpoint and P4 exact next |', '| `AZT-EXEC-MAP.md` | v0.27 | Derived execution map with canonical PR #58 checkpoint and D-027/P4-C exact next |')
replace_once(
    'docs/source/SOURCE_MANIFEST.md',
    '**Exact next:** P4 Real Pilot QA on canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` under the existing owner approval. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Git tag/GitHub Release and final production deployment remain separate P5 owner gates.',
    '**Exact next:** D-027 / P4-C Standalone Core implementation on a bounded work branch. P4 Real Pilot QA remains owner-approved but `P4 PILOT ACCESS BLOCKED` from the current QA environment; this does not create any Theme runtime dependency. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Git tag/GitHub Release and final production deployment remain separate P5 owner gates.'
)

# Sanity assertions.
checks = {
    'docs/source/AZT-02-architecture.md': ['v0.9', 'Standalone Core runtime independence — D-027'],
    'docs/source/AZT-04-roadmap-qa-decisions.md': ['v0.35', 'D-027', 'P4-C / D-027'],
    'docs/source/AZT-EXEC-MAP.md': ['v0.27', 'P4-C — Standalone Core Independence'],
    'docs/source/SOURCE_MANIFEST.md': ['v0.9', 'D-001..D-027', 'P4-C'],
}
for path, needles in checks.items():
    text = Path(path).read_text()
    for needle in needles:
        if needle not in text:
            raise SystemExit(f'missing postcondition {needle!r} in {path}')

print('PASS: D-027 authoritative source ratification patched')
