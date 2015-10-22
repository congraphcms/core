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
class Trunk implements TrunkContract
{
	/**
	 * Item Storage
	 * 
	 * @var array
	 */
	private $itemStorage;

	/**
	 * Collection Storage
	 * 
	 * @var array
	 */
	private $collectionStorage;

	/**
	 * Creates Trunk
	 */
	public function __construct()
	{
		$this->itemStorage = [];
		$this->collectionStorage = [];
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
		if($data instanceof Model)
		{
			$this->putItem($data);
		}
		if($data instanceof Collection)
		{
			$this->putCollection($data);
		}

		throw new Exception('You are trying to put invalid object to trunk.');
	}

	/**
	 * Add item to trunk
	 * 
	 * @param  Model  $item
	 * 
	 * @return void
	 */
	public function putItem($item)
	{
		if(empty($item->getId()) || empty($item->getType()))
		{
			throw new Exception('You are trying to put invalid object to trunk.');
		}

		if( ! array_key_exists($item->getType(), $this->itemStorage) )
		{
			$this->itemStorage[$item->getType()] = [];
		}

		$this->itemStorage[$item->getType()][$item->getId()] = $item;
	}

	/**
	 * Add collection to trunk
	 * 
	 * @param  Collection  $collection
	 * 
	 * @return void
	 */
	public function putCollection($collection)
	{
		if( ! array_key_exists($collection->getType(), $this->collectionStorage) )
		{
			$this->collectionStorage[$collection->getType()] = [];
		}
		$collectionKey = $this->collectionKey($collection->getParams());
		$this->collectionStorage[$collection->getType()][$collectionKey] = $collection;

		foreach ($collection as $item)
		{
			$this->putItem($item);
		}
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
			return $this->hasCollection($key, $type);
		}

		return $this->hasItem($key, $type);
	}

	/**
	 * Check if item exists in cache
	 * 
	 * @param  mixed  $id
	 * @param  mixed  $type
	 * 
	 * @return boolean
	 */
	public function hasItem($id, $type)
	{
		if(array_key_exists($type, $this->itemStorage) && array_key_exists($id, $this->itemStorage[$type]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if collection exists in cache
	 * 
	 * @param  array  $params
	 * @param  mixed  $type
	 * 
	 * @return boolean
	 */
	public function hasCollection($params, $type)
	{
		$collectionKey = $this->collectionKey($params);
		if(array_key_exists($type, $this->collectionStorage) && array_key_exists($collectionKey, $this->collectionStorage[$type]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get item or collection from cache
	 * 
	 * @param  mixed $key
	 * @param  mixed $type
	 * 
	 * @return Model | null
	 */
	public function get($key, $type)
	{
		if(is_array($key))
		{
			return $this->getCollection($key, $type);
		}

		return $this->getItem($key, $type);
	}

	/**
	 * Get item by id and type
	 * 
	 * @param  mixed $id
	 * @param  mixed $type
	 * 
	 * @return Model | null
	 */
	public function getItem($id, $type)
	{
		if(array_key_exists($type, $this->itemStorage) && array_key_exists($id, $this->itemStorage[$type]))
		{
			return $this->itemStorage[$type][$id];
		}

		return null;
	}

	/**
	 * Get collection by params and type
	 * 
	 * @param  array $params
	 * @param  mixed $type
	 * 
	 * @return Model | null
	 */
	public function getCollection(array $params, $type)
	{
		$collectionKey = $this->collectionKey($params);
		if(array_key_exists($type, $this->collectionStorage) && array_key_exists($collectionKey, $this->collectionStorage[$type]))
		{
			return $this->collectionStorage[$type][$collectionKey];
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
	public function forget($id, $type)
	{
		if( array_key_exists($type, $this->itemStorage) && array_key_exists($id, $this->itemStorage[$type]) )
		{
			unset($this->itemStorage[$type][$id]);
		}
		if( array_key_exists($type, $this->collectionStorage) )
		{
			unset($this->collectionStorage[$type]);
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
		if( array_key_exists($type, $this->itemStorage) )
		{
			unset($this->itemStorage[$type]);
		}
		if( array_key_exists($type, $this->collectionStorage) )
		{
			unset($this->collectionStorage[$type]);
		}
	}

	/**
	 * Make base64 key from collection params
	 * 
	 * @param  array $params
	 * 
	 * @return string
	 */
	protected function collectionKey(array $params)
	{
		return base64_encode(json_encode($params));
	}
}