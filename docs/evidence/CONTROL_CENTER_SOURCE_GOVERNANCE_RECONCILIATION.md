# Control Center Source-Governance Reconciliation

Date: 2026-09-03
Decision: owner-approved AZnet Theme Control Center architecture

This checkpoint records the one-time source-level reconciliation required before U0 production implementation. It is not an implementation progress ledger.

Authoritative source successors prepared from the previous working sources:

- AZT-01 Product Charter / Ownership: v0.2 -> v0.3
- AZT-02 Architecture / Integration Contracts: v0.4 -> v0.5
- AZT-04 Roadmap / QA / Decision Log: v0.16 -> v0.17

Enduring decision captured:

- AZnet Theme owns a centralized Theme presentation/configuration Control Center.
- Customer-ready direction includes Quick Setup, Theme-owned presentation presets/design controls, bounded Header/Footer composition, presentation-only Content/Woo controls, Gutenberg Patterns, integration diagnostics, and Theme presentation import/export.
- No proprietary page builder or Flatsome/UX Builder clone.
- WordPress, WooCommerce, RootProfile, and ConvertFlow retain their authoritative data/semantics.
- Theme settings use public WordPress Theme Mod APIs; admin assets remain scoped.
- Theme update continuity uses the same `aznet-theme/` installation directory.
- U0-U6 progress remains in GitHub/evidence and must not repeatedly version-bump source documents.

Prepared source files and SHA-256 values are recorded in the delivery checkpoint outside the repository because the authoritative DOCX files are Project artifacts rather than Theme source-code files.
