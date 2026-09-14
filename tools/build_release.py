"""Build a self-contained WordPress ZIP and a separate server example archive."""
from pathlib import Path
import hashlib
import json
import zipfile

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'dist'
OUT.mkdir(exist_ok=True)
manifest = json.loads((ROOT / 'composer.json').read_text(encoding='utf-8-sig'))
manifest['require']['php'] = '>=8.3'
manifest['autoload']['psr-4'] = {'Panje\\': 'src/'}
(ROOT / 'composer.json').write_text(json.dumps(manifest, indent=2) + '\n', encoding='utf-8')

def add_tree(archive, folder, prefix):
    for path in sorted(folder.rglob('*')):
        if path.is_file() and '__pycache__' not in path.parts and path.suffix != '.pyc':
            archive.write(path, prefix + path.relative_to(folder).as_posix())

plugin = OUT / 'panje-install.zip'
with zipfile.ZipFile(plugin, 'w', zipfile.ZIP_DEFLATED) as archive:
    add_tree(archive, ROOT / 'plugin', 'panje/')
    add_tree(archive, ROOT / 'src', 'panje/src/')
    archive.write(ROOT / 'composer.json', 'panje/composer.json')
with zipfile.ZipFile(plugin) as archive:
    assert archive.testzip() is None
    for required in ['panje/panje.php', 'panje/src/Core/Plugin.php', 'panje/src/Infrastructure/Persistence/ReportRepository.php']:
        assert required in archive.namelist(), required

server = OUT / 'panje-server-example.zip'
with zipfile.ZipFile(server, 'w', zipfile.ZIP_DEFLATED) as archive:
    add_tree(archive, ROOT / 'python', 'python/')
    add_tree(archive, ROOT / 'docs', 'docs/')
for path in [plugin, server]:
    digest = hashlib.sha256(path.read_bytes()).hexdigest()
    path.with_suffix('.zip.sha256').write_text(digest + '  ' + path.name + '\n', encoding='ascii')
    print(path.name, path.stat().st_size, digest)
