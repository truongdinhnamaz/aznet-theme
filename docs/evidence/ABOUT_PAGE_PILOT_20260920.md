# About Page Pilot — lstamduchn.vn — 2026-09-20

## Scope

Live pilot surface only: `https://lstamduchn.vn/gioi-thieu/`.

This checkpoint is intentionally isolated from the Homepage workstream. No Homepage content, Homepage composer setting, Homepage pattern, Homepage CSS file, or WPVibe draft-theme file was changed by this deployment.

## Ownership

- WordPress owns the published Page content and links.
- AZnet Theme remains presentation owner.
- Canonical reusable About composition/presentation remains in PR #175.
- The live pilot uses a site-local WordPress Custom CSS projection scoped only to `.aznet-theme-page-kit--about` because the existing WPVibe draft is older than the active live Theme and may contain work from another conversation.
- This pilot override is not a substitute for the canonical Theme source and is not an integration-contract PASS.

## Live state before deployment

- Active Theme: AZnet Theme `1.3.15`.
- Existing WPVibe draft: `aznet-theme-wpvibe-draft`, metadata `1.3.12`; preserved and not published, overwritten or deleted.
- About Page: WordPress Page ID 34, slug `gioi-thieu`; title and authored excerpt already existed; body was empty.
- WordPress-native sources reused for public facts:
  - six published Services child Pages;
  - published Process Page;
  - published Team Page destination;
  - published Contact Page;
  - existing media attachment 182.

## Live changes

1. Published Page 34 body now contains the approved About editorial composition:
   - opening;
   - approach/story;
   - principles;
   - four-step process;
   - six service groups linked to their published Pages;
   - Team teaser;
   - verification/trust band;
   - Contact CTA.
2. Site-local Custom CSS post `227` with slug `aznet-theme` was created and selected through Theme mod `custom_css_post_id=227`.
3. The CSS is scoped to `.aznet-theme-page-kit--about`; the Homepage does not contain that class, so the override does not style the Homepage surface.
4. Active Theme remains `1.3.15`; no Theme downgrade or draft publication occurred.

## Verification

- Live About DOM contains the expected `.aznet-theme-page-kit--about` root and all authored sections.
- Custom CSS marker and burgundy variables are present in rendered live HTML.
- Homepage check found no `.aznet-theme-page-kit--about` root.
- Lighthouse mobile: Accessibility 100/100; Best Practices 100/100.
- Lighthouse desktop: Accessibility 100/100; Best Practices 100/100.
- Fresh site inventory after deployment still reports active AZnet Theme `1.3.15`.

## Rollback

Non-destructive rollback path:
1. set Theme mod `custom_css_post_id` back to `-1`; this disables the pilot CSS without deleting the CSS post;
2. restore the prior WordPress revision for Page 34 if the authored body needs to be reverted.

The existing WPVibe draft remains untouched and available to its owning workstream.


## Horizontal alignment refinement

After visual review of the live pilot, the About Page was still visibly narrower than the Law 01 Homepage because the generic Standard Page contract constrains `.aznet-theme-page__content` to the content measure, while the Burgundy Law 01 Homepage uses a 96rem constrained inner container.

Pilot-only refinement:
- Page 34 outer article now uses the same desktop width formula as the Burgundy Law 01 Homepage: `min(calc(100% - (2 * gutter)), 96rem)`;
- the About Page header is allowed to the same 96rem shell;
- Page 34 content max-width is removed so the About Page Kit can use the full aligned shell;
- mobile retains the Theme mobile gutter;
- selectors are scoped to `article.post-34.aznet-theme-page`, so the Homepage workstream remains untouched.

Fresh verification after refinement:
- live Homepage still renders `aznet-theme-homepage--law-01-burgundy-gold`;
- About CSS override is present in rendered HTML;
- mobile Accessibility 100/100 and Best Practices 100/100;
- desktop Accessibility 100/100 and Best Practices 100/100.
