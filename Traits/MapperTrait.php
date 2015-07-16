<?php 
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Traits;

/**
 * MapperTrait for mapping events/handlers/commands
 * 
 * Gives class ability to map events/handlers/commands
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
trait MapperTrait
{

	/**
	 * All of the mappings.
	 *
	 * @var array
	 */
	protected static $mappings = [];

	/**
	 * The fallback mapping Closures.
	 *
	 * @var array
	 */
	protected static $mappers = [];

	/**
	 * Register mappings.
	 *
	 * @param  array  $mappings
	 * @return void
	 */
	public static function map(array $mappings, $key = 'default')
	{
		self::$mappings = array_merge_recursive(self::$mappings, [ $key => $mappings ]);
	}

	/**
	 * Register a fallback mapper callback.
	 *
	 * @param  \Closure  $mapper
	 * @return void
	 */
	public static function mapper(Closure $mapper, $key = 'default')
	{
		self::$mappers[$key] = $mapper;
	}

	/**
	 * Get mappings for the resource.
	 *
	 * @param  mixed  $command
	 * @return string
	 */
	public static function getMappings($resource, $key = 'default')
	{
		$resourceName = $resource;

		if( is_object($resource) )
		{
			$resourceName = get_class($resource);
		}

		$mappings = [];
		if( isset(self::$mappers[$key]) )
		{
			$mappings = array_merge_recursive( $mappings, call_user_func(self::$mappers[$key], $resourceName) );
		}

		if( isset(self::$mappings[$key]) && isset(self::$mappings[$key][$resourceName]) )
		{
			$mappings = array_merge_recursive( $mappings, (array) self::$mappings[$key][$resourceName] );
		}

		return $mappings;
	}

	/**
	 * Get mapping segment.
	 *
	 * @param  string  	$mapping
	 * @param  int  	$segment
	 * @return string
	 */
	protected static function getSegment($mapping, $segment)
	{
		return explode('@', $mapping)[$segment];
	}

}