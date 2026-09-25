# Team Add Member Immediate Publication — 2026-09-25

**Decision:** D-039  
**Candidate:** AZnet Theme 1.3.47  
**Branch:** `work/homepage-map-v1-3-38-baseline`

## Change

The bounded Homepage Team **Thêm nhân sự** action now creates the validated direct child WordPress Page with `post_status=publish` immediately.

This supersedes only the D-038 draft-first Add Member detail. WordPress remains the data owner; manual draft/private Team child Pages remain supported and excluded from public Team resolvers.

## TDD evidence

RED on the exact 1.3.46 candidate:

```text
FAIL: Add Member still does not publish immediately.
```

The initial contract was then tightened to scope only `homepage_team_member_insert_data()` after it correctly exposed an unrelated draft-copy helper in the same file.

GREEN on the 1.3.47 candidate:

```text
PASS: Team Add Member publishes immediately and UI matches the behavior.
```

## Candidate verification

- production PHP lint: 136 files PASS;
- `assets/js/admin/homepage-authoring.js`: syntax PASS;
- Theme header version: 1.3.47;
- `AZNET_THEME_VERSION`: 1.3.47;
- focused immediate-publication contract: PASS;
- local package production files `functions.php`, `inc/admin/homepage-authoring.php`, `inc/admin/homepage.php`, and `style.css` have Git blob SHA values identical to canonical branch head;
- ZIP integrity: PASS;
- package SHA-256: `62bf625bde8ca454bd57877606dde62229deeb49c3383188ad1ac85452938fe2`.

## QA boundary

The owner previously authorized candidate packaging despite GitHub L3/L4 runners being unavailable. This 1.3.47 artifact inherits that candidate-only status; no production deployment or main merge is claimed.
