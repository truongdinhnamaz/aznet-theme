# F6/F7 ConvertFlow Runtime Integration Blocker

**Date:** 2026-09-07  
**Scope:** Milestone F / Homepage Shell / F6 Save-Preview-Publish regression + F7 analytics/conversion regression  
**Repository:** `truongdinhnamaz/aznet-theme`

## Verdict

**BLOCKED / UNKNOWN — actual combined AZnet Theme + ConvertFlow runtime cannot be established with the currently authorized CI carrier.**

This is an external authorization boundary, not a Theme production defect. F6/F7 must not be promoted from static/native evidence, and no fixture/private-storage shortcut is permitted.

## Source-required evidence

F6 requires actual integration evidence that the Theme shell does not mutate or reinterpret ConvertFlow/WordPress Save/Preview/Publish lifecycle. F7 requires actual integration evidence that the Theme shell does not change ConvertFlow resolver/state/analytics/conversion ownership or emitted behavior.

The accepted execution boundary forbids replacing this with a synthetic contract, copied provider logic, direct private storage inspection, or a bundled provider snapshot in the Theme repository.

## Exact provider resolved

The actual source-owner repository is:

`truongdinhnamaz/convert-flow` (private)

Historical W8 provider commit `228a2c511223c9f3394e72956b42bebb6e51ff0e` exists in that repository.

Current ConvertFlow source authority (`CF-03 v1.27`) records:

- active verified QA/development candidate: Core `0.1.0 P5.239` + Pro `0.1.0-alpha.120/P5.164`;
- Core installable package: `convertflow-0.1.0-p5.239-course-learning-c1-forward-integration.zip`;
- exact source/package QA artifact run: `33835571610`;
- artifact ID: `9923158317`;
- artifact wrapper digest: `sha256:7855baff57c0f4e28702a62a95c18cdcc4b1177480b6a27a27fd2203aca48c66`;
- installable Core SHA-256: `8f046828a795a1a2d2d92a1664aff580c6c4554664f0cdf1226e521d84224de8`.

The artifact was downloaded through the authorized GitHub connector outside the Theme repository and independently hash-checked. The outer artifact and contained Core package hashes match the source-owner evidence exactly. No provider file was committed to `aznet-theme`.

## Carrier-access probe

A test-only Theme branch was created from the current PR #27 synthetic candidate:

`test/f6-f7-provider-access-20260907`

It added only `.github/workflows/f6-f7-provider-access.yml` and attempted to fetch the exact source-owner artifact through GitHub's artifact API using the workflow's own `${{ github.token }}`. No provider bytes, credentials or temporary signed URLs were embedded in the repository.

GitHub Actions run:

`34081082004`

Result: **expected probe failure / blocker reproduced**.

Observed boundary:

`provider_artifact_http_status=404`

The workflow token permissions reported by GitHub are limited to the current Theme repository (`Contents: read`, `Metadata: read`). They do not authorize downloading a private sibling-repository artifact.

## Root cause

The Theme CI runtime has no authorized cross-repository credential or private integration carrier for `truongdinhnamaz/convert-flow`.

This prevents assembling an actual combined runtime in Theme CI while preserving the required security and ownership boundaries.

The following are intentionally rejected as invalid workarounds:

- committing/copying the private ConvertFlow package or source into public `aznet-theme`;
- embedding an expiring private artifact download URL in a public Theme branch/workflow;
- reading ConvertFlow private option/meta/table/class state to simulate lifecycle assertions;
- implementing or modifying ConvertFlow in the Theme repo;
- claiming F6/F7 from F2/F4/F8 evidence by inference.

## Retained PASS

The blocker does not invalidate already established Theme-owned evidence:

- F1/F2/F3/F5 retained PASS;
- F4 canonical Theme Integration Contract v1 token bridge verification PASS — run `34080636997`;
- F8 native WordPress 6.9 / PHP 8.1.34 runtime + browser/a11y PASS — run `34080758335`, 3/3 viewports.

Those results do not satisfy F6/F7.

## Minimum unblock requirement

F6/F7 can resume when **one** owner-authorized integration carrier is available without transferring ConvertFlow source ownership to the Theme, for example:

1. a cross-repository read credential/installation token exposed to the test runtime through an approved secret mechanism; or
2. a private integration runner/workspace that is already authorized to access both `aznet-theme` candidate bytes and the exact ConvertFlow source-owner package/artifact.

Once available, the Theme test must install the exact source-owner candidate ephemerally and exercise public WordPress/ConvertFlow behavior only. The provider package must not be committed or persisted as Theme source.

## Remaining state

- **F6:** BLOCKED / UNKNOWN.
- **F7:** BLOCKED / UNKNOWN.
- **F9:** BLOCKED because F6/F7 are not closed.
- **PR #27:** remains draft/unmerged; no main merge claim.
- **E5-C/E5-D:** unchanged and outside this blocker.
