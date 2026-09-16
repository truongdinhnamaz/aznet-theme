from pathlib import Path

RUN = '35054176392'
ARTIFACT = '10430017585'
DIGEST = 'sha256:c2066532e341f6243f466f5ed885822e5428d6ec344a0f74af20b9247921785b'
RELEASE = '7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78'
RELEASE_TREE = '740406a02ea77a4cb6fdd8f2ee98b7b88f909560'
PR72 = '15f25e4f6d8e64c588866405629b793a85ee0323'
RESTORED = 'e7e5a9c2d2a867f631b029f3f77a675e32fad573'
RESTORED_TREE = 'd50c9f04465f7f6990ac3747ee55a848e3187beb'


def replace_once(path, old, new):
    p = Path(path)
    s = p.read_text()
    n = s.count(old)
    if n != 1:
        raise SystemExit(f'{path}: expected exactly one match, got {n}: {old[:140]!r}')
    p.write_text(s.replace(old, new, 1))


# AZT-03: current-state bullet must reflect deployment closure, not the pre-deployment gate.
p = 'docs/source/AZT-03-baseline-provenance.md'
replace_once(
    p,
    '- P5 final-candidate technical verification: exact canonical source, deterministic double-build, exact package/source byte match, packaged PHP lint, clean WordPress 6.9 zero-plugin runtime/browser verification and theme-switch rollback/switch-back reference all PASS. Owner-approved Git tag `v1.1.0` and GitHub Release publication are now complete; production deployment remains separately gated.',
    f'- P5 final-candidate technical verification, owner-approved Git tag/GitHub Release publication, and owner-approved production deployment disposition for `tamduchanoi.aznet.vn` are complete. Fresh read-only deployment verification run `{RUN}` PASS at the tested Theme-owned/site-operations scope; provider L5 remains separate and unproven.',
)

# AZT-04: remove remaining live-state contradictions while keeping historical checkpoints explicit.
p = 'docs/source/AZT-04-roadmap-qa-decisions.md'
replace_once(
    p,
    '| R6 | Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 technical candidate evidence retained; v1.1.0 publication later completed under P5, while production deployment remains separate |',
    '| R6 | Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 technical candidate evidence retained; publication and production deployment were later completed under P5, while R6 remains technical provenance only |',
)
replace_once(
    p,
    'Current canonical source is `main@7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`, Theme metadata `1.1.0`. D-027 source/implementation closure remains retained through PR #63/#64; P4 public QA merged through PR #66, authenticated read-only QA through PR #67, P1 identity inspection through PR #69, P1/P4 closure through PR #70, and P5 technical closure through owner-approved PR #71. P5 technical run `35051428372` verified final package bytes; publication run `35052694111` then published tag `v1.1.0` and the matching GitHub Release asset.',
    f'Live `main` HEAD is resolved from GitHub at execution time. Stable release anchor `v1.1.0` points to `{RELEASE}`, tree `{RELEASE_TREE}`. Publication source closure PR #72 merged at `main@{PR72}`; restored post-noop checkpoint `main@{RESTORED}` has identical tree `{RESTORED_TREE}` and zero file delta from PR #72. P5 technical run `35051428372` verified final package bytes, publication run `35052694111` published the matching tag/Release, and production read-only run `{RUN}` closed deployment at the tested Theme-owned/site-operations scope.',
)
replace_once(
    p,
    'The earlier deterministic R6 candidate SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` remains historical release-provenance evidence only. P5 independently rebuilt and verified the final post-hardening production bytes as `aznet-theme-1.1.0.zip`, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; owner-approved publication is now complete, while final production deployment remains separately gated.',
    f'The earlier deterministic R6 candidate SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` remains historical release-provenance evidence only. P5 independently rebuilt and verified the final post-hardening production bytes as `aznet-theme-1.1.0.zip`, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; owner-approved publication and production deployment disposition are now complete, with fresh deployment evidence in run `{RUN}`.',
)
replace_once(
    p,
    'A GitHub release check on 08/09/2026 historically established that publication had not yet occurred at that checkpoint. That historical `PUBLICATION_PENDING` state was superseded on 16/09/2026 by owner-approved publication run `35052694111`; final production deployment remains a separate gate.',
    f'A GitHub release check on 08/09/2026 historically established that publication had not yet occurred at that checkpoint. That historical `PUBLICATION_PENDING` state was superseded on 16/09/2026 by owner-approved publication run `35052694111`; final production deployment was then separately owner-approved and verified by read-only run `{RUN}`.',
)
replace_once(
    p,
    '**Next:** P4 Real Pilot QA when pilot access is available. P5 publication/deployment remains gated and requires separate owner approval; no provider L5 certification is inferred from Core PASS.',
    '**Historical next at D-027 closure:** P4 Real Pilot QA when pilot access became available; P5 publication/deployment required later separate owner approvals. Those release-critical steps are now closed, while no provider L5 certification is inferred from Core PASS.',
)
replace_once(
    p,
    '`P2 Editorial Single-Post PASS -> P3 Editorial Listing/Search PASS -> P4-C Standalone Core PASS -> P4 Real Pilot QA PASS -> P1 cleanup PASS -> P5 technical candidate PASS -> P5 publication PASS -> production deployment GATED`',
    '`P2 Editorial Single-Post PASS -> P3 Editorial Listing/Search PASS -> P4-C Standalone Core PASS -> P4 Real Pilot QA PASS -> P1 cleanup PASS -> P5 technical candidate PASS -> P5 publication PASS -> production deployment PASS`',
)
replace_once(
    p,
    'P1/P4 pilot sign-off and P5 publication are closed; production deployment is the remaining explicit owner gate in this sequence.',
    'P1/P4 pilot sign-off, P5 publication and owner-approved production deployment verification are closed; the v1.1 release-critical path is complete.',
)
replace_once(p, '- final production deployment;\n', '')
replace_once(
    p,
    'Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration remains historical PASS evidence. D-027 source PR #63 and owner-approved implementation PR #64 remain canonical history. P4 public QA PR #66 and owner-approved authenticated QA PR #67 are merged; canonical `main@bf45a315e93fa22c441c81377f98ff34901d7ca5` passed exact-main run `35048609753`. P4 public + authenticated QA is retained and P1 cleanup is PASS after owner-approved deletion of only `aznet-theme-release-v1.0.0`; post-delete run `35050519817` closes final P4 pilot sign-off at the tested Theme-owned scope. P5 technical closure PR #71 was owner-approved and merged; the owner then separately approved publication as tag `v1.1.0` + GitHub Release, completed by run `35052694111`. Provider L5 certification and final production deployment remain unapproved/separate gates.',
    f'Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration remains historical PASS evidence. D-027 source PR #63 and owner-approved implementation PR #64 remain canonical history. P4 public QA PR #66 and owner-approved authenticated QA PR #67 are merged; canonical `main@bf45a315e93fa22c441c81377f98ff34901d7ca5` passed exact-main run `35048609753`. P4 public + authenticated QA is retained and P1 cleanup is PASS after owner-approved deletion of only `aznet-theme-release-v1.0.0`; post-delete run `35050519817` closes final P4 pilot sign-off at the tested Theme-owned scope. P5 technical closure PR #71 was owner-approved and merged; the owner then separately approved publication as tag `v1.1.0` + GitHub Release, completed by run `35052694111`, and separately approved the existing-package production deployment disposition verified by run `{RUN}`. Provider L5 certification remains separate/unproven.',
)

