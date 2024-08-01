<?php

use Kirby\Cms\Asset;
use Kirby\Filesystem\F;

if (!function_exists('isViteDevMode')) {
	/**
	 * Check if Vite is in development mode by verifying the presence of the manifest file.
	 *
	 * @return bool True if Vite is in development mode, false otherwise.
	 */
	function isViteDevMode(): bool
	{
		$manifestPath = kirby()->option('timnarr.kirby-helpers.vite.manifestPath');
		return !F::exists($manifestPath);
	}
}

if (!function_exists('inlineViteAsset')) {
	/**
	 * Inline a Vite asset (stylesheet or script) based on the environment (development or production).
	 * Supports multiple files if an array is provided.
	 *
	 * @param string|array $files The asset file path or an array of asset file paths.
	 * @param string $type The type of the asset ('stylesheet' or 'script').
	 */
	function inlineViteAsset(string|array $files, string $type): void
	{
		$files = (array)$files; // Ensure $files is an array

		if (isViteDevMode()) {
			foreach ($files as $file) {
				$filePath = vite()->asset($file);
				if ($type === 'stylesheet') {
					echo '<link rel="stylesheet" href="' . $filePath . '">';
				} elseif ($type === 'script') {
					echo '<script src="' . $filePath . '" type="module"></script>';
				}
			}
		} else {
			$content = '';
			foreach ($files as $file) {
				$fileContent = F::read(vite()->asset($file));
				$content .= $fileContent;
			}

			if ($type === 'stylesheet') {
				echo '<style>' . $content . '</style>';
			} elseif ($type === 'script') {
				echo '<script>' . $content . '</script>';
			}
		}
	}
}
