# AZnet Theme — Law Site Provisioning v1.1 Design

**Date:** 2026-09-09  
**Status:** Design approved in chat; written-spec review required before implementation  
**Stacked base:** `work/law01-premium-presentation@4ab36cd97ba1481fd934482ad6fbe0474ef4e5e9`  
**Upstream dependencies:** Homepage Composer + Law 01 (PR #58), Law Site Provisioning v1 (PR #59), Law 01 Premium presentation (PR #60); all draft/open/unmerged when this spec was written  
**Product:** AZnet Theme  
**Scope:** Law Site Provisioning v1.1 — smart recommendations, starter editorial content, starter media, index-safety and launch cleanup

## 1. Problem

Law Site Provisioning v1 can create/map WordPress-native Pages, Categories, menu state and Homepage Composer references, but the setup experience still asks users to make too many low-level decisions and can leave a newly provisioned Homepage visually incomplete when editorial categories contain no public Posts or the Front Page has no real featured image.

The desired experience is stronger:

> Activate AZnet Theme → open “Thiết lập website nhanh” → the wizard inspects the site and preselects safe recommendations → the user reviews one explicit change plan → the wizard reuses suitable existing WordPress objects, creates only what is missing, imports starter media where needed, creates starter editorial content where needed, wires Law 01, and leaves a coherent Homepage immediately visible.

This must remain safe for existing active sites. Faster setup must not become silent semantic inference, destructive overwrite, whole-site noindex on an active website, private Rank Math writes, duplicate demo content, or long-term Theme ownership of WordPress content.

## 2. Decision

Extend Law Site Provisioning with a new provisioning blueprint/version, **`law01-v1.1`**, while retaining compatibility with objects provisioned by `law01-v1`.

The v1.1 design adds four bounded capabilities:

1. **Recommendation-first setup** — use observed WordPress state to prepare non-authoritative default selections that the user can accept or change.
2. **Starter editorial coverage** — create neutral WordPress Posts only where the selected Homepage editorial sources would otherwise have no renderable item.
3. **Starter media import** — import project-owned starter images into the WordPress Media Library and use them only where real media is absent.
4. **Index-safe starter publication** — new/mostly-empty sites may publish starter Posts only when the confirmed plan also enables temporary WordPress-native search-engine discouragement; active sites never receive automatic whole-site noindex.

The invariant remains:

> **SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.**

Provisioning is a one-time/bootstrap operation. After successful creation/import, WordPress owns the resulting Pages, Posts, Categories, attachments, menu items and publication state.

## 3. Non-goals

Law Site Provisioning v1.1 does not:

- create a generic page builder or demo-import framework;
- create Theme-owned legal/business content stores;
- infer authoritative semantics from slug, URL, Page ID, title, post body or taxonomy heuristics;
- continuously synchronize starter content;
- overwrite user-authored Page/Post bodies, titles, excerpts, featured images or taxonomy membership;
- write Rank Math, Yoast or another SEO plugin’s private metadata;
- create a private SEO/noindex engine;
- fabricate lawyers, organizations, addresses, phone numbers, testimonials, certifications, awards, case outcomes or metrics;
- publish invented current legal news;
- create substantive legal advice tied to statutes, deadlines, current regulations or a specific person’s facts;
- delete all demo/starter content after setup;
- auto-enable indexing after content review;
- alter RootProfile, ConvertFlow or WooCommerce domain state.

## 4. Relationship to existing v1

`law01-v1.1` is an additive upgrade path, not a replacement that invalidates v1 provenance.

Rules:

- existing `law01-v1` Pages/Categories/menu/content-map references remain valid;
- v1.1 discovery recognizes prior AZnet provisioning provenance and proposes reuse rather than creating duplicates;
- current explicit Homepage Content Map references win over provenance and recommendations;
- user edits made after v1 provisioning are preserved;
- v1.1 may recommend adding missing starter Posts/media to a previously provisioned site, but only through a new confirmed change plan;
- applying the Law 01 presentation preset remains separate from provisioning.

## 5. Site classification

Classification changes **default recommendations only**. It never creates authoritative content semantics.

### 5.1 `PREVIOUSLY_PROVISIONED`

Use this mode when valid AZnet Law provisioning provenance/receipt exists for `law01-v1` or `law01-v1.1`.

Default behavior:

- reuse current valid Content Map references first;
- reuse surviving provisioned objects by provenance role;
- propose recreating only missing roles;
- propose adding only missing starter editorial/media coverage;
- do not rewrite prior starter copy;
- do not duplicate menu items, Posts, terms, Pages or attachments.

### 5.2 `NEW_OR_MOSTLY_EMPTY`

Use this mode only when all of these observable conditions are true:

- no valid prior Law provisioning provenance;
- no static Front Page is configured;
- no Primary Menu is assigned;
- there are zero published native Posts;
- there are at most two non-trashed Pages discovered by the existing typed Page inventory.

This classification uses counts/configuration only. It does not inspect titles/slugs to decide whether content is “real”.

Default behavior:

- recommend creating all missing Law 01 structural Pages/Categories;
- recommend creating/assigning the starter Primary Menu;
- recommend importing starter media when no real featured image exists;
- recommend creating starter Posts for empty editorial source categories;
- recommend publishing starter Posts **only together with** temporary WordPress-native search-engine discouragement;
- recommend setting the new Home Page as static Front Page and enabling `law-01`.

### 5.3 `EXISTING_ACTIVE`

Any site not matching the two modes above is treated as existing/active.

Default behavior:

- preserve existing Front Page and menu unless the user explicitly chooses a change;
- prioritize reuse recommendations;
- do not set whole-site noindex;
- do not overwrite featured images or content;
- if an editorial source has no renderable public Post, recommend a starter Post but keep it **Draft** unless an accepted public SEO-owner contract can safely provide per-Post noindex;
- absence of such a public SEO contract is a normal fail-safe state, not an excuse for private plugin metadata writes.

## 6. Recommendation model

Recommendations are **proposal state**, not authoritative mappings.

A recommendation may preselect a control in Step 2, but no mapping/content/configuration changes become authoritative until the user confirms the immutable Step 3 change plan.

### 6.1 Recommendation precedence

For Page/Category roles, use this order:

1. **Current valid explicit Content Map reference** — retain by default.
2. **Valid provisioning provenance role** — recommend reuse.
3. **Unique exact normalized display-label match** — recommend reuse as a non-authoritative suggestion.
4. **No unique confident candidate** — recommend Create for missing supported roles or require user choice when ambiguous.

No slug or URL matching is allowed.

### 6.2 Exact normalized display-label match

The v1.1 recommendation engine may normalize display labels only for comparison by:

- trimming leading/trailing whitespace;
- collapsing repeated internal whitespace;
- Unicode-safe case folding/lowercasing where supported.

It must preserve lexical content and Vietnamese diacritics. It must not remove words, derive slugs, apply fuzzy similarity, use URL structure or maintain a hidden semantic synonym table.

Examples:

- blueprint `Dân sự` + one existing Category `Dân sự` → confident suggestion;
- blueprint `Dân sự` + `Pháp luật dân sự` only → no confident automatic selection;
- two exact normalized `Dân sự` candidates → ambiguous, require user confirmation.

### 6.3 Recommendation states

Each role is presented as one of:

- `CURRENT` — current valid explicit mapping/configuration;
- `PROVISIONED_REUSE` — prior AZnet provisioning output survives;
- `SUGGESTED_REUSE` — unique exact normalized display-label match;
- `AMBIGUOUS` — multiple or insufficiently confident candidates;
- `SUGGESTED_CREATE` — no safe existing candidate;
- `OPTIONAL_SKIP` — optional role may be skipped.

The UI must visually distinguish `SUGGESTED_REUSE` from `CURRENT` so users do not confuse a Theme suggestion with existing authoritative configuration.

## 7. Step 2 UX — recommended setup first

Step 2 moves away from a long list of raw `Create / Reuse / Skip + dropdown` controls as the primary experience.

For each role, show a compact recommendation card.

Example reuse card:

```text
Dân sự
Tìm thấy: Dân sự (#18) · 23 bài public
Khuyến nghị: sử dụng danh mục hiện có
[✓ Dùng danh mục này] [Chọn nguồn khác]
```

Example create card:

```text
Tin pháp luật
Chưa có nguồn được xác nhận
Khuyến nghị: tạo mới “Tin pháp luật”
[✓ Tạo mới]
```

Example ambiguous card:

```text
Dân sự
Có nhiều ứng viên cần xác nhận
[Chọn danh mục…]
```

The page provides one primary action:

**“Dùng các thiết lập được khuyến nghị”**

and a secondary expandable area:

**“Tùy chỉnh nâng cao”**

Advanced controls expose the full typed choices without hiding what will happen.

## 8. Editorial source coverage

The goal is:

> **Every selected Homepage editorial source should have at least one renderable item after setup whenever publication can be done safely.**

Before proposing starter Posts, the wizard checks the selected/mapped Category sources using native WordPress publication/query state.

Rules:

- selected Category already has at least one public native Post → do not create a starter Post for coverage;
- selected Category exists but has zero public Posts → recommend one starter Post;
- Category will be created by the plan → recommend one starter Post;
- rerun finds surviving starter Post provenance for the role → reuse; do not duplicate;
- user modified a prior starter Post → treat it as ordinary WordPress content; do not resync starter copy;
- starter coverage is scoped to selected Law 01 editorial sources, not every Category on the site.

## 9. Starter editorial pack

For a new/mostly-empty Law 01 site, the recommended v1.1 starter pack contains at least these eight Posts:

| Role / Category | Starter Post title |
| --- | --- |
| `knowledge_business` | Những tài liệu doanh nghiệp nên chuẩn bị trước khi trao đổi pháp lý |
| `knowledge_civil` | Những tài liệu nên chuẩn bị khi phát sinh tranh chấp dân sự |
| `knowledge_criminal` | Những thông tin nên ghi nhận khi cần hỗ trợ pháp lý trong vụ việc hình sự |
| `knowledge_real_estate` | Hồ sơ cơ bản cần rà soát khi làm việc với vấn đề đất đai |
| `knowledge_family` | Thông tin nên chuẩn bị trước khi trao đổi về vấn đề hôn nhân và gia đình |
| `knowledge_labor` | Hồ sơ thường cần rà soát khi phát sinh vấn đề trong quan hệ lao động |
| `case_analysis` | Cách hệ thống hóa tài liệu trước khi phân tích một vụ việc pháp lý |
| `legal_news` | Cách theo dõi và kiểm tra thông tin pháp luật trước khi sử dụng |

Each starter Post includes:

- ordinary WordPress `post` identity;
- title;
- short excerpt;
- approximately 400–700 words of neutral evergreen starter copy;
- sensible H2/H3 structure;
- selected Category membership;
- one starter featured image when available;
- bounded AZnet provisioning provenance;
- starter-content fingerprint used only for readiness diagnostics, not synchronization.

### 9.1 Starter copy safety

Allowed:

- document-preparation checklists;
- general intake/organization guidance;
- generic questions a client can prepare before a consultation;
- neutral explanations that a legal issue should be assessed from actual documents and facts;
- editorial placeholders that are clearly intended to be reviewed.

Forbidden:

- article numbers or quotations from laws/regulations as substantive advice;
- filing/appeal/limitation deadlines;
- current-law claims that can become stale;
- invented current events or “latest legal news”;
- invented disputes/case outcomes;
- definitive legal conclusions for a hypothetical person;
- unsupported business/identity credentials.

The `legal_news` starter is an evergreen editorial-method article, not a fabricated news story.

## 10. Publication and index-safety policy

### 10.1 New/mostly-empty sites

The recommended plan couples two operations:

1. publish the selected starter Posts;
2. temporarily set WordPress-native search visibility to discourage indexing (`blog_public = 0`).

The Step 3 change plan must display this explicitly.

If the user declines temporary search-engine discouragement, the safe fallback is:

- starter Posts become Draft;
- no public starter publication occurs merely to fill the Homepage.

There is no silent “publish without noindex” fallback.

Before changing `blog_public`, capture its pre-run value. Record whether provisioning actually changed it.

### 10.2 Existing/active sites

Never set `blog_public = 0` merely to protect starter Posts on an active site.

If no accepted SEO-owner public contract exists for per-Post noindex:

- newly created starter Posts remain Draft;
- public Homepage completeness may remain `READY WITH WARNINGS` until real public Posts exist.

If a future accepted public SEO-owner contract can apply per-Post noindex:

- the Theme may consume that public contract after a separate architecture/source gate;
- it still may not direct-write Rank Math/Yoast/private SEO storage.

No such provider contract is required for v1.1 implementation. The v1.1 fail-safe baseline is Draft on active sites.

### 10.3 Search-visibility cleanup

After content editing, the Control Center/readiness flow may offer:

**“Tôi đã hoàn thiện nội dung”**

It then re-runs readiness and, only if provisioning itself changed `blog_public` from an indexable pre-run value, presents an explicit action:

> “Website đang tạm ngăn công cụ tìm kiếm lập chỉ mục do Thiết lập nhanh. Bạn có muốn cho phép lập chỉ mục trở lại?”

The Theme never auto-restores indexing.

If `blog_public` was already `0` before provisioning, the Theme must not infer ownership and must not switch it to `1`.

## 11. Starter media pack

Law 01 v1.1 ships a small project-owned starter media pack intended for bootstrap presentation only.

Minimum pack:

- one large landscape Hero image;
- three or four reusable editorial images for starter Posts;
- optional additional decorative editorial asset only if needed to avoid obvious repetition.

Visual direction:

- premium legal/professional environment;
- architecture, legal documents, desk, books, material/detail imagery, controlled natural light;
- navy/bronze compatibility with Law 01;
- no fake lawyer/person identity;
- no fake firm logo;
- no award/certification/seal;
- no embedded claims or hard-coded text.

Assets must have clear project provenance/licensing suitable for redistribution in the Theme package. Generated/project-owned imagery is preferred over ambiguous third-party stock licensing.

## 12. Media import behavior

Packaged starter assets are **source assets for provisioning**, not the long-term WordPress presentation source.

When the user confirms import:

1. copy/sideload the packaged image through supported WordPress media APIs into uploads;
2. create a normal WordPress attachment;
3. set bounded attachment metadata/alt text/provenance;
4. assign the attachment as Featured Image only to objects explicitly shown in the plan;
5. after successful setup, rendering uses the WordPress attachment URL/ID, not a Theme-folder asset URL.

Rules:

- existing featured image → reuse, do not overwrite;
- no featured image → recommend starter import;
- prior provisioned starter attachment survives → reuse it;
- deleted starter attachment → recommend re-import;
- rerun must not duplicate attachments merely because setup ran again;
- media import failure is fail-soft: keep the CSS Hero fallback / image-less card presentation, report a warning, and do not corrupt the rest of the setup transaction.

## 13. Hero behavior

The premium CSS geometric panel remains a valid fail-soft fallback.

Preferred state after v1.1 provisioning:

- real Front Page featured image exists → render it;
- no real image and user confirms starter media → import starter Hero and set it as Front Page Featured Image;
- media import fails or user skips → retain the existing CSS geometric fallback.

The Theme never replaces a user-selected Front Page Featured Image on rerun.

## 14. Provenance and user-modification detection

Created starter Posts and imported attachments receive bounded internal provenance, for example:

```text
_aznet_theme_provisioned_by = law01-v1.1
_aznet_theme_provisioning_role = starter_post:knowledge_civil
_aznet_theme_provisioning_run = <opaque run id>
```

For starter Posts, store an initial **starter fingerprint** over the starter-owned initial fields needed only for readiness diagnostics, such as:

- title;
- excerpt;
- post content;
- selected starter Category IDs;
- starter Featured Image attachment ID.

A mismatch means `USER_MODIFIED` for diagnostics.

Rules:

- fingerprint mismatch never causes automatic overwrite;
- v1.1 never resynchronizes starter copy after successful provisioning;
- a modified starter Post becomes ordinary WordPress content for all practical behavior;
- provenance never becomes authoritative legal/business semantics.

## 15. Change-plan model

Step 3 remains the hard mutation boundary.

The plan groups operations into human-readable sections:

### Reuse existing WordPress content

Examples:

- `REUSE Page #42 → about`
- `REUSE Category #18 “Dân sự” → knowledge_civil`

### Create WordPress structure

Examples:

- `CREATE Category “Phân tích vụ việc”`
- `CREATE Page “Quy trình tư vấn”`

### Create starter editorial content

Examples:

- `CREATE+PUBLISH starter Post → knowledge_civil`
- `CREATE+DRAFT starter Post → legal_news`

Publication state must be visible per operation.

### Import starter media

Examples:

- `IMPORT Hero starter image → Media Library`
- `SET Front Page Featured Image → imported attachment`

### Update WordPress/Theme configuration

Examples:

- `SET Static Front Page`
- `MAP Homepage Content Map`
- `SET Homepage preset → law-01`
- `SET Search visibility → discourage indexing` (new/mostly-empty site only, when confirmed)

The user must confirm:

**“Tôi đã xem các thay đổi trên và đồng ý áp dụng.”**

No starter Post, attachment, indexing change or mapping may be created outside the displayed plan, except internal rollback/provenance bookkeeping.

## 16. Transaction and rollback

Extend the existing current-run transaction model.

Before mutation, snapshot at minimum:

- normalized Homepage settings affected by the plan;
- `show_on_front` / `page_on_front`;
- Primary Menu location state affected by the plan;
- `blog_public` when the plan intends to change it;
- IDs of reused objects needed for validation.

During the run, record created IDs for:

- Pages;
- Categories;
- Posts;
- attachments;
- menus/menu items.

If the run fails before successful handoff:

- restore captured settings/configuration;
- rollback only objects created by that failed run when safe and supported;
- never delete reused pre-existing content;
- never treat a successful prior run as disposable demo content.

After successful provisioning, created WordPress content is handed off to WordPress/user ownership. There is no general “delete starter site” transaction.

## 17. Readiness model

Retain the distinction:

- `SETUP_READY`
- `READY_WITH_WARNINGS`
- `INCOMPLETE`

and add starter/index diagnostics.

### 17.1 New/mostly-empty expected successful state

A fully accepted recommended setup should prove:

- Front Page is configured;
- Law 01 is active;
- required Page mappings resolve;
- Primary Menu resolves;
- Hero has a WordPress Featured Image or valid fail-soft fallback;
- selected Homepage editorial source groups have renderable public Posts;
- starter publication is protected by the confirmed temporary WordPress-native noindex state;
- no duplicate provisioning roles exist.

This may be `SETUP_READY` but remains **not Launch Ready** until real content review is complete.

### 17.2 Existing/active expected state

If starter Posts are forced to Draft because no public SEO contract exists, the site may be `READY_WITH_WARNINGS` with a clear message that public editorial sections require real published content.

This is an intentional safety outcome, not a setup bug.

## 18. Launch checklist

After setup, show a concrete checklist such as:

- thay ảnh Hero mẫu bằng ảnh thật;
- xác nhận tên/giới thiệu của tổ chức;
- bổ sung đội ngũ thật qua đúng source owner;
- xác nhận thông tin liên hệ;
- rà soát/chỉnh các starter Posts;
- thay các starter featured images nếu cần;
- xác nhận search visibility trước khi launch.

Warnings should identify untouched starter objects using fingerprints/provenance where available.

The checklist must not claim legal/compliance approval or SEO readiness beyond the specific checks performed.

## 19. Component boundaries

Recommended implementation boundaries:

- `inc/theme/provisioning-recommendations.php` — pure recommendation calculation from discovery + blueprint + existing mappings/provenance;
- `inc/theme/provisioning-blueprints.php` — v1/v1.1 immutable starter definitions;
- `inc/theme/provisioning-starter-content.php` — starter Post definitions/fingerprints only;
- `inc/theme/provisioning-media.php` — starter asset manifest/import helpers;
- `inc/theme/provisioning-indexing.php` — WordPress-native `blog_public` planning/ownership marker/readiness helpers only;
- existing `provisioning-discovery.php` — read-only inventory/counts;
- existing `provisioning-plan.php` — explicit immutable operation model;
- existing `provisioning-runner.php` — bounded apply/rollback orchestration;
- existing `provisioning-readiness.php` — setup/launch diagnostics;
- existing `inc/admin/provisioning.php` — recommendation-first wizard UI.

Files may be adjusted during implementation if tests show a simpler boundary, but recommendation, media, starter-content and indexing concerns must not collapse into one monolithic admin file.

## 20. Dependency and ownership gates

### 20.1 SEO providers

v1.1 implementation must not create or modify another product/repository to obtain per-Post noindex.

If no accepted public SEO provider contract exists:

- active-site starter Posts are Draft;
- record that richer per-Post noindex publication is unavailable;
- continue all Theme-owned safe paths.

### 20.2 RootProfile / ConvertFlow / WooCommerce

No new dependency is required.

The provisioning system does not create identity data, Journeys, resolver state, commerce state or private provider data.

## 21. Proposed source ratification

Implementation must not silently rely on this chat/spec as a permanent architecture decision. After written-spec approval and before production code, ratify the change in the authoritative AZT source.

Proposed decision text for AZT-04 (final ID/version assigned during source task):

> **Law Site Provisioning may present non-authoritative smart recommendations from observed WordPress-native state, but every reuse/create/media/indexing choice remains explicit in the confirmed change plan. Starter Posts/media become WordPress-owned after provisioning. New/mostly-empty sites may couple published starter content with temporary WordPress-native whole-site noindex; active sites must never receive whole-site noindex for starter content and fall back to Draft absent an accepted public per-Post SEO contract.**

AZT-02 should document recommendation authority, starter-media/content ownership and index-safety boundaries. AZT-03 changes only when canonical baseline/provenance actually changes.

## 22. TDD / QA gates

### L0 — Source/state

- revalidate PR #58/#59/#60 and canonical `main`;
- ratify source decision before behavior implementation;
- preserve existing valid PASS unless invalidated.

### L1 — Static/ownership

Verify:

- no private SEO/plugin storage reads/writes;
- no slug/URL semantic mapping;
- packaged media manifest has explicit redistribution provenance;
- starter content contains no forbidden fabricated claims/current legal news;
- assets remain surface-aware;
- no hidden demo deletion path.

### L2 — RED → GREEN contracts

At minimum cover:

- site classification determinism;
- recommendation precedence;
- unique exact-label suggestion;
- ambiguity does not auto-select;
- current Content Map wins;
- v1 provenance upgrades without duplicates;
- existing content/featured images are preserved;
- starter Post created only when selected source lacks public coverage;
- no starter duplication on rerun;
- new-site starter Publish is coupled to confirmed `blog_public=0`;
- declining noindex makes starter Posts Draft;
- active-site setup never changes whole-site `blog_public`;
- absent SEO public contract forces active-site starter Posts Draft;
- media import/provenance/idempotency;
- media failure is fail-soft;
- rollback restores `blog_public` and other captured config when current run fails;
- starter fingerprints never trigger overwrite.

### L3 — WordPress runtime

Run at least these disposable scenarios:

1. empty site → recommended one-click setup → full structural + editorial + media setup, starter Posts public, `blog_public=0`;
2. empty site but user declines temporary noindex → starter Posts Draft;
3. existing active site with matching Categories + public Posts → reuse, no demo Post spam, no whole-site noindex;
4. existing active site with empty selected Category → starter Post Draft;
5. ambiguous same-label candidates → no silent mapping;
6. prior `law01-v1` site → upgrade/reuse without duplicates;
7. rerun after user edits starter Page/Post/image → preserve edits;
8. injected media/write failure → current-run rollback/fail-soft behavior as designed;
9. previously `blog_public=0` site → no ownership claim and no automatic re-enable.

### L4 — Browser/visual/a11y

For new-site recommended setup, prove at desktop/tablet/mobile:

- recommendation-first wizard is understandable and keyboard accessible;
- Step 3 clearly identifies Reuse/Create/Starter/Media/Search Visibility operations;
- explicit consent is required;
- resulting public Homepage has Hero image and populated Services/About/Team/Topics/Knowledge/Analysis/News/Process/FAQ/CTA surfaces when the user accepted the full recommended plan;
- no horizontal overflow;
- no empty placeholder cards;
- one logical H1;
- axe critical/serious blockers = 0;
- no Theme-caused browser/runtime errors;
- noindex state is observable when new-site starter Posts are public.

For existing active site, prove that no whole-site noindex is introduced.

### L5 — Integration

No integration PASS is implied for an SEO provider unless an accepted public contract exists and is tested separately.

### L6 — Candidate/release

- full retained regression;
- deterministic package build;
- exact installable ZIP integrity;
- package contains intended starter assets and no test/workflow-only junk;
- candidate approval does not imply `main` merge, tag, GitHub Release or production deployment.

## 23. Acceptance criteria

Law Site Provisioning v1.1 is implementation-complete only when fresh evidence proves all of the following:

1. A new/mostly-empty site can accept the recommended setup and obtain a coherent Law 01 Homepage with starter Hero media and populated editorial sections.
2. Starter public Posts on that new site are coupled to explicit temporary WordPress-native noindex.
3. Existing active sites are never whole-site noindexed merely for starter content.
4. Existing valid Page/Category mappings and public Posts are reused rather than duplicated.
5. Unique exact-label matches may be preselected only as clearly marked suggestions; ambiguous matches require user confirmation.
6. Missing structural/editorial sources are recommended for creation with visible plan items.
7. Starter Posts are created only where coverage is missing and never continuously synchronized.
8. Existing user content and featured images are not overwritten.
9. Starter media is imported into WordPress Media Library and becomes WordPress-owned presentation data.
10. v1 provenance upgrades/reruns are idempotent.
11. Current-run rollback protects prior state and never deletes reused content.
12. Readiness distinguishes setup completeness from launch/index readiness.
13. No private SEO/provider storage/API is used.
14. L1–L4 plus retained regression/package evidence are fresh for the exact implementation head before any completion claim.

## 24. Rollback / recovery

Implementation must remain a separate stacked branch until upstream dependencies are resolved.

Safe recovery paths:

- provisioning execution failure → current-run rollback/receipt;
- user dislikes presentation → change Homepage preset/mapping without deleting WordPress content;
- starter content is no longer wanted → user edits/unpublishes/deletes it through normal WordPress ownership, not Theme mass-delete logic;
- starter media is replaced → WordPress Featured Image/media workflow;
- temporary noindex was provisioner-owned → explicit user-confirmed restore only;
- upstream PR changes invalidate assumptions → rebase/revalidate before implementation continues.

## 25. Exact next after written-spec approval

1. invoke `writing-plans`;
2. make authoritative AZT source ratification Task 1;
3. create a stacked implementation branch from the latest valid premium/provisioning dependency state;
4. implement recommendation/index/content/media behavior via RED → GREEN;
5. generate/package project-owned starter imagery during the media task;
6. verify L3/L4 empty-site and existing-site safety;
7. build a fresh deterministic candidate ZIP;
8. stop before `main` merge/release/deploy unless explicitly approved.
