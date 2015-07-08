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

if (!function_exists('cbMergeCollections')) {
	/**
	 * Merge collections of cookbook objects
	 *
	 * @param  array  $array
	 * @return boolean
	 */
	function cbMergeCollections(array $col_1, array $col_2)
	{
		$args = func_get_args();

		$merged = [];
		foreach ($args as $col) {
			if( ! is_array($col) )
			{
				throw new InvalidArgumentsException('cbMergeCollections expects all parameters to be arrays.');
			}

			foreach ($col as $obj) {
				if( ! cbInCollection($merged, $obj->id, $objt->type) )
				{
					$merged[] = $obj;
				}
			}
		}

		return $merged;
	}
}

if (!function_exists('cbInCollection')) {
	/**
	 * Check if object is in collection
	 *
	 * @param  array  	$array
	 * @param  int  	$id
	 * @param  string  	$type
	 * @return boolean
	 */
	function cbInCollection(array $col, $id, $type = null)
	{
		foreach ($col as $colObject) {
			if($colObject->id == $id)
			{
				if($type !== null)
				{
					if($colObject->type == $type)
					{
						return true;
					}
					continue;
				}
				return true;
			}
		}

		return false;
	}
}