# About below-Hero — final focus-regression addendum

This adds fresh evidence to `ABOUT_BELOW_HERO_PARITY_20260920.md`; it does not rewrite the earlier checkpoint.

Final code commit: `0a58a622753eecbc7ff8aeda4b4d7b54f8a9eddb`, draft PR #179, branch `fix/about-below-hero-parity-20260920`.

Self-review found that a focus outline using `currentColor` could become white against a light surrounding band or dark against a dark surrounding band. A regression measured the outline color against the surrounding solid background, not merely whether an outline existed.

- RED: two affected CTA styles, repeated across ten viewport/container combinations = 20 failed conditions.
- Minimal GREEN: burgundy focus outline on light bands; white focus outline on the dark intro/CTA bands.
- Final isolated replay: all ten combinations pass the full defined layout, normal-text contrast, target-size and keyboard-focus contrast checks.
- Final stylesheet SHA-256: `5bd9693b7593e6db815f967bb643992ce213e5f6c4be78992bb0603467be09bd`.
- Final retained Git blob: `df025d65598e1e68ff5c0e63d635e5b060e1acab`, verified against the locally tested bytes.
- Final result JSON SHA-256: `5bb7e6e21aff2587d193c20d08fd2d8a1044404b9e270da30eb93ddf03ee1a1d`.
- Focus fix was applied to the same draft stylesheet.
- Fresh post-fix WPVibe audits: mobile Accessibility100 / BestPractices100; desktop Accessibility100 / BestPractices100.

The external browser network blocker and scope limits remain unchanged. Local replay and Lighthouse do not constitute exact full-site visual approval, independent review, L5 integration or L6 release. No live Page content or shared Theme publication was performed.

Full reproduction files and before/after screenshots are retained in the conversation QA archive. Rollback remains removal of the new guarded enqueue only. Exact Next remains independent complete-preview visual verification when browser access is available, before publication.
