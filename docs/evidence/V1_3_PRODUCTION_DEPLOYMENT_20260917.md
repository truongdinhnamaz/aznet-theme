# AZnet Theme v1.3.0 Production Deployment — 17/09/2026

## Scope

Owner-approved production deployment of the exact published AZnet Theme `v1.3.0` release asset to `https://tamduchanoi.aznet.vn`, followed by an independent authenticated/read-only post-deploy verification. This evidence closes Theme-owned/site-operations deployment only. It does not claim optional-provider L5 certification.

## Published release identity

- Release: `v1.3.0` / GitHub Release `390623960`.
- Release asset: `aznet-theme-1.3.0.zip` (`asset 570039299`).
- Exact package SHA-256: `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`.
- Rollback package: published `v1.2.0`, SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`.

## Owner-approved deployment

Deployment branch: `ops/deploy-v1.3.0-tamduc`.

Trigger commit: `6b0058ccc92739edafc903f8588cf11d931fee4f`.

GitHub Actions deployment run `35222200660` completed `success`.

The deploy job completed all guarded steps successfully:

- static deployment contract;
- exact v1.3.0 release download + SHA verification;
- exact v1.2.0 rollback package download + SHA verification;
- Playwright harness syntax;
- WordPress core Theme Upload/Replace deployment;
- authenticated System Health and public desktop/mobile verification;
- evidence upload.

Deployment artifact:

- artifact ID `10497483290`;
- name `tamduc-v1-3-production-deploy`;
- digest `sha256:84d041445b606279a0caebef60b9c38206f5a9a88b8e1b759453484dfad1c6b1`.

Deployment summary recorded:

- before Theme version `1.2.0`;
- after Theme version `1.3.0`;
- mutation `theme_replaced:1.2.0->1.3.0`;
- Standalone Core remained `ready`;
- approved WordPress menu identity/location retained;
- public desktop/mobile HTTP `200`, one `main`, one H1, overflow `0`, approved logo + phone retained;
- `rollback=[]`, `blocked=[]`, `error=null`.

## Independent post-deploy verification

Independent verifier trigger commit: `980f0d02b12dcf26029721a6e36e72f712c82409`.

GitHub Actions run `35223053733` completed `success`.

The read-only verification job completed successfully across:

- read-only static contracts;
- verifier syntax;
- authenticated exact deployed Theme/System Health check;
- exact approved phone/menu-location continuity;
- desktop/mobile public shell verification;
- retained broad public pilot matrix;
- fresh evidence upload.

Post-deploy artifact:

- artifact ID `10497925626`;
- name `tamduc-v1-3-postdeploy-readonly`;
- digest `sha256:756f765fe70f2ca61978111678853204808ea4cb4cd44fdd3453d50e177313b2`.

Fresh read-only summary confirmed:

- active Theme `1.3.0`;
- Standalone Core `ready`;
- `header-utility` retained on WordPress menu ID `15`;
- approved phone `024 3716 4123` / `tel:+842437164123` retained;
- desktop/mobile public HTTP `200`, one `main`, one H1, overflow `0`, logo retained;
- verifier mutation flag `false` and result `PASS`;
- broad public matrix `28/28` with no Theme-owned blocking failure.

Four accessibility observations in authored content remain classified `CONTENT_AUTHORED_SEMANTICS`; they are not Theme-owned blocking defects and do not transfer content/domain ownership to the Theme.

## Cleanup / rollback hygiene

Temporary deployment + verification workflows, browser harnesses and static contracts were removed after successful evidence capture by cleanup commit `6ef53f0bd20c69c65b88a753627436e31976d606`.

A fresh compare of `main@2c4e6f0442335ac3e690e97beaca4d4bf6405fa5` to cleaned `ops/deploy-v1.3.0-tamduc` returned zero changed files. The ops branch therefore retains evidence history without a net production/source delta.

## State

**AZnet Theme v1.3.0 — PUBLICATION PASS + PRODUCTION DEPLOYMENT PASS** at the verified Theme-owned/site-operations scope on `tamduchanoi.aznet.vn`.

Provider L5 remains separate and unclaimed. No RootProfile/ConvertFlow/Woo domain ownership is inferred from this deployment.

## Next

Reconcile AZT-03, AZT-04, AZT-EXEC-MAP and `SOURCE_MANIFEST.md` to this deployment checkpoint. Any optional-provider certification or new Theme milestone requires a separately approved slice.
