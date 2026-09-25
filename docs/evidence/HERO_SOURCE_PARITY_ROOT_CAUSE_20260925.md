# Law 01 Hero Source Parity Root-Cause Fix — 2026-09-25

**Candidate:** AZnet Theme 1.3.48  
**Implementation production head:** `8ec2afdfd631b07baec1c16666edca6e61a50d73`  
**Branch:** `work/homepage-map-v1-3-38-baseline`

## Symptom

An administrator could edit Hero content in the dedicated **Thiết kế Hero / Sửa Hero đơn giản** screen, save successfully, and still see the previous Hero content on the public Homepage.

## Root cause

The backend Hero authoring path and the Law 01 frontend were not reading the same effective mapping.

Backend save path:

```php
homepage_source_value( 'law-01', 'hero', settings() )
```

This correctly resolves the scoped source `homepage_law01_hero_block` (with legacy fallback rules in the shared resolver).

Before this fix the public Law 01 Hero template bypassed that resolver and read legacy Theme settings directly:

```php
setting( 'homepage_hero_block', 0 )
setting( 'homepage_hero_variant', 'split' )
setting( 'homepage_hero_page', 0 )
```

Therefore a site with a valid scoped Law 01 Hero mapping could save the intended WordPress Hero block while the frontend continued rendering the old legacy mapping/fallback.

The same direct-legacy pattern also existed for Hero fallback Page, Contact and Services references inside the Hero template.

## Fix

`template-parts/homepage/law-01/hero.php` now reads every Law 01 Hero-related source through the same typed/effective resolver used by admin:

- `hero`
- `hero_variant`
- `hero_page`
- `contact`
- `services`

using one normalized `$theme_settings = settings()` snapshot.

No parallel content store, source migration, slug heuristic or provider/private data read was introduced.

## TDD evidence

Focused contract first failed on the exact 1.3.47 production package:

```text
FAIL: Law 01 frontend is not consuming the same effective source as Hero admin:
homepage_source_value( 'law-01', 'hero', $theme_settings )
```

After the production change:

```text
PASS: Hero save and Law 01 frontend share the same effective scoped source resolver.
```

The runtime workflow fixture was also changed so that:
- legacy `homepage_hero_block = 0`;
- scoped `homepage_law01_hero_block` points to the valid published Hero;
- scoped variant = `media-left`;
- legacy variant deliberately differs.

That fixture reproduces the exact source-divergence class that previously escaped coverage.

## Candidate verification

Local candidate package was built from the byte-identical 1.3.47 production package plus the exact three production changes for 1.3.48.

Git blob parity against canonical branch:
- `template-parts/homepage/law-01/hero.php`: `073d00cfdd61436479200f06383adeb9dea17bc6`
- `style.css`: `5836379fe9b631d7c505b1472967450b24794913`
- `functions.php`: `0bc8a149ec42b1f8797770c54eaa427ed8fefc6f`

Verification:
- focused Hero source parity contract: PASS;
- PHP lint: 136 production PHP files PASS;
- admin authoring JavaScript syntax: PASS;
- ZIP integrity: PASS;
- package SHA-256: `721ad2f4a8a029f226bd47c7ecdd3cb2ef75a5e9ed39f59077df027352b9ec79`.

## QA boundary

Fresh GitHub attempts on 1.3.48 still fail before runner allocation:
- V1 Core Pull Request CI run `36093287277`: all jobs 0 executed steps;
- Homepage Composer Law 01 Browser Quality run `36093287479`: job 0 executed steps.

Therefore L3/L4 remain UNKNOWN because infrastructure still does not execute the prepared runtime/browser regression. The owner previously authorized candidate packaging despite this runner outage. No main merge or production deployment is claimed.
