# Law 01 Homepage Phase A Audit — 28/09/2026

## Scope

Read-only L0 audit for the approved Law 01 Homepage completion program.

Canonical repository: `truongdinhnamaz/aznet-theme`  
Canonical main: `6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`  
Theme metadata on canonical main: `1.3.54`  
Execution branch: `work/law01-completion-audit-20260928`  
Pilot: `https://lstamduchn.vn`

This checkpoint does not claim release, production deployment, provider L5, or fresh pilot browser PASS.

## Governing boundary

SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.

The audit preserves WordPress ownership of Page/Post titles, excerpts, Page hierarchy, Categories and native queries. Theme must not infer missing Service or Team names from slugs, URLs, Page IDs, authors or provider-private state.

## Current canonical state

- Canonical `main` is unchanged from the approved design-time base: `6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`.
- The execution branch is ahead only by the approved design/plan documentation at this checkpoint and is not behind canonical main.
- Law 01 composition is driven by the shared `homepage_effective_surface_map('law-01')`; frontend composer reads that model.
- Existing accepted Burgundy Law 01 Homepage presentation remains retained evidence unless fresh execution invalidates it.
- Historical/open Law 01 branches are reference only and are not treated as authoritative over current main.

## Fresh pilot read-only state

Fresh WordPress Core REST readback on 28/09/2026 confirms:

- active Theme: AZnet Theme `1.3.53`;
- static Front Page: Page #2;
- Posts page: Page #294;
- Services parent: Page #122 `Dịch vụ pháp lý`;
- Team parent: Page #142 `Đội ngũ`;
- Process Page #180 and FAQ Page #181 remain published;
- Contact Page #36 remains published;
- WPVibe Connect `1.18.0` is installed but inactive;
- connection is therefore read-only for this audit.

### WordPress source gaps retained

Services children under Page #122:

- #174 title `Tư vấn pháp luật`;
- #175 title `Tranh tụng`;
- #176 title `Đại diện ngoài tố tụng`;
- #177 title `Các dịch vụ pháp lý khác`;
- #178 published with an empty WordPress title;
- #179 published with an empty WordPress title.

Team children under Page #142:

- #339 published with an empty WordPress title;
- #326 published with an empty WordPress title.

Editorial Categories:

- Category #13 `Phân tích vụ án`: 0 published Posts;
- Category #1 `Tin tức`: 0 published Posts.

The pilot currently has five published native Posts, none assigned to Category #13 or #1.

These are authoritative-source gaps. Theme must not invent the missing names or legal articles.

## Fresh browser availability

A fresh rendered-DOM attempt on the public Homepage failed before Theme DOM inspection with a redirect-loop/network error:

`ERR_TOO_MANY_REDIRECTS` on the WPVibe snapshot callback URL.

Therefore pilot L4 is `QA_UNKNOWN / BLOCKED_ENVIRONMENT` for this checkpoint. No browser PASS is inferred from earlier fixture evidence.

## Homepage audit matrix

| Surface | Effective source / owner | Render gate | Fresh pilot state | Classification | Fresh QA layer | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| Header / navigation | WordPress menus + Theme presentation | Header preset / menu availability | source present; fresh rendered DOM unavailable | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | No fresh evidence invalidates retained Header behavior. |
| Hero | shared Law 01 Hero source resolver; WordPress block/Page/fallback | renderable mapped source or bounded WordPress fallback | Front Page and authored Hero-era content exist | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | Do not reopen without a fresh regression. |
| Profile / trust | WordPress About/Team + Theme composition | About or Team Page exists | About exists; Team parent exists | THEME_DEFECT for Team cards / QA_UNKNOWN_L4 | L0 | Current public Team projection accepts untitled child Pages. |
| Services | WordPress Services parent + direct published children | parent exists and child query non-empty | 6 published children; 2 untitled | THEME_DEFECT + SOURCE_GAP | L0 | Current raw child query can render unnamed public cards. Theme must fail soft without inferring slug labels. |
| About | WordPress About Page | mapped published Page | Page #34 has title/excerpt | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | Source-backed. |
| Team | WordPress Team parent + published child Pages | Team parent exists; child query may be empty | 2 published children; both untitled | THEME_DEFECT + SOURCE_GAP | L0 | Public person cards must omit unnamed Pages while admin authoring must still expose them for repair. |
| Latest Posts | mapped knowledge Categories / native Posts | mapped terms + Posts | 5 Posts exist in knowledge categories | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | Request-local ledger remains the accepted duplicate-prevention model. |
| Topics | mapped native Categories | at least one valid mapped Category | native categories exist | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | No fresh source defect identified. |
| Analysis | Category #13 | valid term + at least one Post | 0 Posts | SOURCE_GAP; fail-soft behavior expected | L0 | Section should remain omitted; no placeholder legal content. |
| Legal News | Category #1 | valid term + at least one Post | 0 Posts | SOURCE_GAP; fail-soft behavior expected | L0 | Section should remain omitted; no placeholder legal content. |
| Process | WordPress Page / authored Core List | mapped/fallback Page + supported authored list | Page #180 published | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | No prose-to-step inference allowed. |
| FAQ | WordPress Page / authored Core Details | mapped/fallback Page + supported authored details | Page #181 published | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | No extra domain store. |
| Final CTA | Contact Page / public-safe contact projection | at least one renderable contact source | Page #36 published | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | No provider-private read. |
| Footer | WordPress menus/contact/policy + Theme presentation | Footer context availability | source previously populated; fresh DOM unavailable | PASS_RETAINED / QA_UNKNOWN_L4 | L0 | Footer remains independent from Law 01 Homepage variant ownership. |

