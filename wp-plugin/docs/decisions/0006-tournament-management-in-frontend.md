# 0006: Tournament management in the frontend, not wp-admin

**Status:** Accepted (2026-10-24)

## Context

Tournament managers are usually not site administrators, and they shouldn't need
or see wp-admin. Today, access is checked against WordPress logins:
`Auth::isAdmin()` or the username being listed in `TournamentConfig->managers`.

We need dynamic, readable URLs such as `turniere/[slug]/[year]/`.

## Decision

- **Frontend management:** tournament management lives in the site's frontend.
  wp-admin is only for site administrators and site-wide settings (e.g. DWZ
  import, default styling, who has full access).
- **Permissions (first version):** logins are WordPress user accounts.
  Tournaments are rows in our own tables, not posts or pages. Access is a
  plain check:
  - **Full access:** users with the custom capability `schach_world_admin`.
    It is granted to specific users; WordPress administrators get it
    automatically.
  - **Per tournament:** users in the tournament's manager list (stored as user
    IDs), as today.
  - Every REST endpoint touching a tournament runs this check.
- **Routing:** the plugin creates a base page (e.g. "Turniere") on activation
  and stores its ID in an option. Rewrite rules map subpaths to that page with
  query variables:

  ```php
  add_rewrite_rule(
      '^turniere/([^/]+)/([0-9]{4})/?$',
      'index.php?page_id=' . $basePageId . '&schach_world_tournament=$matches[1]&schach_world_year=$matches[2]',
      'top'
  );
  ```

  WordPress renders the base page inside the theme. Our block or shortcode on
  that page reads the query variables and outputs the matching custom element
  (e.g. `<schach-world-tournament slug="…" year="…">`).
- **Robust base page:** this is the WooCommerce pattern (Shop, Cart, My
  Account), with safeguards against admins editing the page:
  - The plugin injects the element via the `the_content` filter on the base
    page. Page content is optional intro text, and removing a block from it
    breaks nothing.
  - The base page is selectable in the plugin settings.
  - If it is deleted or unpublished, an admin notice offers to recreate it.
  - We chose this over a custom post type per tournament (data stays in our
    own tables) and over virtual pages without a real page (fragile theme
    integration, especially with block themes).
- **Server-side page handling:** PHP resolves the tournament before rendering:
  - Unknown slugs return a real 404.
  - The canonical URL is set to the tournament's.
  - **Titles:** themes render the page title outside `the_content` (classic
    themes via `the_title()`, block themes via the Post Title block), so:
    - The heading is replaced via the `the_title` filter, guarded by
      `in_the_loop()` and `is_main_query()` so menus and widgets keep the base
      page's title.
    - The `<title>` tag is set via `document_title_parts`, plus the title
      filters of common SEO plugins (e.g. Yoast's `wpseo_title`).
    - Breadcrumbs keep showing the base page, which is correct as the parent
      level.
  - The element renders no main heading of its own on the base page. The
    theme's title is the heading.
  - The base page is created with comments closed and no featured image.
- **Keeping users out of wp-admin:** users without admin rights don't see the
  admin bar and are redirected from `/wp-admin` back to the site. The redirect
  must exempt `admin-ajax.php` (used by other plugins).
- **REST access:** logged-in frontend users call the REST API with cookie
  authentication and the `wp_rest` nonce.

## Consequences

- **Data model:** tournaments store a manager list. This replaces
  `TournamentConfig->managers`.
- **Configurable prefix:** the URL prefix follows the base page's slug, so site
  admins can rename it. Rewrite rules must be flushed on activation and when
  the base page's slug changes.
- **Tournament editor in Angular:** it runs in the frontend, not as a wp-admin
  screen. wp-admin keeps only a small settings page.
- **Later:** letting regular club users create tournaments needs a role
  capability for creating tournaments and an owner per tournament. WordPress's
  `map_meta_cap` filter is the standard mechanism if the plain check becomes
  too limited.
