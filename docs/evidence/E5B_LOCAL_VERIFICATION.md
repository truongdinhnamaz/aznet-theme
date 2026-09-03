# E5-B Local Verification — Dormant RootProfile Current-Surface Consumer

Date: 2026-09-03

## Closure statement

**E5-B STATUS: PASS local L0-L2 only.**

**PRODUCTION TAKEOVER: NOT ENABLED.**

**E5-A ROOTPROFILE PUBLISHER: BLOCKED/UNKNOWN until the canonical RootProfile repository is accessible and the source-owned `rootprofile/presentation/current-surface/v1` publisher is implemented and tested there.**

**E5-C WORDPRESS RUNTIME/BROWSER/A11Y: NOT RUN / UNKNOWN.**

**E5-D TAKEOVER: LOCKED.**

No E5-B result may be used to infer E5-C, E5-D, E6 or E7 PASS.

## Verified source state

- Repository: `truongdinhnamaz/aznet-theme`
- Branch: `work/e5b-current-surface-consumer`
- Verified pre-evidence source head: `6dca2233fd92b517f27ce70f06245583fb1c3149`
- Base branch: `main`
- Base commit: `4d73fc26145fc7df22060db3de582fcd8ed23a11`
- GitHub compare at verification time: ahead 17, behind 0.
- Frozen E4 recovery artifact: `aznet-theme-0.1.0-alpha.7-e4-candidate.zip`
- Frozen E4 SHA-256: `5ee905ba2788fa99334c36da512e193c9e63d8d9b7225aa658164cdc2840e483`
- Internal Theme version remains `0.1.0-alpha.7`.

The isolated local verification tree was reconstructed from the verified E4 artifact and the exact active-branch production blobs. Relevant Task 1-3 production blob identities matched GitHub before closure verification.

## Fresh closure verification

Command run immediately before creating this evidence:

```bash
./scripts/verify-e5b.sh
```

Observed output:

```text
PASS: E5 current-surface consumer contract
PASS: E5 current-surface payload-to-model adapters
PASS: E5 dormant current-surface dispatcher
PASS: E5-B ownership / no-takeover static contract
PASS: E5-B offline contracts
PASS: production PHP lint 22/22
```

The verifier is present in the GitHub tree as `scripts/verify-e5b.sh`, mode `100755`, blob `bef8886463d6c440a9a93671d40da39301ac976b`.

## E5-B implementation checkpoints

### Task 1 — Current-surface consumer

- Code commit: `fcedd1c8a5b140e344aaca875b9e55881d14dbd7`.
- Evidence: `docs/evidence/E5B_TASK1_CURRENT_SURFACE_CONSUMER.md`.
- Adds exact public consumer boundary for `rootprofile/presentation/current-surface/v1`.
- Validates contract `rootprofile.current_surface`, version `1`, and allowed surfaces `person_profile`, `organization_profile`, `contact`.
- Validates embedded existing Provider v2/v1 payloads without direct RootProfile storage/private-class reads.
- Fail-soft for absent, malformed, unsupported or throwing provider chains.

### Task 2 — Payload-to-model adapters

- Code tree checkpoint: `8b68d67a97c18070557d43ab0c1f0eba873b1916`.
- Evidence: `docs/evidence/E5B_TASK2_PAYLOAD_MODEL_ADAPTERS.md`.
- Adds pure `contact_surface_model_from_payload()` and `profile_surface_model_from_payload()` adapters.
- Preserves Provider-resolved semantics/order/navigation and existing E4 renderability behavior.
- Existing provider-derived Contact/Profile model paths remain compatible.

### Task 3 — Dormant dispatcher

- Production/test commit: `953bc6a6f32727ea283c1f0496dc25211d9d758e`.
- Evidence: `docs/evidence/E5B_TASK3_DORMANT_DISPATCHER.md`.
- Adds `render_current_rootprofile_surface()` and requires the dispatcher module from Theme bootstrap.
- Dispatcher maps Person/Organization contexts to the Profile renderer and Contact context to the Contact renderer with surface-scoped CSS.
- Dispatcher is deliberately dormant: no request-lifecycle takeover registration exists.

### Task 4 — Ownership / no-takeover gate

