---
paths:
  - "magic-page-query-helper.php"
  - "uninstall.php"
  - "includes/**/*.php"
---

# Testing standard

## Minimum (every change)
1. `php -l` passes on every changed PHP file (the PostToolUse hook does this).
2. Manual smoke test on a WordPress site with `WP_DEBUG` on:
   - The plugin activates without notices or errors.
   - Settings → MPQH Dashboard (`manage_options`): whether the `location` taxonomy is registered, how many location terms are stored, and the last front-end request stats.
   - A front-end request shows no plugin markup. With a `location` taxonomy present, later `get_term_meta()` lookups for those terms can be served from the bulk cache.
3. If you touched the updater, test the Check Again flow against a draft release.

## Automated tests
No suite yet; see PLAN.md.
- Location: `tests/unit/` (Brain Monkey) and `tests/integration/` (wp-phpunit). Naming: `Test_<Class_Name>.php`, methods `test_<behavior>_<condition>`.
- Every bug fix ships with a regression test that fails before the fix. Unit tests never hit the network.
- CI runs on PHP 7.4 and the latest 8.x.

## Priority test targets
- `MPQH_GitHub_Updater::get_release()` and `is_allowed_package_url()` — GitHub response and package URL.
- `MPQH_GitHub_Updater::filter_update_transient()` — the update WordPress is offered.
- `MPQH_Magic_Page_Query_Helper::shortcircuit_term_meta()` — term meta served from the bulk cache.
- `MPQH_Magic_Page_Query_Helper::maybe_load_cache()` — the bulk term-meta query.
- `MPQH_Plugin_Info::filter_plugins_api()` — View details modal content.
- `uninstall.php` — removes the `mpqh_github_release` site option on uninstall.
