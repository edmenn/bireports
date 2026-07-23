# BI Portal project map

## Runtime and deployment

- Official branch: `main`; release: `v2.0.0`.
- Stack: Laravel 13, PHP 8.5, PostgreSQL 18, Tabler UI.
- Main compose file: `docker-compose.secure.yml`; application image: `birentable/php-app:prod`.
- Browser preview: `http://localhost:8080`, container `bireports-preview`, same PostgreSQL data as the main environment.
- Recreate `bireports-preview` after rebuilding `birentable/php-app:prod`; `docker restart` keeps the old image ID.
- Build with `docker build -f Dockerfile.app -t birentable/php-app:tabler .`, tag as `birentable/php-app:prod`, run `code_sync`, recreate `app queue_worker scheduler web`, then `php artisan optimize`.

## Shared UI ownership

- Layout markup: `resources/views/layouts/app.blade.php`.
- Tabler compatibility and geometry: `public/css/tabler-bridge.css`.
- Legacy/shared component styling: `public/css/custom.css`.
- Runtime theme variables and global themed states: `resources/views/partials/theme-styles.blade.php`.
- Theme editor and live preview: `resources/views/configurations/theme.blade.php`.
- Sidebar state persistence: `public/js/menu-state.js`.

## Locked visual invariants

- Change-isolation contract: behavior outside the explicit request is frozen. Record a baseline before editing and compare after; shared selectors/components require verification of every affected consumer.

- Sidebar widths: expanded `16rem` (256 px), compact `4.5rem` (72 px).
- Sidebar and page wrapper remain in one non-wrapping flex row: the sidebar is sticky and the wrapper width is `100% - --app-sidebar-width`, so their shared edge stays exact in both desktop states and on pages with vertical scrollbars.
- Desktop scrolling belongs exclusively to the 100vh `.page-wrapper`; `body.app-shell` and `.page` are overflow-locked so the sidebar never moves. Mobile retains normal document scrolling.
- Sidebar has no right border or inset shadow. Page wrapper has no border, radius, shadow, padding-bottom, or flex gap. Footer has zero margin and no border. Preserve AdminLTE radii: header `0 0 1rem 0`, footer `0 1rem 0 0`.
- Header and footer use the same `--app-navbar-bg` and `--app-navbar-text` values.
- Footer reproduces the compact AdminLTE metrics: no forced minimum height, `.875em` text, `line-height: 1.1`, and `.25rem` vertical padding. It stays pinned with `position: sticky; bottom: 0` on short and long pages.
- In every rendered view, `.main-footer` must remain a direct child of `.page-wrapper`; a footer moved under `<body>` indicates malformed view markup and invalidates geometry checks.
- `.content-header` is a non-shrinking flex child so wrapped header actions never overlap or get covered by `.page-body` cards.
- The shared `.content-header` bottom border is the divider on every internal view. Page-specific tabs and filters render in `content`, below that divider.
- Authenticated page headers do not duplicate sidebar navigation with Back or Home buttons. Auth recovery and error-page escape links are exempt.
- Shared `page-actions` rely on the page header's `justify-content: space-between`; they must not use `margin-left: auto`, which forces actions onto a second row on desktop.
- The shared component uses `.app-page-header`, not Tabler's `.page-header`; the latter forces a column layout and stacks desktop actions.
- Full logo is visible expanded; compact logo is visible at 40x40 reduced. Desktop toggle switches icon-only vs icon-and-text navigation and persists locally.
- In compact mode, clicking a parent icon keeps its Bootstrap collapse open and reveals the child icons; only closed groups remain hidden.
- `menu-state.js` initializes on `DOMContentLoaded`, after the deferred Vite module exposes Bootstrap, so open groups persist across navigation and reload.
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
- 2026-07-22: restore the footer as a sticky bottom bar on both short and long pages without changing sidebar/header geometry.
- 2026-07-22: restore the compact pre-Tabler footer sizing instead of forcing the Tabler 56 px navbar height.
- 2026-07-22: isolate desktop scrolling in `.page-wrapper` so the sidebar remains immobile without changing mobile document scrolling.
- 2026-07-22: restore the AdminLTE lower-right header radius and upper-right footer radius.
- 2026-07-22: render the trusted formatted settings guide consistently so HTML tags never appear as literal interface text.
- 2026-07-22: prevent flex shrink from collapsing content headers over their page cards.
- 2026-07-22: route user block/unblock actions through theme colors and remove the last eager jQuery dependency from the capacities view.
- 2026-07-22: keep disabled outline actions on theme colors instead of allowing Tabler's default blue state to override them.
- 2026-07-22: standardize the Metrics header divider by moving its tabs into page content; remove redundant Back/Home header actions from authenticated views.
- 2026-07-22: allow compact sidebar groups in `show`/`collapsing` state to override the closed-group `display:none` rule.

## Required visual matrix

For layout/theme work, check `/portal`, `/users`, `/audit`, and `/configurations/theme` at:

- desktop expanded, light;
- desktop compact, light;
- desktop expanded, dark;
- mobile 390 px, light and dark.

Measure bounding rectangles for sidebar/header/footer edges; do not rely only on appearance.
