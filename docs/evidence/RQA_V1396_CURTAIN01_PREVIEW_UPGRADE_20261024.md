# RQA — AZnet Theme 1.3.96 Curtain 01 pilot-shaped preview and upgrade gate

**Date of verification:** 2026-10-24. **Canonical repository:** `truongdinhnamaz/aznet-theme`.  
**Candidate:** `main@afdeeacd3927eb0b7763045ab732b2f0cbebd1c7` (1.3.96) with exactly the 353-byte Theme-owned WooCommerce archive CSS addition already verified through Draft PR #506.  
**Preview ZIP SHA-256:** `50a4189be371cda131eda1eb0dd95ff739c43e4e4228015e1b4d4a958703a266`, **preview only, no 1.3.96 public Release/tag**.

## Valid current state

- WPVibe authenticated read: `remquocanh.vn` still has AZnet Theme **1.3.92**, WordPress **7.1.3**, WooCommerce **11.2.1**, `curtain-01` visual/homepage preset. WordPress Theme mod `custom_css_post_id=1133` remains active from previously approved native Additional CSS hotfix, with live L4 real site 9/9 category × viewport PASS [#39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109).
- `aznet.vn` actually runs AZnet Theme **1.3.96**, and GitHub `main` `style.css` + `AZNET_THEME_VERSION` both 1.3.96. GitHub latest published Release remains 1.3.92. Distinguish site deployment, source main, preview and signed Release. No new tag or main merge.
- Diff `v1.3.92` → `main@afdeeacd` includes **14 Theme runtime source files** for presentation features (e.g. Digital 01 and shared assets/composer/header/footer), so CSS-only browser verification on v1.3.92 cannot certify a full 1.3.96 upgrade on RQA.

## PASS — clean WordPress with exact preview package

- [GitHub Actions #39984680097](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39984680097), SUCCESS: 241 runtime files with only 1 changed archive CSS file vs `main@afdeeacd`; 164 PHP 8.1 lint PASS; deterministic package SHA-256 above.
- Disposable WordPress 6.9, PHP 8.1, **zero optional plugins**: Theme activation/Home/Post, Theme-switch and restoration to original 1.3.96 binary PASS.
- Chromium/axe-core Home/Post × 390/768/1280 **6/6 PASS** for narrow layout, landmarks, tab continuation, no critical/serious axe violations; retained AZT-04 O-008 approved ConvertFlow CSS bridge allowed, plugin-private assets absent. No full WCAG guarantee.

## PASS — RQA Curtain 01 pilot-shaped preview at the actual WP/Woo version

- [GitHub Actions #40069541986](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40069541986) **SUCCESS**, exact commit `9b59265772e6e438515df1825ef5d3c0baaadf0d`, job `120799798857`.
- Reused repository's previously retained `C5 Curtain 01 Pilot-shaped Preview` fixture/acceptance on **the exact deterministic candidate ZIP SHA**, but upgraded disposable runtime to WordPress **7.1.3**, PHP **8.4**, WooCommerce **11.2.1**, aligning core versions with current RQA.
- Browser screenshot and axe results on **320, 390, 1024, 1440 px**. All 10 expected native Curtain 01 homepage surfaces, six product/catalogue cards, six category showcase cards, three knowledge cards and four category chips appeared correctly in the fixture. No horizontal overflow or serious/critical axe violations in the recorded scenarios; shop, native product and admin authoring gates passed.
- Artifact `rqa-v1396-curtain01-woo-isolated-preview-9b59265772e6e438515df1825ef5d3c0baaadf0d`, ID **12550532371**, screenshot/axe JSON/fixture/runtime log. This is a synthetic WordPress/Woo fixture, **not** authoritative RQA content and **not** a ConvertFlow L5 certification.

## In-place upgrade/rollback rehearsal — separate gate

- New isolated source-side harness `.github/scripts/rqa_v1396_isolated_upgrade_rollback.sh` and `.github/scripts/rqa_v1396_pilot_data_snapshot.php` compare WordPress-native pages, posts, menus, Theme presentation settings, and Woo public product projections across Theme 1.3.92 → exact 1.3.96 ZIP → old Theme restore.
- The test also models native WordPress `custom_css_post_id`: retain it during upgrade, disable redundant CSS **only after** candidate CSS digest check, and re-enable it if rollback to 1.3.92 is required.
- First run [#40069899734](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40069899734) FAILED during isolated CSS post setup after the official old ZIP was verified; not an AZnet Theme package failure. Additional readback instrumentation is in progress; no upgrade/rollback PASS claim until exact completed run.
- No live RQA Theme/plugin files, content, price, hotline, ConvertFlow private storage or WPVibe draft changed in this work.

## Owner boundaries and next

**PASS:** Source-verified 1.3.96 ZIP L1/L2, clean WordPress L3/L4, isolated Curtain 01+Woo pilot-shaped L3/L4.  
**BLOCKED/UNKNOWN:** full in-place upgrade/rollback rehearsal pending; real RQA ConvertFlow/RootProfile integration L5, visual parity against protected live site, official Theme release/merge/production update L6.  
**Important version integrity:** same `1.3.96` metadata exists with and without the RQA CSS fix. The custom CSS ZIP must not be circulated as a second official `1.3.96` Release; version promotion and package publication require product-owner decision.  
**EXACT NEXT:** diagnose the isolated CSS-post setup gate of the upgrade rehearsal, then verify source-owned content parity and rollback. If GREEN, reconcile release candidate evidence against PR #506; do not deploy or remove live hotfix #1133 without a separate authorized release plan.
