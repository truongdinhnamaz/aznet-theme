# R6 Deterministic v1.x Release-Candidate Infrastructure — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 Task 5 pre-promotion infrastructure  
**Canonical base:** `main@a6a901b5712291330368935d662aeda8370e618b`  
**Latest fully verified infrastructure head:** `25780625e8522de5f96b0dd25dea6274de4f5f22`  
**Verified tree:** `fb3f5232a39f09d1d191ef5a4a5557d611799755`  
**Theme metadata:** `1.0.0` — intentionally not promoted in this task

## 1. Delivered

- `scripts/build-release-package.py` — deterministic, version-aware release package builder;
- `.github/workflows/v1-release-candidate.yml` — reusable manual candidate workflow restricted to canonical `main`;
- `tests/offline/r6-release-workflow-contract.php` — deterministic/package/release-boundary guard.

The workflow builds an artifact candidate only. It does not create a Git tag, GitHub Release or deployment.

## 2. RED -> GREEN history

### RED 1 — missing reusable release workflow

Head: `687c9455a1f43e97fdfe94b0c1f8a9272f6cfac4`  
Run: `34182381779`  
Job: `101923891698`

Expected failure:

```text
FAIL: missing v1-release-candidate.yml
```

This established the missing-infrastructure baseline.

### Initial implementation and retained-identity review

The first builder implementation was deterministic in isolation, but review against the already-accepted G release package policy found a provenance risk: it used a different fixed ZIP timestamp and a weaker directory-exclusion interpretation.

Because release bytes are provenance-sensitive, R6 did not silently establish a second package identity rule.

### RED 2 — retained deterministic identity mismatch

Head: `44905a7b8b983d4c85060e7d9c841f9084324ed7`  
Run: `34182685870`  
Job: `101924784465`

The strengthened contract deliberately required the retained package identity rules and failed exactly at:

```text
FAIL: package builder missing deterministic/version marker: FIXED_ZIP_TIME = (2020, 1, 1, 0, 0, 0)
```

Earlier R6 contracts in the same run remained green. This isolated the failure to package provenance rather than Theme behavior.

### Final GREEN

Head: `25780625e8522de5f96b0dd25dea6274de4f5f22`  
Tree: `fb3f5232a39f09d1d191ef5a4a5557d611799755`  
Run: `34182766540`  
Job: `101925012266`  
Result: **SUCCESS**

Fresh final run completed successfully for:

- exact R6 checkout identity;
- R6 asset-scope contract;
- ConvertFlow public-capability gate;
- reusable PR/exact-main CI contract;
- reusable release-workflow contract;
- full reusable v1 core verification chain;
- deterministic release package builder exercised twice and byte-compared;
- exact unzip/source-tree comparison;
- WordPress 6.9 clean browser baseline;
- WooCommerce 10.9.4 browser baseline;
- runtime error-boundary check;
- artifact upload.

Artifact:

- name: `r6-performance-baseline`;
- ID: `10039500525`;
- digest: `sha256:f362ef0dad8ffffd36caed7aba3031e026dae4285f3c497333517fa865920cca`.

Independent artifact inspection confirmed:

```text
checkout head = 25780625e8522de5f96b0dd25dea6274de4f5f22
checkout tree = fb3f5232a39f09d1d191ef5a4a5557d611799755
pre-promotion package = aznet-theme-1.0.0.zip
package SHA-256 = 477b4f398c3072bfe1d5553ecbbc70591a1129719d7eed68e91632df6a85b2af
clean baseline = 12/12, failures 0, max horizontal overflow 0
Woo baseline = 10/10, failures 0, max horizontal overflow 0
PHP Fatal/Warning/Parse/Uncaught markers = none
```

The `1.0.0` package above exists only to exercise the infrastructure before the promotion gate. It is not a v1.1 release candidate and is not published.

## 3. Retained deterministic package identity

The R6 builder preserves the accepted G candidate semantics:

- ZIP root: `aznet-theme/`;
- fixed entry timestamp: `(2020, 1, 1, 0, 0, 0)`;
- stable sorted paths;
- `ZIP_DEFLATED` compression level 9;
- file mode `0644` through ZIP external attributes;
- excludes `.git`, `.github`, `docs`, `scripts`, `tests` at any path depth;
- excludes root `README.md` and `aznet-preview.png`;
- requires `style.css`, `functions.php`, `theme.json`, `front-page.php`, `index.php`;
- requires `style.css` Version and `AZNET_THEME_VERSION` to equal the requested package version.

This avoids a second package/provenance dialect.

## 4. Reusable candidate workflow boundary

`V1 Release Candidate Package` is `workflow_dispatch` only and requires:

- branch ref `refs/heads/main`;
- checkout SHA exactly equal to `GITHUB_SHA`;
- read-only repository contents permission;
- style/PHP version equality;
- full reusable v1 core verification;
- two deterministic builds + `cmp`;
- SHA-256;
- unzip and exact source-byte comparison;
- packaged PHP lint;
- extracted-package install/activation/render on WordPress 6.9;
- artifact upload.

It contains no release-publication or deployment action.

## 5. Gate

This task proves the reusable candidate infrastructure on the R6 branch. The actual v1.1 package cannot be built until metadata promotion to `1.1.0` is explicitly approved and performed after exact-main pre-promotion closure.

## NEXT

Complete the R6 infrastructure pull-request gate, merge only with owner approval, prove the new exact-main workflow on canonical `main`, and only then evaluate legacy workflow retirement. Stop before changing metadata to `1.1.0` until explicit approval.