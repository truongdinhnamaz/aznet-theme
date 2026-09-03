# W8 Fail-Soft Note

The Theme bridge is a Theme-owned CSS custom-property projection with no runtime call into ConvertFlow. If ConvertFlow is absent, the variables remain inert. If ConvertFlow is present, its own public integration stylesheet consumes them inside ConvertFlow-owned surfaces and keeps provider-owned fallbacks. This avoids a PHP hard dependency and preserves Theme/provider independence.
