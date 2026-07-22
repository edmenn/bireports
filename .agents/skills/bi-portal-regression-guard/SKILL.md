---
name: bi-portal-regression-guard
description: Preserve BI Portal architecture, UI decisions, theme behavior, deployment state, and previously fixed regressions. Use for every code, configuration, dependency, Docker, security, translation, theme, responsive, navigation, header, sidebar, footer, form, or button change in the bireports project.
---

# BI Portal Regression Guard

## Required workflow

1. Read `references/project-map.md` completely before editing.
2. Inspect `git status`, preserve unrelated work, and trace shared callers/selectors before changing code.
3. Write a one-sentence change contract: what may change and what adjacent behavior must remain unchanged. Capture the relevant pre-change DOM structure, computed dimensions, colors, states, and responsive behavior as the baseline.
4. Reuse the shared layout, theme variables, components, and deployment path recorded in the map.
5. Make the smallest root-cause change. Do not add per-view overrides when a shared rule exists. Before editing a shared selector/component, enumerate every consumer and include them in regression verification.
6. Run `bash .agents/skills/bi-portal-regression-guard/scripts/check-invariants.sh`.
7. Compare the post-change result with the captured baseline. Reject any unrelated difference in size, position, spacing, color, state, content, behavior, permissions, validation, or error handling.
8. For visual changes, verify desktop expanded, desktop compact, mobile, light, and dark. Check the touched view plus `/portal`, `/users`, `/audit`, and `/configurations/theme` when theme or layout is involved.
   On every checked route, assert that `.main-footer.parentElement` is `.page-wrapper`; CSS measurements are invalid if the browser repaired malformed HTML and moved layout elements.
9. Rebuild and restart the real Docker environment and preview; health-check both.
10. Update `references/project-map.md` in the same commit whenever an invariant, architecture decision, route, dependency, runtime, credential procedure, or deployment command changes.

## Non-negotiable UI rules

- Header, sidebar, page wrapper, and footer touch with no border, shadow, gap, radius, or blank strip at their shared edges.
- Expanded sidebar is 256 px; compact sidebar is 72 px. Header and footer left edges must equal the sidebar right edge in both states.
- Footer follows content without extra bottom padding and remains at the viewport bottom on short pages.
- Theme configuration is the single color source. Menu active/hover and non-destructive buttons use its variables in light and dark modes.
- Destructive actions remain red; semantic status badges may retain status colors.
- Shared forms and action buttons keep consistent heights, widths, gaps, wrapping, and responsive behavior.
- Adding, removing, or changing one component must not restyle its siblings. Example: adding one button cannot alter existing button dimensions, wrapping, spacing, colors, or alignment unless the user explicitly requests that broader change.
- A change to one view must not alter other views through a broad CSS selector, shared component, JavaScript listener, translation key, configuration default, middleware, or database mutation without explicit scope and regression coverage.
- Never deploy a visual change based only on compilation or one screenshot.

## Stop conditions

- Do not commit or deploy if the invariant script fails.
- Do not accept a theme/layout change if any required viewport or mode regresses.
- Do not accept any unrelated before/after difference, even if the requested feature works.
- Do not overwrite user changes or reset the database unless explicitly authorized.
- If current behavior conflicts with the map, verify Git history and live behavior, then correct the map and code together.
