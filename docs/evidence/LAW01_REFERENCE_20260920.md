# Law 01 reference implementation checkpoint - 2026-09-20

## Scope and provenance

Continue PR #176 on `work/law01-demo-match-20260920`, parent `aba549a54877d21c38e3d83bf77094ad8b40d9df`. Canonical main is not merged or deployed by this checkpoint.

Baseline production bytes were recovered from exact-package artifact 10598572656 (workflow run 35489295419). Its recorded checkout is PR merge-test commit `55dae49ea5c57090b091372c48668346b551294f`. Before replacement, the three modified existing files were individually compared with the parent branch using Git blob SHA: bootstrap `a675383bf2456055cb50076a3d31bac8d682b49e`, site-header `5c41beb614afa5785ab92934c7dfd5338b5c09d1`, latest `36a69af3ec9b12d455fdf66ae1206e6761a7f4cf`. All matched. Existing unrelated files are retained through the parent Git tree, not overwritten from the package.

## Delivered delta

- Source-backed slim topbar: native WordPress description, header-utility menu and optional existing footer-social menu. No invented office hours, social links or contact facts.
- Scoped serif typography, red brand/black headline hierarchy, subordinate supporting copy, compact section spacing, About/Team fractional columns, landscape article crop, footer colors and responsive styles.
- Real keyboard-accessible article read-more links with escaped URLs and labels.
- A surface-scoped, content-fingerprinted stylesheet loader. Other homepage variants and non-homepage surfaces retain existing behavior.
- Existing six native service Pages reordered explicitly using their WordPress `menu_order`, aligning the pilot's existing position-based decorative icons. No slug/title inference or private meta was introduced.

## Verification

Fresh local production PHP lint: 113 files, zero failures. Three actual-template/loader contracts passed after recorded expected RED failures. Component-browser fixtures passed at widths 1440, 1024, 390 and 320. Removing the reference CSS reproduces missing serif hierarchy, wrong image crop and 1024px overflow. These fixtures are NOT live WordPress screenshots and do NOT prove pixel parity.

Authenticated WPVibe reads confirmed the new stylesheet is enqueued by WordPress, three article CTA anchors render, the service ordering is applied and the topbar consumes real native sources. Final preview audits after the topbar change returned Accessibility 100/100 and Best Practices 100/100 on both desktop and mobile. These automated category scores are not a complete accessibility certification or a pixel-parity score.

Full core verification is requested by `.github/workflows/law01-reference-regression.yml`; completion must be read from the new run, not inherited from the parent. Parent history included failures X2 Search/404/Empty States (35489295406) and X5 Native Form Controls (35489295346); do not describe the parent as all-green.

## Content, image and visual gaps

The mapped Team Page has no legitimate member entries or portraits. Generated names, roles and the mockup's 10+/500+/95% claims are not production data. Existing office address is retained instead of copying the mockup address. Hero/editorial media still differ from the supplied reference. Process, FAQ and Final CTA remain intact from the accepted implementation; no prior section was silently removed to mimic the screenshot.

The local browser cannot navigate to the pilot from this environment (administrator network restriction). This limitation is not evidence that the website is down. Actual pilot screenshot comparison, artwork replacement, integration closure and release remain OPEN.

## Rollback / recovery

Draft only: remove the new bootstrap require/hook and header template-part call, restore the previous `latest.php`, then remove the unused new reference PHP/CSS/topbar files. Do not discard the entire existing draft. For the six native service Pages 174..179, restore `menu_order` to 0 using WordPress APIs. This ordering change is shared native content, not draft-isolated.

Never publish the draft or merge main without its separate approval/backup gate. The WPVibe preview token is intentionally not committed.

## Exact next

Review fresh CI for this commit and resolve any regression introduced by this delta before continuing pilot artwork and real-browser visual comparison. Preserve the current draft and all unrelated prior PASS work.
