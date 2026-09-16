# X3 — Media / Gallery / Embed Design

**Project:** AZnet Theme  
**Milestone:** v1.2 WordPress Experience Completion  
**Slice:** X3 — Media / Gallery / Embed  
**Status:** Approved design / pre-implementation  
**Date:** 2026-09-16  
**Base:** `main@e33e3f1a2d48a8867e11a6fcc66eadd71d72da6e`

## 1. Goal

Make WordPress-native authored media robust, readable and responsive across AZnet Theme content surfaces without turning the Theme into a gallery application, media workflow owner or content engine.

X3 covers presentation for common WordPress Core/Gutenberg media output and common legacy WordPress media markup while preserving WordPress ownership of media data, attachment metadata, block state, shortcode rendering, embed semantics and content lifecycle.

## 2. Ownership boundary

### WordPress owns

- Media Library items and attachment metadata.
- Block content and serialized block attributes.
- Image sizes, attachment URLs and attachment lifecycle.
- Gallery block/gallery shortcode semantics and ordering.
- Caption text and authored content semantics.
- Video/audio/embed block output and provider embed markup.
- `the_content()` rendering, block transforms and shortcode execution.
- Alignment classes emitted by Core.

### AZnet Theme owns

- Responsive presentation of authored media inside Theme content surfaces.
- Layout containment and overflow protection.
- Visual treatment of figures, captions and galleries.
- Presentation of Core alignment classes within Theme layout limits.
- Focus presentation when authored media contains interactive descendants.
- Editor/frontend presentation parity where Theme can provide it safely.
- Surface-aware Theme asset loading.

### Forbidden

- New gallery/media domain store.
- Lightbox/modal application engine.
- Custom gallery ordering state or duplicate attachment metadata.
- Media upload/edit workflow takeover.
- CDN, image optimizer or media proxy subsystem.
- Provider/private API reads.
- Authoritative media semantics inferred from URL, filename, slug or attachment heuristics.
- JavaScript added only to recreate behavior WordPress already owns.

## 3. Supported surfaces

X3 applies to authored WordPress-native content rendered by `the_content()` on:

1. native single Post;
2. native Page;
3. a Page used as the static Front Page.

It does not broaden into Search, 404, Archive result cards, WooCommerce surfaces, provider surfaces or Theme-generated Homepage Composer sections. Existing surface owners and asset gates remain intact.

The Theme may share the same media presentation module across the three authored-content surfaces, but the module must remain independently gated rather than globally loaded across the ecosystem.

## 4. Supported media output

### 4.1 Modern Core/Gutenberg output

X3 must present at least:

- Image block and native `<img>` output;
- `figure` / `figcaption`;
- Gallery block and nested image figures;
- Video block;
- Audio block;
- Embed block / responsive embed wrappers;
- provider iframe output when WordPress/Core emits it;
- Core alignment classes including normal, wide and full alignment where supported by the surrounding Theme layout.

### 4.2 Legacy WordPress output

The owner approved compatibility option **A**. X3 therefore also supports common legacy output that remains normal in migrated WordPress sites:

- `.wp-caption`;
- `.wp-caption-text`;
- `.gallery`;
- `.gallery-item`;
- `.gallery-icon`;
- `.gallery-caption`;
- gallery shortcode / Classic Editor output;
- ordinary legacy image alignment classes where WordPress emits them.

Legacy support is presentation compatibility only. X3 does not create a second legacy rendering engine.

## 5. Presentation architecture

### 5.1 Dedicated shared media module

Use one bounded Theme-owned media presentation module, expected as `assets/css/components/media.css`, rather than scattering X3 rules across `article.css`, `generic-content.css` and unrelated components.

The module should consume existing AZnet tokens and WordPress classes. It should not introduce a separate token system.

Existing article/generic content CSS may retain rules that are genuinely article/layout-specific. Duplicated generic media containment rules should be consolidated only where doing so directly serves X3 and does not create unrelated refactoring.

### 5.2 Surface-aware loading

The media module should load only when the current request can render authored Page/Post content in X3 scope:

