# AZnet Theme Source Governance

**Status:** ACCEPTED / FROZEN-BY-DEFAULT  
**Scope owner:** repository source-lifecycle governance only  
**Repository:** `truongdinhnamaz/aznet-theme`

## 1. Core rule

**SOURCE GUIDES IMPLEMENTATION. GITHUB RECORDS IMPLEMENTATION STATE.**

Once an AZnet Theme source baseline has been accepted, that source is treated as fixed for normal implementation work. It is not a running progress log and must not be revised merely because code, tests, commits, pull requests, CI runs, package hashes or milestone slices advance.

The normal execution loop is:

`read frozen source -> resolve latest valid GitHub state -> code -> test/verify -> commit/PR/evidence -> continue`

Do not insert a source-document rewrite between implementation slices unless a true source-owned decision has changed.

## 2. Authority split

The existing AZT source set remains authoritative by subject:

- **AZT-00** — source priority and documentation governance.
- **AZT-01** — product charter and ownership.
- **AZT-02** — architecture and integration contracts.
- **AZT-03** — baseline/provenance rules and registered baseline evidence.
- **AZT-04** — roadmap, QA gates and decision log.
- **Implementation Slice Map** — derived execution map; never overrides the AZT source owners.

This file owns only the repository rule that separates **stable source** from **live implementation state**. It does not redefine product scope, domain ownership, architecture, public contracts, QA semantics or roadmap decisions already owned elsewhere.

## 3. What is frozen

After acceptance, source content is frozen-by-default for implementation purposes.

The following do **not** justify a new source version by themselves:

- a feature or bugfix commit;
- a branch or pull request opening/closing/merging;
- a RED/GREEN test result;
- a runtime/browser/integration/release run;
- a package rebuild, file count or SHA-256 change;
- a slice or milestone moving from ACTIVE to PASS when the governing rule itself did not change;
- reconciliation of repository evidence that does not alter architecture, ownership, contract or governance.

Those facts belong in GitHub code history, PRs, Actions, release artifacts, and `docs/evidence/*` checkpoints as appropriate.

## 4. Live implementation state

The latest valid implementation state must be determined from GitHub, in this order as applicable:

1. canonical `main` head;
2. relevant work/feature branch and PR state;
3. fresh CI/test/runtime/browser/integration evidence;
4. release/package artifacts and hashes when the question is byte-specific;
5. repository evidence/checkpoint files.

A historical source checkpoint must never override fresher GitHub implementation evidence when the source-owned rule has not changed.

### AZT-03 clarification

After the source freeze, AZT-03 is a **baseline/provenance authority**, not a continuously rewritten ledger of every new canonical commit, PR status, CI run or package hash.

New implementation evidence may supersede an old recorded implementation checkpoint without requiring a new AZT-03 file. The provenance rules remain binding; the live commit/state comes from GitHub.

## 5. When source may be reopened

Source is reopened only at a **true hard gate** where the content owned by that source must materially change, for example:

- product scope or presentation/domain ownership changes;
- architecture mode or dependency policy changes;
- a public/versioned integration contract is added, removed or changed incompatibly;
- support floor, namespace/prefix family or other technical governance changes;
- roadmap/milestone/gate semantics materially change;
- QA/release governance changes;
- the owner explicitly decides to replace or amend the accepted baseline.

A source change requires explicit owner approval before implementation proceeds under the new rule. Evidence alone never grants permission to change source.

## 6. No automatic source-document generation

For normal AZnet Theme implementation work:

- do **not** create `AZT-03 vNext`, `AZT-04 vNext`, Slice Map vNext or another source successor merely to record progress;
- do **not** generate or hand off updated DOCX/PDF source files merely because code advanced;
- do **not** duplicate GitHub state into source documents for synchronization purposes;
- do **not** rewrite historical source/evidence to make it look current.

If no true source hard gate exists, the correct action is to keep coding/testing/verifying and record evidence in GitHub.

## 7. Implementation discipline remains unchanged

Freezing source does not weaken gates. Implementation must still follow the accepted AZnet Theme rules, including:

- Theme owns presentation; domain owners keep authoritative data/state.
- Integration uses public/versioned contracts only.
- Provider absence/error/version mismatch fails soft/safe.
- No private storage reads, domain-logic cloning or parallel truth stores in the Theme.
- No inference of authoritative identity/routing from slug/title/Page ID/URL heuristics.
- QA layers remain distinct; PASS is claimed only at the layer freshly evidenced.
- Production feature/bugfix behavior uses RED -> minimal GREEN -> regression where applicable.
- Main merge, release/deploy, takeover and other approval-gated actions remain hard gates.

## 8. Conflict rule

If older documentation wording appears to require a source update for every implementation checkpoint, this governance decision supersedes that **process interpretation only**.

It does not supersede the underlying architecture, ownership, public contracts, provenance requirements or QA gates of AZT-00/01/02/03/04.

**Default behavior:** keep source fixed; move implementation forward in GitHub.
