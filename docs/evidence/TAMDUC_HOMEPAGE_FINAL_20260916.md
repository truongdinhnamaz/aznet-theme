# Tâm Đức Homepage Final L4 Evidence — 2026-09-16

## Scope

Pilot site: `https://tamduchanoi.aznet.vn/`

Branch: `ops/tamduc-homepage-completion`

This checkpoint verifies the public Homepage after the approved, bounded WordPress-owned corrections. It does not claim Theme release/deployment, RootProfile/ConvertFlow L5 integration, or any client fact that was not authoritative on the pilot.

## Applied site-owned corrections retained

The bounded apply run completed on the real pilot before this final verification:

- Site Tagline: `Trọn Tâm với khách – Vẹn Đức với nghề`.
- Page #142 title: `Dội ngũ` → `Đội ngũ`.

No Theme production PHP/CSS/JS was modified for those corrections.

## Mobile navigation failure root cause

The first final L4 run (`35112303322`) failed only at `390x844` and `320x800` with `PRIMARY_NAVIGATION` because the harness required a visible `<nav>` landmark at all viewport widths.

Fresh screenshots showed the actual mobile contract: the desktop navigation landmark is collapsed and the visible `Menu` trigger remains available. Canonical AZnet Theme browser tests already exercise this contract through `[data-aznet-theme-nav-trigger]`, `[data-aznet-theme-nav-panel]`, `aria-expanded`, keyboard `Enter`, `Escape`, overflow checks, and focus return.

A RED contract run (`35113676128`, head `b355763c16c987aefa23cea7952ea014abebbeef`) failed as intended because the Tâm Đức final harness did not yet contain the mobile interaction contract.

The minimal GREEN change updated only the Tâm Đức L4 harness. Desktop viewports still require a visible navigation landmark; mobile viewports now verify the collapsed navigation interaction contract rather than treating the intentionally hidden panel as a defect.

## Fresh final GREEN

Workflow: `Tâm Đức Homepage Final L4`

Run ID: `35113766833`

Head SHA: `6f8ca0acfeac8a6dd1abad9ea6a275c94e2e596b`

Conclusion: `success`

Artifact: `tamduc-homepage-final-l4`

Artifact ID: `10453825794`

Digest: `sha256:ef5523aaadc0aab2a94bacc8ca6e72ba9e89eee4c58ccb9bb15d7dede511eed8`

`summary.json` reports `blocking_failures = []`.

### 1440x1000

- HTTP 200.
- exactly one `<main>`.
- at least one H1.
- horizontal overflow 0.
- visible desktop navigation landmark.
- public internal CTA links present.
- Theme-owned request failures 0.
- page errors 0.
- Axe critical/serious violations 0.
- approved Site Tagline visible in footer.
- corrected `Đội ngũ` heading visible.

### 1024x900

Same gates as 1440x1000: all PASS.

### 390x844

- HTTP 200.
- exactly one `<main>`.
- at least one H1.
- horizontal overflow 0.
- mobile trigger visible.
- initial `aria-expanded=false`.
- keyboard `Enter` opens the mobile panel and sets `aria-expanded=true`.
- open mobile panel horizontal overflow 0.
- keyboard `Escape` closes the panel and returns `aria-expanded=false`.
- focus returns to the mobile navigation trigger.
- public internal CTA links present.
- Theme-owned request failures 0.
- page errors 0.
- Axe critical/serious violations 0.
- approved Site Tagline and corrected `Đội ngũ` visible.

### 320x800

Same mobile-navigation and public L4 gates as 390x844: all PASS.

## Branch provenance check

Comparison against `main` at `6d6459f4497d1b7612c36622a13e7c5c71ba2a73` shows this branch contains site-ops workflows, evidence, mutation plan, and test harnesses only. Production Theme PHP/CSS/JS files are unchanged.

## PASS

- Tâm Đức Homepage bounded site-owned corrections: PASS.
- Public Homepage L4 across 1440/1024/390/320: PASS with fresh evidence.
- Mobile navigation keyboard/open/close/focus/overflow contract: PASS.
- Ownership boundary: PASS; no client data was hard-coded into production Theme source.

## BLOCKED / UNKNOWN

- Client facts/assets not authoritatively available remain UNKNOWN: approved logo, real hotline/phone, office/Hero image, personnel/photos/credentials, address and other business claims.
- Desired presentation capability that exists only in unreleased canonical source remains `BLOCKED_RELEASE_CAPABILITY` until the separate Theme release/deployment gate is approved.
- RootProfile/ConvertFlow provider L5 is NOT CLAIMED by this Homepage checkpoint.
- Theme release/tag/deployment is NOT CLAIMED and was not performed.

## EXACT NEXT

Integration of this site-ops/evidence branch is a separate owner gate. Do not merge `main`, release, tag, or deploy Theme without explicit approval.
