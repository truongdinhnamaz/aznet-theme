# W2 Verification Plan Amendment

**Applies to:** `docs/superpowers/plans/2026-09-03-w2-single-product-shell.md`, Task 3 Step 2 only.

## Reason

During execution, the retained W1 verifier was inspected and found to already invoke `scripts/verify-e5b.sh` and the full production PHP lint. Calling E5-B and production lint again from W2 would duplicate already-retained checks in the same verification chain and conflicts with the project rule not to rerun PASS work without a new question/invalidation.

## Superseding verifier sequence

`scripts/verify-w2.sh` must run exactly:

```bash
php tests/offline/w2-product-asset-scope-contract.php
php tests/offline/w2-product-css-contract.php
php tests/offline/w2-product-ownership-static-contract.php
bash scripts/verify-w1.sh
```

`verify-w1.sh` remains responsible for its four W1 contracts, retained `verify-e5b.sh`, and production PHP lint. Therefore a successful W2 run still proves the original Task 3 acceptance without duplicate execution.

No production scope, ownership, acceptance criterion or evidence depth is changed by this amendment.
