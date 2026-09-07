# G2 — Bounded Retirement Closure — 2026-09-07

Repository: `truongdinhnamaz/aznet-theme`  
Branch: `work/v1-core-completion`  
Consumes: `G1_CLEANUP_CANONICAL_DESTINATIONS_20260907.md`.

## Rule

Historical production code may be retired only after all of the following are proven together:

1. Theme owns the candidate as presentation;
2. a canonical destination exists;
3. current use-site evidence proves the candidate is superseded/unreferenced;
4. regression protection and rollback are known.

Unknown, Mixed, Adapter, domain-owned, WordPress-hierarchy and external-provenance-only candidates fail closed.

## G1 input

G1 produced an **empty eligible production deletion list**. Current Header/Footer, generic templates, native Homepage, Profile/Contact presentation adapters and Woo presentation paths are either canonical active destinations, required WordPress fallbacks, or active optional capabilities. Repository-only evidence/test/docs assets belong to package exclusion rather than destructive Git cleanup.

## Retirement action

**NO_SAFE_PRODUCTION_RETIREMENT**

No production PHP, CSS, JS, template, `theme.json` or integration adapter is deleted or modified in G2.

A RED regression is not manufactured for a nonexistent deletion. If a future candidate becomes concrete, that future bounded retirement must first establish a protecting RED/guard and known rollback before behavior-affecting removal.

## Ownership check

G2 does not:

- delete or alter RootProfile/ConvertFlow/Woo domain logic;
- reinterpret optional integration code as dead code;
- remove `index.php` or another WordPress hierarchy fallback;
- remove repository evidence solely to make the release package smaller;
- modify canonical source.

## PASS

**G2 PASS — explicit no-op retirement.** No unprotected Theme-owned production retirement is required for core v1.0 based on current evidence.

## Rollback

No production delta exists. Reverting this evidence commit has no runtime effect.

## NEXT

**G3 — establish a fresh repeatable core static/security/package hygiene gate and run retained L1-L2 contracts.**