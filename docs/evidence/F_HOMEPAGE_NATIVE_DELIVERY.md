# F Homepage Native Delivery Evidence

Date: 2026-09-07
Repository: `truongdinhnamaz/aznet-theme`
Base main: `11f66c1508254c69bba92f97e9725a08cc64e41e`
Work branch: `work/f-homepage-native-delivery`
Scope: Theme-owned Homepage shell/native fallback only

## Ownership boundary

This delivery slice belongs only to AZnet Theme presentation ownership:

- Theme owns `front-page.php`, Header/Footer/site shell, native WordPress Page-body composition and responsive/accessibility presentation.
- WordPress remains owner of Page content/query semantics; the template preserves normal `the_content()` rendering.
- No provider business/domain logic, private storage reads, authoritative routing heuristics, lifecycle/state/analytics/conversion logic, or plugin source is added to the Theme.
- F6/F7 actual external integration evidence is not claimed by this slice. F9 release closure is not claimed.
- Source documents remain frozen; this file is implementation evidence only.

## TDD: F1/F2/F3/F5

### RED

The four Theme-owned contracts were added before `front-page.php` existed.

GitHub Actions run `34085264164` on RED head `7db0090d1450beead9684bd94a29b186e071bf15` failed for the intended missing behavior:

```text
FAIL: front-page.php missing
```

### Minimal GREEN

A native `front-page.php` was added with:

- standard `get_header()` / `get_footer()` composition;
- exactly one Theme-owned `main#main`;
- normal WordPress Loop and `the_content()` Page-body boundary;
- native no-content fallback;
- no provider/domain/private-storage coupling.

Initial GREEN production commit: `437c5c6f9053f710acac5902005c7e85f122e0f8`.

Run `34085301293` PASS on PHP 8.1.34:

- F1 native front-page shell contract PASS;
- F2 WordPress body integration boundary contract PASS;
- F3 Homepage ownership static contract PASS;
- F5 Homepage absent/native fail-soft contract PASS;
- `front-page.php` PHP lint PASS.

## TDD: native runtime/browser surface

### RED

The first real WordPress runtime gate used WordPress 6.9 + PHP 8.1.34 and rendered the native Page body/Header, but failed because the explicit Theme presentation surface class was absent:

- run `34085367906`;
- candidate `68364f0d5b83fa38d9663364bee9fc36dd2871d5`;
- failing requirement: `aznet-theme-main--front-page`.

### Minimal GREEN

Only the Theme presentation class was added to the existing main element.

Fix commit: `7e9d00ee79c36a2716a5c74875a52c2f4350e0c5`.

Current production blob:

- `front-page.php` -> `f23c9e2fae86b6c39c3cdc574c83579abfb6d9c1`.

Fresh contract run `34085451823` PASS.

Fresh native L3/L4 run `34085451771` PASS:

- WordPress 6.9;
- PHP 8.1.34;
- MySQL 8.0;
- Theme activates and Homepage HTTP/runtime sentinel renders;
- Theme Header/Main/Footer present;
- no PHP Fatal/Warning/Parse/Uncaught markers in the runtime gate;
- Playwright/axe PASS at 1440x1000, 1024x900 and 390x844;
- one `main#main`, no duplicate IDs, horizontal overflow <= 1px;
- visible keyboard skip-link focus to `#main`;
- mobile navigation opens without horizontal overflow;
- axe critical/serious = 0;
- browser console/page errors = 0.

Runtime/browser artifact:

- artifact ID `10005065710`;
- wrapper digest `sha256:6c3383ce4d2d35db80649ba28eced2662d27989649e16f46b307cc9b259c0734`.

## Retained regression and candidate package

Regression/package run `34085529974` on candidate head `761488b74f6f870d1b6dee0ca80a6c7a4980b549` PASS:

- retained E5-B contracts PASS;
- retained W1-W6 contract/regression chains PASS;
- retained W8 public Theme Integration Contract bridge PASS;
- F1/F2/F3/F5 contracts PASS;
- complete production PHP lint 29/29 PASS;
- deterministic package rebuild byte-identical;
- package file count: 45;
- installable package SHA-256: `c5a01a7929da190670ecba976211a1d8c255ddf2b450eea944b45c759b126707`;
- unzip/exact-byte reverify PASS;
- internal Theme version remains `0.1.0-alpha.7`;
- retained integration blobs remain exact:
  - `inc/theme/assets.php` -> `f19e134137412fa79a4865824c89a76c5024ce8a`;
  - `assets/css/integrations/convertflow.css` -> `39115a63f00a369be72b40dd01dbd988e1d55cc5`;
- packaged `front-page.php` blob -> `f23c9e2fae86b6c39c3cdc574c83579abfb6d9c1`.

Candidate package artifact:

- artifact ID `10005072390`;
- wrapper digest `sha256:a7741e2fd81b1fad71a241a4e1b7f210139ae38a6013247a0fccfb315b3902e6`.

This package is verification evidence only; it is not a release/promotion claim.

## Not claimed

This slice does not claim:

- F6 Save/Preview/Publish external integration PASS;
- F7 resolver/state/analytics/conversion external integration PASS;
- F9 integration/release closure;
- Milestone E completion;
- production deployment;
- release promotion.

## Rollback / recovery

Until owner-approved main merge, canonical `main@11f66c1508254c69bba92f97e9725a08cc64e41e` remains the recovery point. The bounded production delta is the new Theme-owned `front-page.php`; rollback is to close/revert the delivery PR without changing external provider data or behavior.

## Next

Open a bounded Theme-native PR to `main`, run fresh pull-request contract/runtime/browser/regression-package checks on the actual PR candidate, and stop at the owner merge approval gate.
