<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('is_assoc')) {
	/**
	 * Check if array is associative array
	 *
	 * @param  array  $array
	 * @return boolean
	 */
	function is_assoc(array $array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}
}