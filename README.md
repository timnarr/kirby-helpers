# Kirby Helpers

Kirby Helpers is a collection of useful helper functions for Kirby CMS.

## Installation via Composer
To install Kirby Helpers via Composer, run the following command:

```bash
composer require timnarr/kirby-helpers
```

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
