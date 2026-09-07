# G6 — Core Lifecycle: Update / Theme Switch / Rollback — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Workflow: `.github/workflows/g-core-lifecycle.yml`

## Initial failed harness run

Run: `34106683612`  
Job: `101693206593`  
Head: `7ecbcee7d3fb3b0d8477fd8466a0f70c3f2a9fca`

Result: **FAIL**, but only because the test harness assumed an external `twentytwentysix` theme package was present/downloadable. Prior Theme activation, native data creation, prior runtime and in-place candidate replacement had already passed. No Theme lifecycle defect was reproduced.

The failure was reduced to the test fixture dependency:

- `wp theme activate twentytwentysix` -> theme not found;
- `wp theme install twentytwentysix --activate` -> theme not found.

Artifact from this failed harness run: `10012668664`, SHA-256 `01b86ea963e86b8d1a28df67a3683126a37b37a32ad869e3814e0bc3e412c9a5`.

## Minimal harness correction

Commit: `a875966bf0584505b8b1cfa0741126834390c0d2`

The lifecycle test now creates a deterministic local alternate Theme fixture (`g6-switch-target`) inside the disposable WordPress runtime. This removes an unrelated network/package dependency while preserving the behavior under test: switch away from AZnet Theme, then back.

No production AZnet Theme PHP/CSS/JS/template behavior changed.

## Fresh GREEN evidence

GitHub Actions run: `34106938428`  
Job: `101694010718` (`lifecycle`)  
Result: **SUCCESS**  
WordPress: `6.9`  
PHP: `8.1.34`  
Prior rollback/source commit: `b7443a96c8687493a0a4994aeca23b5e09a8770c`

### Lifecycle assertions proven

1. Install/activate prior AZnet Theme bytes under the stable `aznet-theme/` directory.
2. Create WordPress-native persistent state: static front Page, Page, Post and Primary Menu assignment.
3. Create opaque external-state sentinel `aznet_external_domain_sentinel=preserve-me` without inventing or reading a provider schema.
4. Replace AZnet Theme in place with current candidate bytes; content, menu assignment, front-page option and opaque external sentinel survive.
5. Switch to the independent local alternate Theme fixture; WordPress-native content/front-page configuration and opaque external sentinel survive.
6. Switch back to AZnet Theme; Primary Menu assignment, external sentinel and frontend rendering survive.
7. Roll back the exact AZnet Theme directory to `main@b7443a96...`; alpha.7 metadata, content, menu assignment, external sentinel and frontend rendering survive.
8. Runtime log contains no Theme-caused `PHP Fatal error`, `Warning`, `Parse error` or `Uncaught` marker.

Artifact: `g6-core-lifecycle`  
Artifact ID: `10012757676`  
Artifact ZIP SHA-256: `c0095c030d9ab9b5fbf10cfa8e20fa66c8f78d691d9e2ef10f0ccd995f212ece`

## PASS

**G6 lifecycle/rollback PASS.** In-place replacement, switching away/back and rollback preserve WordPress-native site state and an opaque external-data sentinel without the Theme claiming provider data ownership.

## Boundary

This does not yet prove deterministic final-package bytes or v1.0 release metadata. Provider-specific domain migration is not claimed.

## NEXT

**G7 — run the retained regression suite, build the candidate package twice byte-identically, verify package exclusions/SHA/unzip exact bytes/lint, and activate the extracted package on WordPress 6.9 for a package-byte smoke.**