# P1 Pilot Identity Inspection — 2026-09-16

**Pilot:** `https://tamduchanoi.aznet.vn`  
**Scope:** authenticated, read-only WordPress core Themes inventory plus AZnet Theme System Health identity cross-check  
**Branch:** `work/p1-pilot-identity-inspection`  
**Canonical base:** `main@d47a8cfd39edd0171fbc0487bb7e46f9eafa596c`  
**Mutation policy:** no theme activation, switch, update or deletion; no settings/content/plugin mutation

## RED -> GREEN

1. RED contract run `35049475475` failed for the intended reason: `P1 pilot identity inspection workflow is missing`.
2. Minimal manual-only workflow + browser harness were added.
3. GREEN runtime run `35049569760` completed SUCCESS using the existing masked `PILOT_WP_USER` / `PILOT_WP_PASSWORD` GitHub Actions secrets.
4. Artifact:
   - name: `p1-pilot-identity-inspection`
   - artifact ID: `10428277781`
   - digest: `sha256:3a7f57c1f836eb131f424d7c20e22ae9f3fdd0cb38f450ee3a41d7de4db11252`

## Observed WordPress Themes inventory

| Folder identity | Theme name | Version | State | Classification |
| --- | --- | --- | --- | --- |
| `aznet-theme` | AZnet Theme | `1.1.0` | active | canonical active identity |
| `aznet-theme-release-v1.0.0` | AZnet Theme | `1.0.0` | inactive | `INACTIVE_CANDIDATE` |
| `flatsome` | Flatsome | `3.19.1` | inactive | unrelated / outside P1 AZnet identity scope |

No inactive AZnet Theme reports the same `1.1.0` version as the active Theme, so the inspection does not classify any AZnet identity as `AMBIGUOUS`.

## System Health cross-check

The same authenticated read-only session reports:

- `product = aznet-theme`
- active Theme name = `AZnet Theme`
- active Theme version = `1.1.0`
- Standalone Core status = `ready`

This agrees with the active WordPress Themes card at folder identity `aznet-theme`.

## Safety result

The runtime evidence records:

- `mutation_performed = false`
- `deletion_authorized = false`
- `inspection_status = CANDIDATES_FOUND`
- `blocking_failures = []`
- inactive candidates = `1`
- ambiguous identities = `0`

The harness contract forbids theme delete/activate/update routes and allows only the WordPress login-submit click. The final repository workflow is `workflow_dispatch` only with `contents: read`.

## Provenance note

During P1 preparation an accidental root sentinel file `noop` was created on `main@9df065c72d0a0256998347a5c5a9486db8250ba5` and immediately removed at `main@d47a8cfd39edd0171fbc0487bb7e46f9eafa596c`. Direct compare from the approved PR #68 merge `5ad97ea52defacf49afdcfd48f47b05ba75ea2de` to `d47a8cfd39edd0171fbc0487bb7e46f9eafa596c` reports zero changed files, so the two history commits do not alter Theme/source bytes.

## PASS / BLOCKED

**PASS:** read-only P1 identity inspection is complete. The active canonical Theme identity is proven as `aznet-theme` v1.1.0. Exactly one distinct inactive legacy AZnet Theme identity is proven: `aznet-theme-release-v1.0.0` v1.0.0.

**BLOCKED / OWNER GATE:** deletion has not been performed and is not authorized by this inspection. Deleting `aznet-theme-release-v1.0.0` is destructive WordPress site-operations work and requires separate explicit owner approval.

## Exact next

If the owner explicitly approves deletion of **only** `aznet-theme-release-v1.0.0`, remove that inactive Theme through WordPress core lifecycle tooling, then immediately re-run read-only inventory + System Health + public smoke to prove:

- exactly one intended AZnet Theme remains;
- `aznet-theme` v1.1.0 is still active;
- Standalone Core remains `ready`;
- primary site presentation remains intact.

Stop before any other theme deletion, Git tag, GitHub Release, final production deployment or optional-provider L5 claim.
