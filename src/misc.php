<?php

use Kirby\Cms\File;
use Kirby\Cms\Html;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Url;
use Kirby\Exception\Exception;
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
 * Increment or decrement a heading level by a specified number of steps.
 *
 * @param string $level The current heading level (e.g., 'h1', 'h2', ..., 'h6').
 * @param int $steps The number of steps to increment (positive) or decrement (negative). Default is 1.
 * @return string The new heading level, clamped between 'h1' and 'h6'.
 * @throws InvalidArgumentException If the provided heading level is not valid.
 *
 * @example
 * incrementHeadingLevel('h2', 1) // returns 'h3'
 * incrementHeadingLevel('h2', -1) // returns 'h1'
 * incrementHeadingLevel('h6', 1) // returns 'h6' (clamped at maximum)
 * incrementHeadingLevel('h1', -1) // returns 'h1' (clamped at minimum)
 */
function incrementHeadingLevel(string $level, int $steps = 1): string
{
	// Validate input
	if (!in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
		throw new InvalidArgumentException("[kirby-helpers] Invalid heading level: `{$level}`. Allowed values are 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'.");
	}

	// Extract the numeric level
	$currentLevel = (int)substr($level, 1);

	// Calculate new level and clamp between 1 and 6
	$newLevel = max(1, min(6, $currentLevel + $steps));

	return 'h' . $newLevel;
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

		$ignoredPages ??= new Pages([]);

		$ignoredPagesIds = array_map(
			fn ($page) => $page->uuid()->id(),
			iterator_to_array($ignoredPages)
		);

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


/**
 * Reads the SVG content and adds accessibility attributes based on custom fields.
 *
 * @param File $file The file object representing the SVG.
 * @param string $title The title for the SVG (optional).
 * @param string $description The description for the SVG (optional).
 * @param bool $isDecorative Whether the SVG is decorative (optional).
 * @return string The modified SVG content with accessibility attributes.
 */
if (!function_exists('readAccessible')) {
	function readAccessible(File $file, string $title = '', string $description = '', bool $isDecorative = false): string
	{
		try {
			if ($file->extension() !== 'svg') {
				return $file->read();
			}

			$svgContent = $file->read();

			// Try to get values from custom fields if not provided
			if (empty($title) && $file->svgTitle()->isNotEmpty()) {
				$title = $file->svgTitle()->value();
			}

			if (empty($description) && $file->svgDescription()->isNotEmpty()) {
				$description = $file->svgDescription()->value();
			}

			// Check if marked as decorative in custom field
			if ($file->svgDecorative()->toBool()) {
				$isDecorative = true;
			}

			if ($isDecorative) {
				$svgContent = str_replace(
					'<svg',
					'<svg aria-hidden="true"',
					$svgContent
				);
			} else {
				$uniqueId = uniqid('svg-');
				$finalTitle = $title ?: $file->alt()->or($file->name())->value();

				// aria-labelledby="uniqueTitleID uniqueDescID" (use the title and desc ID’s) – both title and description are included in aria-labelledby because it has better screen-reader support than aria-describedby
				$ariaAttributes = 'role="img" aria-labelledby="' . $uniqueId . '-title"';
				if ($description) {
					$ariaAttributes = 'role="img" aria-labelledby="' . $uniqueId . '-title ' . $uniqueId . '-desc"';
				}

				$svgContent = str_replace('<svg', '<svg ' . $ariaAttributes, $svgContent);

				$titleElement = '<title id="' . $uniqueId . '-title">' . Html::encode($finalTitle) . '</title>';
				$descElement = $description ? '<desc id="' . $uniqueId . '-desc">' . Html::encode($description) . '</desc>' : '';

				$svgContent = preg_replace('/(<svg[^>]*>)/', '$1' . $titleElement . $descElement, $svgContent);
			}

			return $svgContent;
		} catch (Exception $e) {
			throw new InvalidArgumentException(
				"[kirby-helpers] Failed to read or process file: {$file->filename()}. " . $e->getMessage()
			);
		}
	}
}

/**
 * Build a mailto link with optional subject and body parameters.
 *
 * @param string $email The email address (will be obfuscated).
 * @param string|null $subject Optional subject line for the email.
 * @param string|null $body Optional body text for the email.
 * @return string The complete mailto link with query parameters.
 *
 * @example
 * buildMailtoLink('test@example.com', 'Hello', 'This is a test')
 * // returns 'mailto:obfuscated@email.com?subject=hello&body=this-is-a-test'
 *
 * @example
 * buildMailtoLink('test@example.com')
 * // returns 'mailto:obfuscated@email.com'
 */
function buildMailtoLink(string $email, string|null $subject = null, string|null $body = null): string
{
	// Start with mailto and obfuscated email
	$mailto = 'mailto:' . Kirby\Toolkit\Str::encode($email);

	$params = [];

	// Add subject if provided
	if (!empty($subject)) {
		$params[] = 'subject=' . rawurlencode($subject);
	}

	// Add body if provided
	if (!empty($body)) {
		// Convert literal \n to actual line breaks
		$body = str_replace('\\n', "\n", $body);
		// Normalize line breaks to \r\n (CRLF) for email compatibility
		$body = str_replace(["\r\n", "\r", "\n"], "\r\n", $body);
		$params[] = 'body=' . rawurlencode($body);
	}

	// Append parameters if any exist
	if (!empty($params)) {
		$mailto .= '?' . implode('&', $params);
	}

	return $mailto;
}
