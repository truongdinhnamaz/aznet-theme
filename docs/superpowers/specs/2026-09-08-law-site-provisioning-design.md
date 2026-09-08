# AZnet Theme — Law Site Provisioning Design

**Date:** 2026-09-08  
**Status:** Design approved in chat; written-spec review required before implementation  
**Stacked base:** `work/homepage-composer-law01@e7f3117948e00efb89430aec14f758bcac8a7081`  
**Upstream implementation dependency:** Homepage Composer + Law 01 (PR #58, draft/open/unmerged at spec creation)  
**Product:** AZnet Theme  
**Scope:** Law Site Provisioning v1 — safe setup wizard for new and existing WordPress sites

## 1. Problem

Homepage Composer + Law 01 can render a professional legal-services Homepage from mapped WordPress Pages and Categories, but a newly activated site may not yet have the required WordPress structure. A user who activates AZnet Theme should be able to reach a coherent, editable Law 01 website quickly without manually creating every Page, Category, menu, Front Page assignment and Content Map reference.

The setup experience must also be safe on existing sites. It must not overwrite existing content, guess semantic ownership from slugs/titles, create duplicate Pages on repeated runs, or make Theme switching destructive.

The desired product experience is:

> Activate AZnet Theme → choose “Thiết lập website nhanh” → review an explicit change plan → provision or map the missing WordPress-native structure → open a Law 01 Homepage that already looks coherent and is ready for real content editing.

## 2. Decision

Implement **Law Site Provisioning v1** as an explicit, idempotent, WordPress-native setup subsystem separate from Homepage template switching.

The design has five core principles:

1. **Activation never auto-provisions.** Theme activation may show an invitation, but database mutation starts only after the user explicitly launches and confirms the setup wizard.
2. **Provisioning is separate from presentation preset application.** Selecting Law 01 later changes presentation state only; it never recreates or rewrites content.
3. **Existing-site safety is the default.** Existing Pages/Categories are reused only after explicit user mapping; no semantic slug/title/URL inference is authoritative.
4. **Provisioned content becomes WordPress-owned content.** The Theme may create starter objects through public WordPress APIs, but those objects remain ordinary WordPress Pages/Categories/menus/content and survive Theme switching.
5. **Every mutation is planned, bounded, provenance-aware and recoverable within the current run.** No blind XML import and no destructive “reset demo site” action.

## 3. Relationship to Homepage Composer

Law Site Provisioning depends on the approved Homepage Composer concepts but does not replace them.

### Homepage Composer owns

- `homepage_preset` presentation selection;
- stable Content Map references;
- Law 01 rendering;
- runtime validation/fail-soft behavior;
- Control Center diagnostics.

### Provisioning owns

- activation invitation;
- site discovery/preflight;
- explicit setup plan;
- creation of missing WordPress-native starter objects when the user chooses “create”;
- mapping/reuse decisions chosen by the user;
- initial menu/Front Page/setup wiring;
- starter copy seeding;
- provisioning provenance/receipt;
- readiness assessment after setup.

### Provisioning does not own

- ongoing Page/Post/Category content semantics;
- legal truth or legal advice;
- RootProfile Person/Organization identity;
- ConvertFlow Journey/resolver/conversion semantics;
- WooCommerce state;
- future editorial synchronization.

## 4. Ownership boundary

The invariant remains:

> SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.

WordPress remains authoritative for:

- Page/Post object identity and body content;
- Page hierarchy;
- taxonomy terms and membership;
- publication state;
- menus;
- static Front Page selection;
- media;
- native query semantics.

AZnet Theme may perform an explicit setup operation that creates WordPress-native objects through public APIs because setup is a bounded bootstrap action, not a parallel domain store. After successful creation, the Theme must treat those objects as ordinary WordPress content.

The Theme must not copy Page/Post bodies into Theme settings, create a private Homepage content store, create Theme-owned legal-service CPTs, or continue syncing starter copy after the user edits content.

## 5. Activation behavior

Theme activation must remain safe and reversible.

On activation:

- do not create Pages;
- do not create Categories;
- do not create Posts;
- do not change Front Page selection;
- do not change menus;
- do not enable Law 01 automatically;
- do not overwrite Theme settings beyond existing activation-safe behavior.

The Theme may show a dismissible admin invitation:

**“Thiết lập website nhanh”**

The invitation links to the Provisioning Wizard. Dismissing it has no data effect. The wizard is also reachable later from the AZnet Theme Control Center.

## 6. Supported site modes

The wizard supports both new and existing sites.

### 6.1 New or mostly empty site

The wizard may propose creating the complete Law 01 starter structure, including Front Page, Pages, child Pages, Categories, menu and Content Map mappings.

### 6.2 Existing site

The wizard first inventories the current WordPress-native objects and then requires explicit mapping/reuse decisions.

Existing-site rules:

- existing content is never overwritten merely because a title resembles a starter role;
- the wizard may present Pages/Categories as selectable candidates by object type, but it does not auto-assert semantic equivalence from title/slug;
- the default behavior is **reuse/map where the user explicitly chooses an existing object; create only where the user explicitly chooses create**;
- existing object title, slug, body, hierarchy, taxonomy membership and publication status remain unchanged unless that exact change is explicitly shown in the plan and confirmed;
- existing primary menu remains untouched unless the user explicitly chooses to create/replace the intended menu location.

## 7. Wizard flow

Law Site Provisioning v1 uses a four-step wizard.

### Step 1 — Kiểm tra website

Read-only discovery/preflight.

Show at minimum:

- whether a static Front Page is configured;
- number of Pages;
- number of Categories;
- number of Posts;
- whether Homepage Composer settings already exist;
- whether Law 01 is already active;
- current mapped Homepage slots and their diagnostics where available;
- whether a primary menu is assigned;
- whether a prior Law Site Provisioning receipt/provenance record exists.

This step does not mutate content.

### Step 2 — Chọn cấu trúc Law 01

For every required or optional starter role, the user chooses one of the allowed actions:

- **Reuse existing** — select an existing WordPress object of the correct type;
- **Create new** — provision the starter object;
- **Skip** — only for optional roles.

Required Setup Ready roles cannot be skipped unless an equivalent valid source is mapped.

The wizard must not expose a free-form slug/URL/manual-ID field as the primary setup path.

### Step 3 — Xem trước thay đổi

Before any write, generate an explicit immutable-in-run change plan.

Example:

```text
REUSE  Page #42 “Giới thiệu”               → about
CREATE Page “Dịch vụ pháp lý”              → services
CREATE 6 direct child service Pages
REUSE  Category #17 “Dân sự”               → knowledge topic
CREATE Category “Phân tích vụ việc”        → case_analysis
CREATE Primary Menu “AZnet Primary”
SET    Front Page                           → new Home Page
MAP    homepage_about_page                  → #42
SET    homepage_preset                      → law-01
```

The plan must distinguish:

- read-only reuse;
- create;
- update Theme presentation/reference settings;
- WordPress configuration changes such as Front Page/menu assignment;
- publication state of newly created starter objects.

No hidden write is allowed outside the displayed plan, except internal run bookkeeping needed for rollback/provenance.

### Step 4 — Áp dụng + kiểm tra

Apply the confirmed plan using public WordPress APIs and the existing normalized Theme settings write path.

After mutation, re-read the resulting state and verify:

- required references resolve to the intended WordPress object types;
- configured Front Page exists and is public when required;
- Law 01 is active when chosen;
- Primary Menu assignment is valid when created/selected;
- no duplicate provisioning role was created in the same or previous successful run;
- Homepage Composer diagnostics are consistent with the new mapping.

Show a final setup summary and handoff actions.

## 8. Starter blueprint v1

The first provisioning blueprint is `law01-v1`.

### 8.1 Pages

Create only when the user chooses “Create new”.

Required/standard structure:

- Trang chủ
- Giới thiệu
- Dịch vụ pháp lý
  - Luật Doanh nghiệp
  - Dân sự & Tranh chấp
  - Hình sự
  - Đất đai & Bất động sản
  - Hôn nhân & Gia đình
  - Lao động
- Đội ngũ luật sư
- Quy trình tư vấn
- Câu hỏi thường gặp
- Liên hệ

All are ordinary WordPress `page` objects.

### 8.2 Categories

Starter taxonomy structure:

- Kiến thức pháp luật
  - Doanh nghiệp
  - Dân sự
  - Hình sự
  - Đất đai
  - Hôn nhân & Gia đình
  - Lao động
- Phân tích vụ việc
- Tin pháp luật

All are ordinary WordPress `category` terms.

The Theme does not infer correspondence between a service Page and a knowledge Category merely because labels are similar. Any future cross-linking is explicit configuration/content, not title matching.

### 8.3 Menu

On a new site, the wizard may create a primary menu containing a bounded starter navigation such as:

- Trang chủ
- Giới thiệu
- Dịch vụ pháp lý
- Đội ngũ luật sư
- Kiến thức pháp luật
- Liên hệ

On an existing site, creating or assigning a new Primary Menu requires explicit confirmation in the change plan. Existing menu content must not be overwritten silently.

### 8.4 Front Page

If the user creates a new Home Page and confirms the plan, the wizard may set:

- `show_on_front=page`;
- `page_on_front=<created or explicitly selected home page ID>`.

If an existing site already has a Front Page, replacing it requires an explicit plan item and user confirmation.

## 9. Hybrid publication policy

Use a hybrid publication strategy.

### Publish by default when newly created

- Trang chủ;
- Giới thiệu;
- Dịch vụ pháp lý;
- direct service child Pages;
- Đội ngũ luật sư;
- Quy trình tư vấn;
- Câu hỏi thường gặp;
- Liên hệ.

These Pages use neutral starter copy that is safe as structural website copy but still requires client review before launch.

### Draft by default

Any starter editorial Posts used to demonstrate content structure, including:

- legal knowledge examples;
- case-analysis examples;
- legal-news examples.

Draft examples must never appear as current public legal information until a human reviews and publishes them.

The blueprint may also choose to create zero demo Posts in v1 if implementation/testing demonstrates that draft examples do not materially improve setup value. The architecture does not depend on demo Posts existing.

## 10. Starter copy rules

Starter copy must be complete enough that newly provisioned structural Pages do not look broken, but it must remain neutral, editable and non-authoritative.

### Allowed starter copy

- general positioning copy;
- service-introduction copy at a high level;
- generic workflow descriptions;
- generic instructions for preparing documents before contacting the firm;
- generic contact CTA wording;
- generic FAQ about how consultation intake works;
- editable headings/placeholders that clearly request real organization-specific information.

### Forbidden starter copy

- fake firm names presented as real;
- fake lawyer names/person profiles;
- fake address, phone or email;
- years of experience;
- client counts;
- case counts;
- success/win rates;
- rankings/certifications/awards;
- testimonials;
- claims such as “top law firm” or “leading lawyer” without an owner-backed source;
- substantive legal advice tied to statutes, deadlines or current law;
- invented case results;
- invented current legal news.

### Example safe Home Page starter copy

Heading:

**Giải pháp pháp lý rõ ràng cho cá nhân và doanh nghiệp**

Supporting copy:

“Chúng tôi hỗ trợ khách hàng tiếp cận vấn đề pháp lý theo hướng rõ ràng, thực tiễn và phù hợp với từng hoàn cảnh cụ thể.”

The copy is presentation/editorial starter material, not a factual credential claim.

## 11. FAQ starter structure

The starter FAQ Page may contain 5–6 generic consultation-process questions, for example:

- Tôi cần chuẩn bị gì trước khi tư vấn?
- Quy trình tiếp nhận yêu cầu diễn ra thế nào?
- Chi phí tư vấn được xác định ra sao?
- Có thể gửi hồ sơ trước khi đặt lịch không?
- Tôi nên cung cấp những thông tin nào khi liên hệ?

Answers must remain process-oriented and generic. Do not promise a fixed fee, response time, confidentiality regime or legal outcome unless the site's real owner/source later supplies those facts.

Where practical, starter FAQ content should use ordinary WordPress-authored HTML structures that Homepage Composer can style without creating a Theme FAQ datastore.

## 12. Process starter structure

The starter Process Page may use four editable steps:

1. Tiếp nhận yêu cầu
2. Đánh giá vấn đề
3. Đề xuất phương án
4. Đồng hành thực hiện

Descriptions must be clearly generic and editable. The Theme must not later treat this starter wording as authoritative business workflow state.

## 13. Idempotency

Provisioning must be safe to run repeatedly.

A successful rerun must not create duplicates such as:

- `Giới thiệu (2)`;
- `Liên hệ (2)`;
- a second “Phân tích vụ việc” term;
- duplicate child service Pages;
- duplicate provisioning-owned menu items merely because the wizard ran again.

Idempotency is based on **provenance markers for objects that the provisioning subsystem itself created**, plus the current explicit Content Map — not semantic title/slug matching.

Rules:

- if a previously provisioned object still exists, the wizard can recognize it as its own prior output;
- if the object was deleted, the wizard may offer to recreate that role;
- if the Content Map was remapped to a different object, the explicit current mapping wins;
- if an existing unmarked object has the same title as a starter role, the wizard must not silently assume ownership or reuse it;
- reruns never overwrite user-modified content merely to “sync the template”.

## 14. Provisioning provenance

Objects created by the wizard may receive bounded internal provenance metadata.

Example Page metadata:

```text
_aznet_theme_provisioned_by = law01-v1
_aznet_theme_provisioning_role = about
_aznet_theme_provisioning_run = <opaque run id>
```

Equivalent bounded metadata may be stored for supported WordPress objects/terms where public APIs permit it.

Provenance answers only:

> “Was this object created by this AZnet provisioning blueprint, and for which provisioning role?”

It does **not** become authoritative business semantics. Current Content Map references remain authoritative for Homepage presentation source selection.

Provenance must not be used to infer Person identity, organization identity, legal expertise, routing or provider ownership.

## 15. User modification protection

After successful provisioning, WordPress content is user-owned/native content.

The Theme must not continuously sync starter copy.

For future migrations, a starter object may be considered eligible for an automatic starter-copy update only if the system can prove it remains untouched since creation. If that proof is not robust and inexpensive in v1, the safe rule is simpler:

> v1 never auto-updates starter copy after successful provisioning.

This is the recommended v1 rule.

## 16. Transaction and rollback model

Provisioning is a bounded multi-write operation and must keep a run receipt.

### 16.1 Before writes

Capture the relevant pre-run state needed for rollback, including at minimum:

- normalized Homepage Theme settings affected by the plan;
- current `show_on_front` / `page_on_front` values if the plan changes them;
- current primary menu assignment if the plan changes it;
- list of objects/terms/menu artifacts that do not yet exist and will be created by this run.

### 16.2 During the run

Record exact IDs of objects created by this run.

Do not treat pre-existing/reused objects as rollback-deletable.

### 16.3 Failure before successful handoff

If a confirmed provisioning run fails before completion:

- restore Theme settings modified by the run;
- restore Front Page/menu assignments modified by the run where safe;
- remove only objects demonstrably created by this failed run and not adopted by any successful prior run;
- never delete reused/pre-existing objects;
- record failure diagnostics.

### 16.4 After success

The run receipt becomes historical provenance/evidence, not a future delete command.

The public admin interface must not expose a “Delete all demo content” or “Reset site” destructive action.

After success, normal rollback is presentation/configuration oriented:

- set `homepage_preset=off`;
- remap Homepage sources;
- restore a previous Theme package if needed.

Provisioned WordPress content remains in WordPress unless the user explicitly deletes it through normal WordPress content management.

## 17. Change-plan integrity

Once Step 3 is confirmed, the apply operation should execute against the exact confirmed plan.

If material state changes between plan generation and apply — for example, a selected Page was deleted or the Front Page changed — the wizard must stop and require a new preflight/plan rather than silently adapting.

This prevents TOCTOU-style setup surprises and makes rollback/evidence reliable.

## 18. Readiness model

Provisioning success does not mean the legal website is ready for production launch.

Use two readiness concepts.

### 18.1 Setup Ready

The website is structurally operational when at minimum:

- a valid static Front Page exists;
- Law 01 is selected;
- required Homepage references resolve;
- Services source resolves;
- About source resolves;
- Contact Page source resolves;
- Primary Menu exists/is assigned when part of the selected setup path;
- Homepage renders without a provisioning/runtime error.

Required Setup Ready roles:

- Front Page / Hero source;
- Services;
- About;
- Contact;
- Primary Menu;
- Law 01 preset.

### 18.2 Optional sections

These may fail soft without making setup fail:

- Team;
- Knowledge topics/latest;
- Case analysis;
- Legal news;
- Process;
- FAQ.

### 18.3 Launch Ready

Launch Ready is a stronger editorial/business readiness concept. v1 must not silently claim Launch Ready merely because provisioning succeeded.

The final wizard may show **READY WITH WARNINGS** until real organization/site information is reviewed.

Typical warnings:

- add/confirm real logo;
- confirm organization/contact information;
- replace generic starter copy;
- add real photography;
- review navigation;
- review legal/editorial content before publishing draft Posts;
- verify provider-backed profile/contact data where applicable.

The wording should communicate:

**“Thiết lập hoàn tất — Website đã sẵn sàng để chỉnh nội dung.”**

not:

**“Website đã sẵn sàng phát hành.”**

## 19. Final wizard handoff

After a successful run, show three primary actions:

1. **Xem trang chủ** — open the public Front Page.
2. **Chỉnh nội dung cần thiết** — open a checklist/dashboard of starter Pages and launch warnings.
3. **Quản lý Trang chủ** — open Control Center → Trang chủ for Content Map/preset diagnostics.

The handoff page also shows a compact summary of objects created vs reused and the resulting Setup Ready status.

## 20. Admin architecture

Provisioning should extend the existing AZnet Theme admin system rather than create a separate admin application.

Suggested focused modules for implementation planning:

- `inc/admin/provisioning.php` — wizard routing/rendering/authorization;
- `inc/theme/provisioning-blueprints.php` — immutable Law 01 blueprint definitions and safe starter copy;
- `inc/theme/provisioning-discovery.php` — read-only site inventory and typed candidate lists;
- `inc/theme/provisioning-plan.php` — validated change-plan model;
- `inc/theme/provisioning-runner.php` — bounded public-API apply/rollback orchestration;
- `inc/theme/provisioning-provenance.php` — internal provisioning markers/receipts;
- existing `inc/theme/settings.php` / Homepage Content Map — final mapping persistence;
- existing Control Center — entry point and post-run diagnostics.

Exact filenames may be adjusted during implementation planning to match current repository boundaries, but responsibilities must remain separated.

## 21. Security and authorization

The wizard performs destructive-capable writes and therefore requires normal WordPress admin security controls.

At minimum:

- appropriate capability check (administrator-level Theme/site configuration capability, normally `manage_options` or the project's accepted equivalent);
- nonces for state-changing requests;
- strict allow-list validation of blueprint keys/actions/object IDs;
- no arbitrary class/function/file execution from request input;
- no free-form SQL;
- public WordPress APIs for Page/term/menu/configuration writes;
- escaped/sanitized admin output/input;
- no secrets in run receipts/provenance.

## 22. Failure handling

Failure states must be explicit and recoverable.

Examples:

- selected existing Page was deleted before apply → abort, no heuristic substitute;
- term creation fails after several Pages were created → rollback only current-run creations/settings where safe;
- Front Page assignment cannot be verified → Setup Ready fails and the wizard reports the exact issue;
- Homepage mapping validation returns INVALID → do not claim successful setup;
- optional section source is missing → successful setup may continue with warning/fail-soft section;
- provenance metadata cannot be written for a created object → treat idempotency safety as compromised; either fail that creation/run or use a documented alternative marker before success.

No partial success may be presented as full success without listing remaining incomplete items.

## 23. Performance constraints

Provisioning is an admin-only, infrequent operation, but it must remain bounded.

- discovery may count/query only the object types needed by the wizard;
- do not scan arbitrary post meta or entire content bodies searching for semantic matches;
- do not crawl the site;
- do not load provider ecosystems globally;
- starter object counts are fixed/bounded by the blueprint;
- runtime Homepage performance remains governed by Homepage Composer, not provisioning.

## 24. Accessibility and UX

Wizard UI requirements:

- keyboard usable;
- semantic headings/step navigation;
- visible focus states;
- error summary associated with invalid fields;
- actions described by effect (`Reuse`, `Create`, `Skip`), not ambiguous icons alone;
- Step 3 change plan readable without color as the only status cue;
- destructive implications, especially Front Page/menu replacement, clearly labeled;
- final readiness warnings actionable and linked to relevant edit screens where practical.

## 25. QA model

Implementation must be verified in layers.

### L0 — Source/State

Before production code, ratify the accepted provisioning architecture/roadmap in the authoritative AZT source documents. This spec itself does not supersede AZT-02/AZT-04.

### L1 — Static

Verify:

- no provider-private access;
- no semantic slug/title auto-mapping;
- no XML/demo-import dependency introduced silently;
- no destructive reset action;
- security gates/nonces/capability checks present;
- Classic Editor policy remains unchanged.

### L2 — Contract/TDD

RED→GREEN tests for:

- blueprint shape;
- discovery read-only behavior;
- change-plan generation;
- existing-site no-overwrite behavior;
- idempotent rerun;
- provenance precedence vs explicit Content Map;
- hybrid publish/draft policy;
- starter-copy forbidden-claim scan;
- rollback of a mid-run injected failure;
- no auto-provision on Theme activation;
- post-success rerun does not duplicate objects;
- explicit mapping wins over old provenance.

### L3 — Real WordPress runtime

Disposable WordPress 6.9+/PHP 8.1+ scenarios:

1. empty site → full Law 01 setup;
2. existing site with Pages/Categories → reuse/map without overwriting;
3. existing Front Page/menu → explicit change-plan behavior;
4. rerun after success → zero duplicate starter roles;
5. injected mid-run failure → bounded rollback;
6. user edits a provisioned Page → rerun preserves user content.

### L4 — Browser/A11y

Verify wizard and resulting Homepage:

- desktop/tablet/mobile admin usability where WordPress admin supports it;
- keyboard/focus;
- no browser console errors caused by Theme;
- final Law 01 public Homepage still passes retained L4 checks;
- setup completion links navigate correctly.

### L5 — Integration

Only claim provider integration where a fresh public-contract test exists. Provisioning v1 itself must work without RootProfile/ConvertFlow/Woo.

### L6 — Completion/Release

Main merge, release package and deployment remain owner approval gates.

## 26. Non-goals for v1

- generic multi-industry provisioning engine;
- arbitrary XML/WXR demo importer;
- full visual page builder;
- one-click destructive reset;
- automatic synchronization of starter copy after setup;
- semantic auto-detection from slugs/titles;
- RootProfile Person/team creation;
- ConvertFlow Journey creation;
- WooCommerce product/catalog setup;
- SEO plugin configuration;
- real legal-content generation or legal-advice publishing;
- automatic launch/deploy.

The internal architecture should allow future blueprints, but v1 implements only the Law 01 setup path required by the approved product goal.

## 27. Acceptance criteria

Law Site Provisioning v1 is acceptable only when all of the following are true:

1. Theme activation alone creates no WordPress content.
2. The user explicitly launches and confirms the wizard before writes.
3. Empty-site setup can create the approved Law 01 WordPress-native structure and map it to Homepage Composer.
4. Existing-site setup can reuse explicitly selected objects without overwriting their content.
5. The exact pre-write change plan is visible before apply.
6. Re-running after success creates no duplicate provisioning roles.
7. No slug/title/URL heuristic silently selects authoritative semantic sources.
8. Structural starter Pages use neutral copy and approved hybrid publication policy.
9. Demo/editorial Posts, if created, are Draft by default.
10. No fake lawyer/person, contact data, credential, metric, testimonial, result or current legal-news claim is published by the Theme.
11. A mid-run failure can restore the affected Theme/Front Page/menu state and remove only current-run creations where safe.
12. After success, there is no destructive “delete all demo content” feature.
13. User-edited provisioned content is preserved on future runs.
14. Setup Ready and Launch Ready are not conflated.
15. Final handoff exposes `Xem trang chủ`, `Chỉnh nội dung cần thiết`, and `Quản lý Trang chủ`.
16. Homepage Composer/Classic Editor/ownership regressions remain green.
17. Main merge/release/deploy remain explicit owner gates.

## 28. Rollback

During an unsuccessful provisioning run, rollback is transaction-scoped and may remove only objects created by that failed run while restoring captured settings/Front Page/menu state where safe.

After a successful provisioning handoff, no automatic content rollback/delete is provided. The supported presentation rollback is `homepage_preset=off` or a different mapping/preset. Package rollback is reinstalling the previous known-good Theme package. WordPress-native content persists.

## 29. Source-governance requirement before code

This feature introduces an accepted setup/provisioning capability and therefore must be ratified in the authoritative source before production implementation.

At implementation-plan time, the first gate must update the appropriate AZT sources to establish at minimum:

- Theme may own an explicit bounded provisioning/setup workflow that creates WordPress-native starter objects through public APIs;
- this bootstrap capability does not transfer ongoing WordPress content ownership to Theme;
- provisioning and preset switching are separate operations;
- idempotency/provenance/rollback constraints;
- existing-site no-overwrite requirement;
- release/QA gates for the new subsystem.

No production provisioning code should be merged ahead of that source gate.

## 30. Exact next after written-spec approval

After the user reviews and approves this written spec:

1. invoke the writing-plans workflow;
2. create a detailed implementation plan with source ratification as Task 1;
3. execute on an isolated work/feature branch with RED→GREEN tests;
4. verify empty-site, existing-site, rerun and rollback runtime scenarios;
5. retain PR/release/deploy approval gates;
6. only then produce the next downloadable Theme candidate.