- Evidence: `docs/evidence/E5B_TASK4_OWNERSHIP_GATE.md`.
- Adds `tests/offline/e5-no-takeover-static-contract.php` and executable `scripts/verify-e5b.sh`.
- Fresh full verifier PASS: four E5-B contracts + 22/22 production PHP lint.
- The static gate rejects private RootProfile namespace/storage/routing/takeover dependencies and checks that the dispatcher remains unregistered.
- A plan-level false positive from the raw `_rootprofile_` substring was traced to the approved function name `render_current_rootprofile_surface()` and corrected at the test layer to detect actual quoted `_rootprofile_*` storage-key literals. No production code was changed to satisfy that false positive.

## Net branch delta versus main at verification time

Production paths:

```text
M inc/integrations/rootprofile.php
M inc/theme/bootstrap.php
M inc/theme/contact-surface.php
M inc/theme/profile-surface.php
A inc/theme/rootprofile-current-surface.php
```

Source-only plan/evidence/verification paths:

```text
A docs/evidence/E5B_TASK1_CURRENT_SURFACE_CONSUMER.md
A docs/evidence/E5B_TASK2_PAYLOAD_MODEL_ADAPTERS.md
A docs/evidence/E5B_TASK3_DORMANT_DISPATCHER.md
A docs/evidence/E5B_TASK4_OWNERSHIP_GATE.md
A docs/superpowers/plans/2026-09-02-e5b-theme-current-surface-consumer.md
A scripts/verify-e5b.sh
A tests/offline/e5-current-surface-consumer-contract.php
A tests/offline/e5-current-surface-model-contract.php
A tests/offline/e5-current-surface-dispatcher-contract.php
A tests/offline/e5-no-takeover-static-contract.php
```

`tests/` and `scripts/` are repository/source verification assets and remain outside distributable Theme package scope. No E5-B release ZIP was created; package/release remains E7 responsibility.

## Ownership and no-takeover invariants

E5-B does not:

- call `TruongDinhNam\RootProfile\*` implementation classes;
- read RootProfile option/meta/table/private CPT storage;
- infer Profile/Contact using slug, title, URL, Page ID, post type or query vars;
- register `template_include`, `template_redirect`, `the_content` or another production takeover hook;
- reinterpret RootProfile `none|external|studio` presentation ownership;
- modify canonical/profile URLs or mapping state;
- create a parallel RootProfile identity/contact/profile truth store;
- retire RootProfile fallback rendering;
- promote the Theme release version.

RootProfile remains owner of request resolution, canonical/mapped surface semantics and authoritative identity/profile/contact data. AZnet Theme owns only dormant presentation consumption and rendering preparation through the approved public contract boundary.

## PASS by evidence layer

- **L0 Source/State:** PASS for E5-B Theme branch scope and frozen E4 ancestry/recovery identity.
- **L1 Static:** PASS for ownership/no-takeover scan and production PHP lint 22/22.
- **L2 Contract/TDD:** PASS for current-surface consumer, payload-to-model adapters and dormant dispatcher contracts, with RED→GREEN evidence recorded per task.
- **L3 Runtime:** UNKNOWN / not run for E5 current-surface integration.
- **L4 Browser/Visual/A11y:** UNKNOWN / not run for E5 current-surface integration.
- **L5 Integration:** LOCKED pending E5 runtime gate and source-owned RootProfile publisher.
- **L6 Completion/Release:** LOCKED; no release promotion/package closure is claimed.

## Blocked dependency

E5-A belongs to RootProfile and must be implemented in RootProfile's canonical source, not in this Theme repository. Required public publisher:

```text
hook: rootprofile/presentation/current-surface/v1
contract: rootprofile.current_surface
version: 1
```

It must resolve the current request inside RootProfile using RootProfile-owned routing/mapping semantics and expose only the normalized public-safe context defined by the approved E5 spec. It must not expose internal Person/Page IDs or silently authorize production takeover.

## Exact next

1. Open the E5-B implementation PR against `main` with scope explicitly limited to local L0-L2 and no production takeover.
2. Locate or obtain access to the canonical RootProfile repository and implement/test E5-A there.
3. Only after E5-A is verified, open E5-C for real database-backed WordPress runtime + desktop/tablet/mobile + keyboard/focus/console/a11y/long-data verification.
4. E5-D takeover remains a separate approval/gate after E5-C; it is not implied by this checkpoint.
