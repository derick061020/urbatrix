#!/usr/bin/env python3
"""Fix broken @foreach($errors->{{ __('all() as $var)') }}.

Pattern to fix:
  @foreach($errors->{{ __('all() as $X)') }}
  ->
  @foreach($errors->all() as $X)

And remove the keys "all() as $error)" / "all() as $e)" / "all() as $err)"
from lang JSON files (they are not real translation keys).
"""
import json
import re
from pathlib import Path

ROOT = Path("/home/kira/Escritorio/projects/launchbase/launchbase")
VIEWS = ROOT / "resources/views"

# Match the exact broken pattern, capturing the variable name
PATTERN = re.compile(
    r"\$errors->\{\{\s*__\(['\"]all\(\)\s+as\s+\$(\w+)\)['\"]\)\s*\}\}"
)

changed_files = 0
total_fixes = 0
broken_keys_seen = set()

for p in VIEWS.rglob("*.blade.php"):
    text = p.read_text(encoding="utf-8")
    if "all() as" not in text or "__(" not in text:
        continue
    def repl(m):
        global total_fixes
        var = m.group(1)
        broken_keys_seen.add(f"all() as ${var})")
        total_fixes += 1
        return f"$errors->all() as ${var})"
    new_text = PATTERN.sub(repl, text)
    if new_text != text:
        p.write_text(new_text, encoding="utf-8")
        changed_files += 1
        print(f"[FIX] {p.relative_to(ROOT)}")

print()
print(f"Files fixed: {changed_files}")
print(f"Total fixes: {total_fixes}")

# Clean up the lang JSON
es = json.loads((ROOT / "lang/es.json").read_text())
en = json.loads((ROOT / "lang/en.json").read_text())
removed = 0
for k in broken_keys_seen:
    if k in es: del es[k]; removed += 1
    if k in en: del en[k]
(ROOT / "lang/es.json").write_text(json.dumps(es, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
(ROOT / "lang/en.json").write_text(json.dumps(en, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print(f"Removed {removed} bogus keys from lang JSONs")
print(f"Bogus keys: {sorted(broken_keys_seen)}")