# EXEC map: align its live summary with the now-complete P5 deployment.
p = 'docs/source/AZT-EXEC-MAP.md'
replace_once(
    p,
    '> **Canonical-main checkpoint:** D-027/P4/P1 remain retained; owner-approved P5 technical closure PR #71 merged to `main@7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`. P5 technical run `35051428372` verifies the final deterministic `1.1.0` package, and owner-approved publication run `35052694111` published tag `v1.1.0` plus the matching GitHub Release asset. Optional integrations remain additive and ownership boundaries are unchanged.',
    f'> **Canonical-main checkpoint:** live `main` HEAD is resolved from GitHub at execution time. Stable release anchor `v1.1.0` is `{RELEASE}`, tree `{RELEASE_TREE}`; publication closure PR #72 merged at `{PR72}`, and restored post-noop checkpoint `{RESTORED}` has identical tree `{RESTORED_TREE}`. P5 technical run `35051428372`, publication run `35052694111`, and production read-only run `{RUN}` close the v1.1 release-critical path. Optional integrations remain additive and ownership boundaries are unchanged.',
)
replace_once(
    p,
    '**Completed execution:** `PR #58 canonical integration -> D-027 closure -> P4 public/authenticated PASS -> P1 cleanup PASS -> PR #70 -> P5 exact-main + deterministic package/lifecycle PASS run 35051428372 -> owner-approved PR #71 -> main 7dbbb0e8... -> owner-approved publication run 35052694111 -> annotated tag v1.1.0 + GitHub Release + exact asset digest PASS -> P5 PUBLICATION PASS / DEPLOYMENT GATED`.',
    f'**Completed execution:** `PR #58 canonical integration -> D-027 closure -> P4 public/authenticated PASS -> P1 cleanup PASS -> PR #70 -> P5 exact-main + deterministic package/lifecycle PASS run 35051428372 -> owner-approved PR #71 -> publication run 35052694111 -> annotated tag v1.1.0 + GitHub Release PASS -> owner-approved production deployment disposition -> read-only run {RUN} PASS -> P5 PASS`.',
)
replace_once(
    p,
    '| R6 Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 complete; exact-main + deterministic `1.1.0` R6 candidate verified; publication/deployment not implied |',
    '| R6 Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 retained technical provenance; R6 itself did not imply publication/deployment, both of which were later completed under P5 |',
)
replace_once(
    p,
    'Current canonical source baseline is `main@7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, Theme `1.1.0`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`. D-027 PR #63/#64 remain retained; P4 public QA PR #66, authenticated QA PR #67, P1 inspection PR #69, P1/P4 closure PR #70 and P5 technical closure PR #71 are merged. P5 run `35051428372` verified the final package; publication run `35052694111` published tag `v1.1.0` and the matching GitHub Release asset.',
    f'Live `main` HEAD is resolved from GitHub at execution time. Stable release anchor `v1.1.0` is `{RELEASE}`, tree `{RELEASE_TREE}`; publication closure PR #72 merged at `{PR72}` and restored post-noop checkpoint `{RESTORED}` has identical tree `{RESTORED_TREE}`. Theme metadata is `1.1.0`. P5 run `35051428372` verified the final package, publication run `35052694111` published tag/Release, and production read-only run `{RUN}` verified the owner-approved deployment disposition.',
)
replace_once(
    p,
    '**Next:** resume P4 pilot when access exists. P5 remains gated until P4 PASS and separate publication/deployment approval.',
    '**Historical next at D-027 closure:** resume P4 pilot when access existed, then seek separate P5 publication/deployment approvals. Those release-critical steps are now closed.',
)

