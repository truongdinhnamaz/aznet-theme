# v1.3.22 Service Page Release Closure — 20/09/2026

## Scope

Owner-approved release path for the mapped Service Page landing presentation added after the v1.3.21 technical baseline.

This evidence closes repository technical verification and GitHub publication for v1.3.22. It does **not** claim production deployment to `lstamduchn.vn`.

## Service Page implementation

- PR #199: `Present mapped service Pages as landing surfaces`.
- Final implementation head: `67b77f53b96f3b7dac16cd220813147b48fdf463`.
- PR #199 merged to canonical `main@cbedacc1225cab2fdacbe11483bd746545378619`.
- WordPress Pages remain content/query owners.
- AZnet Theme adds presentation only for direct child Pages of the explicitly mapped Services Page.
- Contact CTA uses the explicitly mapped Contact Page.
- No slug/title/URL heuristic, private provider storage, parallel service store or Category conversion is introduced.
- Service-specific CSS is scoped to the mapped service detail surface.

## Service Page verification

- Y1 Page Experience final candidate: L1/L2/L3/L4 PASS.
- V1 Core PR CI, retained regression, X6 release closure and the triggered cross-surface regression set completed successfully.
- An R2 browser run transiently failed with local runtime connection reset/refused; the unchanged candidate passed on retry attempt 3. No service-page production regression was identified.

## v1.3.22 promotion

- Release PR #200 promoted Theme metadata from `1.3.21` to `1.3.22`.
- Final release head: `c5c7433bc558f3094aa69fd762793bca2348e2e9`.
- All 14 workflows triggered on the final release head completed SUCCESS, including:
  - Release Version Consistency Gate;
  - Y5 Client Delivery Release;
  - X6 Cross-surface Release Closure;
  - V1 Core Pull Request CI;
  - D-027 retained/package gates;
  - Law Site Provisioning candidate/browser gates;
  - retained cross-surface regressions.
- PR #200 merged to canonical `main@7ea9a189b124482b495f6e2adff668c070ff94de`.

## Exact-main verification

- V1 Exact Main Verification run `35521042638`: SUCCESS.
- X6 Cross-surface Release Closure run `35521042601`: SUCCESS.
- X6 browser/axe, runtime, package/source identity, lifecycle and rollback checks passed.
- Deterministic package:
  - `aznet-theme-1.3.22.zip`;
  - 159 production files;
  - 113 packaged PHP files verified;
  - SHA-256 `d5197073641ebc6b7764ffca31409b56cf9e447d533899999db94b28d5e02790`.
- X6 evidence artifact `10608505607`, digest `sha256:a8d8a2cee65e8debf91191cf422d47f655c56117d8b8ea19cd4889485732f616`.

## Publication

- Publication workflow: `OPS Publish v1.3.22`, run `35521247319`: SUCCESS.
- Annotated tag `v1.3.22`: tag object `a552ee75f05ff1b8f9733137d2015692a20e565d` -> exact canonical commit `7ea9a189b124482b495f6e2adff668c070ff94de`.
- GitHub Release: `392486195`.
- Release asset: `577094851`, `aznet-theme-1.3.22.zip`.
- Release asset SHA-256: `d5197073641ebc6b7764ffca31409b56cf9e447d533899999db94b28d5e02790`.
- Publication evidence artifact `10608601176`, digest `sha256:03f1e3965173e284ca9bd18a21550a8524ee6410dbac89cdb9d288ae9f53698c`.

## Production deployment gate

The owner explicitly approved the release/deploy path, but production mutation has not been performed.

Fresh WPVibe account readback on 20/09/2026 confirms `lstamduchn.vn` remains connected, but the current rolling 24-hour allowance is exhausted at 550/500 calls with 0 remaining. More usage is expected around 21/09/2026 03:40 UTC. Therefore authenticated production preflight, rollback preparation, deployment and fresh post-deploy verification are currently **BLOCKED_EXTERNAL_ACCESS**.

No production mutation is claimed. `1.3.10` remains the last verified live Theme version under existing production evidence until a fresh authenticated read succeeds.

## State

**v1.3.22 TECHNICAL PASS / PUBLICATION PASS / PRODUCTION DEPLOYMENT BLOCKED_EXTERNAL_ACCESS.**

## Exact next

When authenticated production access becomes available, perform fresh `lstamduchn.vn` preflight, verify active Theme/version and rollback path, deploy the exact published `aznet-theme-1.3.22.zip` asset, then verify the Homepage and mapped Service Page surfaces before claiming production PASS.
