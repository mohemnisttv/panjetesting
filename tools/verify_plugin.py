from pathlib import Path
import re

root = Path(__file__).resolve().parents[1]
required = [
    'plugin/panje.php', 'plugin/includes/class-rest.php',
    'plugin/includes/class-db.php', 'plugin/includes/class-foods.php',
    'src/Core/Plugin.php', 'src/Core/Container.php',
]
errors = []
for item in required:
    if not (root / item).is_file(): errors.append(f'missing: {item}')
entry = (root / 'plugin/panje.php').read_text(encoding='utf-8-sig')
if "register_activation_hook" not in entry: errors.append('activation hook missing')
if "Panje_DB::maybe_upgrade" not in entry: errors.append('upgrade hook missing')
for php in (root / 'plugin').rglob('*.php'):
    text = php.read_text(encoding='utf-8-sig', errors='ignore')
    if '\x00' in text: errors.append(f'binary content: {php}')
    if re.search(r'\$wpdb->query\(\s*["\'].*\$\w+', text):
        print('WARN: review SQL construction')
if errors:
    raise SystemExit('\n'.join(errors))
print(f'OK: checked {len(required)} required files and PHP sources')
