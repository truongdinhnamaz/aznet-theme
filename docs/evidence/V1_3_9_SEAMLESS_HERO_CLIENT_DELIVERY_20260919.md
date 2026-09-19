# AZnet Theme 1.3.9 — Seamless Law 01 Hero Client Delivery Candidate

**Date:** 19/09/2026
**Repository:** `truongdinhnamaz/aznet-theme`
**Canonical base:** `main@597639e26a2cb242c3c0a71e5459ab5d66a0701d`
**PR:** #150
**Verified production head:** `b4ead6ea541074fa76b1657d20de07828c5bfde9`

## Purpose

The owner reported that the Law 01 Hero still looked visually boxed: the text/image area exposed obvious boundaries against the full-width page background. The requested correction is presentation-only: keep the shared content alignment, but let the Hero surface blend naturally into the viewport so the content does not look like a separate centered panel.

Because AZnet Theme 1.3.8 is already canonical and already has a deterministic package identity, the improved bytes are promoted to **1.3.9**. Reissuing different bytes as 1.3.8 would create ambiguous provenance.

## Visual RED -> GREEN

Visual RED head: `ffd2c8e48498bf0849ec8fa022feddb52da80806`.

The new contract failed for the intended reason:

`Law 01 Hero text column must not paint its own boxed background against the full-width section surface.`

Minimal production correction at `72941562c009299da9b9a84ffaf7b68a9fad33ec`:

- Hero section remains full-width with the accepted cream surface;
- Hero text column background becomes transparent so it does not paint a separate panel;
- the shared 96rem grid remains authoritative for content alignment;
- the image surface derives its bleed from the shared shell and extends to the viewport edge on desktop;
- on stacked/mobile layouts the image surface bleeds to both viewport edges;
- Hero overflow is clipped at the section boundary to prevent horizontal scroll.

The first browser GREEN candidate exposed one stale retained assertion that measured the 52% visual ratio from the now-bleeding image width. The retained browser contract was corrected to measure the 48% content column against the shared Hero grid instead. Exact visual head `8cf81cb0ec7955d7605e32913aa36af7fe31b20d` then completed **14/14 triggered workflows SUCCESS, 0 failure**.

Law 01 Browser Quality run: `35405740629`.
Browser artifact: `10572278488`.
Artifact digest: `sha256:819160a4b0dd17ae729794c585612791fde1fe07fc2c8b5da54f3faffa1cd406`.

The 1920px screenshot was manually inspected: the cream text surface now continues naturally to the left viewport edge, while the Hero image surface reaches the right viewport edge without the previous outer gutter. The 390px screenshot confirms the stacked image surface reaches both viewport edges without horizontal overflow.

## Package RED -> GREEN

Release RED head: `886c3d751326d16a90bddfa54bcaafa7b9af31d4`.

Y5 failed for the intended reason while production metadata still declared 1.3.8:

`Y5 promoted style.css must be exactly 1.3.9`

Minimal GREEN promoted only:

- `style.css`: `Version: 1.3.9`
- `functions.php`: `AZNET_THEME_VERSION = 1.3.9`

Y5/X6 guards were extended for the exact approved `1.3.8 -> 1.3.9` promotion boundary.

Exact production head `b4ead6ea541074fa76b1657d20de07828c5bfde9` completed **19/19 triggered workflows SUCCESS, 0 failure**.

## Installable package

File: `aznet-theme-1.3.9.zip`
Production files: **145**
Packaged PHP lint: **108/108 PASS**
SHA-256: `33a70a1d63ae96c21d6efe381f8a0fea299ac0c14a49df56c5edfe8c2eb9404a`

Y5 run: `35406199947`
Promoted-package artifact: `10571874751`
Artifact digest: `sha256:892e96890c189950000367c886bccd70b697de6c0ed442bcbb3527480ae7d9d9`

The package was built twice with byte identity, matched the exact production source, passed packaged PHP lint, installed on clean WordPress 6.9 with zero active third-party plugins, passed browser/runtime smoke, and passed switch-away/switch-back continuity.

The direct WordPress-installable ZIP was extracted from the GitHub Actions artifact wrapper and independently verified to contain `aznet-theme/style.css` declaring version 1.3.9.

## Ownership and gates

- Theme remains presentation owner.
- No provider/domain ownership changes.
- RootProfile authoritative Team integration remains `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
- Canonical merge of PR #150 remains an explicit approval gate.
- Git tag, GitHub Release and production deployment remain separate approval gates.
