<?php

use Kirby\Cms\File;
use Kirby\Cms\Html;
use Kirby\Cms\Layout;
use Kirby\Cms\Layouts;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Url;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Sane\Sane;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

/**
 * Validate a heading level string.
 *
 * @param string $level The heading level to validate.
 * @return void
 * @throws InvalidArgumentException If the provided heading level is not valid.
 */
if (!function_exists('validateHeadingLevel')) {
	function validateHeadingLevel(string $level): void
	{
		if (!in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
			throw new InvalidArgumentException(
				"[kirby-helpers] Invalid heading level: `{$level}`. Allowed values are 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'."
			);
		}
	}
}

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
		validateHeadingLevel($level);

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
if (!function_exists('incrementHeadingLevel')) {
	function incrementHeadingLevel(string $level, int $steps = 1): string
	{
		validateHeadingLevel($level);

		$currentLevel = (int)substr($level, 1);
		$newLevel = max(1, min(6, $currentLevel + $steps));

		return 'h' . $newLevel;
	}
}

/**
 * Determine if a link should open in a new tab (if external) and return an array of attributes.
 *
 * @param string $link The URL link.
 * @param bool $omitHref Optional. If true, the href attribute will be null.
 * @return array The attributes for the link.
 */
if (!function_exists('setBlankIfExternal')) {
	function setBlankIfExternal(string $link, bool $omitHref = false): array
	{
		$isInternal = str_starts_with($link, Url::home()) ||
			str_contains($link, 'mailto:') ||
			str_contains($link, 'tel:') ||
			str_contains($link, 'sms:');

		$attrs = [];
		if (!$omitHref) {
			$attrs['href'] = $link;
		}
		if (!$isInternal) {
			$attrs['target'] = '_blank';
		}

		return $attrs;
	}
}


/**
 * Generate a link label based on the type and data provided.
 *
 * @param string $type The type of link (e.g., 'internal', 'document', 'external', 'mail', 'tel', 'custom', 'anchor').
 * @param string|Page|File|\Closure $data The data used to generate the label.
 * @return string The generated link label.
 * @throws InvalidArgumentException If an invalid type is provided or if data for certain types does not meet the expected type.
 */
