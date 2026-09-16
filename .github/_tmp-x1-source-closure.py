from pathlib import Path


def replace_once(path: str, old: str, new: str) -> None:
    p = Path(path)
    text = p.read_text()
    count = text.count(old)
    if count != 1:
        raise SystemExit(f'{path}: expected exactly one occurrence, found {count}: {old!r}')
    p.write_text(text.replace(old, new, 1))


evidence = '''# X1 Comments Surface Closure Evidence — 16/09/2026

## Scope

X1 closes the first v1.2 WordPress Experience Completion slice: native WordPress Comments presentation only. WordPress remains authoritative for comment data, moderation, threading, reply state, pagination semantics and lifecycle. AZnet Theme owns only template composition, presentation, responsive/accessibility behavior and Theme-owned asset loading.

No RootProfile, ConvertFlow, WooCommerce/provider contract or Theme version promotion is part of X1.

## RED -> GREEN provenance

- RED contract: `tests/offline/x1-comments-surface-contract.php`.
- RED run `35059406912` failed for the intended pre-change reason: `comments.php` was missing. This was not a PHP syntax or infrastructure failure.
- Minimal GREEN introduced native `comments.php`, X1-scoped `comments.css`, native-Post-only `comments_template()` composition and surface-aware comment assets.
- GREEN run `35059691123` on head `5bc42c976c8adebfdf6ac1a5d35f65e3c8384c6f` passed the X1 contract, retained editorial single-post regression and PHP lint.
- One retained test was narrowed, not weakened globally: `editorial-single-post-contract.php` now permits only the exact WordPress Core read `get_option( 'thread_comments' )`; other `get_option(` reads remain forbidden by that contract.

## L3 — real WordPress runtime

Run `35060029655` on head `bdda36eedc1e7318ce4cb7edcb832a204312d8cc` completed SUCCESS on WordPress `6.9`, PHP `8.1`, zero active plugins.

Observed runtime assertions:

- native Post renders exactly the Theme comments surface;
- parent comment and nested child reply render through WordPress native comment APIs;
- native comments pagination renders;
- native comment form renders;
- `comments.css` loads on the comments surface and is absent from Home;
- WordPress Core `comment-reply` loads when threaded comments are enabled;
- no Theme/provider datastore, custom comment query or comment lifecycle engine is introduced.

Runtime artifact: `10432266002`, digest `sha256:e752a9aa02b8e3a7b88c7e99bfdf759bca23870f26ff488a451e3ec95cfa707d`.

The initial runtime attempt `35059843328` failed only because WP-CLI `eval-file` cannot execute a fixture containing `declare(strict_types=1)` in its eval context. The fixture-only declaration was removed; production Theme behavior was unchanged by that fix. Failure artifact `10432500194` is retained as debugging provenance only and is not PASS evidence.

## L4 — browser / responsive / accessibility

Run `35060242381` on exact head `e5f61c14c9f58ff2930888794c4acee37f8acf18` completed SUCCESS.

The Playwright + axe matrix passed **3/3** at:

- `1440x1000`;
- `1024x900`;
- `390x844`.

The matrix enforces, per viewport:

- one document `<main>`;
- visible `#comments.aznet-theme-comments`;
- one native comment list;
- parent + nested child present;
- native comment form textarea and native comments pagination present;
- zero horizontal overflow;
- keyboard-reachable visible focus indicator on reply link and comment textarea;
- zero critical/serious axe violations inside `#comments`;
- zero console errors, page errors and failed non-document subresources;
- X1 comments stylesheet observed.

L4 artifact: `10431563663`, digest `sha256:3f2fc1e3a83ca7a413a12e99aadcef801d71a7bcef83fb2983433e1f76f049aa`.

## Final functional closure gate

Run `35060458118` on functional closure head `1b58012f78eab6525e5e8e424e8904995035f4ea` completed SUCCESS.

In addition to the L3/L4 matrix, this gate verified:

- `git diff --check` against canonical `main`;
- no X1 delta under `inc/integrations/` or `assets/css/integrations/`;
- no change to `functions.php` or `style.css` version declarations;
- Theme metadata remains exactly `1.1.0`;
- X1 comments contract PASS;
- retained editorial single-post contract PASS;
- retained editorial listing/search contract PASS;
- retained D-027 provisioning-independence contract PASS;
- PHP lint PASS for changed X1 PHP/runtime fixture;
- clean WordPress 6.9 zero-plugin runtime and browser matrix PASS.

Closure artifact: `10432306423`, digest `sha256:f35d19996f56c5202e32a0ad6101c02c14708bb39fd380364b89b6e81a2c9637`.

The first source-only closure runner `35060748364` failed before any job was created because the temporary workflow embedded an invalid YAML heredoc. It executed no repository mutation and no site/runtime action; the source-closure harness was then reduced to a standalone temporary Python script.

## Implementation boundary

Production files added/changed by X1 are bounded to the native Theme surface:

- `comments.php`;
- `single.php`;
- `inc/theme/assets.php`;
- `assets/css/components/comments.css`.

Retained/new verification lives under `tests/` and `.github/workflows/x1-comments-surface.yml`.

No provider adapter is changed. No Theme metadata is promoted. Provider L5 remains separate and is not inferred.

## Rollback

X1 is presentation-only and independently reversible. Reverting the X1 production/template/asset commits removes the Theme comments presentation without deleting or mutating WordPress-owned comments. Theme switching continues to leave comment data under WordPress ownership.

## PASS / UNKNOWN / NEXT

**PASS:** X1 Comments Surface at L1-L4 on the functional closure head, with fresh exact runtime/browser evidence and retained ownership/version regressions.

**UNKNOWN / not claimed:** provider L5; X2 behavior; merge-to-main exact-head state; v1.2 release/package closure.

**NEXT:** X2 — Search / 404 / Empty States. Write and review its bounded implementation plan before production implementation.
'''
Path('docs/evidence/X1_COMMENTS_SURFACE_20260916.md').write_text(evidence)

