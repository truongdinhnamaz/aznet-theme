# P4 Native Editable Homepage Blueprint — L1-L4 Evidence

**Date:** 2026-09-08  
**Scope:** AZnet Theme P4 Homepage production hardening  
**Canonical base:** `main@08b74209013fc7c71d2a6d98ea0e457ccfd336d4`  
**Verified feature head:** `e164d56f2cfd3196628d88452181def2b837ce0a`  
**Verified PR synthetic merge:** `6e6038e6381a974d60db9ad64f32f3653b3d7301`  
**PR:** #55

## 1. Result boundary

The Native Editable Homepage Blueprint implementation is GREEN through automated L1-L4 on the verified production bytes.

Delivered Theme-owned presentation:

- native full-page Block Pattern `aznet-theme/homepage-professional-services`;
- pattern category `aznet-theme-pages` / `AZnet — Pages`;
- editable Hero, trust, services, about, process, latest-Posts Query, FAQ and final CTA sections;
- WordPress core Query block for recent Posts;
- dedicated Homepage stylesheet loaded only on `is_front_page()`;
- class-scoped Block Editor presentation parity;
- read-only Control Center Homepage Setup guidance;
- unchanged `front-page.php` WordPress `the_content()` ownership boundary.

The Theme does not auto-create, auto-publish, overwrite or set the Front Page. It does not introduce provider-private reads, a custom Homepage content store, custom ranking/query logic or SEO/schema duplication.

This evidence does **not** claim the Tâm Đức real-site Homepage content has already been inserted/published, does not close the remaining P4 route matrix, and does not authorize tag/GitHub Release/final deployment.

## 2. RED -> GREEN history

### Structure/ownership RED

Run `34217201993`, job `102031650386` failed after retained contracts and production lint at the intended new gate:

```text
FAIL: homepage professional-services pattern missing
```

Minimal GREEN added the native full-page pattern and `AZnet — Pages` category.

### R2 stale exact-count guard

Adding a seventeenth native pattern exposed an R2 test that treated the historical total `16` as a permanent repository-wide maximum. No R2 capability defect was reproduced.

The shallow test fix preserved all named R2 pattern/capability checks, changed the R2 core total to a floor of at least 16 and kept the Woo-gated total exact at 2. This permits later Theme-owned native patterns without weakening R2 behavior assertions.

### Asset-scope RED

Run `34217681157`, job `102033207903` reached the new asset contract after the structure gate was GREEN and failed for the intended reason:

```text
FAIL: homepage frontend asset helper missing
```

Minimal GREEN added `homepage.css`, Front Page-only frontend enqueue and class-scoped Block Editor parity.

### R1 stale blanket editor-hook guard

The new scoped editor stylesheet exposed an R1 test that prohibited every `enqueue_block_editor_assets` hook. R1's actual visual-preset editor mechanism remains `add_editor_style( editor_stylesheets() )`.

The shallow test correction permits only the dedicated Homepage editor hook while retaining the assertion that visual-preset assets are not moved onto that hook.

### Control Center RED

Run `34218256690`, job `102035058616` passed structure and asset gates, then failed exactly at the missing read-only setup helper:

```text
FAIL: Homepage Setup guidance missing: function render_homepage_setup_card(): void
```

Minimal GREEN added WordPress-native Front Page status/edit/configuration guidance without content mutation.

## 3. Runtime/browser harness debugging

### Default WordPress Post fixture

Initial P4 browser run `34218636393` reached the disposable WordPress fixture and failed because a clean WordPress install already contained the default `Hello world!` Post, making the expected six fixture Posts total seven.

The harness was made deterministic by deleting pre-existing disposable Posts before creating the six controlled Vietnamese editorial fixtures. No production Theme code changed for this correction.

### Real accessibility defect: inverse About eyebrow contrast

Run `34218838505` reached all four browser viewports and reproduced the same axe `color-contrast` serious violation at each viewport.

Artifact `10052923955` identified exactly one failing node:

```html
<p class="has-primary-color has-text-color has-sm-font-size">Về chúng tôi</p>
```

Computed foreground/background were `#17181b` / `#111722`, contrast `1.01:1` where `4.5:1` was required.

