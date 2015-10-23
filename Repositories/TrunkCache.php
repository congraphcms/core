<?php 
/*
 * This file is part of the Cookbook package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Repositories;

use stdClass;
use Exception;
use Cookbook\Contracts\Core\TrunkContract;

/**
 * In call data transfer cache
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class TrunkCache implements TrunkContract
{
	/**
	 * Item Storage
	 * 
	 * @var array
	 */
	private $storage;

	/**
	 * Creates Trunk
	 */
	public function __construct()
	{
		$this->storage = [];
	}

	/**
	 * Add item or collection to trunk
	 * 
	 * @param  mixed  $data
	 * 
	 * @return void
	 */
	public function put($data)
	{
		if( ! $data instanceof DataTransferObject)
		{
			throw new Exception('You are trying to put invalid object to trunk.');
		}

		if( ! array_key_exists($data->getType(), $this->storage) )
		{
			$this->storage[$data->getType()] = [];
		}

		$storageKey = $this->storageKey($data->getParams());
		$this->storage[$data->getType()][$storageKey] = $data;
	}

	/**
	 * Check cache for item or collection
	 * 
	 * @param  mixed  	$key
	 * @param  string  	$type
	 * 
	 * @return boolean 
	 */
	public function has($key, $type)
	{
		if(is_array($key))
		{
			return $this->hasItem($this->storageKey($key), $type);
		}

		if(is_string($key))
		{
			return $this->hasItem($key, $type);
		}

		if(is_int($key))
		{
			return $this->hasItem($this->storageKey([$key]), $type);
		}

		return false;
	}

	/**
	 * Check if item exists in cache
	 * 
	 * @param  string  $key
	 * @param  string  $type
	 * 
	 * @return boolean
	 */
	protected function hasItem($key, $type)
	{
		if(array_key_exists($type, $this->storage) && array_key_exists($key, $this->storage[$type]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get item or collection from cache
	 * 
	 * @param  mixed $key
	 * @param  string $type
	 * 
	 * @return DataTransferObject | null
	 */
	public function get($key, $type)
	{
		if(is_array($key))
		{
			return $this->getItem($this->storageKey($key), $type);
		}

		if(is_string($key))
		{
			return $this->getItem($key, $type);
		}

		if(is_int($key))
		{
			return $this->getItem($this->storageKey([$key]), $type);
		}

		return null;
	}

	/**
	 * Get item by id and type
	 * 
	 * @param  string $key
	 * @param  string $type
	 * 
	 * @return DataTransferObject | null
	 */
	protected function getItem($key, $type)
	{
		if(array_key_exists($type, $this->storage) && array_key_exists($key, $this->storage[$type]))
		{
			return $this->storage[$type][$key];
		}

		return null;
	}

	/**
	 * Clear item from cache
	 * 
	 * @param  mixed $id
	 * @param  mixed $type
	 * 
	 * @return void
	 */
	public function forget($key, $type)
	{
		if(is_array($key))
		{
			return $this->forgetItem($this->storageKey($key), $type);
		}

		if(is_string($key))
		{
			return $this->forgetItem($key, $type);
		}

		if(is_int($key))
		{
			return $this->forgetItem($this->storageKey([$key]), $type);
		}
	}

	/**
	 * Clear item from cache
	 * 
	 * @param  string $id
	 * @param  string $type
	 * 
	 * @return void
	 */
	protected function forgetItem($key, $type)
	{
		if( array_key_exists($type, $this->storage) && array_key_exists($key, $this->storage[$type]) )
		{
			unset($this->storage[$type][$key]);
		}
	}

	/**
	 * Clear type from cache
	 * 
	 * @param  mixed $type
	 * 
	 * @return void
	 */
	public function forgetType($type)
	{
		if( array_key_exists($type, $this->storage) )
		{
			unset($this->storage[$type]);
		}
	}

	/**
	 * Clear all cache
	 * 
	 * @return void
	 */
	public function forgetAll()
	{
		$this->storage = [];
	}

	/**
	 * Make base64 key from DataTransferObject params
	 * 
	 * @param  array $params
	 * 
	 * @return string
	 */
	protected function storageKey(array $params)
	{
		return base64_encode(json_encode($params));
	}
}