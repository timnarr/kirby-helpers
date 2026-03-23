<?php

use Kirby\Cms\Blocks;
use Kirby\Cms\Html;

/**
 * Load a CSS file lazily.
 *
 * @param string $file The CSS file path.
 * @param bool $omitNoscript If true, omit the noscript fallback. Default: false.
 *
 * @example
 * cssLazy('assets/css/carousel.css', true);
 *
 * @example with vite
 * cssLazy(vite()->asset('styles/carousel.scss'), true);
 */
if (!function_exists('cssLazy')) {
	function cssLazy(string $file, bool $omitNoscript = false): void
	{
		echo Html::css(url: $file, options: [
			'as' => 'style',
			'rel' => 'preload',
			'fetchpriority' => 'low',
			'onload' => "this.onload=null;this.rel='stylesheet'",
		]);

		if (!$omitNoscript) {
			echo '<noscript>' . Html::css(url: $file) . '</noscript>';
		}
	}
}


/**
 * Load CSS file only if a defined block is used.
 *
 * @param string $file The CSS file path.
 * @param string $blockType The block type to check.
 * @param Blocks|array $blocks The Blocks object or array of used block type strings.
 * @param bool $lazy Optional. If true, load the CSS file lazily.
 *
 * @example
 * cssIfBlock('assets/css/carousel.css', 'carousel', $page->text()->toBlocks(), true);
 *
 * @example with vite
 * cssIfBlock(vite()->asset('styles/carousel.scss'), 'carousel', $page->text()->toBlocks(), true);
 */
if (!function_exists('cssIfBlock')) {
	function cssIfBlock(string $file, string $blockType, Blocks|array $blocks, bool $lazy = false): void
	{
		$found = $blocks instanceof Blocks
			? $blocks->hasType($blockType)
			: in_array($blockType, $blocks);

		if ($found) {
			if ($lazy) {
				cssLazy($file);
			} else {
				echo Html::css(url: $file);
			}
		}
	}
}


/**
 * Load a CSS file only for a defined page template or an array of templates.
 *
 * @param string $file The CSS file path.
 * @param string|array $template The page template name or an array of template names.
 * @param bool $lazy Optional. If true, load the CSS file lazily.
 */
if (!function_exists('cssIfTemplate')) {
	function cssIfTemplate(string $file, string|array $template, bool $lazy = false): void
	{
		$templates = is_array($template) ? $template : [$template];
		$currentTemplate = page()->intendedTemplate()->name();

		if (in_array($currentTemplate, $templates, true)) {
			if ($lazy) {
				cssLazy($file);
			} else {
				echo Html::css(url: $file);
			}
		}
	}
}
