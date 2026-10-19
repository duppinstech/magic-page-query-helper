---
paths:
  - "magic-page-query-helper.php"
  - "uninstall.php"
  - "index.php"
  - "includes/**/*.php"
---

# Code style

## PHP
- Target **PHP 7.4**. For PHP 7.4 that means no `match`, enums, `readonly`, constructor promotion, `?->`, union types, or `str_contains` without a polyfill.
- WordPress Coding Standards: spaces inside parentheses, Yoda conditions for literals; match the file's array syntax and indentation. Don't reformat lines you didn't change.
- Prefix every global function, option, meta key, transient, hook and AJAX action with `mpqh_`. Text domain `magic-page-query-helper` on every translatable string.
- Docblock on every public method and every hook the plugin fires.
- No namespace and no autoloader. The main file loads `includes/` with `require_once`.
- Class names use the `MPQH_` prefix: `MPQH_Magic_Page_Query_Helper`, `MPQH_GitHub_Updater`, `MPQH_Plugin_Info`.
- `MPQH_GitHub_Updater::boot()` and `MPQH_Plugin_Info::boot()` register their hooks at load. `MPQH_Magic_Page_Query_Helper::init()` runs on `plugins_loaded` at priority 5.
- No field library. No global functions. `uninstall.php` and `index.php` are procedural files.

## JavaScript
- Data from PHP arrives through `wp_localize_script` or `wp_add_inline_script`; never inline secrets.
- This plugin has no JavaScript and no build step.

## CSS
- Scope front-end CSS under a plugin wrapper or body class so it doesn't leak into themes or wp-admin.
- Don't edit vendored libraries (`*.min.*`, third-party bundles).

## Comments
- Explain *why*, not *what*.
