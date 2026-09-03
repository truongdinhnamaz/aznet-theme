# W8 Test Harness Provenance

Date: 2026-09-03
Scope: test-only provenance for W8 retained regression closure.

The W8 production candidate ends at commit `615a35b576e28ee22aacc9524fcc37bd5f1e0a29`. Subsequent W8 work-branch commits before evidence changed only test/evidence/plan files.

Retained ownership-test corrections:

- W6: `7c92cb1df0ff1a5c11c87029cdafcbd8fac5ecd9`
- W2: `f4979e5f1a23df4fa9521b5d95a3bc6ebfdbd103`
- W3: `174591010a8a48077adcea73ed5b40d4400ec246`
- W4: `9b26b7a64f81a3ded1976578e83605a71350613d`
- W5: `9adc990c3eb20474f4bf1ebe213d4e874cd0d610`

These changes do not relax the surface ownership invariant. They remove an obsolete assumption that the shared Theme asset registry can never contain a later public integration. Each Woo surface still rejects ConvertFlow coupling inside its own PHP/CSS and retained private/domain prohibitions remain.

Fresh independent test branch:

- `test/w8-convertflow-coexistence-v2`
- head `3e119fe74b8a0f4953a471d6d6831ab5bd2ef57d`
- workflow run `33761153314`
- job `100667509616`
- conclusion `success`

The workflow file is intentionally test-branch-only and is not part of the W8 production branch.
