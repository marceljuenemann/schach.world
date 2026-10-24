# 0003: Styling: Shadow DOM, no Bootstrap, CSS variables as the only styling API

**Status:** Accepted (2026-10-24)

## Context

The plugin renders into themes we don't control. WordPress themes style
`button`, `input`, `table` and `label` aggressively, often with high-specificity
selectors. The current UI assumes a page-global Bootstrap 5, which can't be
assumed on other sites.

Popular plugins (Gravity Forms, WPForms, The Events Calendar, WooCommerce) use a
global stylesheet in the light DOM. They scope it under a wrapper class, expose
CSS custom properties, and offer a "style level" setting. They accept theme
leakage and fight it with specificity; Gravity Forms' documented override
selectors show how far that goes. That approach largely stems from PHP-rendered
markup, template overrides and page builders. None of these apply here, because
our UI is rendered client-side by Angular.

## Decision

- **Shadow DOM:** each root custom element uses `ViewEncapsulation.ShadowDom`.
  Child components keep Angular's default Emulated encapsulation.
- **No Bootstrap / ng-bootstrap:** replacements:

  | Today | Replacement |
  |---|---|
  | `NgbModal` | native `<dialog>` with `showModal()` |
  | `ngbAccordion` | native `<details>`/`<summary>` |
  | `ngbTooltip` | Popover API or plain hint text |
  | `ngbNav`, `ngbTypeahead`, `ngbDropdown` | small own components, or headless `@angular/aria` primitives (see [open questions](../open-questions.md)) |
  | `form-floating` | regular labels above fields |
  | Dashicons | inline SVG icons |

- **Own base stylesheet:** no CSS framework. We write a small base stylesheet
  ourselves that styles native elements (inputs, selects, buttons, tables,
  dialogs, details, alerts) using only our own tokens.
  - The scope is small: the current UI uses about 60 Bootstrap classes.
  - A framework such as Pico CSS would add its own variable layer that we'd have
    to map onto our tokens, plus behavior to switch off (e.g. automatic dark
    mode).
- **Overlays:** nothing attaches to `<body>`. Dialogs and popovers use the
  browser's top layer and stay inside the shadow root.
- **Styling API:** CSS custom properties are the only public styling API.
  - They inherit through the shadow boundary, so they can be set anywhere on
    the page.
  - Each token falls back to the block theme's `theme.json` presets, then to a
    built-in default:

    ```css
    :host {
      --_accent: var(--schach-world-accent, var(--wp--preset--color--primary, #2563eb));
      --_border: var(--schach-world-border-color, color-mix(in srgb, currentColor 25%, transparent));
    }
    ```

  - Neutral colors (borders, muted text, surfaces) are derived from
    `currentColor` so that dark and colorful themes work without configuration.
  - The public token list stays short (roughly 10–15: accent, accent contrast,
    status colors, radius, spacing, border, surface, optional font overrides).
- **Theme integration:** typography (`font`, `color`) is inherited from the
  theme on purpose. Other inherited properties that commonly misbehave
  (`letter-spacing`, `text-transform`, `line-height`) are reset on `:host`.

## Consequences

- **No theme leakage:** themes can't break the UI, so no "style level" setting
  and no theme-specific CSS fixes are needed.
- **Limited customization:** sites can only change what the variables expose.
  `::part()` names can be added later if needed. Adding API is non-breaking;
  removing or renaming a variable is a breaking change.
- **Fonts:** `@font-face` doesn't work reliably inside a shadow root. We inherit
  the theme font. If we ever ship a font, it must be declared in the document.
- **ARIA:** `aria-labelledby` and similar references can't cross the shadow
  boundary. Labels and the elements they label must live in the same shadow root.
- **Must be verified early:**
  - Emulated child component styles end up in the shadow root.
  - Browser autofill and password managers work on the registration form.
- **Events:** events leaving the component are retargeted to the host element,
  which only affects external click tracking.
- **Reuse elsewhere:** components are only isolated from page CSS when they
  render inside a shadow root. If a component is ever reused in the league
  manager, it must be wrapped in a shadow root there too. Otherwise Bootstrap's
  global styles apply.
