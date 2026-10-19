#!/usr/bin/env bash
# Build magic-page-query-helper.zip from tracked files only, excluding development files.
# Used by .github/workflows/release.yml; run locally with `bash bin/build-zip.sh` to inspect the package.
set -euo pipefail

cd "$(dirname "$0")/.."
rm -rf build magic-page-query-helper.zip
mkdir -p build/magic-page-query-helper

# Tracked files only (never local settings, vendor/, or untracked scratch files), minus dev-only paths.
git ls-files -z \
  | grep -zvE '^(\.github/|\.claude/|tests/|bin/|docs/|\.gitignore$|\.gitattributes$|\.editorconfig$|AGENTS\.md$|CLAUDE\.md$|OWNERSHIP\.md$|CONTRIBUTING\.md$|CHANGELOG\.md$|INTENT\.md$|PLAN\.md$|intent\.md$|plan\.md$|composer\.(json|lock)$|phpcs\.xml\.dist$|phpunit\.xml\.dist$)' \
  | xargs -0 -I{} cp --parents {} build/magic-page-query-helper/

( cd build && zip -qr9 ../magic-page-query-helper.zip magic-page-query-helper )
unzip -l magic-page-query-helper.zip
