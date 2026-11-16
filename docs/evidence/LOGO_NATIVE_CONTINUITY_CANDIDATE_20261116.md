# Native Custom Logo continuity - candidate 2026-11-16

Base: main 07eab630ba4a546500e6a5eaca285458a423c122, AZnet Theme 1.3.110.
Observed reference: anpvietnam.vn Header and Footer restored after setting native WordPress theme mod custom_logo=9727. This was a site configuration issue, not an image-rendering defect in Theme code.

Bounded candidate:
- On a future theme switch to AZnet Theme, preserve ONLY an explicit, valid, previous WordPress-native custom_logo attachment when the current Theme value is empty.
- Show a scoped admin notice in AZnet Theme Control Center for an unset/invalid image. Manual site-owned selection remains authoritative.
- No filename/URL/site_icon/third-party-option guessing, no cross-site IDs, no plugin reads.
- No production takeover, no release/version bump, no site mutation.

Local evidence: RED before module existed (require missing), then PHP lint PASS and offline contract GREEN covering existing, valid old, missing, malformed, permissions and notice scope. WordPress L3, browser L4, integration L5 and release L6 are not claimed.

Acceptance gate before release: WordPress 6.9+/PHP 8.1+ runtime, theme switch from a native custom-logo theme, site without logo, site with existing logo, page Header/Footer + a11y/mobile, negative tests and package/unzip/reverify. Rollback: revert this isolated commit or remove require and module.
