#!/usr/bin/env bash
set -euo pipefail

# Copies language-specific assets from resources/lang/<locale> to public/lang/<locale>
# so they can be served by Nginx and (optionally) tracked by Git.

shopt -s nullglob
ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
cd "$ROOT_DIR"

mkdir -p public/lang

copied=0
# 1) Prefer storage/app/public/lang as the source-of-truth if present
if [ -d storage/app/public/lang ]; then
  rsync -a --delete storage/app/public/lang/ public/lang/
  copied=$(($(find storage/app/public/lang -type f | wc -l)))
else
  # 2) Fallback: copy image files found under resources/lang/<locale>
  for d in resources/lang/*; do
    [ -d "$d" ] || continue
    locale=$(basename "$d")
    mkdir -p "public/lang/$locale"
    for f in "$d"/*.{png,PNG,jpg,JPG,jpeg,JPEG,svg,SVG,webp,WEBP,gif,GIF}; do
      [ -f "$f" ] || continue
      cp -a "$f" "public/lang/$locale/"
      copied=$((copied+1))
    done
  done
fi

echo "Synced $copied asset(s) into public/lang/"