# Manifest: top summary and historical P5 paragraph must not describe deployment as pending.
p = 'docs/source/SOURCE_MANIFEST.md'
replace_once(
    p,
    'Canonical source is reconciled through the completed v1.1 R6 program, P2/P3 editorial hardening, PR #58 Homepage/Law01/Provisioning integration, D-027 Standalone Core closure, P4/P1 pilot sign-off, P5 final-candidate technical verification, and owner-approved v1.1.0 Git tag + GitHub Release publication.',
    'Canonical source is reconciled through the completed v1.1 R6 program, P2/P3 editorial hardening, PR #58 Homepage/Law01/Provisioning integration, D-027 Standalone Core closure, P4/P1 pilot sign-off, P5 final-candidate technical verification, owner-approved v1.1.0 Git tag + GitHub Release publication, and owner-approved production deployment verification.',
)
replace_once(
    p,
    'P5 final-candidate technical verification run `35051428372` explicitly checked out canonical production bytes at `main@f0b2d581b16a663e4b422234e6ac15568bcc7985`, tree `f21884f5989239858261721c5002c5158d6d9be0`. Exact-main/core verification and clean WordPress `6.9` zero-plugin runtime/browser gates passed. The final package `aznet-theme-1.1.0.zip` was built twice byte-identically, exact-matched 121 production files, linted 93 packaged PHP files, passed clean WordPress/browser gates, preserved WordPress-owned continuity through `twentytwentyfive` switch-away and passed again after switching back. ZIP SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; exact-main artifact `10429087315`; final-package artifact `10428893463`. Owner-approved PR #71 then merged technical source closure to `main@7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78` without production Theme byte changes. Owner-approved publication run `35052694111` published annotated tag `v1.1.0` and GitHub Release `389622362`; release asset `567100593` has GitHub digest `sha256:72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`, and publication evidence artifact `10428918492` has digest `sha256:544895a168e12b7260e086b14f85ecfb3ae4505fcc600feff7d27bbbd6f88814`. P5 state is PUBLICATION PASS / DEPLOYMENT GATED. Evidence: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md` and `docs/evidence/P5_PUBLICATION_20260916.md`.',
    f'P5 final-candidate technical verification run `35051428372` explicitly checked out canonical production bytes at `main@f0b2d581b16a663e4b422234e6ac15568bcc7985`, tree `f21884f5989239858261721c5002c5158d6d9be0`. Exact-main/core verification and clean WordPress `6.9` zero-plugin runtime/browser gates passed. The final package `aznet-theme-1.1.0.zip` was built twice byte-identically, exact-matched 121 production files, linted 93 packaged PHP files, passed clean WordPress/browser gates, preserved WordPress-owned continuity through `twentytwentyfive` switch-away and passed again after switching back. ZIP SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; exact-main artifact `10429087315`; final-package artifact `10428893463`. Owner-approved PR #71 then merged technical source closure without production Theme byte changes. Owner-approved publication run `35052694111` published annotated tag `v1.1.0` and GitHub Release `389622362`; release asset `567100593` has the verified package digest. Owner-approved production deployment disposition was then verified by fresh read-only run `{RUN}`, artifact `{ARTIFACT}` (`{DIGEST}`). P5 publication + production deployment are PASS at the verified scope. Evidence: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md`, `docs/evidence/P5_PUBLICATION_20260916.md`, and `docs/evidence/P5_PRODUCTION_DEPLOYMENT_20260916.md`.',
)
