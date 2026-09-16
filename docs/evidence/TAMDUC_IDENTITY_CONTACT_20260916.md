# Tâm Đức Identity / Contact Evidence — 2026-09-16

## Scope

Bounded WordPress-owned site-configuration slice for `https://tamduchanoi.aznet.vn/`.

Authoritative owner-approved inputs:

- Official logo: the logo supplied and explicitly approved by the owner for Tâm Đức Hà Nội.
- Official phone: `024 3716 4123`.
- Normalized phone link: `tel:+842437164123`.

Ownership boundary:

- WordPress owns Media, Custom Logo, and Menu data.
- AZnet Theme only consumes/renders those WordPress-owned presentation inputs.
- No RootProfile, ConvertFlow, WooCommerce, private option/meta/CPT/table access.
- No production Theme PHP/CSS/JS change in this slice.
- No Theme release, deploy, semantic-version promotion, or provider L5 claim.

## TDD / root-cause checkpoints

The bounded apply harness was hardened through RED → GREEN regressions before the successful production mutation:

1. WordPress rendered the menu save control in a hidden toolbar. The regression uses native DOM activation rather than Playwright actionability for that native control.
2. The menu-create screen already uses `menu=0`. The regression waits for an explicit nonzero WordPress menu ID before continuing.
3. A partial menu from an interrupted attempt was recovered idempotently. Only an empty menu or a menu containing exactly the approved phone item may be adopted; unexpected content aborts before new mutation.
4. AZnet Theme renders the WordPress-owned custom logo with `img.aznet-theme-site-header__logo`, not WordPress core's default `img.custom-logo`. The public assertion now verifies the actual Theme presentation selector while retaining the core selector as a compatibility fallback.

Failed attempts preserved the bounded rollback contract; no failed run is treated as PASS.

## Production apply — PASS (L3 + bounded public assertion)

Workflow: `Tâm Đức Identity Contact Apply`

- Run: `35122588708`
- Head: `9f23c64a6c5163c7010a0708a12afab5ced99f71`
- Artifact: `10457872916` (`tamduc-identity-contact-apply`)
- Artifact digest: `sha256:43a148f0c0fd286489b15f407b81db9d84a725a92e69be46f4897eb8609cb8a5`

Observed before state:

- Active production Theme version reported by System Health: `1.1.0`.
- Custom Logo: absent.
- Public logo count: `0`.
- Public `tel:` links: none.
- Existing bounded WordPress menu: ID `15`, `Tâm Đức - Liên hệ chính thức`.
- Menu ID `15` already contained exactly one item: `024 3716 4123` → `tel:+842437164123`.

Applied/confirmed state:

- Official logo uploaded as WordPress Media ID `167`.
- Native WordPress Custom Logo set to Media ID `167`.
- Public logo rendered once at `https://tamduchanoi.aznet.vn/wp-content/uploads/2026/09/tam-duc-ha-noi-logo-official.jpg`.
- Existing WordPress menu ID `15` was safely adopted; no duplicate phone item was created.
- Menu ID `15` remains exactly one item: `024 3716 4123` → `tel:+842437164123`.
- Rollback list: empty.
- Error: null.

## Public final verification — PASS (L4)

Workflow: `Tâm Đức Identity Contact Final L4`

- Run: `35122938067`
- Head: `013f2ddc2449c2672c8867a26dff0adb7572d865`
- Artifact: `10458372243` (`tamduc-identity-contact-final-l4`)
- Artifact digest: `sha256:cd193316cc42db3f2d3ce4ab53ca774275ca375a4a5438ff4c9b29ce7471b959`

Fresh public/read-only checks:

### 1440×1000

- HTTP 200.
- Official logo count: `1`.
- Official logo source: `https://tamduchanoi.aznet.vn/wp-content/uploads/2026/09/tam-duc-ha-noi-logo-official.jpg`.
- `<main>` count: `1`.
- `<h1>` count: `1`.
- Horizontal overflow: `0`.
- Console errors: `0`.
- Page errors: `0`.
- Request failures: `0`.

### 390×844

- HTTP 200.
- Official logo count: `1`.
- Official logo source: same official WordPress Media URL.
- `<main>` count: `1`.
- `<h1>` count: `1`.
- Horizontal overflow: `0`.
- Console errors: `0`.
- Page errors: `0`.
- Request failures: `0`.
- Mobile navigation trigger visible.
- Initial `aria-expanded=false`.
- Enter opens panel and sets `aria-expanded=true`.
- Open-panel horizontal overflow: `0`.
- Escape closes panel and restores `aria-expanded=false`.
- Focus returns to the trigger.

`blocking_failures: []` for the final public L4 run.

## BLOCKED / UNKNOWN

### BLOCKED_RELEASE_CAPABILITY — public phone rendering

The approved phone is stored correctly in WordPress-owned menu ID `15`, but the active production Theme `1.1.0` does not register the `header-utility` menu location. Therefore no public `tel:` link is rendered by the deployed Theme.

This is intentionally **not** bypassed by hardcoding the phone into Theme runtime, moving it into an unrelated menu, reading private storage, or otherwise violating ownership. Public rendering remains gated on a compatible Theme release/deploy that exposes the already-accepted generic header-utility presentation capability.

### UNKNOWN / not claimed

- No provider L5 integration PASS is claimed.
- No release/deploy PASS is claimed.
- No v1.2 semantic-version promotion is claimed.

## Checkpoint

**PASS** — owner-approved official logo is applied through WordPress Custom Logo and freshly verified on desktop/mobile public surfaces; approved phone is preserved exactly in the WordPress-owned bounded menu.

**BLOCKED** — public header rendering of the phone is blocked by deployed Theme 1.1.0 presentation capability, not by missing authoritative data.

**NEXT** — integrate this evidence/QA branch through the normal review path; do not merge/release/deploy without the corresponding integration/release gate.
