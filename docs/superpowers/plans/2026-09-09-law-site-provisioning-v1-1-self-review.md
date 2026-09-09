# Law Site Provisioning v1.1 — Plan Self-Review Clarifications

This file is normative for execution together with `2026-09-09-law-site-provisioning-v1-1.md`. It closes two ambiguities found during the required plan self-review; it does not expand product scope.

## 1. Explicit Step-2 selection payload

The v1.1 admin handler must sanitize and pass this bounded selection shape into `provisioning_build_plan()`:

```php
$selections['starter'] = [
    'create_missing_editorial' => true,
    'import_media'             => true,
    'publish_on_new_site'      => true,
    'discourage_indexing'      => true,
];
```

Rules:

- `publish_on_new_site=true` is effective only in `NEW_OR_MOSTLY_EMPTY` mode.
- If `discourage_indexing=false`, generated starter Posts are Draft even when `publish_on_new_site=true`.
- `EXISTING_ACTIVE` ignores whole-site indexing consent and keeps new starter Posts Draft in v1.1 because no accepted per-Post SEO contract exists.
- The Step-3 immutable plan contains the final explicit operations; this selection payload is never authoritative by itself.

## 2. Persist the minimal successful receipt needed for later indexing cleanup

The current v1 admin result transient is time-limited, so it cannot prove ownership days later when the user finishes editing. Task 6 must therefore persist one bounded Theme-owned setup-provenance record after a successful v1.1 run:

```php
const PROVISIONING_LAST_SUCCESS_OPTION = 'aznet_theme_provisioning_last_success';

[
    'schema_version' => 1,
    'blueprint' => 'law01-v1-1',
    'run_id' => '<opaque run id>',
    'pre_blog_public' => 1,
    'search_visibility_changed_by_run' => true,
    'created_post_ids' => [ /* integer IDs only */ ],
    'created_attachment_ids' => [ /* integer IDs only */ ],
]
```

Implement focused helpers in `inc/theme/provisioning-provenance.php`:

```php
function provisioning_store_last_success( array $receipt ): void;
function provisioning_last_success(): array;
```

Use the Theme-owned WordPress option `aznet_theme_provisioning_last_success`; do not copy Post bodies, taxonomy semantics, SEO metadata or provider/domain data into it.

The explicit restore-index action may set `blog_public` back to `1` only when this persisted record proves all of the following:

```text
blueprint = law01-v1-1
pre_blog_public = 1
search_visibility_changed_by_run = true
current blog_public = 0
```

After a successful explicit restore, update the record so `search_visibility_changed_by_run=false`; do not delete WordPress content/media and do not infer ownership when the record is absent or malformed.

## 3. Task-6 file/interface correction

Task 6 also modifies `inc/theme/provisioning-provenance.php` because starter fingerprints and the persisted success receipt live in that bounded provisioning-provenance module.
