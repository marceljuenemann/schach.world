# Open questions

- **Plugin name on WordPress.org:** "schach.world" with slug `schach-world` is
  assumed (see [0005](decisions/0005-naming-prefix.md)). Confirm before the
  first submission: the slug can't be changed after approval and must not
  contain "WordPress".
- **Combobox and tabs:** `@angular/aria` (in developer preview since Angular 21;
  check stability) or own implementations following the WAI-ARIA patterns.
- **Admin settings page:** tournament management is in the frontend (see
  [0006](decisions/0006-tournament-management-in-frontend.md)), so wp-admin
  only needs site-wide settings. The Settings API is probably enough; Angular
  only if it gets complex.
- **URL scheme:** where management views live relative to the public
  tournament pages (e.g. `turniere/[slug]/[year]/verwaltung/` vs. a separate
  `turnierverwaltung/` page).
- **User accounts for club users (later):** open WordPress registration, invitations
  by an admin or tournament manager, or another flow.
- **License on WordPress.org:** the plugin is AGPL-3.0-or-later. The directory
  requires GPL-compatible licenses and recommends "GPLv2 or later". AGPLv3 is
  combinable with GPLv3, but confirm with plugins@wordpress.org before the
  first submission.
- **Minimum PHP version:** `8.2` proposed.
- **Language and i18n:**
  - German-only at first, or English source strings with German translations
    via translate.wordpress.org.
  - In the latter case, how translations reach the Angular frontend.
- **Distribution:** WordPress.org directory (discoverability, trust, but no
  staged rollouts) or self-hosted updates.
- **DSB data terms:** whether redistribution or local use of the DWZ export by
  third-party sites is permitted.
- **SaaS:** WordPress multisite on schach.world, or another hosting model.
- **Privacy:** implement WordPress privacy exporters and erasers for
  registration data (expected, not strictly required).
