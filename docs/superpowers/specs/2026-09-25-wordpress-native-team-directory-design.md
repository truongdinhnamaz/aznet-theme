# AZnet Theme — WordPress-native Team Directory Design

**Date:** 2026-09-25  
**Status:** Written spec approved by product owner on 2026-09-25; amended by D-039 on 2026-09-25 for immediate Team member publication  
**Product:** AZnet Theme  
**Scope:** Law 01 Team authoring, Homepage Team presentation, Team directory Page presentation, and Law 01 provisioning  
**Baseline:** Current AZnet Theme work branch `work/homepage-map-v1-3-38-baseline`, including the separated Hero Library work through 1.3.44 candidate behavior.

## 1. Goal

Make the Law 01 Team experience editable and coherent without creating a Theme-owned personnel database.

The administrator must be able to manage real team members using ordinary WordPress content:

- upload/select one portrait;
- enter the person's name;
- enter a short role/title;
- see the same people in the Homepage Team section;
- open a public **“Xem tất cả”** Team Page that shows all published members;
- provision/reuse that Team Page from **Thiết lập nhanh** like the other core Law 01 Pages.

The Homepage admin and frontend must reflect the same WordPress-owned member set and order.

## 2. Existing architecture to preserve

The current Law 01 implementation already has the correct ownership shape:

- `homepage_team_page` / `homepage_law01_team_page` is a typed reference to one ordinary WordPress Page.
- Homepage Team currently reads direct published child Pages of that mapped Team Page.
- A child Page already has all required native fields:
  - `post_title` for member name;
  - `post_excerpt` for short role/title;
  - Featured Image for portrait;
  - `post_content` for optional long-form biography;
  - `menu_order` for ordering.
- Law 01 provisioning already knows a `team` Page role, but Team is not currently treated as a required Law 01 Page in the quick-setup flow.
- The current frontend may fall back to generated/reference portraits when there are no real member children. That fallback is no longer appropriate once Team is treated as real people.

This design extends those existing flows instead of introducing a new content model.

## 3. Decision

Use a **WordPress-native parent Page + direct child Pages** model.

### Parent Page

The mapped Law 01 Team Page is the public directory landing Page.

Default title for newly provisioned sites:

> **Đội ngũ của chúng tôi**

Existing sites keep their currently mapped Team Page and are not automatically duplicated or renamed.

### Member Page

Each team member is one direct child Page of the mapped Team Page.

Field mapping:

| Team concept | WordPress owner/source |
| --- | --- |
| Name | child Page `post_title` |
| Role / position / short specialty | child Page `post_excerpt` |
| Portrait | child Page Featured Image |
| Optional biography | child Page `post_content` |
| Public URL | child Page permalink |
| Display order | child Page `menu_order` |
| Publication | child Page `post_status` |

No Theme CPT, repeater, JSON blob, second Theme Mod store, custom personnel table, or private provider storage is introduced.

## 4. Ownership boundary

### WordPress owns

- Team parent Page;
- member child Pages;
- names;
- roles/titles;
- portraits/media;
- biographies;
- publication status;
- permalinks;
- child hierarchy and `menu_order`.

### AZnet Theme owns

- mapping the Team presentation slot to the parent Page;
- Homepage Team card layout;
- Team directory Page layout;
- bounded admin authoring UI over the native Page fields;
- CTA labels such as **“Xem tất cả”**;
- presentation limits such as four cards on the Homepage;
- fail-soft presentation.

### RootProfile boundary

This WordPress-native Team directory is editorial website content. It does **not** become authoritative RootProfile Person identity/profile truth.

If a future requirement needs RootProfile-backed Person identity or Profile semantics, that is a separate public-contract integration decision. This slice must not read RootProfile private storage, copy RootProfile identity semantics, or claim that a Team child Page is an authoritative Person entity.

## 5. Homepage Team presentation

The Law 01 Homepage Profile surface keeps About + Team as the existing composite visual section.

### Team data

Read:

```php
homepage_direct_published_children( $team_page_id, 4 )
```

The same resolver/order used by the frontend must be used by the Homepage admin preview.

### Member card

For each published direct child Page:

- portrait from Featured Image;
- name from Page title;
- role/title from Page excerpt;
- link to the child Page permalink.

If the excerpt is empty, omit the role line rather than inventing text.

If the portrait is missing, render a text-only card; do not substitute an AI/generated person portrait.

### Homepage limit

Show at most **4** published members on the Homepage.

The parent Team directory Page is the canonical place to see all published members.

### CTA

Replace the current Team CTA wording with:

> **Xem tất cả**

Destination: permalink of the mapped Team parent Page.

The CTA is shown whenever the mapped parent Page is publicly available, even if fewer than four members exist.

