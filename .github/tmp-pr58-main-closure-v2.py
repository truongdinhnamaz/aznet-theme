from pathlib import Path

src_path = Path('.github/tmp-pr58-main-closure.py')
src = src_path.read_text()
old = '''replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "PR #58 is now the sole canonical-main integration gate. No main merge, exact-main PASS, L5 provider certification, Git tag, GitHub Release or production deployment is implied by PR-head evidence.",
    f"PR #58 canonical-main integration gate is cleared at `main@{MAIN}`, with D-022 exact-main verification `{RUN}` PASS. L5 provider certification, Git tag, GitHub Release, destructive pilot cleanup and final production deployment remain separate gates.",
)
'''
new = '''replace_once(
    "docs/source/AZT-04-roadmap-qa-decisions.md",
    "Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. P4 pilot QA execution is now approved. None of these approvals include Git tag/GitHub Release or final production deployment.",
    f"Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration is cleared at `main@{MAIN}` and D-022 exact-main run `{RUN}` is PASS. P4 pilot QA execution remains approved. None of these approvals include Git tag/GitHub Release, destructive duplicate-theme cleanup, L5 provider certification or final production deployment.",
)
'''
if old not in src:
    raise SystemExit('expected obsolete AZT-04 gate replacement block missing')
src = src.replace(old, new, 1)
code = compile(src, str(src_path), 'exec')
exec(code, {'__name__': '__main__'})
