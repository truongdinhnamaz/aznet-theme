# D-049 owner-approved v1.3.89 release candidate — 2026-10-19

## Authorization and proven source
- User explicitly approved merge of PR #470 and publication of AZnet Theme v1.3.89.
- PR #470 exact candidate `3d635a16733d951a6f8cc8ceea9021ef587088ea` passed 31/31 workflows including clean WordPress/WooCommerce and browser desktop/mobile/keyboard/a11y. Merged at canonical `main@203fe05b0efbec966315e0c6abfd24734aa1c1df`.
- This bounded version promotion updates Theme metadata from 1.3.88 to 1.3.89 and does not alter the reviewed menu/Hero implementation. Core defaults OFF; WordPress/WooCommerce continue to own product category menus and facts.

## One-shot publication gate
- Restrict automated publication to exact version `1.3.89`, merged `main`, no other version or PR branch; bail on a conflicting existing tag or release.
- Build exact-source deterministic ZIP twice and compare; verify all packaged PHP syntax, package contents/entrypoint, and fresh WordPress 6.9 activation. Record exact tag commit, artifact hash and GitHub Release asset after publication.
- Any failed gate means no release PASS. Release pipeline must preserve an installable candidate artifact for recovery.

## Production is separate
- Minh Nguyên currently runs AZnet Theme `1.3.88`; native menu #143 is assigned to `product-catalog` with 19 WordPress-owned entries, `homepage-rail` selected. Owner-authored Hero text is already saved in existing presentation settings.
- Never publish or discard the older WPVibe Draft; it contains independent unfinished work. The new release does not automatically install on production.
- Site deployment requires a verified backup/rollback and fresh desktop/mobile production-browser evidence. If a problem arises, disable `header_catalog_mode` or restore a verified previous Theme package without mutating native menu data.

## X6 version promotion gate — reproducible RED and minimal GREEN

The first v1.3.89 release PR head `19b3f95d4cc7fb7588d6cebe275df6c921aec025` passed the other 16/17 PR workflows, including full Y5 package/lifecycle and version-consistency checks, but X6 workflow `39243574848` failed at its explicitly retained version whitelist with `FAIL: unsupported X6 Theme version state`. This is a governance-controlled missing acceptance branch, not permission to bypass X6. The bounded patch adds only the exact one-step 1.3.88→1.3.89 metadata diff acceptance plus retained offline regression, retaining the strict failure path for any extra metadata or versions. Re-run all CI and do not merge until the X6 gate and exact final candidate PASS.

## Publication run initial failure and recovery (same owner-approved version)

The one-shot exact-main publication run [39245106102](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39245106102) failed **before** the publish step, at the deterministic double-build comparison on main `76d7e732fba090d0ad555c5f78ac94e64f1672ce`. Logs show first archive **229 files / SHA-256 e7c2b3c373b969d1c9d4c619de6fee5d34d0d316af26f3eb02ff383db04aaf8c**, then second archive **230 files / SHA-256 9982ea1734354c3f12f909b10f2e55f3493dfa191e19ca0d266264286c1d35be**. Root cause: first ZIP was written inside source `dist/`, but the packager did not exclude generated `dist/` from its source enumeration; second build recursively included first ZIP. No GitHub tag or Release was created.

Minimum root fix: add `dist` to excluded generated directories in the shared deterministic builder, and wire a synthetic double-build regression (two ZIP outputs inside the source tree, byte-identical and no `dist/` archive members). The approved one-shot publication workflow explicitly re-triggers on this builder change, but only a deliberate merge title beginning `D049 Release v1.3.89:` may publish. No Theme production PHP/CSS/JS, WordPress menu or site data changes. Re-run exact recovery-head CI, then publish from the new exact canonical main SHA with the same expected installable package checksum if it remains byte-identical.
