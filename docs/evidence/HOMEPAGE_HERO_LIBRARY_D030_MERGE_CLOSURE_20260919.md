# D-030 Homepage Hero Library — canonical merge closure

**Date:** 19/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`  
**Merged PR:** #156  
**Final verified PR head:** `c8a14556706a49bb380669b72bffd5b6f0cd98d5`  
**Canonical merge:** `main@01ddbff1843dfd8a0e1cc276be34144f35aeba35`  
**Theme metadata:** `1.3.11`

## Result

The product owner approved canonical merge of PR #156.

PR #156 merged successfully to `main@01ddbff1843dfd8a0e1cc276be34144f35aeba35`.

Git comparison from final verified PR head `c8a14556...` to the merge commit reports one merge commit ahead and **zero changed files**. The canonical merge therefore preserves the exact verified production/test/source tree.

D-030 is implemented as a Theme-owned presentation layer over one WordPress-owned Core-block synced Hero (`wp_block`):

- WordPress owns Hero copy, blocks and media;
- Theme stores only the typed `homepage_hero_block` reference and allow-listed `homepage_hero_variant`;
- initialization is explicit, nonce/capability protected and draft-first;
- the public Hero does not switch to the new synced block until that block is published;
- visual-variant changes reuse the same WordPress content and do not rewrite Hero copy;
- legacy Hero Page and Site/Front Page projection remain compatibility fallbacks;
- no parallel Theme Hero content store, proprietary block type, provider private read or domain ownership transfer is introduced.

## Final-head verification

Final PR head `c8a14556706a49bb380669b72bffd5b6f0cd98d5` completed **37/37 triggered workflows SUCCESS, 0 failure**.

Retained release-critical examples:

- `V1 Core Pull Request CI` run `35422400223` — SUCCESS;
- `R5 Control Center Browser Quality` run `35422400232` — SUCCESS;
- `Homepage Composer Law 01 Browser Quality` run `35422400293` — SUCCESS;
- `F Homepage Native Regression Package` run `35422400276` — SUCCESS;
- `X6 Cross-surface Release Closure` run `35422400172` — SUCCESS;
- `Y5 Client Delivery Release` run `35422400222` — SUCCESS;
- `Release Version Consistency Gate` run `35422400149` — SUCCESS.

The R5 browser gate retains the previously documented WordPress-core `WP_Query::rewind_posts()` warning as a separately recorded known observation rather than relabeling it as a clean-log result. Unexpected PHP fatal/warning/parse/uncaught markers remain blocking.

## 1.3.11 package evidence

Y5 run `35422400222` produced and verified the deterministic candidate:

- `aznet-theme-1.3.11.zip`;
- **147 production files**;
- **110 packaged PHP files lint PASS**;
- SHA-256 `723f502f81033136410a49ee1196ff098837b20edbdadaf754cadf522efe9fb0`;
- promoted-package artifact `10576859974`, artifact digest `sha256:856195e951cfbc73ba6a25bf339e92f985b51a542d1229290e33a1eec3ebfc38`.

This is candidate/package evidence only. It does not publish a Git tag or GitHub Release.

## Fresh exact-main verification

Push-triggered `V1 Exact Main Verification` run `35422599658` completed **SUCCESS** on exact merge SHA `01ddbff1843dfd8a0e1cc276be34144f35aeba35`.

Both jobs passed:

- `static-contracts`;
- `clean-runtime-browser` on WordPress 6.9 / PHP 8.1 support-floor verification.

Artifacts:

- static/contracts: `10577094776`, digest `sha256:ebfcd19fc6e967d18fe8e85a92139730b27ee404e7aeeceff3c1462e6c87df29`;
- clean runtime/browser: `10578340381`, digest `sha256:51b9d455b7c43a2771903921f0e53c0bbe860e97c8461003da92c7aa9245d09b`.

D-030 is therefore **PASS / MERGED TO CANONICAL MAIN** at the verified L1-L4 core scope plus deterministic candidate-package evidence.

## Release boundary

- Canonical repository Theme metadata: `1.3.11`.
- Published GitHub Release remains `v1.3.10`.
- Verified production deployment remains historical `v1.3.0`.
- No `v1.3.11` tag, GitHub Release or production deployment is authorized or implied by this merge.
- RootProfile authoritative Team projection remains separately blocked under issue #112.

## Rollback

Code rollback is the canonical Git revert path for PR #156 / merge commit `01ddbff18...`.

Content rollback is non-destructive: the WordPress-owned synced Hero can remain Draft or be returned to Draft, allowing the retained legacy/fallback Hero source to resume. No Theme-owned content migration needs reversing.

## NEXT

Create and merge the source/evidence synchronization PR for this closure after explicit owner approval. Publication of `v1.3.11` and any production deployment remain separate gates.
