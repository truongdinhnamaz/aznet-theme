# E5-B Task 2 — Payload-to-Model Adapters Checkpoint

**Date:** 2026-09-03  
**Branch:** `work/e5b-current-surface-consumer`  
**Code tree checkpoint:** `8b68d67a97c18070557d43ab0c1f0eba873b1916`  
**Scope:** Extract pure provider-payload → existing Contact/Profile presentation-model adapters; no dispatcher and no production route/takeover wiring.

## RED evidence

Command run in isolated workspace before production changes:

```bash
php tests/offline/e5-current-surface-model-contract.php
```

Observed:

```text
FAIL: contact_surface_model_from_payload() is not implemented
EXIT=1
```

The failure was caused by the missing approved Task 2 adapter API.

## GREEN / lint evidence

Fresh commands after implementation:

```bash
php tests/offline/e5-current-surface-model-contract.php
php -l inc/theme/contact-surface.php
php -l inc/theme/profile-surface.php
```

Observed:

```text
PASS: E5 current-surface payload-to-model adapters
No syntax errors detected in inc/theme/contact-surface.php
No syntax errors detected in inc/theme/profile-surface.php
```

## GitHub byte identity

Local Git blob hashes after GREEN:

```text
inc/theme/contact-surface.php                         6814aa153a95341074ae3b6d08cfbeaa7a55db21
inc/theme/profile-surface.php                         be1669c0ceb3425617e2e468ccfcc53cccf0e518
tests/offline/e5-current-surface-model-contract.php  5fa9e1674631ee634b8ce285ae7ce59d5f6fcfe6
```

The GitHub tree at `8b68d67a97c18070557d43ab0c1f0eba873b1916` contains those exact three blob SHAs. A temporary transport/probe file was removed before checkpoint closure and is absent from the final tree.

Net comparison from Task 1 checkpoint `74d5900047a554f47071eb7fd26d09d61aa4fce5` to Task 2 code tree shows exactly three paths:

```text
M inc/theme/contact-surface.php
M inc/theme/profile-surface.php
A tests/offline/e5-current-surface-model-contract.php
```

## What Task 2 implements

- `contact_surface_model_from_payload(array $contact_payload, ?array $organization_payload = null): ?array`.
- Contact adapter preserves authoritative Contact entity UUID/display name, optional same-UUID Organization enrichment, existing public detail-empty rejection, output field shape/order, and mismatch fail-soft behavior.
- `contact_surface_model()` still uses Provider v1 and delegates transformation to the pure adapter.
- `profile_surface_model_from_payload(string $resource, array $payload): ?array`.
- Profile adapter preserves provider-resolved section order, labels, anchors, navigation flags, public-safe data, optional section type/origin, signals, updated_at, and Person organization/role context.
- `profile_surface_model()` still uses Provider v2 and delegates transformation to the pure adapter.
- Existing E4 `profile_surface_model_is_renderable()` semantics were not changed.
- No RootProfile route, storage, private class, slug/Page heuristic, dispatcher, bootstrap hook, or takeover path was introduced.

## PASS by scope/layer

- **Task 2 implementation:** PASS.
- **L0 source/scope:** PASS — exact three-path net delta verified on GitHub.
- **L1 static:** PASS — modified production PHP files lint clean.
- **L2 contract:** PASS — RED observed before implementation; GREEN observed after implementation; provider-derived model paths remain equivalent to explicit payload-adapter paths in the contract test.

## BLOCKED / UNKNOWN

- **E5-A RootProfile publisher:** BLOCKED/UNKNOWN until a canonical RootProfile repository is accessible through the linked GitHub connector.
- **E5-B dispatcher:** NOT STARTED; Task 3 is next.
- **E5-C WordPress runtime/browser/a11y:** UNKNOWN / not run.
- **E5-D production takeover:** LOCKED.
- **Milestone E:** remains ACTIVE; Task 2 does not close E5.

## Next action

Execute **Task 3 — Dormant current-surface dispatcher** from `docs/superpowers/plans/2026-09-02-e5b-theme-current-surface-consumer.md`, beginning with a failing offline dispatcher contract. The dispatcher must remain unregistered on WordPress request lifecycle hooks.
