<?php

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Exception\Exception;
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
	 */
	function inlineViteAsset(string|array $files, string $type): void
	{
		$files = is_array($files) ? $files : [$files];

		if (isViteDevMode()) {
			foreach ($files as $file) {
				$filePath = vite()->asset($file);
				if ($type === 'stylesheet') {
					echo Html::css(url: $filePath);
				} elseif ($type === 'script') {
					echo Html::tag(name: 'script', attr: ['type' => 'module', 'src' => $filePath]);
				}
			}
		} else {
			$content = '';
			foreach ($files as $file) {
				try {
					$assetPath = vite()->asset($file);
					$fullPath = Url::path($assetPath);
					$realPath = realpath($fullPath);
					$rootPath = realpath(kirby()->root());

					if ($realPath === false || !str_starts_with($realPath, $rootPath)) {
						throw new InvalidArgumentException("[kirby-helpers] Invalid asset path: {$file}");
					}

					$fileContent = F::read($realPath);
					$content .= $fileContent . "\n";
				} catch (Exception $e) {
					throw new InvalidArgumentException(
						"[kirby-helpers] Failed to read asset: {$file}. " . $e->getMessage()
					);
				}
			}

			if ($type === 'stylesheet') {
				echo Html::tag(name: 'style', content: [$content]);
			} elseif ($type === 'script') {
				echo Html::tag(name: 'script', content: [$content]);
			}
		}
	}
}
