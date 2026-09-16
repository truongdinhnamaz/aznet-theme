# Tâm Đức identity/contact — production apply and L4 evidence — 2026-09-16

## Scope

Bounded WordPress-owned identity/contact completion for `https://tamduchanoi.aznet.vn/`.

Authoritative owner-approved inputs:

- Official logo: the Tâm Đức – Hà Nội round logo supplied and explicitly approved in the project session.
- Official phone: `024 3716 4123`.
- Normalized phone link: `tel:+842437164123`.

Ownership boundary:

- WordPress Media / Custom Logo / Menu own the data.
- AZnet Theme only renders public presentation capability.
- No client identity/contact data is hardcoded into Theme runtime PHP/CSS/JS.
- No RootProfile, ConvertFlow, WooCommerce, private option/meta/CPT/table inspection or mutation.

## TDD / debugging history

The bounded one-shot apply harness was developed with RED → GREEN and rollback-on-error.

Observed regressions and fixes:

1. WordPress exposed the menu save control in a hidden header toolbar. Playwright actionability timed out. Regression switched to native DOM activation for the specific WordPress control.
2. The menu create screen already uses `menu=0`; the old numeric URL predicate could resolve before WordPress returned a real menu ID. Regression requires an explicit nonzero menu ID.
3. A prior interrupted attempt left bounded menu `Tâm Đức - Liên hệ chính thức` as menu ID `15`. Recovery became idempotent: reuse only when the menu is empty or already contains exactly the approved phone link; unexpected contents abort before mutation.
4. The deployed Theme renders Custom Logo through `wp_get_attachment_image()` with class `aznet-theme-site-header__logo`, not WordPress core class `custom-logo`. The public verification selector was corrected and locked by a regression test.

Failed attempts either aborted before mutation or rolled back the newly uploaded logo/media. No provider/domain storage workaround was introduced.

## Successful bounded production apply

Workflow run: `35122588708`

Apply head: `9f23c64a6c5163c7010a0708a12afab5ced99f71`

Result: `SUCCESS`

Artifact:

- ID: `10457872916`
- Name: `tamduc-identity-contact-apply`
- Digest: `sha256:43a148f0c0fd286489b15f407b81db9d84a725a92e69be46f4897eb8609cb8a5`

Verified effects:

- Official logo was uploaded to WordPress Media and assigned through native Custom Logo.
- The public homepage renders the approved logo through Theme-owned presentation.
- WordPress menu ID `15` is retained as the authoritative WordPress-owned phone surface and contains the approved phone link.
- No duplicate phone menu was created.

## Deployed capability boundary

The production site remains on AZnet Theme `1.1.0`.

The deployed version does not register the `header-utility` WordPress menu location. Therefore the approved phone can be stored correctly in WordPress but cannot yet be rendered in the Theme header through the accepted presentation path.

Classification: `BLOCKED_RELEASE_CAPABILITY`.

This is intentionally not worked around with hardcoding, private storage, URL heuristics, or client-specific Theme runtime logic.

## Public L4 final verification

TDD RED workflow run: `35122870552` — failed at the new static contract before the public final harness existed.

TDD GREEN workflow run: `35122938067` — `SUCCESS`.

GREEN head: `013f2ddc2449c2672c8867a26dff0adb7572d865`.

Artifact:

- ID: `10458372243`
- Name: `tamduc-identity-contact-final-l4`
- Digest: `sha256:cd193316cc42db3f2d3ce4ab53ca774275ca375a4a5438ff4c9b29ce7471b959`

Public checks at `1440×1000` and `390×844`:

- HTTP `200`.
- `main` count = `1`.
- H1 count = `1`.
- horizontal overflow <= 1 px.
- no page errors.
- no console errors.
- approved logo is visible with a non-empty image source.
- approved `tel:+842437164123` is not public yet, matching the known deployed capability block rather than being misclassified as a data failure.

Fresh evidence-checkpoint verification:

- run `35123270039`
- head `3bb1ae33ad53cee7a551946268d8d48899cf49a2`
- result `SUCCESS`.

## Integration hygiene

Before PR integration, the one-shot mutation workflow/harness/static apply contract and client logo payload were retired from the branch. Historical apply evidence is retained; only public/read-only L4 verification remains executable.

No production Theme PHP/CSS/JS changes are part of this slice.

## PASS

- Owner-approved official logo applied through WordPress-owned Custom Logo.
- Owner-approved phone stored in an exact WordPress-owned menu surface without duplicate menu creation.
- Public logo rendering verified at desktop and mobile L4.
- Ownership and fail-safe boundaries preserved.
- One-shot mutation mechanics retired before integration.

## BLOCKED / UNKNOWN

- `BLOCKED_RELEASE_CAPABILITY`: phone/header utility public rendering requires a separately approved compatible Theme release/deployment.
- Provider L5 is not claimed.
- Theme release/deploy/version promotion is not part of this slice.

## NEXT

Create a Pull Request from `ops/tamduc-identity-contact` to `main`; do not merge, release, or deploy without a separate explicit gate.
