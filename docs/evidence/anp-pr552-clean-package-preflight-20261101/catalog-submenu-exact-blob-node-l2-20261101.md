# ANP PR #552 — exact Git blob Node L2 replay (2026-11-01)

## Scope and source identity
- Candidate: Draft PR #552 at `fc6725654ab20767fecd17412416b2ad12a6c2bd` (not merged/deployed).
- Exact original file `assets/js/catalog-navigation.js`: Git blob `01c47765f7484b0f7338088c63f22e1852b43314`.
- Exact original file `tests/offline/catalog-navigation-accessibility-behavior.test.mjs`: Git blob `2d1b33136d8484ed3e1603e842b9a7a8029cab95`.
- Both files were reconstructed in an isolated ephemeral local workspace, then `git hash-object` verified the bytes against **GitHub's actual source blob SHA** before test execution. No source edits were made.

## Fresh local execution (Node v22.16.0)
```text
Candidate fc6725654ab20767fecd17412416b2ad12a6c2bd
git blob JS:
01c47765f7484b0f7338088c63f22e1852b43314
git blob test:
2d1b33136d8484ed3e1603e842b9a7a8029cab95
Node:
v22.16.0
Syntax JS:
Syntax test:
Run test:
PASS: native catalog disclosure icon, naming, click, Escape and source content (Node-only fixture)
```
- Node commands `node --check assets/js/catalog-navigation.js`, `node --check tests/offline/catalog-navigation-accessibility-behavior.test.mjs`, then `node tests/offline/catalog-navigation-accessibility-behavior.test.mjs` all exited 0.
- Captured local log SHA-256: `50d7c151e6babac2fa896e4ecbf85c349dd074ccbad9313bccae7aa49bf75b08`; local log is not uploaded as a ZIP.
- Contract proves generated child-category disclosure button has `aria-controls`, initial `aria-expanded=false`, click updates `true`, Escape resets `false` and returns focus to the same button; accessible icon is aria-hidden; label text is updated and localized. All assertions passed in a mock DOM.

## Evidence classification
- **PASS L1/L2**: exact JS source + exact focused Node harness local syntax/behavior on this candidate.
- **UNKNOWN L4**: independent read-only TinyFish preview run [186550a5](https://agent.tinyfish.ai/runs/186550a5-42a2-4be2-860f-4c1a7a0bcdef) reported that the attribute was absent, but also observed changed accessible labels and Escape closure. That report did not provide raw `outerHTML`, numeric viewport, or focus state, and so **does not overturn the exact-blob L2 PASS or establish that live DOM works**.
- No speculative `aria-haspopup` addition: current implementation is a native menu-link disclosure control with `aria-expanded`, not a WAI-ARIA `menu/menubar` popup widget.
- The main heading `DANH MỤC SẢN PHẨM` is distinct from the individual child-category toggle; comparing those two texts is not a valid label-in-name assertion.
- No Theme live, mixed WPVibe preview, source owner or site data was modified. This evidence branch is not the release branch.

## Outstanding gates
Full exact 242-path ZIP not built: runner cannot directly fetch the public GitHub archive and no authorized raw-byte transfer is currently available. Neither packaged 164-file PHP lint nor WordPress L3/L4 release candidate is claimed. The host+MySQL restore point remains unverified, as does main-merge/live-publish approval. Keep PR #552 in Draft and do not delete or publish unrelated Wellness 01 draft files.

**Exact Next:** obtain executable exact-source bytes/approved staging for full deterministic ZIP+test chain (or host backup authority for release), then independently measure the preview's `button.getAttribute('aria-expanded')` at actual browser DOM before deciding whether an L4 fix is needed.
