#!/bin/sh
set -eu

css=public/css/tabler-bridge.css
theme=resources/views/partials/theme-styles.blade.php

check() {
  grep -Fq "$2" "$1" || { echo "Missing invariant in $1: $2" >&2; exit 1; }
}

check "$css" '.app-sidebar.navbar-vertical { border-right: 0 !important; box-shadow: none !important; }'
check "$css" 'body.sidebar-compact { --app-sidebar-width: 4.5rem; }'
check "$css" 'gap: 0;'
check "$css" '.page-body { flex: 1 0 auto; margin-bottom: 0; padding: 0; }'
check "$css" 'margin: 0; background: var(--app-navbar-bg'
check "$theme" '.app-sidebar .nav-item.active > .nav-link'
check "$theme" 'background-color: var(--app-btn-bg) !important;'

echo 'BI Portal invariants: OK'
