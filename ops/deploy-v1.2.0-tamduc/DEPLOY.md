# Tam Duc production deployment v1.2.0

Owner approval: explicit production deployment approval in project session after v1.2.0 publication and PR #91 source closure.

Target: https://tamduchanoi.aznet.vn
Expected before state: active AZnet Theme 1.1.0, or idempotently 1.2.0 if an external actor already completed the exact deployment.
Target package: published aznet-theme-1.2.0.zip, SHA-256 cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798.
Rollback package: published aznet-theme-1.1.0.zip, SHA-256 72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258.

The workflow uses WordPress core theme upload/replace UI only. It verifies the previously approved WordPress-owned phone menu before deployment, assigns it to the newly available header-utility location only when the exact approved menu is present, then verifies Theme 1.2.0 and desktop/mobile public output. On a post-replacement failure it attempts to clear the run-created header-utility assignment and restore the exact published v1.1.0 package.

No provider storage, domain data, provisioning, content mutation, plugin mutation, SEO mutation or destructive theme deletion is in scope.
