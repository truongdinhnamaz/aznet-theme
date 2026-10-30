"""Current-commit native bundle includes new Theme assets without importing test/worktree files."""
import importlib.util
import json
from pathlib import Path
import subprocess
import tempfile

root = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('wellness_native', root / 'tests/browser/wellness01-native-local.py')
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)
assert hasattr(module, 'production_inventory'), 'native runner has no current-commit inventory resolver; new Page CSS is omitted'

with tempfile.TemporaryDirectory(prefix='wellness-inventory-') as folder:
    repo = Path(folder)

    def git(*args):
        return subprocess.check_output(['git', '-c', 'user.name=QA Fixture', '-c', 'user.email=qa@example.test', *args], cwd=repo, text=True).strip()

    def write(path, content):
        p = repo / path
        p.parent.mkdir(parents=True, exist_ok=True)
        p.write_text(content)

    git('init', '-q')
    write('functions.php', '<?php // fixture\n')
    write('ops/approved.json', '{}\n')
    write('docs/evidence/wellness01/2026-10-29-v06-source-manifest.json', json.dumps({'production_files': {'functions.php': '', 'ops/approved.json': ''}}))
    write('tests/not-production.php', '<?php // test only\n')
    write('scripts/build-release-package.py', (root / 'scripts/build-release-package.py').read_text())
    git('add', '.')
    git('commit', '-qm', 'fixture initial source')
    before = git('rev-parse', 'HEAD')
    write('assets/css/components/page-wellness-01.css', '/* new asset */\n')
    write('page-templates/wide.php', '<?php // native template\n')
    write('blocks/new-directory/render.php', '<?php // future packaged directory\n')
    git('add', '.')
    git('commit', '-qm', 'fixture additional production assets')
    after = git('rev-parse', 'HEAD')
    write('assets/uncommitted.css', '/* must not leak into bundle */\n')
    old = module.production_inventory(repo, before)
    new = module.production_inventory(repo, after)
    assert 'assets/css/components/page-wellness-01.css' not in old
    assert 'assets/css/components/page-wellness-01.css' in new
    assert 'page-templates/wide.php' in new
    assert 'blocks/new-directory/render.php' in new, 'packaged files in new directories were silently omitted'
    assert 'ops/approved.json' in new
    assert 'tests/not-production.php' not in new
    assert 'assets/uncommitted.css' not in new
    assert len(new) == len(set(new))
    try:
        module.production_inventory(repo, 'HEAD')
    except ValueError:
        pass
    else:
        raise AssertionError('symbolic/non-pinned source was accepted')
print('PASS: native bundle follows pinned Git source, including added CSS/templates and approved auxiliary files')
