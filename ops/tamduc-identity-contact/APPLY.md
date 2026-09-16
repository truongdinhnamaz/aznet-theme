# Tâm Đức identity/contact one-shot apply

Owner-approved authoritative inputs for `https://tamduchanoi.aznet.vn/`:

- Official logo: `logo-official.jpg`, resized/compressed from the logo supplied and explicitly approved by the owner in the current project session.
- Official phone: `024 3716 4123`.
- Normalized phone link: `tel:+842437164123`.

Boundaries:

- WordPress-owned Media / Custom Logo / Menu surfaces only.
- No Theme production PHP/CSS/JS changes.
- No RootProfile, ConvertFlow, WooCommerce, private option/meta/CPT/table access.
- Exact-before preconditions and rollback-on-error are enforced by the browser harness.
- Header utility assignment occurs only if the active Theme actually registers the public WordPress menu location; otherwise display remains blocked by deployed presentation capability.

Retry history:

1. First runtime attempt rolled back cleanly after WordPress exposed the menu save control in a hidden header toolbar; regression now uses native activation.
2. Second runtime attempt rolled back logo/media cleanly and exposed a separate wait bug: the create screen already uses `menu=0`, which falsely satisfied the old numeric URL predicate. The regression now waits for an explicit nonzero WordPress menu id before continuing.
3. A partial menu from the interrupted attempt is recovered idempotently only when it is empty or contains exactly the approved phone link; any unexpected content aborts before mutation.
4. The next attempt proved the custom-logo save path but the public assertion watched WordPress core's default `img.custom-logo` class. AZnet Theme renders the same WordPress-owned logo as `img.aznet-theme-site-header__logo`; the regression now verifies the actual Theme presentation selector while retaining the core selector as a compatibility fallback.

Owner approval to execute the bounded production mutation was reconfirmed on 2026-09-16.
