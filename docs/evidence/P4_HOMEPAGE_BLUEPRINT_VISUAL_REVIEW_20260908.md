# P4 Homepage Blueprint — Visual Review Supersession and Final Candidate Checkpoint

**Date:** 2026-09-08  
**PR:** #55  
**Final verified feature head before this evidence-only commit:** `ce7f458d4e58db78e0e284e6fb05046f3cceaec2`  
**Status:** PASS — feature candidate through automated L1-L4 + independent screenshot review; merge remains owner-gated

## 1. Why the older green run was superseded

Automated run `34219272396` on older feature head `e164d56f2cfd3196628d88452181def2b837ce0a` passed its then-current L3/L4 assertions, including overflow, keyboard/focus, axe and runtime checks.

Independent visual inspection of that run's real screenshots subsequently found a Theme-owned presentation defect that those assertions did not cover: the Homepage Hero direct children (eyebrow, H1, supporting copy and CTA group) were laid out horizontally because `.aznet-theme-homepage-blueprint__hero` used `display: flex` directly on the WordPress Group container. On mobile this squeezed CTA links until their text wrapped nearly character-by-character.

Therefore the older `e164d56...` L4 result is historical evidence only and is not the final visual acceptance for PR #55.

## 2. Root cause and regression

The visual defect was reduced to `assets/css/components/homepage.css`. No WordPress content/query/provider defect was involved.

A browser regression was added on head `438aaaa375074d67216f6d73c32f29a519596c50` to measure Hero geometry and require:

- supporting copy starts below the H1;
- CTA group starts below the supporting copy;
- CTA width is protected against the original squeeze failure, with a stronger mobile width floor.

The minimal production fix changed only the Hero composition mechanism from row-producing flex layout to vertically auto-placed grid layout while retaining centered vertical alignment:

```css
.aznet-theme-homepage-blueprint .aznet-theme-homepage-blueprint__hero {
    display: grid;
    align-content: center;
}
```

The first post-fix exact-head run proved the stacking geometry was corrected at all four viewports, but the initial regression used an unnecessarily strict universal CTA minimum of `110px`. The short desktop CTA `Liên hệ` naturally rendered at approximately `96.7px`, so that run failed a test assumption rather than presentation behavior.

The test was corrected without changing production CSS: desktop protects against pathological squeeze with an `80px` floor, while mobile requires at least `220px` for the tested 390/320 widths. The vertical stacking assertions remain unchanged.

## 3. Additional retained-gate correction

The feature exposed a stale R2 browser workflow guard that hard-coded exactly 16 files under `patterns/`. R2's owned historical set remains 16 core patterns plus exactly two Woo-gated patterns, but the Theme may legitimately add later native patterns such as the P4 Homepage blueprint.

The retained R2 offline and browser gates now keep the R2 core count as a floor (`>= 16`) and the Woo-gated count exact (`= 2`). Named R2 pattern checks, clean/Woo browser matrices, public Woo capability gating and theme-switch portability remain intact.

Final R2 browser run on the verified head:

- `R2 Pattern Library Browser Quality` run `34221584765` — **SUCCESS**;
- clean WordPress pattern browser quality — SUCCESS;
- WooCommerce 10.9.4 public block/pattern gating — SUCCESS;
- Woo-present Editorial browser quality — SUCCESS;
- theme-switch content portability — SUCCESS;
- runtime error boundary and corrected pattern-count guard — SUCCESS.

## 4. Final P4 exact-head verification

Verified head:

`ce7f458d4e58db78e0e284e6fb05046f3cceaec2`

Workflow:

- `P4 Homepage Blueprint Browser Quality` run `34221584785` — **SUCCESS**;
- job `102045709118` — **SUCCESS**;
- artifact `p4-homepage-blueprint-browser-quality`;
- artifact ID `10054005468`;
- artifact digest `sha256:7b77d38b9f74356aaf6ad100f25b3cf8d458e611dcd3f889adb762170b478462`.