## 6. Remove misleading people fallback

The current Law 01 Profile implementation may render:

- one captioned parent featured image repeated into four portrait slots; or
- generated/reference portrait assets.

Those visuals must not be presented as member cards once this Team directory model is active.

Rules:

- real published child Pages are the only source of person cards;
- a Team parent Page Featured Image may remain ordinary editorial media, but it must not be duplicated into fake member cards;
- generated/reference portraits may not stand in for real team members;
- when there are no published members, keep the Team heading/summary/CTA if the parent Page is valid, but omit the member grid.

## 7. Homepage admin — Team authoring experience

The Team row in the Homepage Map must represent what the visitor sees, not expose a generic Page form as the primary mental model.

### Parent summary

Show:

- section label: **Đội ngũ**;
- status;
- mapped parent Page title;
- number of published member children;
- **Xem tất cả trên website** link to the parent Page.

The parent Page title/summary remains editable through the existing bounded Page quick-edit path.

### Visible member preview

Show exactly the same first four published child Pages that the frontend uses.

Each preview row/card shows:

- portrait thumbnail;
- member name;
- role/title;
- **Sửa** action.

### Edit member

Reuse the existing bounded WordPress-native Page quick-edit mechanics:

- title;
- excerpt;
- Featured Image.

The edit path must be authorized only when the target is a direct child of the currently mapped Team parent Page.

It must not expose arbitrary unrelated Pages.

### Add member

Add a clear **Thêm nhân sự** action inside the Team authoring area.

The action opens a bounded form requiring:

- name;
- role/title may be empty;
- portrait may be empty.

On submit:

- create an ordinary WordPress **published Page**;
- set `post_parent` to the mapped Team parent Page;
- save name to title;
- save role/title to excerpt;
- set Featured Image when supplied;
- append after existing siblings using `menu_order`;
- redirect back to the Team row.

No sample person, invented credentials, biography, phone, email, award, specialty, or avatar is generated.

The bounded **Thêm nhân sự** action publishes the validated child Page immediately so the new member can appear on Homepage/Team directory without a second WordPress publish step. Manual WordPress draft/private child Pages remain valid and stay excluded from public resolvers.

## 8. Public Team directory Page

When the current queried Page is the exact mapped Team parent Page, AZnet Theme may apply a Team-specific presentation because the mapping is an explicit Theme presentation reference.

Do not detect this Page by title, slug, URL text, or heuristic.

### Directory Page structure

Render:

1. normal Page heading;
2. Page excerpt/intro when present;
3. optional Page body content;
4. all published direct child member Pages;
5. member grid using the same card primitive as the Homepage;
6. no four-item Homepage limit.

Member order follows the same native child ordering contract used by the Homepage resolver.

Each card links to the member child Page.

If there are zero published members, render the parent Page normally and omit the member grid.

## 9. Member child Page presentation

A member child Page remains an ordinary WordPress Page and uses the existing standard Page presentation in this slice.

It may show the normal Featured Image, Page title and Page body according to the existing generic Page template.

No dedicated member-profile template or new person-specific data semantics are introduced in this slice.

## 10. Law 01 provisioning / Thiết lập nhanh

Team becomes a first-class required Page for the Law 01 recommended setup.

### Blueprint

For newly created Law 01 Team Page:

- role: `team`;
- default title: **Đội ngũ của chúng tôi**;
- neutral excerpt/body only;
- no child member Pages are created automatically.

The neutral body must explain that real team information should be added by the site owner. It must not fabricate people or professional claims.

### Recommendation / reuse

If the site already has a mapped Team Page:

- recommend reuse;
- do not create a duplicate;
- do not auto-rename the existing Page.

If no Team Page is confirmed:

- recommend/create the Team parent Page through the existing provisioning plan/apply flow.

### Required role

For Law 01 quick setup, `team` moves into the required Page set alongside the other core Law 01 Pages.

The user may choose an existing Page when appropriate, but a completed Law 01 recommended setup must resolve a Team parent Page.

### Mapping

After provisioning/reuse, the existing Theme source mapping must point Law 01 `team` to that exact Page.

No second Team mapping key is introduced.

## 11. Existing-site behavior and migration safety

Existing installations may already have:

- a mapped Team Page titled “Đội ngũ”;
- a parent Featured Image used as illustrative artwork;
- no child member Pages.

Rules:

- keep the existing mapped Page;
- do not create a second “Đội ngũ của chúng tôi” Page automatically;
- do not rename the existing Page automatically;
- do not delete the existing Featured Image;
- stop treating that image/reference art as four people;
- allow the administrator to add real child member Pages;
- the administrator may rename the parent Page through normal WordPress/quick-edit authoring if desired.

This avoids destructive migration and duplicate content.