replace_once('docs/source/AZT-04-roadmap-qa-decisions.md', '**Version:** v0.43', '**Version:** v0.44')
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '| X | v1.2 WordPress Experience Completion | DESIGN RATIFIED / IMPLEMENTATION PLANNING | WordPress Core surfaces only; surface modules + shared primitives; deep polish Comments, Search/404/empty states, Media/gallery/embed; no new provider integration |',
    '| X | v1.2 WordPress Experience Completion | X1 PASS / X2 NEXT | X1 Comments Surface passed L1-L4 with WordPress 6.9 zero-plugin runtime/browser evidence; X2 Search/404/empty states is next; no new provider integration |'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '**State:** **DESIGN RATIFIED / IMPLEMENTATION PLANNING.**',
    '**State:** **X1 PASS / X2 NEXT.**'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '**Design record:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`.\n\n**Exact Next after source ratification:** write and review the implementation plan for **X1 — Comments Surface** before production implementation.',
    '**Design record:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`.\n\n**X1 — Comments Surface:** **PASS at L1-L4** on functional closure head `1b58012f78eab6525e5e8e424e8904995035f4ea`; final functional workflow `35060458118` completed SUCCESS with ownership/diff/version regressions, clean WordPress 6.9 zero-plugin runtime and 3/3 browser/a11y matrix. Evidence: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`. WordPress retains all comment data/lifecycle ownership; provider L5 is not inferred.\n\n**Exact Next:** write and review the bounded implementation plan for **X2 — Search / 404 / Empty States** before production implementation.'
)
replace_once(
    'docs/source/AZT-04-roadmap-qa-decisions.md',
    '**v1.2 WordPress Experience Completion design is ratified. Exact Next is to write and review the X1 Comments Surface implementation plan before production implementation. No provider L5 work is opened or inferred.**',
    '**X1 Comments Surface is PASS at L1-L4. Exact Next is to write and review the X2 Search / 404 / Empty States implementation plan before production implementation. No provider L5 work is opened or inferred.**'
)

