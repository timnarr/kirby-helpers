# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.9.0] - 2026-10-07

### Added

- `inlineCriticalScript(string $path): void` inlines a critical, pre-first-paint script from a Vite `iife` build into a classic `<script>` tag: the source file in development, the built `[name]-iife.js` in production. The directories are configurable via the new `vite.criticalScript.sourceRoot` and `vite.criticalScript.buildRoot` options.
- `viteOption(string $key): mixed` reads a `timnarr.kirby-helpers.vite.*` option and resolves closures.

## [1.8.0] - 2026-10-07

### ⚠️ Breaking changes

- `readAccessible()` / `$file->readAccessible()` now throws an `InvalidArgumentException` for non-SVG files instead of returning their raw (binary) contents.
- `setBlankIfExternal()` now decides based on the host, see `isExternalUrl()` below:
  - Relative links and anchors such as `/contact` or `#top` no longer get `target="_blank"`.
  - Look-alike hosts such as `https://example.com.evil.net` are now treated as external.
  - URLs that merely contain `mailto:`, `tel:` or `sms:` somewhere (e.g. in the query string) are no longer treated as internal.
- `inlineViteAsset()`:
  - An unknown `$type` (anything other than `'stylesheet'` or `'script'`) now throws instead of silently rendering nothing.
  - Exceptions from `vite()` are no longer caught and re-wrapped, so they reach you unchanged.
- `addSvgAccessibilityAttributes()` / `readAccessible()` no longer output the XML prolog (`<?xml …?>`) in front of the inline SVG.

### Added

- `isExternalUrl(string $url): bool` checks whether a URL points to a host other than the site's own (case-insensitive). Relative URLs, anchors and host-less schemes like `mailto:` are never external.
- `linkLabelForHref(string $href): string|null` returns the matching `linkLabel()` for an href. This is the logic behind `autoLinkTitles()`, now usable on its own.
- `cssLoad(string $file, bool $lazy = false): void` loads a stylesheet either lazily or as a regular stylesheet. `cssIfBlock()` and `cssIfTemplate()` use it internally.
- `addSvgAccessibilityAttributes()` adds accessibility attributes to raw SVG markup without needing a Kirby `File` object. `readAccessible()` uses it internally.

### Fixed

- `cssLazy()` never actually loaded lazily: Kirby's `Html::css()` always overrides `rel` with `stylesheet`, so the file loaded render-blocking. It now renders a real `rel="preload"` link. Kirby's `css` component is still applied, so fingerprinting plugins keep working.
- `autoLinkTitles()`:
  - Titles for email and phone links showed raw entity sequences such as `&#x61;&#64;…` in the tooltip because they were encoded twice.
  - `&amp;` in external URLs turned into `&amp;amp;` in the title.
  - Links with a `data-title` attribute were skipped, and a `data-href` could be mistaken for the link target.
- SVG accessibility (`readAccessible()`, `addSvgAccessibilityAttributes()`):
  - A `$` followed by a digit in a title (e.g. `Costs $1`) corrupted the output.
  - Existing `role` and `aria-*` attributes and `<title>`/`<desc>` elements in the SVG are now replaced instead of duplicated. Their text serves as a fallback when no title or description is given.
- `inlineViteAsset()` now resolves files relative to the Kirby index root. It previously depended on PHP's working directory and failed in the CLI and for installations in a subfolder. The check that the file lies inside the index root is also stricter.
- The `ensureLeft`, `ensureRight`, `ensureHashed` and `autoLinkTitles` field methods no longer throw a `TypeError` on empty or missing fields.
- `ensureLeft()`, `ensureRight()` and `buildMailtoLink()` no longer drop the string `"0"` (e.g. `ensureLeft('0', '#')` now returns `'#0'`).
- `cssIfBlock()` with an array of block types now compares strictly.

### Changed

- Docblocks now sit directly on the functions, so IDEs show parameter hints and descriptions.
- Sanitizing an SVG now happens once per request for each distinct SVG, which speeds up pages that render the same icon many times.

## [1.7.0] - 2026-04-26

See the [GitHub release](https://github.com/timnarr/kirby-helpers/releases/tag/v1.7.0).

[Unreleased]: https://github.com/timnarr/kirby-helpers/compare/v1.8.0...HEAD
[1.8.0]: https://github.com/timnarr/kirby-helpers/compare/v1.7.0...v1.8.0
[1.7.0]: https://github.com/timnarr/kirby-helpers/releases/tag/v1.7.0
