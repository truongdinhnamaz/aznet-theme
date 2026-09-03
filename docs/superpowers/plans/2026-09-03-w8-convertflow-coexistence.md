# W8 ConvertFlow Coexistence Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Verify and implement the smallest AZnet Theme-owned public presentation bridge required for bounded L5 coexistence with the current ConvertFlow Theme Integration Contract, without transferring ConvertFlow or WooCommerce domain ownership.

**Architecture:** AZnet Theme exposes the current public `--convertflow-theme-*` presentation vocabulary as a one-way CSS projection from Theme semantic tokens. ConvertFlow continues to own its asset, DOM, Journey/Filter/Fit/Fast Conversion semantics, and fallback presentation. Verification uses exact current ConvertFlow public integration bytes plus retained Woo regression/ownership gates.

**Tech Stack:** WordPress theme PHP 8.1+, CSS custom properties, PHP contract harnesses, GitHub Actions.

**Spec:** `AZnet_Theme_Implementation_Slice_Map_v0.9` W8 + AZT-01/AZT-02 ownership rules.

## Global Constraints

- Theme is presentation owner only.
- No direct ConvertFlow option/meta/table/private-class reads.
- No ConvertFlow Journey/Filter/Fit/Fast Conversion logic copied into Theme.
- WooCommerce retains commerce truth and behavior.
- Provider absent must remain fail-soft.
- No new build stack.
- W8 does not imply W9 release closure or merge approval.

---

### Task 1: Contract-first Theme bridge

**Files:**
- Create: `tests/offline/w8-convertflow-theme-contract.php`
- Create: `assets/css/integrations/convertflow.css`
- Modify: `inc/theme/assets.php`

**Interfaces:**
- Consumes: ConvertFlow Theme Integration Contract v1 public CSS vocabulary.
- Produces: `aznet-theme-convertflow-contract` Theme stylesheet exposing the public vocabulary from AZnet semantic tokens.

- [x] Write RED contract asserting the dedicated bridge, exact public vocabulary, asset handle, no ConvertFlow DOM selectors, and no private storage/domain keys.
- [x] Run RED and confirm failure is the missing Theme bridge.
- [x] Add the minimum CSS projection and enqueue after `aznet-theme-tokens`.
- [x] Run GREEN and PHP lint.
- [x] Commit bounded production changes.

### Task 2: Exact current-provider coexistence harness

**Files:**
- Create: `tests/integration/w8-convertflow-coexistence.php`

**Interfaces:**
- Consumes exact current public ConvertFlow integration files by verified Git blob identity.
- Produces a bounded provider absent/present + Woo off/product-on coexistence assertion.

- [x] Pin exact current ConvertFlow public integration blob identities.
- [x] Compare all 33 current public contract properties with the Theme projection.
- [x] Verify ConvertFlow frontend asset dependency remains provider-owned.
- [x] Exercise provider absent/present and Woo off/product-on coexistence.
- [x] Reject private/runtime coupling from W8 production files.
- [x] Run the exact-byte harness and lint.

### Task 3: Retained Woo regression reconciliation

**Files:**
- Modify: `tests/offline/w2-product-ownership-static-contract.php`
- Modify: `tests/offline/w3-archive-ownership-static-contract.php`
- Modify: `tests/offline/w4-cart-ownership-static-contract.php`
- Modify: `tests/offline/w5-checkout-ownership-static-contract.php`
- Modify: `tests/offline/w6-account-ownership-static-contract.php`

**Interfaces:**
- Consumes: retained W2-W6 ownership invariants.
- Produces: surface-scoped ownership gates that still forbid ConvertFlow coupling in Woo-owned presentation files while allowing a later Theme-level public integration in the shared asset registry.

- [x] Reproduce retained test failure after W8 integration.
- [x] Trace root cause to over-broad historical `convertflow` string bans in shared `inc/theme/assets.php`.
- [x] Scope the ban to each W2-W6 surface-owned PHP/CSS while preserving private `choiceguide_` and Woo/domain prohibitions.
- [x] Run retained Woo asset/ownership regression matrix.

### Task 4: Fresh independent CI closure

**Files:**
- Test-only branch: `.github/workflows/w8-local-regression.yml`

**Interfaces:**
- Consumes: W8 candidate and retained Woo tests.
- Produces: fresh GitHub-hosted PHP 8.1 regression evidence.

- [x] Run W8 public contract.
- [x] Run retained W6 regression chain.
- [x] Run retained W2-W5 ownership gates.
- [x] Lint W8 integration harness.
- [x] Reject private ConvertFlow coupling and ConvertFlow-owned DOM selector targeting.
- [x] Confirm GitHub Actions run `33761153314` succeeds.

### Task 5: Evidence and review checkpoint

**Files:**
- Create: `docs/evidence/W8_CONVERTFLOW_COEXISTENCE.md`

**Interfaces:**
- Consumes: exact-byte local verification and successful CI evidence.
- Produces: bounded L5 W8 closure with explicit non-claims and W9 next action.

- [x] Record source/provider provenance, RED/GREEN cycle, production blob identities, failed-run root causes and fresh successful CI run.
- [x] State W8 PASS only for the bounded public-contract L5 coexistence boundary.
- [x] Keep W9 L6, merge, deployment, E5-C and Milestone F outside the claim.
- [x] Open review PR; do not merge without owner approval.
