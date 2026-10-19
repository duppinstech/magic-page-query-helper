=== Magic Page Query Helper ===
Contributors: duppinstech
Tags: performance, taxonomy, database
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Cuts repeated location-term database queries when a site has a very large location list.

== Description ==

Magic Page Query Helper is for sites that use Magic Page, or a similar location-page generator, with thousands of locations. Those pages can ask WordPress for the same location terms over and over, which makes the page slow.

After you activate this plugin, it loads location term data in bulk and serves later lookups from that result. It does not change Magic Page's files, and it does not replace a full-page cache such as LiteSpeed Cache.

The location taxonomy must already exist. Magic Page creates it. There is nothing to configure. An optional status screen is under Settings → MPQH Dashboard.

= Features =

* One load of all location term meta, so repeated lookups are not separate database queries.
* One load of the location terms themselves, so later term lookups can use WordPress's cache.
* Leaves Magic Page's own files and cache settings alone.
* Settings → MPQH Dashboard shows whether the location taxonomy is present, how many location terms are stored, and what the last front-end request did.

= Support =

Support: [support@duppinstech.com](mailto:support@duppinstech.com). Made by [Duppins Technology](https://duppinstech.com).

== Installation ==

1. Download `magic-page-query-helper.zip` from the latest release.
2. In wp-admin, go to Plugins → Add New → Upload Plugin, choose the zip, and click Install Now.
3. Activate Magic Page Query Helper.
4. Open Settings → MPQH Dashboard if you want to confirm it is running. The plugin works on the front end without this screen.

= Updates =

New releases appear under Dashboard → Updates.

== Frequently Asked Questions ==

= Does this replace Magic Page? =

No. It only reduces repeated queries for the location taxonomy. Magic Page still builds the pages.

= Do I have to configure it? =

No. Activate it and it runs on front-end requests. Settings → MPQH Dashboard is a status screen, not a setup form.

= I already added the must-use plugin "Magic Page – Preload location term meta". What should I do? =

You can remove or disable that must-use plugin. This plugin replaces that approach.

= Does this work with a page cache? =

Yes. It is safe to use with LiteSpeed Cache and other full-page caches. It does not turn Magic Page's own cache back on, and it does not replace that cache.

= The dashboard still shows a high query count. Is the helper running? =

Compare "Short-circuited" with "get_term_meta filter calls" on the dashboard. When those numbers are close, location term meta is being served from the bulk load. A high total query count can still come from other work on the page. Total queries are stored only when SAVEQUERIES is enabled in wp-config.php.

== Changelog ==

= 1.0.1 =
* Plugins screen shows Settings, Sponsor, View details, and Support.
* Updates install from GitHub Releases.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Plugins screen details, and updates from GitHub Releases.

= 1.0.0 =
Initial release.
