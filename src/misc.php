<?php

use Kirby\Cms\File;
use Kirby\Cms\Html;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Url;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Generate an HTML heading element with the specified level, text, and attributes.
 *
 * @param string $level The heading level (e.g., 'h1', 'h2', 'h3', 'h4', 'h5', 'h6').
 * @param string $text The text content of the heading.
 * @param array $attrs An associative array of HTML attributes (optional).
 * @return string The generated HTML string for the heading.
 * @throws InvalidArgumentException If the provided heading level is not valid.
 */
if (!function_exists('heading')) {
	function heading(string $level, string $text, array $attrs = []): string
	{
		// Check if the provided level is valid
		if (!in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
			throw new InvalidArgumentException("[kirby-helpers] Invalid heading level: `{$level}`, as " . get_debug_type($level) . ". Allowed values are 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'.");
		}

		// Return the complete heading element
		return Html::tag(name: $level, content: $text, attr: $attrs);
	}
}


/**
 * Determine if a link should open in a new tab (if external) and return an array of attributes.
 *
 * @param string $link The URL link.
 * @param bool $dontReturnHref Optional. If true, the href attribute will be null.
 * @return array The attributes for the link.
 */
if (!function_exists('setBlankIfExternal')) {
	function setBlankIfExternal(string $link, bool $dontReturnHref = false): array
	{
		$self = Url::home();
		$internalPatterns = ['mailto:', 'tel:', 'sms:', $self];

		$isInternal = array_filter($internalPatterns, fn ($pattern) => str_contains($link, $pattern));

		return [
			...(!$dontReturnHref ? ['href' => $link] : []),
			...(!$isInternal ? ['target' => '_blank'] : []),
		];
	}
}


/**
 * Generate a link label based on the type and data provided.
 *
 * @param string $type The type of link (e.g., 'internal', 'document', 'external', 'mail', 'tel', 'custom').
 * @param mixed $data The data used to generate the label.
 * @return string The generated link label.
 * @throws InvalidArgumentException If an invalid type is provided or if data for certain types does not meet the expected type.
 */
if (!function_exists('linkLabel')) {
	function linkLabel(string $type, string|Page|File $data): string
	{
		return match ($type) {
			'internal' => $data instanceof Page
				? ($data->isHomepage()
					? tt('link_label_internal_home', ['title' => site()->title()])
					: tt('link_label_internal', ['title' => $data->metaTitle()->or($data->title())]))
				: throw new InvalidArgumentException('[kirby-helpers] Data for "internal" type must be an instance of Page, ' . get_debug_type($data) . ' given.'),

			'document' => $data instanceof File
				? tt('link_label_document', ['filename' => $data->filename() . ' (' . $data->niceSize() . ')'])
				: throw new InvalidArgumentException('[kirby-helpers] Data for "document" type must be an instance of File, ' . get_debug_type($data) . ' given.'),

			'external' => is_string($data)
				? tt('link_label_external', ['url' => $data])
				: throw new InvalidArgumentException('[kirby-helpers] Data for "external" type must be a string, ' . get_debug_type($data) . ' given.'),

			'mail' => is_string($data)
				? tt('link_label_mail', ['mail' => Str::encode($data)])
				: throw new InvalidArgumentException('[kirby-helpers] Data for "mail" type must be a string, ' . get_debug_type($data) . ' given.'),

			'tel' => is_string($data)
				? tt('link_label_tel', ['tel' => Str::encode($data)])
				: throw new InvalidArgumentException('[kirby-helpers] Data for "tel" type must be a string, ' . get_debug_type($data) . ' given.'),

			'custom' => is_string($data) || is_callable($data)
				? (is_callable($data) ? $data() : $data)
				: throw new InvalidArgumentException('[kirby-helpers] Data for "custom" type must be a string or a callable, ' . get_debug_type($data) . ' given.'),

			default => throw new InvalidArgumentException('[kirby-helpers] Invalid type provided for linkLabel function: ' . $type),
		};
	}
}


/**
 * Determine if a given page should be excluded from caching based on specified conditions.
 *
 * @param Page $page The page to be checked.
 * @param Pages $ignoredPages A collection of pages that should be ignored.
 * @param array $ignoredSlugs An array of slugs that should be ignored (optional).
 * @param array $ignoredTemplates An array of templates that should be ignored (optional).
 * @return bool Returns true if the page is in one of the ignored lists (templates, slugs, or specific pages); otherwise, returns false.
 *
 * @example
 * shouldIgnorePageFromCache($page, $ignoredPages, ['example-slug'], ['contact-template'])
 *
 * @example
 * 'cache.pages.ignore' => function ($page) {
 *   return shouldIgnorePageFromCache($page, site()->notCachedPages()->toPages(), ['my-slug'], ['my-template']);
 * }
 */
if (!function_exists('shouldIgnorePageFromCache')) {
	function shouldIgnorePageFromCache(Page $page, Pages|null $ignoredPages, array $ignoredSlugs = [], array $ignoredTemplates = []): bool
	{
		$defaultIgnoredSlugs = [];
		$defaultIgnoredTemplates = ['error'];

		$ignoredSlugs = array_merge($defaultIgnoredSlugs, $ignoredSlugs);
		$ignoredTemplates = array_merge($defaultIgnoredTemplates, $ignoredTemplates);

		$ignoredPagesIds = [];
		$ignoredPages ??= new Pages([]);

		foreach ($ignoredPages as $ignoredPage) {
			array_push($ignoredPagesIds, $ignoredPage->uuid()->id());
		}

		// Check if the current page is in one of the ignored lists
		if (
			in_array($page->template(), $ignoredTemplates) ||
			in_array($page->slug(), $ignoredSlugs) ||
			in_array($page->uuid()->id(), $ignoredPagesIds)
		) {
			return true;
		}

		return false;
	}
}


/**
 * This function returns an array of language codes where translations are available
 * for a provided page, ignoring the current language code.
 *
 * @param Page $page The page for which to check available translations.
 * @return array Returns an array of language codes that have translations available, excluding the current language.
 */
if (!function_exists('getAvailableTranslations')) {
	function getAvailableTranslations(Page $page): array
	{
		$languages = kirby()->languages();
		$currentLanguageCode = kirby()->language()->code();

		$availableTranslations = [];

		foreach ($languages as $language) {
			$languageCode = $language->code();

			// Skip the current language
			if ($languageCode === $currentLanguageCode) {
				continue;
			}

			// Check if translation exists for this language code
			if ($page->translation($languageCode)->exists()) {
				$availableTranslations[] = $languageCode;
			}
		}

		return $availableTranslations;
	}
}
