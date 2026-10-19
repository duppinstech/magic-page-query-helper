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

## Local development

There is no Composer or npm install. PHP 7.4+ and WordPress 6.0+ are required, and the `location` taxonomy must already exist.

1. Copy or symlink this folder to `wp-content/plugins/magic-page-query-helper`.
2. Activate **Magic Page Query Helper** in WordPress.

Check syntax with the same command CI runs (Git Bash):

```bash
find . -path ./build -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

## Test

This plugin has no test suite and no test command.

CI also checks that the plugin header version has a changelog section:

```bash
bash bin/release-notes.sh <version> magic-page-query-helper.php
```

Replace `<version>` with the `Version` header in `magic-page-query-helper.php`.

## Release and deploy

A release is a `v*` tag on `main`. `.github/workflows/release.yml` checks that the tag, the plugin header `Version`, the `MPQH_VERSION` constant, and the `Stable tag` in `readme.txt` are the same version, then builds `magic-page-query-helper.zip` and publishes a GitHub Release.

1. Add a `## [x.y.z]` section to `CHANGELOG.md`. Once that file exists, `bin/release-notes.sh` reads it and does not read `readme.txt`. The heading must not contain "Unreleased", and the section must not be empty. Keep the matching `= x.y.z =` notes under `== Changelog ==` in `readme.txt`, which WordPress shows.
2. Tag `vx.y.z` on `main`.

Build the zip locally (Git Bash). The zip is gitignored.

```bash
bash bin/build-zip.sh
```
