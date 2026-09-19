# v1.3.15 Technical Closure — 20/09/2026

## Scope

This evidence records the canonical-main technical closure of AZnet Theme `1.3.15` after owner-approved PR #167. It does **not** publish a Git tag/GitHub Release and does **not** authorize or claim production deployment.

## Canonical implementation

- Predecessor PR #166 merged the verified `1.3.14` below-Hero Law 01 composition to `main@19cbc6cad70d593aa737fb378d169019f3c267aa`.
- PR #167 final head: `a47e2f371db3de6c85eb43f34db7ae360f482458`.
- Final-head check matrix: **24/24 SUCCESS**.
- Earlier RED -> GREEN Team-teaser slice: RED `e26f6855919e307feb834fbeee01c69730b28e9b`; GREEN `c5fbbb87b9ab2d023f0b6de27f0cf33e845d7210` with 25/25 checks SUCCESS before later promotion/retained-workflow guards and the final Team-only fail-soft correction.
- Owner-approved merge commit / canonical main: `6cf48d1653d6468ec5aa25761c1a5b1dc82d4aa3`.
- Final-head -> merge comparison: zero changed files.
- Canonical tree: `09a00e9d930a18f600859aa58f5a4d2a6731962e`.
- Theme metadata: `1.3.15` in both `style.css` and `AZNET_THEME_VERSION`.

## Fresh exact-main verification

Exact canonical main `6cf48d1653d6468ec5aa25761c1a5b1dc82d4aa3`:

- V1 Exact Main Verification run `35473959243`: SUCCESS.
  - `static-contracts`: SUCCESS.
  - `clean-runtime-browser`: SUCCESS.
- X6 Cross-surface Release Closure run `35473959238`: SUCCESS.
- Exact-main check-run summary: **3/3 SUCCESS**.
- X6 browser/axe matrix: **32/32 cases PASS**, zero blocking failures.
- X6 evidence artifact: `10593342179`, digest `sha256:e2561025314574c12ad63af1fd9b4db80e9c78165e833e850fbd490075ddf5f3`.

## Deterministic package identity

The exact-main X6 artifact contains two independently built copies of `aznet-theme-1.3.15.zip`.

- Build A SHA-256: `8f91e9bd3a40a7eae4e6984a77f2735f339d188e52cfc6f97fac57a96bfcecd8`.
- Build B SHA-256: `8f91e9bd3a40a7eae4e6984a77f2735f339d188e52cfc6f97fac57a96bfcecd8`.
- Exact-byte reproducibility: PASS.
- Package size: 214506 bytes.
- Production files: 148.
- Packaged PHP files: 111; X6 packaged-PHP verification PASS.

## Ownership and fail-soft boundary

- AZnet Theme remains presentation owner.
- WordPress retains native content/media/menu ownership.
- RootProfile remains authoritative for organization/person identity and team membership semantics.
- Issue #112 remains OPEN because no public/versioned RootProfile organization-team projection is available.
- Theme does not enumerate WordPress users/authors as authoritative team identity, infer membership from slug/title/URL/Page ID, read RootProfile private storage, or create a parallel team store.
- Law 01 Team presentation remains source-safe: a legitimate mapped Team Page can render its teaser shell; member cards require legitimate mapped published child Pages and are not fabricated.

## Publication and deployment boundary

- Latest public GitHub Release at this reconciliation remains `v1.3.12`.
- Git refs `v1.3.13`, `v1.3.14` and `v1.3.15` are absent at this checkpoint.
- No `v1.3.15` Git tag/GitHub Release publication is claimed.
- No production deployment or live-site mutation is performed or claimed by this closure.
- Publication is the next explicit owner approval gate; production deployment remains a separate gate after publication.

## Result

**PASS — v1.3.15 canonical-main technical closure at L1-L4/L6 evidence scope.**

**BLOCKED — authoritative RootProfile Team membership remains external under issue #112.**

**NOT CLAIMED — v1.3.15 GitHub publication or production deployment.**
