# Kirby Helpers

Kirby Helpers is a collection of useful helper functions for Kirby CMS.

## Installation via Composer
To install Kirby Helpers via Composer, run the following command:

```bash
composer require timnarr/kirby-helpers
```

## Available Functions

### CSS Helpers

#### `cssLazy(string $file, bool $omitNoscript = false): void`
Load a CSS file lazily using preload with low priority.

```php
cssLazy('assets/css/carousel.css');
cssLazy(vite()->asset('styles/carousel.scss'), true);
```

#### `cssIfBlock(string $file, string $blockType, array $usedBlockTypes, bool $lazy = false): void`
Load CSS only if a specific block type is used on the page.

```php
cssIfBlock('assets/css/carousel.css', 'carousel', $pageBlocks);
cssIfBlock(vite()->asset('styles/carousel.scss'), 'carousel', $pageBlocks, true);
```

#### `cssIfTemplate(string $file, string|array $template, bool $lazy = false): void`
Load CSS only for specific page template(s).

```php
cssIfTemplate('assets/css/contact.css', 'contact');
cssIfTemplate('assets/css/forms.css', ['contact', 'signup']);
```

---

### String Helpers

#### `ensureLeft(string $string, string $prefix): string`
Ensure a string starts with a specific prefix.

```php
ensureLeft('example.com', 'https://'); // 'https://example.com'
ensureLeft('https://example.com', 'https://'); // 'https://example.com'
```

#### `ensureRight(string $string, string $suffix): string`
Ensure a string ends with a specific suffix.

```php
ensureRight('example', '.com'); // 'example.com'
ensureRight('example.com', '.com'); // 'example.com'
```

#### `ensureHashed(string $string): string`
Ensure a string starts with a hash character (#). Useful for anchor links.

```php
ensureHashed('section-1'); // '#section-1'
ensureHashed('#section-1'); // '#section-1'
```

---

### HTML/Heading Helpers

#### `heading(string $level, string $text, array $attrs = []): string`
Generate an HTML heading element with specified level, text, and attributes.

```php
heading('h2', 'Welcome', ['class' => 'title']);
// <h2 class="title">Welcome</h2>
```

#### `incrementHeadingLevel(string $level, int $steps = 1): string`
Increment or decrement a heading level, clamped between h1 and h6.

```php
incrementHeadingLevel('h2', 1);  // 'h3'
incrementHeadingLevel('h2', -1); // 'h1'
incrementHeadingLevel('h6', 1);  // 'h6' (clamped)
```

#### `validateHeadingLevel(string $level): void`
Validate a heading level string. Throws exception if invalid.

```php
validateHeadingLevel('h2'); // OK
validateHeadingLevel('h7'); // Throws InvalidArgumentException
```

---

### Link Helpers

#### `setBlankIfExternal(string $link, bool $dontReturnHref = false): array`
Determine if a link is external and return appropriate attributes (target="_blank" for external links).

```php
setBlankIfExternal('https://example.com');
// ['href' => 'https://example.com', 'target' => '_blank']

setBlankIfExternal('mailto:test@example.com');
// ['href' => 'mailto:test@example.com']
```

#### `linkLabel(string $type, string|Page|File $data): string`
Generate accessible link labels for different link types.

```php
linkLabel('internal', $page);   // "Link to page: {title}"
linkLabel('external', 'https://example.com'); // "External link: https://example.com (Opens new tab)"
linkLabel('document', $file);   // "Download file: document.pdf (2.5 MB)"
linkLabel('mail', 'test@example.com'); // "Send email to: test@example.com"
linkLabel('tel', '+1234567890'); // "Call phone number: +1234567890"
```

#### `buildMailtoLink(string $email, string|null $subject = null, string|null $body = null): string`
Build a mailto link with optional subject and body parameters.

```php
buildMailtoLink('test@example.com', 'Hello', 'This is a test');
// 'mailto:obfuscated@email.com?subject=Hello&body=This%20is%20a%20test'

buildMailtoLink('test@example.com', 'Hello', 'Line 1\nLine 2');
// Line breaks are properly encoded
```

---

### Page/Cache Helpers

#### `shouldIgnorePageFromCache(Page $page, Pages|null $ignoredPages, array $ignoredSlugs = [], array $ignoredTemplates = []): bool`
Determine if a page should be excluded from caching.

```php
shouldIgnorePageFromCache($page, site()->notCachedPages()->toPages(), ['my-slug'], ['contact']);
```

#### `getAvailableTranslations(Page $page): array`
Get available translation language codes for a page (excluding current language).

```php
getAvailableTranslations($page); // ['de', 'fr']
```

---

### File Helpers

#### `readAccessible(File $file, string $title = '', string $description = '', bool $isDecorative = false): string`
Read and enhance SVG files with accessibility attributes (title, description, ARIA attributes).

```php
readAccessible($file, 'Icon description', 'Detailed description');
readAccessible($file, '', '', true); // Decorative SVG with aria-hidden
```

---

### Vite Helpers

#### `isViteDevMode(): bool`
Check if Vite is in development mode by verifying manifest file presence.

```php
if (isViteDevMode()) {
    // Development-specific code
}
```

#### `inlineViteAsset(string|array $files, string $type): void`
Inline Vite assets (stylesheet or script) based on environment.

```php
inlineViteAsset('main.css', 'stylesheet');
inlineViteAsset(['app.js', 'vendor.js'], 'script');
```

---

## Field Methods

### `ensureLeft(string $prefix): string`
```php
$page->myField()->ensureLeft('https://');
```

### `ensureRight(string $suffix): string`
```php
$page->myField()->ensureRight('/');
```

---

## File Methods

### `readAccessible(string $title = '', string $description = '', bool $isDecorative = false): string`
```php
$file->readAccessible('Icon title', 'Icon description');
```

---

## Page Methods

### `hasTranslations(): bool`
```php
if ($page->hasTranslations()) {
    // Page has translations
}
```

### `getTranslations(): array`
```php
$translations = $page->getTranslations(); // ['de', 'fr']
```

---

## Options
The following options are available for customization:

| Option | Default | Type | Description |
| ------ | ------- | ---- | ----------- |
| `vite.manifestPath` | `kirby()->root() . '/build/manifest.json'` | string | Path to vites manifest file to determine dev mode. Used by `isViteDevMode()` |

## Translations
Translations are required for the labels returned by the `linkLabel()` function. This plugin provides translations for English and German. The following translation keys are available for customization:

| Key | Default |
| --- | ------- |
| `link_label_internal_home` | `Link to homepage: { title }` |
| `link_label_internal` | `Link to page: { title }` |
| `link_label_document` | `Download file: { filename }` |
| `link_label_external` | `External link: { url } (Opens new tab)` |
| `link_label_mail` | `Send email to: { mail } (Opens new window of your email program)` |
| `link_label_tel` | `Call phone number: { tel } (Opens new window/program)` |


## License
Kirby Helpers is licensed under the [MIT License](./LICENSE). © 2024-present Tim Narr
