# P4 Real Pilot QA — Checkpoint 2026-09-08

**Status:** IN PROGRESS  
**Pilot:** Tâm Đức Hà Nội  
**Canonical source baseline:** `main@08b74209013fc7c71d2a6d98ea0e457ccfd336d4`  
**Pilot candidate:** `aznet-theme-1.1.0-p4-pilot.zip`  
**Candidate SHA-256:** `6a87a314f02a5f98c1cf5e68e075ed5c239203a7540a0b9adb336c608989368e`

## 1. Proven pilot environment

Manual System Health evidence from the real pilot after in-place package replacement shows:

- WordPress `7.1`;
- PHP `8.4.24`;
- active Theme `AZnet Theme` version `1.1.0`;
- `visual_preset = editorial`;
- `header_preset = standard`;
- `woo_catalog_preset = grid`;
- Primary Menu present;
- WooCommerce absent;
- RootProfile v1 public capability present;
- RootProfile v2 public capability present;
- RootProfile current-surface capability absent;
- ConvertFlow remains `unknown` rather than being privately inferred.

This checkpoint records only what the real pilot System Health surface proves. It does not claim provider semantics beyond those public/read-only capability indicators.

## 2. Stable Theme identity / in-place update

The P4 pilot package was uploaded through WordPress and replaced the existing `aznet-theme` installation in place. The reproduced result did **not** create another AZnet Theme entry.

The owner then performed a focused settings-continuity reproduction:

1. set `visual_preset` to `editorial`;
2. confirmed System Health reported `editorial`;
3. uploaded the same P4 candidate again and used WordPress Replace;
4. confirmed System Health still reported `visual_preset = editorial`.

Result: **PASS at the real-pilot update-continuity checkpoint** for the tested Theme-owned presentation setting and stable Theme identity.

This closes the earlier UNKNOWN caused by one post-replacement observation of `default`; that observation was not reproducible as an in-place update defect.

## 3. Additional continuity observed

After the tested replacement:

- Theme remains active as version `1.1.0`;
- Primary Menu remains present;
- RootProfile v1/v2 public capability detection remains present.

No code change is justified from the earlier `default` observation because a Theme-owned settings-loss defect was not reproduced.

## 4. P4 boundary still open

This checkpoint does **not** close P4. The source-defined real-pilot matrix still requires evidence for:

- homepage;
- long single Post;
- Post with long title and long taxonomy labels;
- Page;
- category/archive;
- search results and no-results;
- 404;
- RootProfile profile/contact surfaces when the public contract is available;
- responsive desktop/tablet/mobile behavior;
- horizontal overflow;
- keyboard/focus/landmarks/headings/labels;
- Vietnamese diacritics, long legal citations and tables;
- responsive media/table behavior;
- Rank Math compatibility smoke without Theme metadata duplication;
- Theme-caused PHP fatal/warning/uncaught checks;
- measured asset/performance evidence on actual pilot content;
- rollback/theme-switch evidence as required before P4 exit.

Woo surfaces are not part of this pilot matrix while WooCommerce remains absent.

## 5. Destructive site-ops boundary

Inactive legacy Theme deletion remains a separate P1 site-operations action. This P4 checkpoint does not automate or perform deletion.

Tag, GitHub Release and final production deployment remain P5 approval gates.

## 6. Exact next

Capture and review the real pilot homepage presentation first, then advance route-by-route through the P4 matrix. Any defect must be reproduced and reduced to the shallowest Theme-owned layer before production code changes are made.
