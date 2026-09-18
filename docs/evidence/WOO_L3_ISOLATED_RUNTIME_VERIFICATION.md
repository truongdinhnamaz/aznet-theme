# Woo L3 Isolated Runtime Verification

## Scope

- Product: AZnet Theme only.
- Layer claimed: **L3 Runtime** for the WooCommerce presentation surface classifier and surface-specific Theme asset loading.
- Runtime type: disposable GitHub-hosted WordPress + WooCommerce environment.
- No deployment, activation, or mutation was performed on `remquocanh.vn` or any other external website.
- No RootProfile or ConvertFlow source was added or modified.
- No AZnet Theme production path was changed by this verification branch.

## Provenance

- Canonical repository: `truongdinhnamaz/aznet-theme`
- Production baseline: `main@6235e1462288497c7bbbf5ff9d746aed6d4f1c8b`
- Verification branch: `test/woo-l3-runtime`
- Verified test-infrastructure candidate head: `ae92e9871dc38f08906be7743f0a9319b900f86b`
- Draft PR: #10 `test: run Woo L3 isolated runtime smoke`
- GitHub Actions workflow: `Woo L3 Runtime Smoke`
- Closure run ID: `33730938725`
- Closure job ID: `100570479339`
- Closure conclusion: `success`

## Runtime matrix observed

The closure run recorded:

```text
WordPress=7.1
PHP=8.1.34
WooCommerce=11.0.1
Theme=0.1.0-alpha.7
```

The workflow explicitly rejects WordPress versions lower than 6.9.

## Real HTTP smoke result

The closure job started a real WordPress HTTP server at `127.0.0.1:8080`, created disposable Woo fixtures, established a real Woo cart session before Checkout, and returned these fresh assertions:

```text
PASS: product -> aznet-theme-woocommerce-product-css
PASS: archive -> aznet-theme-woocommerce-archive-css
PASS: cart -> aznet-theme-woocommerce-cart-css
PASS: checkout -> aznet-theme-woocommerce-checkout-css
PASS: account -> aznet-theme-woocommerce-account-css
PASS: generic page -> aznet-theme-generic-content-css only
PASS: Woo L3 real HTTP surface/asset smoke
```

Artifact inspection independently confirmed:

- `product.html` contains only the Theme Product surface stylesheet among the tested surface styles.
- `archive.html` contains only the Theme Archive surface stylesheet.
- `cart.html` contains only the Theme Cart surface stylesheet.
- `checkout.html` contains only the Theme Checkout surface stylesheet.
- `account.html` contains only the Theme My Account surface stylesheet.
- `generic.html` contains the generic-content stylesheet and none of the tested Woo surface styles.

## Artifact

- Artifact ID: `9883832244`
- Artifact name: `woo-l3-runtime-evidence`
- Uploaded ZIP size: 101476 bytes
- GitHub Actions reported SHA-256: `2dddab3ac1fce3f86636203ff1cbb7a4202802fa2b608bcf00c61a89f257773e`
- Contents include `runtime-report.txt`, `runtime-ids.env`, `wp-server.log`, and captured HTML for Product, Archive, Cart, Checkout, My Account and Generic surfaces.

## Debugging provenance

Two earlier failures were diagnosed as test-harness/runtime-state defects rather than Theme defects:

1. The initial archive probe used `/?post_type=product`, which WordPress 7.1 resolved as the home blog. The smoke was corrected to use the Woo-registered Shop page ID.
2. Checkout redirected to Cart while the cart was empty. The smoke was corrected to create a real Woo add-to-cart session and reuse its cookies for Checkout.

A later successful smoke exposed incomplete version-report instrumentation caused by literal quotes in the WP-CLI `--path` value. The report capture was corrected and the final closure run repeated successfully.

No production Theme code was changed to make any of these checks pass.

## PASS

**Woo L3 Runtime PASS** for the verified scope above:

- real WordPress runtime;
- real WooCommerce runtime;
- AZnet Theme active;
- normalized Product / Archive / Cart / Checkout / My Account presentation asset routing;
- generic-content exclusion on those Woo surfaces;
- generic-content loading on a normal WordPress Page.

## BLOCKED / UNKNOWN

This evidence does **not** claim:

- L4 browser visual correctness, responsive rendering, interaction quality, or accessibility;
- L5 coexistence/integration with ConvertFlow or other providers;
- E5-C RootProfile runtime/integration PASS;
- L6 release/production readiness;
- anything about `remquocanh.vn` runtime behavior.

E5-C remains separately blocked on the external RootProfile current-surface contract.

## Next

Exact next Theme-owned verification layer is **Woo L4 Browser / Visual / Accessibility** against an isolated real WordPress + WooCommerce runtime. L4 must be verified separately and must not be inferred from this L3 PASS.