- single native Post;
- native Page;
- static Front Page backed by a Page.

It must not become a globally enqueued stylesheet.

WooCommerce and external provider surfaces remain outside this asset gate.

### 5.3 Editor parity

Where possible, the same media presentation primitives should be available in the WordPress block editor through the existing `editor-styles` / `add_editor_style()` mechanism.

Do not introduce a parallel editor asset subsystem merely for X3 if the existing editor stylesheet mechanism can express the required parity.

Parity means authored media has materially consistent width behavior, gallery spacing, caption treatment and alignment expectations between editor and frontend. Exact browser chrome dimensions are not required to match.

### 5.4 `theme.json`

Use `theme.json` only where it is the correct WordPress-native owner for content/wide layout semantics or editor/frontend parity.

The current `contentSize` and `wideSize` foundation should be retained. X3 should not enable broad Appearance Tools or expose new authoring freedoms unrelated to the slice.

If no `theme.json` change is needed after tests prove the existing layout values are sufficient, leave it unchanged.

## 6. Layout behavior

### 6.1 Images and figures

- Images remain within their applicable content/alignment container.
- Intrinsic aspect ratio is preserved unless authored/Core markup intentionally defines another behavior.
- Theme must not force a universal crop on authored content images.
- `figure` must not cause horizontal overflow.
- Captions remain visually subordinate, readable and associated with their media.
- Long Vietnamese captions must wrap safely.

### 6.2 Galleries

- Core and legacy galleries must form a stable responsive grid/flex presentation without horizontal page overflow.
- Gallery items must be allowed to shrink below desktop column widths.
- Mobile must collapse gracefully rather than preserve a desktop column count at unusable widths.
- Theme must not infer semantic importance or reorder gallery items.
- Existing WordPress links inside gallery items remain intact.

### 6.3 Video and audio

- Video must be constrained responsively and preserve its native controls.
- Audio controls must not overflow narrow containers.
- Theme must not replace native media controls.

### 6.4 Embeds and iframes

- Core responsive embed behavior remains authoritative.
- Theme adds containment safeguards so iframe/embed output does not create page-level horizontal overflow.
- Responsive wrappers may use aspect-ratio behavior only when compatible with WordPress Core output; do not assign one aspect ratio to every embed provider.
- Provider-specific JavaScript or selectors are out of scope.

### 6.5 Alignment

Normal content remains within the Theme content measure.

`alignwide` may expand to the Theme wide container already represented by `--aznet-theme-container-wide` / `theme.json` wide size.

`alignfull` may expand beyond the normal content measure only inside a safe Theme-owned shell. It must not produce horizontal scroll at desktop, tablet or mobile widths.

Alignment support must be expressed from WordPress/Core classes and Theme layout primitives, not by inspecting media URLs or block content heuristically.

## 7. Accessibility

X3 must not rewrite authored alternative text or caption semantics.

Theme responsibilities are presentation-level:

- preserve focus visibility for interactive media descendants/links;
- avoid clipping keyboard focus rings;
- keep captions readable at zoom and narrow widths;
- avoid presentation changes that hide native audio/video controls;
- avoid layout overflow that makes content unreachable;
- preserve semantic figure/caption markup emitted by WordPress.

Axe critical/serious failures caused by Theme-owned presentation are blocking. Existing policy for clearly attributable authored-content semantic defects remains separate and must not become a blanket accessibility ignore.

## 8. Error and fail-soft behavior

Malformed or unusual authored media must fail visually soft:

- unknown embed wrappers remain visible and contained where possible;
- absent captions produce no empty decorative caption chrome;
- missing images remain WordPress/browser behavior, not Theme-repaired data;
- unsupported provider markup must not trigger provider-specific Theme code.

The Theme must never mutate authored content to “fix” media rendering.

## 9. Testing strategy

Production implementation follows RED → intended failure → minimal GREEN → regression.

### L1 — Static

Verify at minimum:

- dedicated X3 media presentation asset exists only after GREEN;
- naming uses `aznet-theme-*` / `--aznet-theme-*` conventions;
- no private storage/provider APIs;
- no gallery/lightbox JavaScript application engine;
- media stylesheet is surface-aware, not globally loaded;
- Theme metadata remains `1.1.0`.