## Theme defects selected for RED→GREEN

### A. Services public renderability

Current `homepage_direct_published_children()` is a raw WordPress published-child resolver. It does not require a non-empty title. The current Services surface/template can therefore accept Pages #178/#179 and render blank public card headings.

Required Theme-owned correction: add a public presentation projection that filters only cards with a non-empty WordPress title and public permalink, applying the public limit after filtering. Raw children remain WordPress/admin-authoring data.

### B. Team public renderability

Current `team_directory_members()` returns published Team child Pages regardless of title. The Homepage profile and public Team directory can therefore render unnamed people cards for Pages #339/#326.

Required Theme-owned correction: preserve raw `team_directory_members()` for authoring/admin but add a public projection that filters unnamed/unlinkable Pages and applies the public limit after filtering. No person identity may be inferred.

## Baseline verification disposition

The current container cannot clone GitHub because outbound DNS/network is unavailable, so the plan's local exact-repository baseline commands cannot be executed in that environment. The superpowers SDD helper scripts are also not exposed in the container filesystem.

Ruling for execution:

- no production GREEN change will be made before the first test-only RED commit triggers the exact-branch Law 01 GitHub workflow;
- that RED run must show retained baseline contracts green and fail for the newly introduced missing public-projection behavior;
- any unrelated baseline failure becomes the first defect to investigate rather than being masked;
- final L1/L2/L3/L4 claims depend on exact-branch workflow evidence, not on this audit document.

## PASS

- L0 canonical/source/ownership audit: PASS.
- Pilot source-gap revalidation: PASS as read-only inventory.
- Exact source ownership classification for the two selected Theme defects: PASS.

## BLOCKED / UNKNOWN

- Fresh pilot L4: UNKNOWN / BLOCKED_ENVIRONMENT due redirect-loop snapshot failure.
- Pilot Theme remains `1.3.53`; canonical main is `1.3.54`. No byte-equivalence claim is made.
- WPVibe plugin-backed draft/file runtime path is unavailable because WPVibe Connect is inactive.
- Fresh executable exact-branch baseline L1-L4 remains pending the first RED workflow run.

## Rollback

This checkpoint is documentation only. No production code or pilot content was mutated.

## Exact next

Task 2: create the RED Services public-renderability contract and fixture on the execution branch, observe the exact-branch workflow fail for the intended missing helper/behavior, then implement the minimal Theme-owned GREEN projection.


## Native execution checkpoint — clean branch

Execution moved to a clean branch from canonical main after the earlier work branch was found to contain unrelated changes.

Branch: `work/law01-phase-a-native-clean-20260928`  
Draft PR: #289  
Current exact head at this checkpoint: `bcffb5e7810e52c09436808a2af81fb58083aef7`

### Theme-owned changes completed on the clean branch

- Added `homepage_renderable_child_pages()` as a presentation-only projection over raw published child Pages.
- Law 01 Services template, shared effective surface map and Profile service-count presentation now consume only renderable Service Pages.
- Homepage admin Services READY/EMPTY state now uses the same public renderability rule.
- Added `team_directory_public_members()` while retaining raw `team_directory_members()` for WordPress/admin authoring.
- Homepage Team presentation, shared surface map and public Team directory now omit unnamed/unlinkable published child Pages without inferring identity from slug/URL/Page ID.
- Homepage Team admin still exposes raw published source Pages for repair, but distinguishes public renderable count and labels an untitled admin row as `Chưa có tên` instead of presenting an empty name.
- Added source-gap fixtures/regressions for untitled Service and Team Pages.
- Added runtime regression for empty Analysis/Legal News Categories.
- Added exact Burgundy Homepage Theme-surface order assertion.
- Added explicit no-`post_name` heuristic guard to the Homepage content-map contract.

### TDD evidence observed

Services helper RED was observed on exact PR CI:
`Public Homepage child projection must exist before Law 01 Services can reject untitled source Pages.`

The minimal helper implementation then passed the targeted Homepage content-map contract and PHP syntax verification in the isolated verification container.

Team public-projection RED was separately observed against the pre-helper source state before adding `team_directory_public_members()`.

### Current external QA state

At the exact head above, GitHub Actions runs have been created but remain queued. The Law 01 Browser Quality run is queued, as are the core/offline/reference regressions. Therefore no exact-head L3/L4 PASS is claimed yet.

Pilot `lstamduchn.vn` remains read-only for this program:

- live Theme observed at `1.3.53`;
- canonical main remains `1.3.54`;
- WPVibe Connect remains inactive;
- public DOM snapshot remains blocked by redirect-loop behavior.

### Rollback

The clean branch is based directly on canonical `main@6fd5db5bb1178e4ecaf48eb98995b2816b8d7b9b`. Production behavior changes are bounded to Services/Team public presentation projections plus their consumers and can be reverted independently by commit.

### Exact next

Wait only for the already-created exact-head CI runs. If any run fails, reproduce the first real failure at the shallowest layer and open one corrective RED→GREEN micro-slice. If the relevant exact-head Law 01/core regressions pass, finalize Phase A evidence and review PR #289. Do not merge, release, version-bump or deploy without the separate gate.
