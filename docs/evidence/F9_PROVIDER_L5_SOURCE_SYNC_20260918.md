# F9 Provider L5 Source Sync — 18/09/2026

## Scope

Docs/state-only closure for AZnet Theme after the separately approved ConvertFlow provider L5 certification completed and the provider candidate became canonical. No production AZnet Theme code changes.

## Canonical provider evidence

- ConvertFlow PR #35 merged exact verified head `7bffb2326d4d8e680fd535419157c02657c29b6c` to `truongdinhnamaz/convert-flow` `main@53b17e5f7fea77414701a8b8b8ac630fee1f8ce0`.
- Compare from exact verified head to the merge commit reports no changed files; canonical merge tree preserves the verified candidate bytes.
- F9-A workflow `35262104796`: SUCCESS — Product Journey narrow-host containment, horizontal overflow `0`.
- F9-B workflow `35262104903`: SUCCESS — P5.244 build identity.
- F9-C workflow `35262104962`: SUCCESS — deterministic Core P5.244 + Pro P5.164 package/source identity.
- F9-D workflow `35262104841`: SUCCESS — AZnet Theme -> Twenty Twenty-Five -> AZnet Theme with ConvertFlow module/Homepage state byte-stable.
- F9-E workflow `35262104970`: SUCCESS — AZnet public token producer -> alternate-theme scoped fail-soft fallback -> AZnet token restoration, with runtime error gate.
- Retained C1 workflow `35262104837`: SUCCESS.
- Core package SHA-256 `0ccede3ee740e85acd3cb1b70ad23b25ea88882a0c3d5e4425e43a876557a084`.
- Exact Pro P5.164 package SHA-256 `c2d2c54ab6897987db484fea7138b13103c8e77785a0c9213970e4f0284a4e44`.
- Package artifact `10509242591`, digest `sha256:64f85878b5f922112832c4ba040967dd7c5ea92a80508c394252e3b0d556265c`.
- F9-E artifact `10508647965`, digest `sha256:d883a1b809382b0239225e600331dce158f383610ef0da99ecdc851667be598e`.
- Exact integration environment: WordPress 6.9, PHP 8.1, AZnet Theme `v1.3.0` reference producer, ConvertFlow Core P5.244 + Pro P5.164, and Twenty Twenty-Five as alternate compatible block theme.

## Provider source authority

- ConvertFlow CF-03 v1.29 source-state sync PR #38 merged to canonical `main@e91c33765e9af0cd23948e10b21e86bd380e90e5`.
- CF-03 records Core P5.244 + Pro P5.164 as the active verified QA/development candidate.
- ConvertFlow released baseline remains Core P5.207 + Pro P5.160.
- CF-07 remains v1.20 because F9 did not change architecture, ownership, dependency semantics or Theme Integration Contract v1.

## Theme source change

- AZT-04 records F9/provider L5 PASS at the exact tested scope and retains AZnet Theme v1.3.0 publication/production deployment state unchanged.
- AZT-EXEC-MAP mirrors the same exact L5 evidence as derived execution state.
- SOURCE_MANIFEST advances the active source-version pointers accordingly.
- Historical F8 P5.242/P5.164 L3-L4 evidence remains retained provenance; it is not rewritten as F9 evidence.

## Boundary

This checkpoint proves only the exact provider/theme L5 certification boundary above: public Theme Integration Contract behavior, ownership separation, theme-switch provider-state continuity, scoped fail-soft fallback and token restoration. It does not promote ConvertFlow released baseline, perform ConvertFlow release/deployment, claim Product v1 completion, or authorize a new Theme milestone/public contract/provider certification slice.

## Next

No new Theme milestone, architecture/public-contract change or additional optional-provider certification starts without a separately approved slice.