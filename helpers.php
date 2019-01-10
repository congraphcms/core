<?php
/*
 * This file is part of the congraph/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('is_assoc'))
{
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
	 * Merge collections of congraph objects
	 *
	 * @param  array  $array
	 * @return boolean
	 */
	function cbMergeCollections(array $col_1, array $col_2)
	{
		$args = func_get_args();

		$merged = [];
		foreach ($args as $col)
		{
			if( ! is_array($col) )
			{
				throw new InvalidArgumentsException('cbMergeCollections expects all parameters to be arrays.');
			}

			foreach ($col as $obj)
			{
				if( ! cbInCollection($merged, $obj->id, $obj->type) )
				{
					$merged[] = $obj;
				}
			}
		}

		return $merged;
	}
}

if (!function_exists('cbInCollection'))
{
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

if (!function_exists('cbParseUrlParams'))
{
	/**
	 * Handle URL params
	 * 
	 * convert GET params from string to array
	 *
	 * @param  string  	$type
	 * @return boolean
	 */
	function cbParseUrlParams($params)
	{
		// cbParseUrlFilters
		if( ! empty($params['filter']) )
		{
			if( ! is_array($params['filter']) )
			{
				throw new BadRequestException("Filter param needs to be array.");
				
			} 

			foreach($params['filter'] as $key => &$filter)
			{
				if( ! is_array($filter) )
				{
					$filter = ['e' => $filter];
					continue;
				}
			}
		} 

		// cbParseUrlFields
		if( ! empty($params['fields']) && ! is_array($params['fields']) )
		{
			$fields = explode(',', $params['fields']);

			array_walk($fields, function(&$item, $key){
				$item = trim($item);
			});

			$params['fields'] = $fields;
		}

		// cbParseUrlInclude
		if( ! empty($params['include']) && ! is_array($params['include']) )
		{
			$includes = explode(',', $params['include']);

			array_walk($includes, function(&$item, $key){
				$item = trim($item);
			});

			$include = [];

			foreach ($includes as $item) {
				$item = explode('.', $item);

				if( ! array_key_exists($item[0], $include) )
				{
					$include[$item[0]] = [];
				}

				if(count($item) > 1)
				{
					$itemIncludes = $item;
					array_shift($itemIncludes);
					$itemIncludes = implode('.', $itemIncludes);

					if( ! array_key_exists('include', $include[$item[0]]) )
					{
						$include[$item[0]]['include'] = $itemIncludes;
					}
					else
					{
						$include[$item[0]]['include'] .= ',' . $itemIncludes;
					}
					
				}
			}

			$params['include'] = $include;
		}

		return $params;
	}
}
if (!function_exists('http_build_query')) {
	function http_build_query($data, $prefix='', $sep='', $key='') {
	   $ret = array();
	   foreach ((array)$data as $k => $v) {
		   if (is_int($k) && $prefix != null) $k = urlencode($prefix . $k);
		   if (!empty($key)) $k = $key.'['.urlencode($k).']';
		   
		   if (is_array($v) || is_object($v))
			   array_push($ret, http_build_query($v, '', $sep, $k));
		   else    array_push($ret, $k.'='.urlencode($v));
	   }
	 
	   if (empty($sep)) $sep = ini_get('arg_separator.output');
	   return implode($sep, $ret);
	}
}