### L2 — Contract/TDD

The RED contract should fail on the pre-X3 baseline for a specific missing media capability, not for unrelated formatting.

GREEN contract should prove:

- modern Core selectors/output are covered;
- approved legacy selectors/output are covered;
- Page/Post/front-page authored surfaces receive the media module;
- Search/404/Archive/Woo/non-authored surfaces do not receive it;
- existing article/generic content behavior is retained;
- no X3 JavaScript/lightbox engine is introduced.

### L3 — Real WordPress runtime

Use WordPress 6.9 with zero mandatory plugins and fixture content containing representative native and legacy media.

Fixture should include at least:

- image with caption;
- long caption;
- Core gallery;
- legacy gallery shortcode/Classic output;
- video;
- audio;
- responsive embed/iframe;
- normal/wide/full alignment cases where Core supports them;
- Page, Post and static Front Page coverage.

### L4 — Browser / responsive / editor parity

Verify representative viewports including 1440, 1024, 390 and 320 widths.

Acceptance includes:

- no page-level horizontal overflow;
- images/video/audio remain usable;
- galleries adapt without clipped items;
- captions wrap and remain readable;
- wide/full alignment remains within safe Theme geometry;
- expected native controls remain available;
- no unexpected console/page errors;
- Axe critical/serious = 0 for Theme-owned presentation;
- editor/frontend parity is materially consistent for image/gallery/caption/alignment presentation.

Retained regression must include relevant X1, X2, P2/P3, Homepage, WooCommerce, provisioning and existing release-critical checks. PASS from one QA layer must not be inferred into another.

## 10. Files expected to change

Exact implementation may refine this set after RED evidence, but the preferred bounded change is:

- new `assets/css/components/media.css`;
- `inc/theme/assets.php` for X3 surface-aware loading;
- `inc/theme/design-system.php` only if needed to expose the same media stylesheet through the existing editor-style mechanism;
- `theme.json` only if RED/L4 evidence proves a native layout declaration is missing;
- new focused offline/runtime/browser tests and workflow/evidence for X3;
- source/checkpoint documents only at closure when roadmap state advances from X3 NEXT to X3 PASS / X4 NEXT.

Avoid template PHP changes unless tests demonstrate markup owned by the Theme itself prevents correct presentation. Authored `the_content()` markup should remain WordPress-owned.

## 11. Alternatives considered

### Alternative 1 — Add rules directly into `article.css` and `generic-content.css`

Rejected as the primary approach because X3 spans Post, Page and static Front Page and would duplicate media rules across components. It also makes later editor parity and asset ownership less explicit.

### Alternative 2 — Build a JavaScript gallery/lightbox layer

Rejected. It exceeds the X3 source boundary, creates a new application subsystem and duplicates behavior that belongs to WordPress/plugins where required.

### Alternative 3 — Support only modern Core blocks

Rejected by owner decision. Common legacy WordPress output is part of X3 compatibility because migrated/editorial sites may still render Classic Editor captions and gallery shortcode markup.

## 12. Rollback

X3 is presentation-only and independently reversible.

Rollback reverts the X3 media stylesheet, surface asset gate, editor-style reference if added, focused tests/workflow and source closure checkpoint. No Post/Page/media/attachment data is mutated, so WordPress content remains intact.

## 13. Exit gate

X3 may be marked PASS only when:

- approved scope above is implemented without ownership expansion;
- RED evidence failed for the intended missing X3 capability;
- L1/L2/L3/L4 fresh evidence succeeds on the exact final feature head;
- retained regressions required by the milestone succeed;
- source closure records X3 PASS / X4 NEXT;
- final feature PR is reviewable and rollback is documented.

Merging to `main` remains a separate product-owner gate. X3 does not promote Theme metadata; version stays `1.1.0` until X6.

## 14. Exact next after spec approval

Write the X3 implementation plan from this approved design, then execute it with TDD on the isolated feature branch. Do not write production behavior before the implementation plan gate is cleared.
