# Curtain 01 unified Homepage Map parity candidate — 27/09/2026

## Scope

Bounded completion of the already-accepted D-041 rollout for `curtain-01`. This slice does not change product/domain ownership and does not publish or deploy production.

Base: canonical `main@55dda598adc7bd677ea2d5301fb42ecc90eae94a`, Theme metadata `1.3.54`.

Branch: `work/homepage-curtain01-map-parity-20260927`.

## RED -> GREEN

`tests/offline/homepage-curtain01-map-parity-contract.php` was committed before the Curtain 01 shared-map implementation.

GREEN candidate now:

- adds `homepage_curtain01_effective_surface_map()` to the request-local shared Homepage model;
- makes Curtain 01 frontend before/after composition consume `homepage_effective_surface_map( 'curtain-01' )` instead of a duplicated hard-coded section order;
- makes the primary `Trang chủ đang hiển thị` admin map support both Law 01 and Rèm 01;
- gives every effective Curtain 01 surface a stable Theme presentation marker/deep-link anchor;
- aligns Curtain 01 Hero, Proof, About, Process, Projects, Knowledge and Contact reads with the preset-scoped effective source resolver;
- shares Category Showcase and Catalogue visibility projections between the effective model and the frontend templates;
- preserves WooCommerce, WordPress and provider ownership; no private storage/direct meta/table reads were introduced.

Effective Curtain 01 order in the test/runtime model:

`hero -> proof -> front-page-content -> about -> category-showcase -> catalogue -> process -> projects -> knowledge -> final-cta`.

## Runner-independent L1/L2 evidence

Direct exact-branch source verification checked the shared resolver/composer/admin/test wiring and 9 Curtain 01 templates.

Result: **PASS — 0 failures**.

The check also rejected private/provider reads (`get_post_meta`, `$wpdb`, `choiceguide_`, `rootprofile_`) in the new shared map/admin path.

## Real WordPress draft evidence — remquocanh.vn

`remquocanh.vn` is a production pilot, so all work was kept inside its isolated WPVibe draft. The live active Theme remained AZnet Theme `1.3.31`; no draft publish, live Theme switch or content mutation occurred.

The draft was first synchronized from canonical 1.3.31 production source to canonical 1.3.54 production-source bytes. The 54 production-source files changed between `c99e6ea8cad412480e1cbdd2e28babca6cb8248f` (1.3.31) and `55dda598adc7bd677ea2d5301fb42ecc90eae94a` were copied successfully; the compare contained no binary-file delta. The Curtain parity candidate production files were then overlaid on the same isolated draft.

Fresh public draft preview readback:

- Theme assets expose `ver=1.3.54`;
- one `main` landmark;
- no WordPress critical-error page;
- exact effective surface order matches the ten-item order above;
- actual Rèm Quốc Anh Process, Project, Knowledge and final CTA content render through the candidate.

Fresh mobile Lighthouse on the candidate draft:

- Accessibility: **100/100**;
- Best Practices: **100/100**;
- Performance: **62/100** (lab only);
- LCP 5.6 s; CLS 0; TBT 480 ms; FCP 2.3 s; Speed Index 4.7 s;
- reported opportunities: unused JavaScript (~31 KiB) and unused CSS (~11 KiB); main-thread diagnostic 2.8 s.

Performance is recorded as evidence, not promoted to a performance PASS criterion from this one lab run.

## Remaining QA boundary

- Public draft WordPress runtime/front-end projection is freshly proven at the scope above.
- Authenticated wp-admin Homepage Map runtime/browser parity remains **UNKNOWN** because WPVibe preview tokens do not authenticate the draft Theme into wp-admin; an admin preview URL resolves to the WordPress login surface.
- The repository C5 fixture/workflow is extended to seed Proof/Process/Projects, run `tests/runtime/homepage-curtain01-map-runtime.php`, and compare authenticated admin/public surface order plus a11y when an executable runner is available.
- GitHub hosted runners have recently terminated jobs before steps execute; no runner PASS is inferred.
- L5 provider integration and L6 release/deployment remain separate.

## Safety

- Live `remquocanh.vn` Theme remains `1.3.31`.
- No production publish/deploy was performed.
- The current WPVibe draft is a review/runtime candidate only and must not be published before canonical merge/release/deployment gates are separately approved.
