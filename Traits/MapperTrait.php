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

use Exception;
use Cookbook\Core\Bus\Command;
use Illuminate\Support\Facades\Bus;

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
	 * List of default resolver methods keyed by mapper groups
	 * 
	 * @var array
	 */
	protected static $defaultResolveMethods = [];

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
	public static function maps(array $mappings, $key = 'default')
	{
		self::$mappings = array_merge_recursive(self::$mappings, [ $key => $mappings ]);
	}

	/**
	 * Register a fallback mapper callback.
	 *
	 * @param  \Closure  $mapper
	 * @return void
	 */
	public static function mapUsing(Closure $mapper, $key = 'default')
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
	 * Resolve mapping end return result
	 * 
	 * @param mixed $resource - mapping name to be resolved
	 * @param array $parameters - params for resolver
	 * @param string $key - mapping section
	 * 
	 * @throws  Exception
	 * 
	 * @return  mixed
	 */
	public function resolveMapping($resource, $parameters = [], $key = 'default')
	{
		$resourceName = $resource;

		if( is_object($resource) )
		{
			$resourceName = get_class($resource);
		}

		$mappings = $this->getMappings($resourceName, $key);

		if(empty($mappings))
		{
			throw new Exception('No resolvers mapped for resource: ' . $resourceName . '.');
		}

		$result = null;

		if(is_array($mappings))
		{
			foreach ($mappings as $mapping)
			{
				$result[] = $this->runResolver($mapping, $parameters);
			}

			return $result;
		}

		return $this->runResolver($mappings, $parameters);
	}

	/**
	 * Handle and run the resolver
	 * 
	 * @param mixed $resolver
	 * @param array $parameters - params for resolver
	 * 
	 * @return  mixed
	 */
	public function runResolver($resolver, $parameters = [])
	{
		if (is_callable($resolver))
		{
            return call_user_func_array($resolver, $parameters);
        }

        if(class_exists($resolver))
        {
        	$instance = $this->container->make($resolver, $parameters);
		    if($instance instanceof Command)
		    {
		    	return Bus::dispatch($instance, $parameters);
		    }
        }

        if(strpos($resolver, '@') !== false)
        {
        	list($class, $method) = explode('@', $this->action['uses']);

        	if (!method_exists($instance = $this->container->make($class), $method)) {
		        throw new Exception('Invalid method mapped on object: ' . $class . '.');
		    }

        	return call_user_func_array([$instance, $method], $parameters);
        }
        throw new Exception('Invalid resolver: ' . $resolver . '.');
	}

}