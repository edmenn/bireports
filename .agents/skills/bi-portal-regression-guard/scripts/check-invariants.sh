#!/bin/sh
set -eu

css=public/css/tabler-bridge.css
theme=resources/views/partials/theme-styles.blade.php
theme_view=resources/views/configurations/theme.blade.php
settings_view=resources/views/configurations/index.blade.php
users_view=resources/views/users/index.blade.php
capacities_view=resources/views/capacities/index.blade.php

check() {
  grep -Fq "$2" "$1" || { echo "Missing invariant in $1: $2" >&2; exit 1; }
}

check "$css" '.app-sidebar.navbar-vertical { border-right: 0 !important; box-shadow: none !important; }'
check "$css" 'body.sidebar-compact { --app-sidebar-width: 4.5rem; }'
check "$css" 'position: sticky; flex: 0 0 var(--app-sidebar-width);'
check "$css" 'overflow: hidden; flex-direction: row; flex-wrap: nowrap;'
check "$css" 'body.app-shell { height: 100vh; overflow: hidden; }'
check "$css" 'height: 100vh; min-height: 0; margin-left: 0 !important; overflow-y: auto;'
check "$css" 'gap: 0;'
check "$css" '.page-body { flex: 1 0 auto; margin-bottom: 0; padding: 0; }'
check "$css" '.content-header { flex: 0 0 auto; padding: 1rem 0 .5rem; }'
check "$css" 'margin: 0; background: var(--app-navbar-bg'
check "$css" '.main-footer { position: sticky; bottom: 0;'
check "$css" 'min-height: 0; display: flex; align-items: center; font-size: .875em;'
check "$css" 'border-radius: 0 0 1rem 0;'
check "$css" 'border-radius: 0 1rem 0 0;'
check "$theme" '.app-sidebar .nav-item.active > .nav-link'
check "$theme" 'background-color: var(--app-btn-bg) !important;'
check "$theme" 'color: var(--app-btn-bg) !important;'
check "$theme" 'border-color: var(--app-btn-bg) !important;'
check "$settings_view" "{!! __('settings.guide.permissions.items.roles') !!}"

if grep -Eq 'btn-outline-(warning|success)' "$users_view"; then
  echo "User block/unblock actions bypass theme colors" >&2
  exit 1
fi

if grep -Fq '$(document).ready' "$capacities_view"; then
  echo "Capacities view executes jQuery before the Vite module is ready" >&2
  exit 1
fi

python3 - "$theme_view" <<'PY'
import sys
from pathlib import Path

source = Path(sys.argv[1]).read_text()
stray = "</x-ui-card>\n            </div>\n        </div>\n    </form>"
if stray in source:
    raise SystemExit("Malformed theme layout: stray closing containers before </form>")
PY

echo 'BI Portal invariants: OK'
