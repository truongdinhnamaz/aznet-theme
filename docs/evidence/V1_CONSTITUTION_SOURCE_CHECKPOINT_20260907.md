# AZnet Theme v1 Constitution Source Checkpoint — 2026-09-07

This repository file is evidence only. It does not replace the authoritative AZnet Theme source documents.

## Canonical implementation state

- Repository: `truongdinhnamaz/aznet-theme`
- Canonical `main`: `815ffcd43653930d1f302a055961ac24cf5ae843`
- Internal Theme version at checkpoint: `0.1.0-alpha.7`
- PR #24: merged; W8/W9 delivery code on canonical main.
- PR #30: merged; Theme-native Homepage shell on canonical main.
- PR #31: evidence-only, open/draft; F6/F7 actual-package integration PASS, F8 integrated compatibility BLOCKED by provider-emitted nested document-level `<main>`.

## Authoritative source artifacts produced for this checkpoint

- `00_Muc_luc_Nguon_va_Governance_v0.3.docx`
  - SHA-256: `33c0500bb86ad0107f1c7b6214faef656c0cb77b6f5180eab6699e7f6cd3386e`
- `01_Product_Charter_va_Ownership_v0.3.docx`
  - SHA-256: `9ab11a0047454d26184d2c249958f7eacc1a5b105b1b7ec16929566ce79780d6`
- `02_Kien_truc_Theme_va_Integration_Contracts_v0.4.docx`
  - SHA-256: `abf78734071416ae034ce5ec8681c3f52466a4798720cecafae2bc5af944939d`
  - Unchanged architecture source carried into the current source set.
- `03_Current_Baseline_va_Code_Provenance_v0.16.docx`
  - SHA-256: `06224ffa32a5fc3d39142a421869157a4e259d8fbc58cf1981a75aad2e1ea065`
- `04_Roadmap_QA_va_Decision_Log_v0.20.docx`
  - SHA-256: `e8f1421b05012a4bf45f2476f5e1abba898110b8504d020204f172114ae3ea0c`
- `05_Hien_phap_san_pham_v1.0.docx`
  - SHA-256: `4c588ea97d54a17ac4db586d3e34c3c249ff52fa913ea731a4bd9563d7b98b97`
- `AZnet_Theme_Implementation_Slice_Map_v0.12.docx`
  - SHA-256: `aac5a17682b1f84491bdf4ce3c8f994af3d52e53bed16b30ec46e560912964ee`

Bundle:

- `AZnet_Theme_Source_Set_20260907.zip`
- SHA-256: `72b8cde8a6e58aa61e5c447dac972d2970e9ac4ffb0f0f4eb8ec2ebded4b696c`

## Source decisions

### AZT-05 Product Constitution v1.0

Approved constitutional rule: AZnet Theme is an independent WordPress theme. Optional plugin/provider integrations are capability/certification tracks and do not define core Theme readiness unless an authoritative future source amendment explicitly makes one mandatory.

### AZT-04 D-016

Accepted: core v1.0 release independence. WordPress-clean Theme quality is the release critical path. External provider defects remain BLOCKED/UNKNOWN under their owner and may not be bypassed with private APIs, direct storage reads, authoritative heuristics or copied domain logic.

## Exact next

`G0` — freeze core-v1.0 scope and inventory the remaining Theme-owned cleanup/release blockers on canonical `main@815ffcd43653930d1f302a055961ac24cf5ae843`.

Production code must not start until the written source/spec review gate is approved.