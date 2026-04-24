=== Magic Page Query Helper ===
Contributors: oduppinsjr
Tags: magic page, performance, cache, taxonomy, database
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bulk-loads location term meta and primes term cache to cut DB queries with Magic Page and many locations.

== Description ==

Reduces database load when using Magic Page (or Whiterail SEO / similar location-based page generators) with thousands of locations.

* What it does: Runs a single bulk query to load all "location" term meta, then short-circuits every get_term_meta() call for those terms so the main plugin never hits the database 10,000+ times per request.
* Safe with: LiteSpeed Cache, other full-page cache plugins. Does not replace or conflict with Magic Page's own cache (which you may have disabled in favor of a third-party cache).
* Survives: Magic Page plugin updates (this plugin does not modify Magic Page files).

Requires the "location" taxonomy to exist (Magic Page creates it). No settings; activate and it runs on the front-end.

If you previously added the mu-plugin "Magic Page – Preload location term meta", you can remove or disable it; this plugin replaces that approach with a single-query cache and get_term_metadata short-circuit.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.

== Frequently Asked Questions ==

= Does this replace Magic Page? =

No. It only reduces redundant queries for the `location` taxonomy.

== Changelog ==

= 1.0.0 =
* Initial release.