## 12. Fail-soft behavior

- Invalid/missing Team mapping: Team region hides or follows the existing safe composition behavior; no heuristic Page search.
- Valid Team parent, zero members: show Team heading/summary/CTA, omit member grid.
- Draft/private member: do not render publicly.
- Member without image: no fake person image.
- Member without excerpt: omit role/title line.
- Member Featured Image deleted: card remains valid without portrait.
- Parent Page unpublished: do not expose it as the public “Xem tất cả” destination.
- Failed member creation: no partial Theme-owned data remains; WordPress error is surfaced.

## 13. Accessibility and responsive presentation

- Portraits use WordPress attachment alt text.
- Card names are proper headings at the correct hierarchy.
- Buttons/links have clear names: **Sửa**, **Thêm nhân sự**, **Xem tất cả**.
- Media controls remain keyboard-accessible using the existing WordPress media workflow.
- Team grids collapse responsively without horizontal overflow.
- Missing images must not remove the accessible member name/role.
- Status must not rely on color alone.

## 14. TDD / QA gates

Implementation follows RED → GREEN.

### L1 — Static/ownership contracts

Reject:

- new Team CPT;
- new personnel repeater/meta store owned by Theme;
- generated sample people;
- title/slug heuristics for Team Page detection;
- private RootProfile reads;
- AI/reference portraits rendered as actual members.

### L2 — WordPress-native behavior

Tests prove:

- a mapped Team parent resolves;
- only direct published child Pages enter public member results;
- Homepage result is bounded to 4;
- order is stable and shared between admin/frontend;
- title/excerpt/thumbnail map to name/role/portrait;
- draft member does not render;
- add-member action creates a published direct child;
- quick edit rejects non-child Page IDs.

### L3 — Provisioning runtime

Tests prove:

- Law 01 blueprint contains Team Page titled **Đội ngũ của chúng tôi** for new creation;
- Team is required in Law 01 recommended setup;
- existing mapped Team Page is reused rather than duplicated;
- no child member Page is provisioned automatically;
- Theme mapping points to the created/reused parent Page.

### L4 — Browser / visual / a11y

Verify:

- Homepage shows no more than 4 real members;
- admin Team preview matches Homepage member order/data;
- **Xem tất cả** reaches Team parent Page;
- Team parent Page shows all published children;
- portrait/name/role render correctly;
- no generated/reference people appear when there are no real members;
- narrow viewport and keyboard authoring remain usable;
- no new serious/critical accessibility issues.

### L5 — Integration

No new external provider contract is introduced.

Retain RootProfile/ConvertFlow/WooCommerce coexistence regressions where the surrounding Homepage already participates.

### L6 — Release

Version bump/package/deployment remain separate release gates after implementation and fresh verification.

## 15. Acceptance criteria

The feature is complete only when all are true:

1. The mapped Team parent is ordinary WordPress Page content.
2. Each member is one direct child Page; no parallel Theme personnel store exists.
3. Admin can enter/edit member name, role/title and portrait through bounded controls.
4. Add Member creates a published child Page under the exact mapped Team parent.
5. Homepage shows the same first four published members/order as the admin preview.
6. Homepage Team cards show portrait, name and role when those native fields exist.
7. No fake/reference/AI portrait is presented as a team member.
8. Homepage **Xem tất cả** links to the mapped Team parent Page.
9. The Team parent Page displays every published direct child member.
10. Law 01 Thiết lập nhanh treats Team as a required core Page and can create/reuse it.
11. New provisioning creates no sample people.
12. Existing mapped Team Pages are not duplicated, auto-renamed or destructively migrated.
13. No RootProfile private storage or identity semantics are copied into Theme.
14. L1-L4 are freshly verified before release; L5 retained regressions remain green where applicable.

## 16. Non-goals

This design does not authorize:

- employee HR records;
- login/user-account linkage;
- RootProfile Person creation or synchronization;
- qualifications/certifications/claims schema;
- phone/email/social fields for each person;
- custom Team CPT;
- arbitrary drag-and-drop builder;
- automatic scraping/import of staff;
- AI-generated people;
- destructive conversion of existing Team content.

Those require separate owner/source and UX decisions if needed later.

## 17. Source-governance impact

After written-spec approval, authoritative source governance must be updated before implementation:

- **AZT-02**: ratify WordPress-native parent/child Team content ownership and Theme presentation-only behavior.
- **AZT-04**: record the accepted Team Directory + provisioning decision and QA gates.
- **AZT-EXEC-MAP**: add the implementation slices.
- **SOURCE_MANIFEST**: register the approved spec/plan as derived execution artifacts.

Until written-spec approval, this document is a proposed implementation design and does not override those authoritative sources.
