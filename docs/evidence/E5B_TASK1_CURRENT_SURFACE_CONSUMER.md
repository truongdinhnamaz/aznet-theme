# E5-B Task 1 — Current-Surface Consumer Checkpoint

**Date:** 2026-09-02  
**Branch:** `work/e5b-current-surface-consumer`  
**Code commit:** `fcedd1c8a5b140e344aaca875b9e55881d14dbd7`  
**Scope:** AZnet Theme current-surface consumer contract only; no dispatcher, no route/takeover wiring.

## RED evidence

Command:

```bash
php tests/offline/e5-current-surface-consumer-contract.php
```

Observed before implementation:

```text
FAIL: current_surface_context() is not implemented
EXIT=1
```

The failure was caused by the missing approved consumer API, not by a syntax/setup error.

## GREEN / verification evidence

Fresh commands after implementation:

```bash
php tests/offline/e5-current-surface-consumer-contract.php
php -l inc/integrations/rootprofile.php
```

Observed:

```text
PASS: E5 current-surface consumer contract
No syntax errors detected in inc/integrations/rootprofile.php
```

Additional ownership scan against the modified consumer reported:

```text
PASS: no forbidden RootProfile routing/storage dependency in consumer
```

The scan checked for RootProfile private-class/storage/routing dependencies including `TruongDinhNam\\RootProfile`, `rootprofile_person`, `rootprofile_organization`, `get_query_var(`, `is_page(`, `get_queried_object_id(`, `template_include`, `template_redirect`, `the_content` and `_rootprofile_`.

## GitHub scope verification

Comparison `plan/e5b-theme-current-surface-consumer...work/e5b-current-surface-consumer` at the Task 1 code commit showed exactly:

```text
M inc/integrations/rootprofile.php
A tests/offline/e5-current-surface-consumer-contract.php
```

No Contact/Profile renderer, template, CSS, Page template, bootstrap route hook or production takeover file changed in this task.

## What Task 1 implements

- Exact hook constant: `rootprofile/presentation/current-surface/v1`.
- Exact contract/version: `rootprofile.current_surface` / `1`.
- Allowed surfaces only: `person_profile`, `organization_profile`, `contact`.
- Nested Provider v2 validation for Person/Organization Profile.
- Nested Provider v1 validation for Contact.
- Fail-soft for absent, malformed, unsupported or throwing current-surface providers.
- Shared pure `validated_provider_payload()` used by existing Provider v1/v2 consumers without changing their contract/version/resource acceptance rules.

## PASS by scope/layer

- **Task 1 implementation:** PASS.
- **L0 source/scope:** PASS — exact two-path code/test delta verified on GitHub.
- **L1 static:** PASS — consumer PHP lint and forbidden routing/storage scan.
- **L2 contract:** PASS — RED observed before implementation; GREEN observed after implementation.

## BLOCKED / UNKNOWN

- **E5-A RootProfile publisher:** BLOCKED/UNKNOWN because no canonical RootProfile GitHub repository is currently accessible through the linked connector. This Theme task does not claim to implement RootProfile-owned source.
- **E5-B model adapters / dispatcher:** NOT STARTED; Task 2 is next.
- **E5-C WordPress runtime/browser/a11y:** UNKNOWN / not run.
- **E5-D production takeover:** LOCKED; no production takeover hook exists in this task.
- **Milestone E global status:** remains ACTIVE; Task 1 does not close E5.

## Next action

Execute **Task 2 — payload-to-model adapters without provider re-resolution** from `docs/superpowers/plans/2026-09-02-e5b-theme-current-surface-consumer.md`, beginning with a failing offline contract before modifying Contact/Profile production modules.