The run proves the registered native pattern can populate a disposable static Front Page on WordPress 6.9, the Theme activates on PHP 8.1, the native Query block renders six controlled Posts, the dedicated Homepage stylesheet loads, and the runtime log contains no blocking Theme PHP error marker.

### Browser matrix

| Viewport | main#main | H1 | Query cards | FAQ details | Overflow | Axe critical/serious | Hero stack | CTA widths |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | --- | --- |
| 1440x1000 | 1 | 1 | 6 | 3 | 0px | 0 | PASS | 168.3 / 96.7 px |
| 1024x900 | 1 | 1 | 6 | 3 | 0px | 0 | PASS | 168.3 / 96.7 px |
| 390x844 | 1 | 1 | 6 | 3 | 0px | 0 | PASS | 277.0 / 277.0 px |
| 320x760 | 1 | 1 | 6 | 3 | 0px | 0 | PASS | 240 / 240 px |

Every case also records:

- no duplicate IDs;
- visible skip-link focus target to `#main`;
- no browser console error;
- no page error;
- no failed request;
- mobile navigation keyboard open/close without overflow at mobile widths.

## 5. Independent screenshot review

The final artifact was downloaded and inspected independently after automation completed.

Desktop `1440x1000` confirms:

- Hero content is vertically composed rather than arranged in a row;
- H1, supporting copy and CTAs have usable hierarchy;
- trust, services, inverse About, process, article grid, FAQ and final CTA remain coherent;
- the previous About-section contrast defect remains fixed.

Mobile `390x844` and `320x760` confirm:

- H1, supporting copy and CTA group stack in reading order;
- both Hero CTA controls retain full usable width;
- no character-by-character CTA wrapping remains;
- service/trust/process/article sections reflow to one-column presentation;
- no visible horizontal overflow is present in the inspected screenshots.

This independent visual review is the evidence that supersedes the earlier screenshot failure.

## 6. Retained final workflow matrix

All 17 pull-request workflows returned **SUCCESS** on exact verified head `ce7f458d4e58db78e0e284e6fb05046f3cceaec2`:

- V1 Core Pull Request CI — `34221584721`;
- P4 Homepage Blueprint Browser Quality — `34221584785`;
- F Homepage Native Regression Package — `34221584711`;
- F Homepage Native Runtime Browser — `34221584665`;
- R2 Pattern Library Static Contracts — `34221584671`;
- R2 Pattern Library Browser Quality — `34221584765`;
- R1 Design System Static Contracts — `34221584856`;
- R1 Design System Browser Parity — `34221584725`;
- R1 Visual Preset Feasibility — `34221584727`;
- R3 Header Static Contracts — `34221584748`;
- R3 Header Browser Quality — `34221586781`;
- R4 WooCommerce Presentation Static Contracts — `34221584784`;
- R4 WooCommerce Presentation Browser Quality — `34221584681`;
- R5 Control Center Static Contracts — `34221584732`;
- R5 Control Center Browser Quality — `34221584744`;
- R6 Performance Baseline — `34221584683`;
- G Core Lifecycle Update Switch Rollback — `34221584745`.

This demonstrates that the Homepage blueprint did not require abandoning Woo, lifecycle, Header, Control Center or earlier design-system regressions.

## 7. Current boundary and next gate

The Native Editable Homepage Blueprint feature candidate is PASS through automated L1-L4 and independent final screenshot review on the verified feature bytes.

This does **not** mean the real Tâm Đức Front Page has already been populated. After an owner-approved PR merge and exact-main/package verification, the real-site step remains:

1. replace the verified Theme package in place;
2. open the existing static Front Page;
3. insert `Patterns → AZnet — Pages → Homepage — Professional Services` once;
4. publish the resulting native WordPress blocks;
5. replace starter copy/media with site-authoritative content;
6. continue the remaining P4 real-site route/integration/performance matrix.

Merge to `main` remains owner-gated. Tag, GitHub Release and final production deployment remain separate P5 gates.