replace_once('docs/source/AZT-EXEC-MAP.md', '**Version:** v0.35', '**Version:** v0.36')
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '| X v1.2 WordPress Experience Completion | DESIGN RATIFIED / IMPLEMENTATION PLANNING | Core WordPress surfaces only; X1-X6 sequence; no provider integration expansion |',
    '| X v1.2 WordPress Experience Completion | X1 PASS / X2 NEXT | X1 Comments Surface L1-L4 PASS; X2 Search/404/empty states next; Core WordPress surfaces only and no provider integration expansion |'
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '**State:** DESIGN RATIFIED / IMPLEMENTATION PLANNING.',
    '**State:** X1 PASS / X2 NEXT.'
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '**X1 — Comments Surface:** native comments template/list/form/reply/pagination presentation; no comment engine/store.',
    '**X1 — Comments Surface:** **PASS at L1-L4** — native comments template/list/form/reply/pagination presentation; WordPress 6.9 zero-plugin runtime and 3/3 browser/a11y matrix verified by run `35060458118`; no comment engine/store. Evidence: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`.'
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '**X2 — Search / 404 / Empty States:** recovery/discovery presentation over native query/routing truth; no custom ranking/query engine.',
    '**X2 — Search / 404 / Empty States:** **NEXT** — recovery/discovery presentation over native query/routing truth; no custom ranking/query engine.'
)
replace_once(
    'docs/source/AZT-EXEC-MAP.md',
    '**v1.2 WordPress Experience Completion design is ratified. Exact Next is a reviewed implementation plan for X1 Comments Surface before production code.**',
    '**X1 Comments Surface is PASS at L1-L4. Exact Next is a reviewed implementation plan for X2 Search / 404 / Empty States before production code.**'
)

replace_once(
    'docs/source/SOURCE_MANIFEST.md',
    'Canonical source is reconciled through the completed v1.1 release-critical path and the owner-approved v1.2 WordPress Experience Completion design ratification. v1.2 remains a WordPress-Core-only Theme milestone; provider compatibility/certification tracks remain separate.',
    'Canonical source is reconciled through the completed v1.1 release-critical path and v1.2 X1 Comments Surface closure. v1.2 remains a WordPress-Core-only Theme milestone; X1 is PASS at L1-L4, X2 Search/404/empty states is next, and provider compatibility/certification tracks remain separate.'
)
replace_once(
    'docs/source/SOURCE_MANIFEST.md',
    '| `AZT-04-roadmap-qa-decisions.md` | v0.43 | v1.1 release-critical PASS retained; v1.2 WordPress Experience Completion X1-X6 design ratified |',
    '| `AZT-04-roadmap-qa-decisions.md` | v0.44 | v1.1 release-critical PASS retained; v1.2 X1 Comments PASS at L1-L4 and X2 exact next |'
)
replace_once(
    'docs/source/SOURCE_MANIFEST.md',
    '| `AZT-EXEC-MAP.md` | v0.35 | Derived execution map with v1.2 X1-X6 sequence and Core-only boundaries |',
    '| `AZT-EXEC-MAP.md` | v0.36 | Derived execution map with X1 PASS, X2 NEXT and retained Core-only boundaries |'
)
replace_once(
    'docs/source/SOURCE_MANIFEST.md',
    'Approved design companion: `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`. It does not supersede AZT source ownership; AZT-04 owns roadmap/decisions and AZT-EXEC-MAP remains derived.',
    'Approved design companion: `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`. X1 closure evidence: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`. Neither supersedes AZT source ownership; AZT-04 owns roadmap/decisions and AZT-EXEC-MAP remains derived.'
)
replace_once(
    'docs/source/SOURCE_MANIFEST.md',
    '**Exact next:** v1.2 WordPress Experience Completion design is ratified. Write and review the X1 Comments Surface implementation plan before production implementation. Provider L5 remains separate and is not inferred.',
    '**Exact next:** X1 Comments Surface is PASS at L1-L4. Write and review the X2 Search / 404 / Empty States implementation plan before production implementation. Provider L5 remains separate and is not inferred.'
)