Root cause: the WordPress `has-primary-color` utility overrode the inverse About section's inherited foreground. The minimal Theme fix scopes that paragraph to `var(--aznet-theme-on-inverse) !important` only inside `.aznet-theme-homepage-blueprint__about`.

## 4. Final L3-L4 verification

Workflow: **P4 Homepage Blueprint Browser Quality**  
Run: `34219272396` — SUCCESS  
Job: `102038295594` — SUCCESS  
Feature head: `e164d56f2cfd3196628d88452181def2b837ce0a`  
PR synthetic merge checked out by Actions: `6e6038e6381a974d60db9ad64f32f3653b3d7301`

Environment:

- Ubuntu 24.04;
- PHP `8.1.34`;
- WordPress `6.9`;
- MySQL `8.0.46`;
- Node `22.23.2`;
- Playwright `1.55.0` / Chromium `140.0.7339.16`;
- axe Playwright `4.10.2`.

Before browser execution, the workflow proved:

- all three P4 offline contracts PASS;
- changed production PHP files lint clean;
- exact Theme candidate activates on WordPress 6.9;
- registered Pattern content can populate a disposable static Front Page;
- six controlled native Posts render through the core Query block;
- Theme Header/Main/Footer and `homepage.css` are present;
- no `PHP Fatal error`, `PHP Warning`, `PHP Parse error` or `Uncaught` marker is found in the tested runtime log.

## 5. Browser / responsive / accessibility matrix

All four cases PASS:

| Viewport | main#main | H1 | Query cards | FAQ details | Overflow | Duplicate IDs | Blocking axe | Console/page/request errors |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | --- |
| 1440x1000 | 1 | 1 | 6 | 3 | 0px | 0 | 0 | 0 |
| 1024x900 | 1 | 1 | 6 | 3 | 0px | 0 | 0 | 0 |
| 390x844 | 1 | 1 | 6 | 3 | 0px | 0 | 0 | 0 |
| 320x760 | 1 | 1 | 6 | 3 | 0px | 0 | 0 | 0 |

At every viewport the first keyboard target is the visible AZnet Theme skip link to `#main` with an active focus indicator. Mobile navigation is additionally opened/closed by keyboard at the mobile widths without creating horizontal overflow.

Final log output:

```text
PASS: p4-homepage-blueprint-1440x1000
PASS: p4-homepage-blueprint-1024x900
PASS: p4-homepage-blueprint-390x844
PASS: p4-homepage-blueprint-320x760
PASS: P4 Homepage blueprint browser/visual/a11y automated verification 4/4
PASS: P4 Homepage blueprint L3-L4 verification
```

## 6. Final artifact

Artifact name: `p4-homepage-blueprint-browser-quality`  
Artifact ID: `10053082646`  
Artifact size: `2,000,041` bytes  
Artifact digest:

`sha256:d4f50f5f45182ebd9e280254a08f3f5d4fe8102da0879bcc8dcf322d1ebcdadb`

Independent artifact inspection confirms each browser case records:

- `status=passed`;
- `mainCount=1`;
- `h1Count=1`;
- `articleCards=6`;
- `detailsCount=3`;
- `overflowPx=0`;
- no duplicate IDs;
- visible skip-link focus indicator;
- `blockingAxeViolations=0`;
- empty console/page/request failure arrays.

## 7. Ownership / portability boundary

The implementation retains:

- WordPress Page/content/query/media authority;
- a normal `the_content()` Front Page boundary;
- core block serialization rather than a proprietary Homepage content schema;
- no automatic production Page mutation;
- no RootProfile identity inference/copy;
- no ConvertFlow Journey/state/resolver access;
- no Woo commerce ownership;
- no Rank Math/SEO metadata duplication.

The disposable workflow is allowed to create/set test content because the fixture owns that isolated WordPress runtime; those mutation steps do not exist in production Theme behavior.

## 8. Next gate

Before merge, clean the PR to approved production/test/spec/plan/evidence files only and require retained final PR verification on the exact production bytes.

Merge to `main` remains owner-gated. After an approved merge, require fresh exact-main verification and deterministic installable package verification. The Tâm Đức real-site next step is then to replace the Theme package, insert `Homepage — Professional Services` once into the existing Front Page, publish the native blocks and continue P4 real-site visual/content/integration QA.
