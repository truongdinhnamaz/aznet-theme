# Law 01 Homepage Closure — 18/09/2026

Repository: `truongdinhnamaz/aznet-theme`

## Scope

Post-release Law 01 presentation closure on canonical AZnet Theme main. This checkpoint records Theme-owned presentation and QA changes only. It does not change WordPress content ownership, RootProfile identity/team semantics, ConvertFlow Journey semantics, WooCommerce state, public integration contracts, or release/deployment state.

## Canonical sequence

- PR #129 — Burgundy Homepage visual closure:
  - production GREEN candidate `680664151df857cf47982de35248bb9084c008c9`;
  - retained runtime/browser expectations reconciled on exact head `182ef13245b73897117ae6243cc14432cc75e622`;
  - merged to `main@9c49cb3d19dbf9354cdb6fdef73fec0b27fd3f38`.
- PR #130 — media-rich visual QA fixture + Team portrait accessibility:
  - RED proved missing visual fixture media;
  - richer fixture exposed real axe `link-name:serious` on linked Team portrait media;
  - minimal production fix `136444d66e50e654183d8154247caf4f8f5caeee` names the portrait link from the WordPress-owned member Page title;
  - merged to `main@65ec3f8f97e5f3f2ea452271a8188e5430341c94`.
- PR #131 — Professional Footer column order:
  - RED `0430514fad407b75593c788b852f6b29079d714b` failed specifically because Contact followed primary navigation;
  - minimal GREEN `30fb1e24fb337749384f033eb3c43082687f427b` changes the `professional` preset composition to Identity -> Contact -> Navigation -> Social while preserving Standard/Compact order;
  - merged to canonical `main@8238248f0d97383d0e43461c481ad6bbc1f480a7`.

## Fresh exact-main verification

`V1 Exact Main Verification` run `35315451153` completed SUCCESS on exact `main@8238248f0d97383d0e43461c481ad6bbc1f480a7`.

Verified scope includes:

- reusable static/contracts PASS;
- clean WordPress 6.9 runtime PASS;
- exact-main core routes PASS;
- browser/responsive/keyboard/focus/a11y verification PASS.

Pre-merge retained gates for PR #131 also completed SUCCESS, including Y3 Footer System, D-027 Standalone Core L3/L4, D-027 Exact Package, retained full regression, V1 Core PR CI, Law Site Provisioning Candidate Package, R6 Performance Baseline, X3 Media Gallery and X4 Pagination Navigation.

## Presentation outcome

Law 01 Burgundy Homepage now retains the approved presentation sequence:

1. Header
2. Hero + trust strip
3. Services
4. About/Profile + Team
5. Latest Posts
6. Professional Footer

Burgundy no longer renders the extra Topics/Analysis/News/Process/FAQ/Final CTA sequence used by the fuller Navy variant. No WordPress content is deleted by this presentation decision.

The Professional Footer order is now:

`Identity -> Contact -> Navigation/Quick links -> Social`

## Ownership boundary

- WordPress remains authoritative for site identity, Pages, Posts, menus, media and editorial lifecycle.
- Theme owns presentation composition and accessibility presentation.
- RootProfile Team/member discovery remains BLOCKED_EXTERNAL_CONTRACT under issue #112; the Theme must not infer authoritative membership from WordPress users/authors, posts, slugs, URLs, Page IDs or private storage.
- No new provider certification, release publication or production deployment is inferred from this post-release source checkpoint.

## PASS

**Law 01 Homepage presentation closure PASS through L1-L4 plus retained exact-package/regression gates on the exact merged source.**

## Next

No new Theme implementation slice is opened by this checkpoint. Preserve the completed core/release state. RootProfile-backed Team integration remains held at issue #112 until the RootProfile owner publishes a suitable public/versioned projection, or until the product owner separately approves a new Theme roadmap slice.
