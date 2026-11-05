#!/usr/bin/env python3
"""Server-side WordPress 6.9 Page asset smoke; no browser/editor or production site access."""
import argparse
import hashlib
from html.parser import HTMLParser
import http.client
import importlib.util
import json
import os
from pathlib import Path
import re
import subprocess
import tempfile
import time
import uuid
import zipfile


def sha(path):
    return hashlib.sha256(Path(path).read_bytes()).hexdigest()


class Document(HTMLParser):
    def __init__(self):
        super().__init__()
        self.styles, self.articles, self.article_ids, self.h1 = [], [], [], 0

    def handle_starttag(self, tag, attributes):
        a = dict(attributes)
        if tag == 'link' and a.get('rel') == 'stylesheet':
            self.styles.append(a)
        if tag == 'article':
            self.articles.append(a.get('class', '').split())
            self.article_ids.append(a.get('id'))
        if tag == 'h1':
            self.h1 += 1


def main():
    if not __debug__:
        raise RuntimeError('Run without Python optimization; verification assertions must execute')
    parser = argparse.ArgumentParser(description=__doc__)
    for key in ['php', 'mysql-base', 'wp-cli', 'wordpress-zip', 'source-sha', 'output', 'work-root']:
        parser.add_argument('--' + key, required=True)
    parser.add_argument('--http-port', type=int, default=9440)
    parser.add_argument('--db-port', type=int, default=9441)
    args = parser.parse_args()
    repo = Path(__file__).resolve().parents[2]
    spec = importlib.util.spec_from_file_location('wellness_native', Path(__file__).with_name('wellness01-native-local.py'))
    shared = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(shared)
    inventory = shared.production_inventory(repo, args.source_sha)
    php, mysql_base = Path(args.php).resolve(), Path(args.mysql_base).resolve()
    phar, archive = Path(args.wp_cli).resolve(), Path(args.wordpress_zip).resolve()
    out = Path(args.output).resolve()
    out.mkdir(parents=True, exist_ok=False)
    Path(args.work_root).mkdir(parents=True, exist_ok=True)
    work = Path(tempfile.mkdtemp(prefix='wellness-pages-', dir=args.work_root))
    token = uuid.uuid4().hex
    env = {**os.environ, 'WP_CLI_CONFIG_PATH': os.devnull, 'WP_CLI_CACHE_DIR': str(work/'wp-cli-cache')}
    env['PATH'] = str(mysql_base/'bin') + os.pathsep + str(php.parent) + os.pathsep + env['PATH']
    processes, handles, completion = [], [], {'success': False, 'source_commit': args.source_sha}

    def run(command):
        r = subprocess.run(command, cwd=repo, env=env, capture_output=True, text=True, timeout=120)
        if r.returncode:
            raise RuntimeError(f'{Path(command[0]).name} failed: {r.stderr[-1200:]} {r.stdout[-500:]}')
        return r.stdout.strip()

    def blob(path):
        return subprocess.check_output(['git', 'show', args.source_sha + ':' + path], cwd=repo)

    def request(route):
        c = http.client.HTTPConnection('127.0.0.1', args.http_port, timeout=15)
        try:
            c.request('GET', route)
            r = c.getresponse()
            body = r.read()
            if r.status != 200:
                raise RuntimeError(f'HTTP {r.status}: {route}')
            return body
        finally:
            c.close()

    try:
        previous = json.loads(blob('docs/evidence/wellness01/2026-10-29-v07-native-provenance.json'))
        inputs = {'php_binary_sha256': php, 'mysql_binary_sha256': mysql_base/'bin/mysqld',
                  'wp_cli_sha256': phar, 'wp_zip_sha256': archive}
        hashes = {name: sha(path) for name, path in inputs.items()}
        if php.read_bytes()[:4] != b'\x7fELF' or any(hashes[k] != previous[k] for k in inputs):
            raise RuntimeError('Native binary/official WordPress input differs from verified v0.7 provenance')
        with zipfile.ZipFile(archive) as z:
            if any(not (work/i.filename).resolve().is_relative_to(work) for i in z.infolist()):
                raise RuntimeError('Unsafe ZIP path')
            z.extractall(work)
        wp = work/'wordpress'
        theme = wp/'wp-content/themes/aznet-theme'
        source_hashes = {}
        for path in inventory:
            content = blob(path)
            if (repo/path).read_bytes() != content:
                raise RuntimeError('Working production differs from source: ' + path)
            target = theme/path
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_bytes(content)
            source_hashes[path] = sha(target)
        required = 'assets/css/components/page-wellness-01.css'
        if required not in source_hashes:
            raise RuntimeError('New Page CSS is missing from the native bundle')
        data = work/'mysql-data'
        common = [str(mysql_base/'bin/mysqld'), '--no-defaults', '--basedir='+str(mysql_base),
                  '--datadir='+str(data), '--innodb-use-native-aio=OFF']
        if os.geteuid() == 0:
            common.append('--user=root')
        run(common + ['--initialize-insecure'])
        db_log = (out/'mysql.log').open('w')
        handles.append(db_log)
        database = subprocess.Popen(common + ['--bind-address=127.0.0.1', '--port='+str(args.db_port),
            '--socket=', '--mysqlx=OFF', '--pid-file='+str(work/'mysql.pid')], env=env, stdout=db_log, stderr=subprocess.STDOUT)
        processes.append(database)
        mysql = [str(mysql_base/'bin/mysql'), '--no-defaults', '--protocol=TCP', '--host=127.0.0.1',
                 '--port='+str(args.db_port), '--user=root', '--batch', '--skip-column-names']
        for _ in range(80):
            if database.poll() is not None:
                raise RuntimeError('Disposable MySQL exited')
            r = subprocess.run(mysql+['-e', 'SELECT @@datadir, VERSION()'], env=env, text=True, capture_output=True, timeout=3)
            if r.returncode == 0:
                break
            time.sleep(.25)
        else:
            raise RuntimeError('MySQL readiness timeout')
        fields = r.stdout.strip().split('\t')
        if len(fields) != 2 or Path(fields[0]).resolve() != data.resolve():
            raise RuntimeError('Foreign MySQL datadir: refusing SQL mutations')
        run(mysql+['-e', "CREATE DATABASE wellness_pages CHARACTER SET utf8mb4; ALTER USER 'root'@'localhost' IDENTIFIED BY 'wellness-pages-local-db-password';"])
        cli = [str(php), str(phar), '--path='+str(wp), '--allow-root']
        run(cli+['config', 'create', '--dbname=wellness_pages', '--dbuser=root', '--dbpass=wellness-pages-local-db-password',
                 '--dbhost=127.0.0.1:'+str(args.db_port), '--skip-check'])
        run(cli+['config', 'set', 'WP_DEBUG', 'true', '--raw'])
        run(cli+['core', 'install', '--url=http://127.0.0.1:'+str(args.http_port), '--title=Native Page QA',
                 '--admin_user=admin', '--admin_password=wellness-pages-local-password', '--admin_email=qa@example.test', '--skip-email'])
        run(cli+['theme', 'activate', 'aznet-theme'])
        setup = work/'setup.php'
        setup.write_text('''<?php
set_theme_mod('aznet_theme_settings', array_merge(\\AZnet\\Theme\\settings_defaults(), ['homepage_preset'=>'wellness-01']));
update_option('blog_public', 0);
$content = '<!-- wp:paragraph --><p>Native Page source sentinel</p><!-- /wp:paragraph --><!-- wp:group {"style":{"color":{"background":"#eeeeee"}}} --><div class="wp-block-group has-background" style="background-color:#eeeeee"><!-- wp:heading --><h2 class="wp-block-heading">Native Group</h2><!-- /wp:heading --></div><!-- /wp:group -->';
$ids=[];
foreach (['front','standard','wide','landing','post'] as $kind) {
 $id=wp_insert_post(['post_type'=>$kind==='post'?'post':'page','post_status'=>'publish','post_title'=>'Native '.ucfirst($kind),'post_content'=>$content],true);
 if (is_wp_error($id)) throw new RuntimeException($id->get_error_message());
 $ids[$kind]=$id;
 if (in_array($kind,['wide','landing'],true)) update_post_meta($id,'_wp_page_template','page-templates/'.$kind.'.php');
}
update_option('show_on_front','page');update_option('page_on_front',$ids['front']);
echo wp_json_encode(['ids'=>$ids,'content_hash'=>hash('sha256',$content)]);
''')
        fixture = json.loads(run(cli+['eval-file', str(setup)]))
        state = wp/'qa-page-state.php'
        state.write_text("<?php require __DIR__.'/wp-load.php'; header('Content-Type: application/json'); echo wp_json_encode(['token'=>'"+token+"','wordpress'=>get_bloginfo('version'),'php'=>PHP_VERSION,'sapi'=>php_sapi_name(),'mysql'=>$wpdb->get_var('SELECT VERSION()'),'plugins'=>get_option('active_plugins',[]),'dropin'=>file_exists(WP_CONTENT_DIR.'/db.php'),'preset'=>\\AZnet\\Theme\\homepage_preset()]);")
        php_log = (out/'php-server.log').open('w')
        handles.append(php_log)
        server = subprocess.Popen([str(php), '-d', 'display_errors=1', '-S', '127.0.0.1:'+str(args.http_port), '-t', str(wp)],
                                  env=env, stdout=php_log, stderr=subprocess.STDOUT)
        processes.insert(0, server)
        for _ in range(80):
            if server.poll() is not None:
                raise RuntimeError('Disposable PHP server exited')
            try:
                runtime = json.loads(request('/qa-page-state.php'))
                if runtime.get('token') != token or server.poll() is not None:
                    raise RuntimeError('Foreign HTTP endpoint')
                break
            except (OSError, http.client.HTTPException, json.JSONDecodeError):
                time.sleep(.25)
        else:
            raise RuntimeError('PHP readiness timeout')
        if runtime['wordpress'] != '6.9' or not runtime['php'].startswith('8.1.') or not runtime['mysql'].startswith('8.0.') or runtime['plugins'] or runtime['dropin']:
            raise RuntimeError('Native runtime identity mismatch')
        records = []

        def capture(name, route, expected, variant=None, identity=None, surface=None):
            body = request(route)
            document = Document()
            document.feed(body.decode())
            styles = {s.get('id'): s for s in document.styles}
            selected = styles.get('aznet-theme-page-wellness-01-css')
            assert bool(selected) == expected, name+' stylesheet scope'
            if identity is not None:
                assert 'post-'+str(identity) in document.article_ids, name+' fixture identity'
                assert b'Native Page source sentinel' in body, name+' source sentinel'
            if surface:
                assert any(surface in classes for classes in document.articles), name+' rendered surface'
            if expected:
                assert selected['href'].split('ver=')[-1].endswith(source_hashes[required][:12]), name+' CSS fingerprint'
                order = [s.get('id') for s in document.styles]
                assert order.index('aznet-theme-page-css') < order.index('aznet-theme-page-wellness-01-css')
                assert order.index('aznet-theme-media-css') < order.index('aznet-theme-page-wellness-01-css')
            if variant:
                assert any('aznet-theme-page--'+variant in classes for classes in document.articles), name+' template variant'
                assert document.h1 == 1, name+' H1'
                assert b'Native Page source sentinel' in body, name+' source content'
            (out/(name+'.html')).write_bytes(body)
            records.append({'name': name, 'stylesheet': bool(selected), 'variant': variant, 'h1': document.h1,
                            'html_sha256': hashlib.sha256(body).hexdigest(), 'pass': True})

        for kind in ['standard', 'wide', 'landing']:
            capture(kind, '/?page_id='+str(fixture['ids'][kind]), True, kind, fixture['ids'][kind])
        capture('front', '/', False, identity=fixture['ids']['front'], surface='aznet-theme-entry--front-page')
        capture('post', '/?p='+str(fixture['ids']['post']), False, identity=fixture['ids']['post'], surface='aznet-theme-entry--post')
        delivered = request('/wp-content/themes/aznet-theme/'+required)
        assert hashlib.sha256(delivered).hexdigest() == source_hashes[required], 'HTTP CSS differs from source'
        css_file = theme/required
        moved = css_file.with_suffix('.css.qa-missing')
        css_file.rename(moved)
        try:
            capture('missing-css', '/?page_id='+str(fixture['ids']['standard']), False, 'standard', fixture['ids']['standard'])
        finally:
            moved.rename(css_file)
        off = work/'off.php'
        off.write_text("<?php $s=get_theme_mod('aznet_theme_settings',[]);$s['homepage_preset']='off';set_theme_mod('aznet_theme_settings',$s);")
        run(cli+['eval-file', str(off)])
        capture('preset-off', '/?page_id='+str(fixture['ids']['standard']), False, 'standard', fixture['ids']['standard'])
        for kind, post_id in fixture['ids'].items():
            value = run(cli+['post', 'get', str(post_id), '--field=post_content'])
            assert hashlib.sha256(value.encode()).hexdigest() == fixture['content_hash'], kind+' source content mutated'
        php_log.flush()
        errors = re.findall(r'.*(?:PHP (?:Warning|Fatal error|Parse error)|Uncaught).*', (out/'php-server.log').read_text())
        assert not errors, 'PHP request errors: '+str(errors[:3])
        provenance = {**hashes, 'source_commit': args.source_sha, 'production_files': source_hashes,
                      'runner_sha256': sha(__file__), 'shared_runner_sha256': sha(Path(__file__).with_name('wellness01-native-local.py')),
                      'runtime': runtime, 'mysql_datadir_fenced': True, 'http_token_fenced': True,
                      'scope': 'server-rendered Page HTML/assets only; no browser/editor or pixel-layout claim'}
        (out/'provenance.json').write_text(json.dumps(provenance, indent=2)+'\n')
        (out/'verification.json').write_text(json.dumps({'runtime': runtime, 'records': records,
            'css_sha256': source_hashes[required], 'content_unchanged': True, 'php_errors': errors}, indent=2)+'\n')
        completion.update(success=True, cases=len(records), production_files=len(source_hashes))
        print('PASS: native WordPress6.9/PHP8.1/MySQL8 Page HTML/assets; '+str(len(records))+' cases', flush=True)
    except Exception as error:
        completion['error'] = str(error)
        raise
    finally:
        for p in processes:
            if p.poll() is None:
                p.terminate()
                try:
                    p.wait(timeout=10)
                except subprocess.TimeoutExpired:
                    p.kill()
                    p.wait()
        for handle in handles:
            handle.close()
        completion['children_stopped'] = all(p.poll() is not None for p in processes)
        (out/'completion.json').write_text(json.dumps(completion, indent=2)+'\n')


if __name__ == '__main__':
    main()
