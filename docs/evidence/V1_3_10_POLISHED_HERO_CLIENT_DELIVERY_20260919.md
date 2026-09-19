# AZnet Theme 1.3.10 — Polished Law 01 Hero Client Delivery Candidate

**Date:** 19/09/2026
**Repository:** `truongdinhnamaz/aznet-theme`
**Canonical base:** `main@597639e26a2cb242c3c0a71e5459ab5d66a0701d`
**PR:** #150
**Verified production head before source-only synchronization:** `a4c3621ec6d651b7e4c4d1085a2b900f8c94454b`

## Purpose

The owner approved one additional presentation polish pass after the seamless-Hero correction: improve the Hero title rhythm and soften the visual transition between the cream text surface and the Hero image so the image edge no longer reads as a hard rectangular seam.

The shared 96rem content shell, the approved desktop 48/52 composition and the Theme/provider ownership boundary remain unchanged.

Because a 1.3.9 package identity had already been generated from the still-unmerged PR #150 candidate before this extra polish was approved, the improved bytes are promoted to **1.3.10**. The 1.3.9 package remains superseded/unmerged provenance and is not reused.

## Visual RED -> GREEN

Visual RED head: `f95fba80020db53c126c41fe52f6fa8020c516eb`.

The retained Core failed for the intended new requirement:

`Law 01 Hero title must use the approved compact editorial typography instead of a blocky default heading.`

Minimal production polish at `bd811fec2619e3a28806540d1b86affa20c36dd6`:

- Hero title uses a tighter editorial rhythm: constrained measure, compact line-height, negative tracking and balanced wrapping;
- Hero image visual becomes an isolated positioning context;
- a decorative cream-to-transparent gradient overlays the image's left edge to soften the transition from the text surface;
- the transition layer is non-interactive;
- the image receives a subtle 1.02 crop scale to avoid reading as a pasted rectangular panel.

The browser contract verifies the rendered transition pseudo-element, its non-interactive behavior and compact computed title line-height. The existing desktop/mobile edge-bleed and no-horizontal-overflow checks remain retained.

## Package RED -> GREEN

Release RED head: `0070f8103ee6b508e2b637983ab1e20b3fd76695`.

The release contract failed for the intended reason while production metadata still declared 1.3.9:

`Y5 promoted style.css must be exactly 1.3.10`

Minimal release GREEN changed only:

- `style.css`: `Version: 1.3.10`
- `functions.php`: `AZNET_THEME_VERSION = 1.3.10`

Because canonical main is still 1.3.8 and neither 1.3.9 nor 1.3.10 has merged yet, the X6 canonical promotion guard correctly validates the eventual `1.3.8 -> 1.3.10` metadata boundary. An initial retained X6 assumption used the unmerged 1.3.9 candidate as the base and was corrected at the test/workflow layer; no production rollback or ownership expansion was required.

## Verification

Exact head `a4c3621ec6d651b7e4c4d1085a2b900f8c94454b` completed **22/22 triggered workflows SUCCESS, 0 failure**.

Law 01 Browser Quality:
- run: `35410299157`
- artifact: `10574376317`
- artifact digest: `sha256:87c3d01aabc47c3b92ca673887d8eb1454fe7a88795cfa9900f41c8acb1729eb`
- viewports retained: 1920x1080, 1440x1000, 1024x900, 390x844, 320x760

The 1440 browser screenshot was manually inspected after the final production polish. The transition now visually blends the cream Hero surface into the image instead of exposing a hard vertical image edge, while the text and downstream sections remain aligned to the shared shell.

## Installable package

File: `aznet-theme-1.3.10.zip`
Production files: **145**
Packaged PHP lint: **108/108 PASS**
SHA-256: `9eb8a3ad1c24b7e75a695199de3e45bf3e196bebd7fff8774f7390969283c92d`

Y5 run: `35410299118`
Promoted-package artifact: `10574586211`
Artifact digest: `sha256:dadb066567c2b6c6d3c1f28f338a22441903fed03a55ad47433ac2d164f58132`

Y5 built the package twice with byte identity, matched exact production source, linted the packaged PHP set, installed on clean WordPress 6.9 with zero active third-party plugins, passed browser/runtime smoke and verified switch-away/switch-back continuity.

## Ownership and gates

- Theme remains presentation owner.
- No provider/domain ownership changes.
- RootProfile authoritative Team integration remains `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
- Canonical merge of PR #150 remains an explicit approval gate.
- Git tag, GitHub Release and production deployment remain separate approval gates.
