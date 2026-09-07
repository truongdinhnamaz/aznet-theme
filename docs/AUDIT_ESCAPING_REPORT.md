# Escaping & Output Safety — UNVERIFIED LEGACY SCAN

Date of original scan: 2026-09-05
Repository: `truongdinhnamaz/aznet-theme`
Status: **UNVERIFIED LEGACY SCAN / TRIAGE ONLY**

This file records observations from an ad-hoc automated search for output patterns such as `the_title`, `the_permalink`, `the_content`, `the_excerpt`, `echo`, `print`, and `printf`.

It is **not** an accepted AZnet Theme QA gate, security certification, vulnerability finding, or authorization to merge any fix branch. The original scan did not include a dedicated RED reproduction proving exploitability for each candidate, and it must not be used to infer runtime/browser/security PASS or FAIL.

## Triage observations retained

The scan flagged candidate locations involving `the_title()` / `the_permalink()` in:

- `index.php`
- `page.php`
- `single.php`
- `template-parts/content/card.php`

It also noted output sites in Header/Footer/Profile templates where WordPress-generated or already escaped markup is involved.

These are **review candidates**, not established defects. WordPress template output APIs have context-specific escaping semantics; any change must be verified against the actual output context and Theme behavior rather than mechanically replacing APIs based only on a text match.

## Historical fix branches

The branches `work/fix-escaping-templates` and `work/fix-escaping-automated` exist as historical candidate work. This report does not authorize either branch for merge and does not claim that either branch fixes a proven vulnerability.

## Required path for any production security change

Treat each candidate as a separate bugfix/security slice:

1. Identify the exact output context and threat model.
2. Write a focused regression/contract test that fails for the unsafe behavior (RED).
3. Confirm the RED fails for the intended reason, not from harness/syntax drift.
4. Apply the smallest production change that makes it GREEN.
5. Run affected retained regressions plus PHP lint/static ownership checks.
6. Escalate to WordPress/browser verification when the output context requires runtime evidence.

Do not change `the_content()` / `the_excerpt()` merely because they output HTML; their HTML behavior is intentional WordPress content rendering and requires a separate product/security decision if stricter policy is desired.

## Companion CSV

`docs/AUDIT_ESCAPING_AUDIT.csv` retains the original paths as triage entries. Severity is normalized to `needs-review` or `informational`; it is not a security severity assessment.

## Governance note

This scan is GitHub evidence only. It does not change AZT architecture, ownership, roadmap gates, or canonical release state. Any accepted security fix must produce fresh evidence at the appropriate QA layer before it can invalidate or supersede prior PASS state.
