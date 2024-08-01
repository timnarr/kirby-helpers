<?php

use Kirby\Cms\Html;

/**
 * Load a CSS file lazily.
 *
 * @param string $file The CSS file path.
 * @param bool $omitNoscript Optional. If true, omit the noscript fallback.
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
		echo Html::css($file, [
			'as' => 'style',
			'rel' => 'preload',
			'fetchpriority' => 'low',
			'onload' => "this.onload=null;this.rel='stylesheet'",
		]);

		if (!$omitNoscript) {
			echo '<noscript>' . Html::css($file) . '</noscript>';
		}
	}
}


/**
 * Load CSS file only if a defined block is used.
 *
 * @param string $file The CSS file path.
 * @param string $blockType The block type to check.
 * @param array $usedBlockTypes The array of used block types.
 * @param bool $lazy Optional. If true, load the CSS file lazily.
 *
 * @example
 * cssIfBlock('assets/css/carousel.css', 'carousel', $pageBlocks, true);
 *
 * @example with vite
 * cssIfBlock(vite()->asset('styles/carousel.scss'), 'carousel', $pageBlocks, true);
 */
if (!function_exists('cssIfBlock')) {
	function cssIfBlock(string $file, string $blockType, array $usedBlockTypes, bool $lazy = false): void
	{
		if (in_array($blockType, $usedBlockTypes)) {
			if ($lazy) {
				cssLazy($file);
			} else {
				Html::css($file);
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
		$templates = (array)$template;
		$currentTemplate = page()->template();

		if (in_array($currentTemplate, $templates, true)) {
			if ($lazy) {
				cssLazy($file);
			} else {
				Html::css($file);
			}
		}
	}
}
