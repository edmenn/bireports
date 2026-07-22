# BI Portal project map

## Runtime and deployment

- Branch: `upgrade/tabler`.
- Stack: Laravel 13, PHP 8.5, PostgreSQL 18, Tabler UI.
- Main compose file: `docker-compose.secure.yml`; application image: `birentable/php-app:prod`.
- Browser preview: `http://localhost:8080`, container `bireports-preview`, same PostgreSQL data as the main environment.
- Build with `docker build -f Dockerfile.app -t birentable/php-app:tabler .`, tag as `birentable/php-app:prod`, run `code_sync`, recreate `app queue_worker scheduler web`, then `php artisan optimize`.

## Shared UI ownership

- Layout markup: `resources/views/layouts/app.blade.php`.
- Tabler compatibility and geometry: `public/css/tabler-bridge.css`.
- Legacy/shared component styling: `public/css/custom.css`.
- Runtime theme variables and global themed states: `resources/views/partials/theme-styles.blade.php`.
- Theme editor and live preview: `resources/views/configurations/theme.blade.php`.
- Sidebar state persistence: `public/js/menu-state.js`.

## Locked visual invariants

- Sidebar widths: expanded `16rem` (256 px), compact `4.5rem` (72 px).
- Sidebar and page wrapper remain in one non-wrapping flex row: the sidebar is sticky and the wrapper width is `100% - --app-sidebar-width`, so their shared edge stays exact in both desktop states and on pages with vertical scrollbars.
- Sidebar has no right border or inset shadow. Page wrapper has no border, radius, shadow, padding-bottom, or flex gap. Footer has zero margin and no border/radius.
- Header and footer use the same `--app-navbar-bg` and `--app-navbar-text` values.
- Footer is 56 px high, follows content without a spacer (`.page-body` has zero bottom margin/padding), and sits at the viewport bottom on short pages.
- In every rendered view, `.main-footer` must remain a direct child of `.page-wrapper`; a footer moved under `<body>` indicates malformed view markup and invalidates geometry checks.
- Full logo is visible expanded; compact logo is visible at 40x40 reduced. Desktop toggle switches icon-only vs icon-and-text navigation and persists locally.
- Mobile has no reserved sidebar strip and no horizontal overflow.
- Active/hover menu states use `--app-hi-bg` / `--app-hi-text`.
- Primary, secondary, default, success, info, and warning action buttons use `--app-btn-bg`, `--app-btn-hover`, `--app-btn-active`, and `--app-btn-text` in light and dark modes. Danger remains semantic red.
- Theme defaults must match in controller, theme partial, editor inputs, and editor JavaScript.

## Regression history

- `81d4d5e`: replace AdminLTE with Tabler.
- `e913f3d`: restore branded collapsible sidebar, header, and footer geometry.
- `4ff45b2`: correct compact logo sizing.
- `538ac6e`: remove the Tabler sidebar seam.
- `7074dd4`: route menu and non-destructive button states through theme variables; unify theme defaults.
- 2026-07-22: repair stray closing containers in the theme view; keep sidebar and wrapper in one desktop flex row so long pages and scrollbar gutters cannot separate header/footer from the menu.

## Required visual matrix

For layout/theme work, check `/portal`, `/users`, `/audit`, and `/configurations/theme` at:

- desktop expanded, light;
- desktop compact, light;
- desktop expanded, dark;
- mobile 390 px, light and dark.

Measure bounding rectangles for sidebar/header/footer edges; do not rely only on appearance.