if (!function_exists('linkLabel')) {
	function linkLabel(string $type, string|Page|File|\Closure $data): string
	{
		return match ($type) {
			'anchor' => is_string($data)
				? tt('link_label_anchor', ['anchor' => $data])
				: throw new InvalidArgumentException('[kirby-helpers] Data for "anchor" type must be a string, ' . get_debug_type($data) . ' given.'),
			'internal' => match (true) {
				!($data instanceof Page) => throw new InvalidArgumentException(
					'[kirby-helpers] Data for "internal" type must be an instance of Page, ' . get_debug_type($data) . ' given.'
				),
				$data->isHomepage() => tt('link_label_internal_home', ['title' => site()->title()]),
				default => tt('link_label_internal', ['title' => $data->metaTitle()->or($data->title())]),
			},

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

			'custom' => $data instanceof \Closure
				? $data()
				: (is_string($data)
					? $data
					: throw new InvalidArgumentException('[kirby-helpers] Data for "custom" type must be a string or a Closure, ' . get_debug_type($data) . ' given.')),

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
		$ignoredTemplates = array_merge(['error'], $ignoredTemplates);
		$ignoredPages ??= new Pages([]);

		$ignoredPageIds = array_map(
			fn ($p) => $p->uuid()->id(),
			iterator_to_array($ignoredPages)
		);

		return in_array($page->intendedTemplate()->name(), $ignoredTemplates) ||
			in_array($page->slug(), $ignoredSlugs) ||
			in_array($page->uuid()->id(), $ignoredPageIds);
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
		if (!kirby()->multilang()) {
			return [];
		}

		$currentCode = kirby()->language()->code();
		$translations = [];

		foreach (kirby()->languages() as $language) {
			$code = $language->code();

			if ($code !== $currentCode && $page->translation($code)->exists()) {
				$translations[] = $code;
			}
		}

		return $translations;
	}
}


/**
 * Get an array of language codes for which the page translation does not exist.
 *
 * @param Page $page The page for which to check missing translations.
 * @return array Returns an array of language codes that are missing translations.
 */
if (!function_exists('getMissingTranslations')) {
	function getMissingTranslations(Page $page): array
	{
		$missing = [];

		foreach (kirby()->languages() as $language) {
			if (!$page->translation($language->code())->exists()) {
				$missing[] = $language->code();
			}
		}

		return $missing;
	}
}


/**
 * Sanitizes SVG markup and injects accessibility attributes: either `aria-hidden` for decorative
 * SVGs, or a `<title>`/`<desc>` pair wired up via `aria-labelledby`.
 *
 * @param string $svgContent The raw SVG markup.
 * @param string $title The title for the SVG (optional).
 * @param string $description The description for the SVG (optional).
 * @param bool $isDecorative Whether the SVG is decorative (optional).
 * @param string $fallbackTitle Title to fall back to when non-decorative and no title was given.
 * @return string The SVG markup with accessibility attributes applied.
 */
if (!function_exists('addSvgAccessibilityAttributes')) {
	function addSvgAccessibilityAttributes(
		string $svgContent,
		string $title = '',
		string $description = '',
		bool $isDecorative = false,
		string $fallbackTitle = ''
	): string {
		$svgContent = Sane::sanitize($svgContent, 'svg');

		if ($isDecorative) {
			return preg_replace('/<svg/', '<svg aria-hidden="true"', $svgContent, 1);
		}

		$uniqueId = uniqid('svg-');
		$finalTitle = $title ?: $fallbackTitle;

		// aria-labelledby with both title and desc IDs has better screen-reader support than aria-describedby
		$labelledBy = $uniqueId . '-title' . ($description ? ' ' . $uniqueId . '-desc' : '');
		$ariaAttributes = 'role="img" aria-labelledby="' . $labelledBy . '"';

		$svgContent = preg_replace('/<svg/', '<svg ' . $ariaAttributes, $svgContent, 1);

		$titleElement = '<title id="' . $uniqueId . '-title">' . Html::encode($finalTitle) . '</title>';
		$descElement = $description ? '<desc id="' . $uniqueId . '-desc">' . Html::encode($description) . '</desc>' : '';

		return preg_replace('/(<svg[^>]*>)/', '$1' . $titleElement . $descElement, $svgContent, 1);
	}
}

/**
 * Reads the SVG content of a Kirby file and adds accessibility attributes based on custom fields.
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

			if (empty($title) && $file->svgTitle()->isNotEmpty()) {
				$title = $file->svgTitle()->value();
			}

			if (empty($description) && $file->svgDescription()->isNotEmpty()) {
				$description = $file->svgDescription()->value();
			}

			$isDecorative = $isDecorative || $file->svgDecorative()->toBool();

			return addSvgAccessibilityAttributes(
				$file->read(),
				$title,
				$description,
				$isDecorative,
				$file->alt()->or($file->name())->value()
			);
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
 * @param string $email The email address (will be obfuscated). Should not be pre-encoded.
 * @param string|null $subject Optional subject line for the email. Should not be pre-encoded.
 * @param string|null $body Optional body text for the email. Line breaks can be added with literal \n. Should not be pre-encoded.
 * @return string The complete mailto link with query parameters.
 *
 * @example
 * buildMailtoLink('test@example.com', 'Hello', 'This is a test')
 * // returns 'mailto:obfuscated@email.com?subject=Hello&body=This%20is%20a%20test'
 *
 * @example
 * buildMailtoLink('test@example.com')
 * // returns 'mailto:obfuscated@email.com'
 *
 * @example with line breaks
 * buildMailtoLink('test@example.com', 'Hello', 'Line 1\nLine 2')
 * // returns mailto link with proper line break encoding
 */
if (!function_exists('buildMailtoLink')) {
	function buildMailtoLink(string $email, string|null $subject = null, string|null $body = null): string
	{
		$mailto = 'mailto:' . Str::encode($email);

		$params = [];

		if (!empty($subject)) {
			$params[] = 'subject=' . rawurlencode($subject);
		}

		if (!empty($body)) {
			$body = str_replace('\\n', "\n", $body);
			// Normalize line breaks to CRLF for email compatibility
			$body = str_replace(["\r\n", "\r", "\n"], "\r\n", $body);
			$params[] = 'body=' . rawurlencode($body);
		}

		if (!empty($params)) {
			$mailto .= '?' . implode('&', $params);
		}

		return $mailto;
	}
}

/**
 * Extract all used block types from a Layouts, Layout object or array of layouts.
 * Iterates through all layouts, their columns, and blocks to find all used block types.
 *
 * @param \Kirby\Cms\Layouts|\Kirby\Cms\Layout|array $layouts The layouts to analyze.
 * @return array An array of block type strings.
 *
 * @example
 * $blockTypes = getUsedBlockTypesFromLayouts($page->sections()->toLayouts());
 * // returns ['heading', 'text', 'image', 'gallery']
 *
 * @example with cssIfBlock
 * $pageBlocks = getUsedBlockTypesFromLayouts($page->sections()->toLayouts());
 * cssIfBlock('assets/css/gallery.css', 'gallery', $pageBlocks);
 */
if (!function_exists('getUsedBlockTypesFromLayouts')) {
	function getUsedBlockTypesFromLayouts(Layouts|Layout|array $layouts): array
	{
		$types = [];

		if ($layouts instanceof Layout) {
			$layouts = [$layouts];
		}

		foreach ($layouts as $layout) {
			foreach ($layout->columns() as $column) {
				foreach ($column->blocks() as $block) {
					$types[] = $block->type();
				}
			}
		}

		return array_values(array_unique($types));
	}
}


/**
 * Automatically add title attributes to links in HTML content.
 * Detects link types (internal pages, files, email, phone, external) and generates appropriate titles.
 *
 * @param string $html The HTML content containing links.
 * @return string The HTML with title attributes added to links (if they don't already have one).
 *
 * @example
 * autoLinkTitles('<a href="/@/page/abc123">Contact</a>')
 * // returns '<a href="/@/page/abc123" title="Link to page: Contact">Contact</a>'
 */
if (!function_exists('autoLinkTitles')) {
	function autoLinkTitles(string $html): string
	{
		return preg_replace_callback(
			'/<a\s+([^>]*?)>/i',
			function ($matches) {
				$attributes = $matches[1];

				if (preg_match('/\btitle\s*=/i', $attributes)) {
					return $matches[0];
				}

				if (!preg_match('/\bhref\s*=\s*(["\'])(.*?)\1/i', $attributes, $hrefMatch)) {
					return $matches[0];
				}

				$href = $hrefMatch[2];
				$title = null;

				if (preg_match('#^/@/page/([a-z0-9]+)#i', $href, $uuidMatch)) {
					try {
						$page = Uuid::for('page://' . $uuidMatch[1])?->model();
						if ($page instanceof Page) {
							$title = linkLabel('internal', $page);
						}
					} catch (\Exception $e) {
						// Invalid or unresolvable UUID: leave the link without a title
					}
				} elseif (preg_match('#^/@/file/([a-z0-9]+)#i', $href, $uuidMatch)) {
					try {
						$file = Uuid::for('file://' . $uuidMatch[1])?->model();
						if ($file instanceof File) {
							$title = linkLabel('document', $file);
						}
					} catch (\Exception $e) {
						// Invalid or unresolvable UUID: leave the link without a title
					}
				} elseif (preg_match('/^mailto:(.+)/i', $href, $mailMatch)) {
					$title = linkLabel('mail', $mailMatch[1]);
				} elseif (preg_match('/^tel:(.+)/i', $href, $telMatch)) {
					$title = linkLabel('tel', $telMatch[1]);
				} elseif (preg_match('#^https?://#i', $href)) {
					$currentHost = parse_url(Url::home(), PHP_URL_HOST);
					$linkHost = parse_url($href, PHP_URL_HOST);

					if ($currentHost !== $linkHost) {
						$title = linkLabel('external', $href);
					}
				}

				if ($title !== null) {
					return '<a ' . trim($attributes) . ' title="' . Html::encode($title) . '">';
				}

				return $matches[0];
			},
			$html
		);
	}
}
