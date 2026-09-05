What
Escape post titles and permalinks in templates to prevent potential XSS; added repository escaping audit.

Files changed
- index.php
- page.php
- single.php
- template-parts/content/card.php
- docs/AUDIT_ESCAPING_REPORT.md
- docs/AUDIT_ESCAPING_AUDIT.csv

Checklist
- [ ] php -l passed for changed files
- [ ] PHPCS / PHPStan passed (if configured)
- [ ] Manual smoke test (home, single post, list pages)
- [ ] Merge to main and create v1.0.0 release

Notes
- No breaking changes expected. the_content() intentionally left unchanged (outputs HTML). See docs/AUDIT_ESCAPING_REPORT.md for details and rationale.
