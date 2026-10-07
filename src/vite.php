<?php

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;

if (!function_exists('viteOption')) {
	/**
	 * Read a `timnarr.kirby-helpers.vite.*` option, resolving closures to their return value.
	 *
	 * @param string $key The option key below `timnarr.kirby-helpers.vite`, e.g. 'manifestPath'.
	 * @return mixed The resolved option value.
	 */
	function viteOption(string $key): mixed
	{
		$value = kirby()->option('timnarr.kirby-helpers.vite.' . $key);

		return is_callable($value) ? $value() : $value;
	}
}

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
			$devMode = !F::exists(viteOption('manifestPath'));
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

if (!function_exists('inlineCriticalScript')) {
	/**
	 * Inline a critical, pre-first-paint JS entry built by Vite in `iife` mode - self-contained, no
	 * `import`/`export`, safe to drop into a plain classic `<script>` tag. Bypasses `inlineViteAsset()`
	 * since the `iife` build has no manifest to resolve against (`manifest: false` in `vite.config.js`
	 * for that mode), so the built file's fixed `[name]-iife.js` output path is used directly instead.
	 *
	 * In development mode the source file is inlined from the `vite.criticalScript.sourceRoot` option,
	 * in production the built file from the `vite.criticalScript.buildRoot` option.
	 *
	 * @param string $path Entry path relative to the source root, e.g. 'javascript/critical.js'.
	 * @throws InvalidArgumentException If the script is outside its root directory or not readable.
	 *
	 * @example
	 * inlineCriticalScript('javascript/critical.js');
	 * // dev:  inlines {sourceRoot}/javascript/critical.js
	 * // prod: inlines {buildRoot}/critical-iife.js
	 */
	function inlineCriticalScript(string $path): void
	{
		if (isViteDevMode()) {
			$rootPath = realpath(viteOption('criticalScript.sourceRoot'));
			$filePath = $path;
		} else {
			$rootPath = realpath(viteOption('criticalScript.buildRoot'));
			$filePath = pathinfo($path, PATHINFO_FILENAME) . '-iife.js';
		}

		$realPath = realpath($rootPath . '/' . $filePath);

		if ($rootPath === false || $realPath === false || !str_starts_with($realPath, $rootPath . DIRECTORY_SEPARATOR)) {
			throw new InvalidArgumentException("[kirby-helpers] Invalid critical script path: {$path}");
		}

		if (!is_readable($realPath)) {
			throw new InvalidArgumentException("[kirby-helpers] Failed to read critical script: {$path}");
		}

		echo Html::tag(name: 'script', content: [F::read($realPath)]);
	}
}
