<?php

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;

if (!function_exists('isViteDevMode')) {
	/**
	 * Check if Vite is in development mode by verifying the presence of the manifest file.
	 * Uses static caching to avoid repeated filesystem checks.
	 *
	 * @return bool True if Vite is in development mode, false otherwise.
	 */
	function isViteDevMode(): bool
	{
		static $devMode = null;

		if ($devMode === null) {
			$manifestPath = kirby()->option('timnarr.kirby-helpers.vite.manifestPath');
			if (is_callable($manifestPath)) {
				$manifestPath = $manifestPath();
			}
			$devMode = !F::exists($manifestPath);
		}

		return $devMode;
	}
}

if (!function_exists('inlineViteAsset')) {
	/**
	 * Inline a Vite asset (stylesheet or script) based on the environment (development or production).
	 * Supports multiple files if an array is provided.
	 *
	 * @param string|array $files The asset file path or an array of asset file paths.
	 * @param string $type The type of the asset ('stylesheet' or 'script').
	 * @throws InvalidArgumentException If the type is invalid or an asset cannot be resolved or read.
	 */
	function inlineViteAsset(string|array $files, string $type): void
	{
		if (!in_array($type, ['stylesheet', 'script'], true)) {
			throw new InvalidArgumentException(
				"[kirby-helpers] Invalid asset type: `{$type}`. Allowed values are 'stylesheet', 'script'."
			);
		}

		$files = is_array($files) ? $files : [$files];

		if (isViteDevMode()) {
			foreach ($files as $file) {
				$filePath = vite()->asset($file);
				echo $type === 'stylesheet'
					? Html::css(url: $filePath)
					: Html::tag(name: 'script', attr: ['type' => 'module', 'src' => $filePath]);
			}

			return;
		}

		$content = '';
		foreach ($files as $file) {
			$content .= F::read(resolveViteAssetPath($file)) . "\n";
		}

		echo Html::tag(name: $type === 'stylesheet' ? 'style' : 'script', content: [$content]);
	}
}

if (!function_exists('resolveViteAssetPath')) {
	/**
	 * Resolve a Vite asset to its absolute path on disk, guarding against paths outside the Kirby index root.
	 *
	 * @param string $file The asset file path as passed to `vite()->asset()`.
	 * @return string The absolute, readable file path.
	 * @throws InvalidArgumentException If the asset is outside the index root or not readable.
	 */
	function resolveViteAssetPath(string $file): string
	{
		// Strip the index URL's path (e.g. `sub` for installations in a subfolder) from the asset URL path
		$assetPath = trim(Url::path(vite()->asset($file)), '/');
		$basePath = trim(Url::path(kirby()->url('index')), '/');

		if ($basePath !== '') {
			if (!str_starts_with($assetPath, $basePath . '/')) {
				throw new InvalidArgumentException("[kirby-helpers] Asset is outside the index URL: {$file}");
			}

			$assetPath = substr($assetPath, strlen($basePath) + 1);
		}

		$rootPath = realpath(kirby()->root('index'));
		$realPath = realpath($rootPath . '/' . $assetPath);

		if ($rootPath === false || $realPath === false || !str_starts_with($realPath, $rootPath . DIRECTORY_SEPARATOR)) {
			throw new InvalidArgumentException("[kirby-helpers] Invalid asset path: {$file}");
		}

		if (!is_readable($realPath)) {
			throw new InvalidArgumentException("[kirby-helpers] Failed to read asset: {$file}");
		}

		return $realPath;
	}
}
