# Magic Page Query Helper

WordPress plugin that reduces database load when using [Magic Page](https://magicpageplugin.com/) (or similar location-based generators) with very large numbers of `location` terms.

## What it does

- Runs **one bulk query** to load all `location` term meta, then short-circuits `get_term_metadata` so `get_term_meta()` does not issue thousands of per-term queries.
- Runs **one bulk query** to load all `location` terms and primes WordPress’s term cache so `get_term()` can hit cache more often.

It does **not** modify Magic Page files. It is compatible with full-page caching (e.g. LiteSpeed Cache) and does not replace Magic Page’s own caching.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- The `location` taxonomy (Magic Page registers it)

## Install

1. Copy the `magic-page-query-helper` folder into `wp-content/plugins/`.
2. Activate **Magic Page Query Helper** in **Plugins**.

No settings page is required for normal operation; an optional **Settings → MPQH Dashboard** page shows status and last front-end request stats.

## Development

- Main file: `magic-page-query-helper.php`
- WordPress plugin readme (for directories): `readme.txt`

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

## Security

See [SECURITY.md](SECURITY.md) for how to report vulnerabilities responsibly.
