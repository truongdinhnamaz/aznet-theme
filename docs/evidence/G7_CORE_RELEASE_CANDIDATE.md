# G7 — Full Regression / Deterministic Core Candidate — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Workflow: `.github/workflows/g-core-release-candidate.yml`

## Initial RED — package harness defect

Run: `34107127259`  
Job: `101694602593`

The retained core static/contract suite passed, but package construction failed. Root cause was isolated to path normalization in the new packaging harness:

```python
rel = path.as_posix().lstrip('./')
```

For a path such as `.git/FETCH_HEAD`, `lstrip('./')` removed the leading dot and produced `git/FETCH_HEAD`. That defeated the `.git` exclusion and generated an invalid manifest/package set. The failed run reported 81 files and then failed exact-byte comparison on the corrupted manifest path.

No production Theme defect was reproduced.

## Minimal GREEN

Harness fix commit: `e0f25c53d51e4ca0598846e6cee22c77789b3cfc`

Path construction now uses:

```python
path.relative_to(root).as_posix()
```

This preserves dot-prefixed path components so `.git` and `.github` exclusions remain authoritative. No production PHP/CSS/JS/template/theme.json behavior changed.

## Fresh GREEN evidence

GitHub Actions run: `34107372956`  
Job: `101695374343` (`release-candidate`)  
Result: **SUCCESS**  
Execution checkout: synthetic PR merge of head `e0f25c53d51e4ca0598846e6cee22c77789b3cfc` into base `b7443a96c8687493a0a4994aeca23b5e09a8770c`  
WordPress: `6.9`  
PHP: `8.1.34`

### Regression and static checks

- G3 core release static contract PASS.
- E5-B retained consumer/model/dispatcher/no-takeover contracts PASS.
- Woo W1-W6 retained capability/surface/ownership/asset contracts PASS.
- W8 ConvertFlow public theme-contract bridge PASS.
- F1/F2/F3/F5 native Homepage/ownership/fail-soft contracts PASS.
- Production PHP lint: 29/29 PASS.

### Deterministic package

- Production package file count: **43**.
- Two independent builds were byte-identical.
- Candidate inner ZIP SHA-256: `b4db6e5ee0efeaec59564285994f455ba5bd692acbc91c1db3bce479d3fb2795`.
- Package excludes `.git`, `.github`, `docs`, `scripts`, `tests`, `README.md`, and `aznet-preview.png`.
- Unzipped candidate was compared byte-for-byte against every manifest-listed production source file.
- Packaged PHP lint: 29/29 PASS.

### Extracted-package runtime

The exact extracted candidate bytes were installed and activated on clean WordPress 6.9 with no active ecosystem plugins. A native static front page rendered the G7 package sentinel plus Theme Header/Main/Footer. No Theme-caused PHP Fatal/Warning/Parse/Uncaught marker was found.

### Artifact

Artifact: `g7-aznet-theme-core-candidate`  
Artifact ID: `10012925135`  
Artifact size: `44689` bytes  
Artifact-container ZIP SHA-256: `55b8a39eeee08e8fb10fb3688032d714f11cfd142e92d662753517db6d152a4b`

The artifact-container hash and the inner candidate-package hash are intentionally different values: the artifact is a bundle containing the candidate ZIP plus manifest/hash/count/runtime evidence.

## PASS

**G7 / L6 technical candidate PASS** for the pre-promotion `0.1.0-alpha.7` production bytes at head `e0f25c53...`.

This authorizes proceeding to the bounded G8 version-promotion closure. It does not itself declare v1.0, merge the PR, tag a release or deploy production.

## Rollback

Registered rollback/source reference remains `main@b7443a96c8687493a0a4994aeca23b5e09a8770c`.

## NEXT

**G8 — establish a RED release-version contract for exact `1.0.0`, remove historical alpha-version assumptions from active release checks, atomically promote `style.css` + `AZNET_THEME_VERSION`, then rerun the full final evidence matrix on the promoted bytes.**