# Law 01 Live Checkpoint — 18/09/2026

## Scope

Durable evidence for the Tâm Đức Hà Nội Law 01 pilot after owner-approved post-release Theme-file publication, Quick Setup rerun verification, bounded WordPress-owned content classification and indexability/SEO readiness work. This is not a new Theme release and does not claim byte identity to the published `v1.3.0` package.

## Canonical repository state

- Canonical AZnet Theme `main`: `371e09035a23664cf38f52f70aa3189e5c10f2cf` after PR #111.
- PR #110 merge: `45073221ba1f243cc95692c002ddfb34ede7a1d6`; exact head `d5e58c4941bec144f05ec8de6a675eb86ebff74f`.
- PR #111 merge: `371e09035a23664cf38f52f70aa3189e5c10f2cf`; exact head `e9afe7a36b4f5d4f0a0f5f6e8c5e8f6ca4765648`.
- PR #109 remains draft/open/unmerged; it is not canonical state.

## Live Theme provenance

- Target: `https://tamduchanoi.aznet.vn`.
- Fresh authenticated site metadata: WordPress `7.1.1`, PHP `8.4.25`, active AZnet Theme `1.3.0`.
- The owner previewed and approved WPVibe draft publication after confirming backup availability. WPVibe published the draft to `aznet-theme` and retained the prior live Theme directory as `aznet-theme-wpvibe-backup`.
- The live Theme includes post-release local Theme-file changes. The Quick Setup fix was reproduced behaviorally in the WPVibe draft; byte-for-byte identity with canonical GitHub main or the published `v1.3.0` release asset was not established. Runtime evidence therefore proves behavior, not package identity.

## Quick Setup rerun / idempotency

- Logged-in admin evidence reached Quick Setup Step 4/4.
- Status: `READY_WITH_WARNINGS`.
- Result: `Created: 0 · Reused: 36`.
- Fresh read-only duplicate query for `law01-v1-2` starter Post/media provenance roles returned zero duplicate roles.
- `READY_WITH_WARNINGS` reflected content-launch checklist items, not a provisioning failure.

## WordPress-owned content / homepage state

- Five existing published Posts were additionally classified into the existing Law 01 child categories without removing their prior category assignments.
- Homepage `Mới cập nhật` then rendered all five real Posts.
- About Page #34 received a bounded excerpt describing only service areas already represented by the site.
- No fictional team member, trust statistic, certification or identity data was created.

## Indexability / SEO readiness

- WordPress `blog_public` was changed from `0` to `1` only after explicit owner approval to open indexing.
- Rank Math homepage meta description was added through the installed SEO provider.
- Fresh mobile Lighthouse after the change: Performance `98/100`, Accessibility `100/100`, Best Practices `100/100`, SEO `100/100`; LCP about `1.6s`, CLS `0`, TBT `0ms`.
- CrUX field data was unavailable; these are lab measurements.

## RootProfile Team blocker

- Public RootProfile routes available on the pilot include person-by-ID, organization and readiness reads.
- No public/versioned collection/projection exists for organization team membership.
- Fresh `GET /rootprofile/v1/organization` returns `404 rootprofile_not_found` / `Organization profile not found.`
- Theme issue #112 records the external dependency: `BLOCKED: Law 01 team section needs authoritative RootProfile projection`.
- Theme must not substitute WordPress user/author enumeration, post/slug/URL/title heuristics, private RootProfile storage reads or a parallel team store.

## Exit / Next

Law 01 live presentation, rerun behavior, indexability and Lighthouse SEO are PASS at the exact tested scope. Team authoritative rendering remains BLOCKED_EXTERNAL_CONTRACT at issue #112. No further Theme implementation is opened by this checkpoint until the RootProfile owner supplies a suitable public/versioned projection or another separately approved roadmap slice is opened.
