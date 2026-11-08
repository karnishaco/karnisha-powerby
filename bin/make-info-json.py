#!/usr/bin/env python3
"""Build info.json (the update manifest) from the plugin header and CHANGELOG.md.

Usage: python3 bin/make-info-json.py <output-path> [<signature-file>]
"""
import datetime, json, re, sys
from pathlib import Path

BASE_URL = "https://cityseo.ir/cloud/karnisha/plugins/powerby/"
root = Path(__file__).resolve().parent.parent

main = (root / "karnisha-powerby.php").read_text(encoding="utf-8")
readme = (root / "readme.txt").read_text(encoding="utf-8")

def header(name, text):
    m = re.search(r"^\s*\*?\s*" + re.escape(name) + r":\s*(.+?)\s*$", text, re.M | re.I)
    return m.group(1) if m else ""

version = header("Version", main)
changelog = ""
m = re.search(r"^## " + re.escape(version) + r"\b.*?\n(.*?)(?=^## |\Z)", (root / "CHANGELOG.md").read_text(encoding="utf-8"), re.M | re.S)
if m:
    changelog = m.group(1).strip()

info = {
    "name": "Karnisha Power By",
    "slug": "karnisha-powerby",
    "version": version,
    "download_url": f"{BASE_URL}karnisha-powerby.zip?ver={version}",
    "requires": header("Requires at least", main),
    "requires_php": header("Requires PHP", main),
    "tested": header("Tested up to", readme),
    "last_updated": datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%d %H:%M:%S"),
    "changelog": changelog,
}
if len(sys.argv) > 2:
    info["signature"] = Path(sys.argv[2]).read_text(encoding="utf-8").strip()
Path(sys.argv[1]).write_text(json.dumps(info, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
print(json.dumps(info, ensure_ascii=False, indent=2))
