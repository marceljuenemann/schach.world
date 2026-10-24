# 0001: WordPress plugin with a thin PHP backend

**Status:** Accepted (2026-10-24)

## Context

The registration system and Swiss Chess result upload are useful to chess clubs
and federations beyond the NSV. Most of them already run WordPress, so a plugin
can be installed without any additional hosting.

WordPress is a weak application framework: no DI container, no ORM, global
functions and awkward testing. The schach.world application originally used
Symfony for exactly that reason. However, new interactive UI is written in
Angular, which leaves the PHP side with little more than persistence, access
control, validation and emails.

## Decision

Build the tournament features as a WordPress plugin. Keep the PHP layer thin:

- REST endpoints via `register_rest_route` with a `permission_callback` on every
  route, and WP nonces for authentication.
- Access control via WordPress roles and capabilities (e.g. a tournament manager
  capability) instead of the current WP→Symfony auth bridge.
- Data in custom tables created with `dbDelta`, with a schema version stored in
  an option for upgrades. No post meta for registration data.
- Background work (e.g. the DWZ import) via Action Scheduler, not raw WP-Cron.
- As few Composer dependencies as possible. Any bundled dependency must be
  namespace-prefixed (Strauss or PHP-Scoper) to avoid version clashes with other
  plugins.
- Tournament configuration moves from PHP files (`/data/registration/*.php`) to
  an admin UI stored in the database.

## Consequences

- **PHP version:** has to follow what typical hosts offer, not our own server.
  Target `Requires PHP: 8.2` (to be confirmed); PHP 8.4-only features such as
  property hooks are not available.
- **No Doctrine:** we use `$wpdb` with prepared statements.
- **Admin UI:** the configuration admin UI is a substantial new piece of work
  that does not exist today.
- **WordPress.org rules apply:**
  - GPL-compatible code.
  - Human-readable source for built JS (link the public repository in `readme.txt`).
  - No assets loaded from external CDNs.
  - External services (e.g. schachbund.de) disclosed in the readme.
  - No features locked behind payment.
  - Use Plugin Check before submitting.
- **Releases:** go to all sites at once with no staged rollout. Automated tests
  (PHPUnit via `wp-env`, Playwright E2E) are therefore more important than in
  the current setup.
- **SaaS:** the hosted variant on schach.world can run the same plugin on a
  WordPress multisite rather than a second backend implementation.
