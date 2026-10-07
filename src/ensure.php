<?php

if (!function_exists('ensureLeft')) {
	/**
	 * Ensure that a string starts with the specified prefix.
	 *
	 * @param string $string The input string.
	 * @param string $prefix The prefix to ensure.
	 * @return string The string starting with the prefix.
	 */
	function ensureLeft(string $string, string $prefix): string
	{
		if ($string === '') {
			return '';
		}

		return str_starts_with($string, $prefix) ? $string : $prefix . $string;
	}
}


if (!function_exists('ensureRight')) {
	/**
	 * Ensure that a string ends with the specified suffix.
	 *
	 * @param string $string The input string.
	 * @param string $suffix The suffix to ensure.
	 * @return string The string ending with the suffix.
	 */
	function ensureRight(string $string, string $suffix): string
	{
		if ($string === '') {
			return '';
		}

		return str_ends_with($string, $suffix) ? $string : $string . $suffix;
	}
}


if (!function_exists('ensureHashed')) {
	/**
	 * Ensure that a string starts with a hash character (#).
	 * Mainly used to ensure jump-to ids start with a #.
	 *
	 * @param string $string The input string.
	 * @return string The string starting with a hash.
	 */
	function ensureHashed(string $string): string
	{
		return ensureLeft($string, '#');
	}
}
