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
use ArrayAccess;
use Exception;
use Cookbook\Core\Facades\Resovler;
use Cookbook\Core\Facades\Trunk;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Abstract object class used for data transfer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class DataTransferObject implements ArrayAccess, Arrayable, Jsonable
{

	/**
	 * Main transfer data
	 * 
	 * @var array
	 */
	protected $data;

	/**
	 * Meta data
	 * 
	 * @var array
	 */
	protected $meta = [];

	/**
	 * Data parameters
	 * 
	 * @var array
	 */
	protected $params = [];

	/**
	 * Whether data is a collection or item
	 * 
	 * @var boolean
	 */
	protected $isCollection;

	/**
	 * List of relations to be included in result
	 * 
	 * @var array
	 */
	protected $relations = [];

	/**
	 * List of included objects
	 * 
	 * @var array
	 */
	protected $included = [];

	/**
	 * Load queue
	 * 
	 * @var array
	 */
	protected static $loadQueue = [];


	/**
	 * Creates new DataTransferObject
	 * 
	 * @param stdClass|array $data
	 */
	public function __construct($data)
	{
		$this->setData($data);
		Trunk::put($this);
	}

	/**
	 * Set transfer data
	 * 
	 * @param mixed $data object or collection of objects
	 */
	abstract public function setData($data);


	/**
	 * Get transfer data
	 * 
	 * @return  mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Set meta data
	 * 
	 * @param string|array 	$key
	 * @param mixed 		$value
	 */
	public function setMeta($key, $value = null)
	{
		if(is_array($key))
		{
			$this->meta = array_merge_recursive($this->meta, $key);
			return $this;
		}

		$this->meta[$key] = $value;

		return $this;
	}

	/**
	 * Get meta data
	 * 
	 * @param string|array 	$key
	 * @param mixed 		$value
	 */
	public function getMeta($key = null)
	{
		if(is_null($key))
		{
			return $this->meta;
		}

		return $this->meta[$key];
	}

	/**
	 * Set parameter data
	 * 
	 * @param string|array 	$key
	 * @param mixed 		$value
	 */
	public function setParams($key, $value = null)
	{
		if(is_array($key))
		{
			$this->params = array_merge_recursive($this->params, $key);
			return $this;
		}

		$this->params[$key] = $value;

		return $this;
	}


	/**
	 * Get params
	 * 
	 * @param string|array 	$key
	 * @param mixed 		$value
	 */
	public function getParams($key = null)
	{
		if(is_null($key))
		{
			return $this->params;
		}

		return $this->params[$key];
	}

	/**
	 * Preload relationships
	 * 
	 * @param  array $relations
	 * @return void
	 */
	public function load($relations = [])
	{
		$this->addRelations($relations);
		
		$this->clearQueue();

		$this->queueUnresolvedObjects($this->data, $this->relations);

		$this->loadQueue();
	}

	/**
	 * Load objects from queue
	 * 
	 * @return void
	 */
	protected function loadQueue()
	{
		foreach (self::$loadQueue as $type => $queries)
		{
			foreach ($queries as $query)
			{
				$ids = [];
				foreach ($query['ids'] as $id)
				{
					if( Trunk::has($id, $type) )
					{
						$object = Trunk::get($id, $type);
						$this->addIncludes($object);
						continue;
					}

					$ids[] = $id;
				}

				if( empty($ids) )
				{
					continue;
				}

				$result = Resolver::resolve($query['type'], $query['ids'], $query['relations']);
				Trunk::put($result);
				
				$this->addIncludes($result);
			}
		}
	}

	/**
	 * Add items to include
	 * 
	 * @param mixed $includes
	 *
	 * @return void
	 */
	public function addIncludes($includes)
	{
		if($includes instanceof Model)
		{
			$this->addItemToInclude($includes);
			return;
		}
		foreach ($includes as $item)
		{
			$this->addItemToInclude($item);
		}
	}

	public function addItemToInclude($item)
	{
		$this->included[$this->objectKey($item)] = $item;
	}

	public function getIncludes($keyed = false)
	{
		$includes = $this->included;
		foreach ($this->included as $key => $object)
		{
			$objIncludes = $object->getIncludes(true);
			$includes = $includes + $objIncludes;
		}

		if( ! $keyed )
		{
			$includes = array_values($includes);
		}

		return $includes;
	}

	/**
	 * Add relation properties
	 * 
	 * @param array | string $relations
	 * 
	 * @return void
	 */
	public function addRelations($relations)
	{
		$relations = ( is_array($relations) ) ? $relations : explode(',', strval($relations));
		foreach ($relations as $prop)
		{
			if( ! in_array($prop, $this->relations) )
			{
				$this->relations[] = $prop;
			}
		}
	}

	protected function addToQueue($object, $relations)
	{
		if( ! array_key_exists($object->type, self::$loadQueue) )
		{
			self::$loadQueue[$object->type] = [];
		}
		$relationsKey = base64_encode(json_encode($relations));
		if( ! array_key_exists($relationsKey, self::$loadQueue) )
		{
			self::$loadQueue[$object->type][$relationsKey] = [ 'type' => $object->type, 'ids' => [], 'relations' => $relations ];
		}
		self::$loadQueue[$object->type][$relationsKey]['ids'][] = $object->id;
	}

	protected function clearQueue()
	{
		self::$loadQueue = [];
	}

	public function queueUnresolvedObjects($data, $relations)
	{
		
		if( is_object($data) )
		{
			if( $data instanceof Model )
			{
				$data = $data->getData();
			}

			if( ! $this->resolved($data) )
			{
				$this->addToQueue($data, $relations);
				return;
			}

			$data = get_object_vars($data);
		}

		if( is_array($data) )
		{
			foreach ($data as $key => $value)
			{
				if($this->hasRelation($key, $relations))
				{
					$nestedRelations = $this->getNestedRelations($key, $relations);
					$this->queueUnresolvedObjects($value, $nestedRelations);
				}
				if(is_int($key))
				{
					$this->queueUnresolvedObjects($value, $relations);
				}
			}
		}
	}

	/**
	 * Check if relation exists
	 * 
	 * @param  string	$key
	 * @param  array	$relations
	 * 
	 * @return boolean
	 */
	protected function hasRelation($key, $relations)
	{
		foreach ($relations as $prop)
		{
			if($prop === $key || 0 === strpos($prop, $key.'.'))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get nested relations for field
	 * 
	 * @param  string	$prop
	 * @param  array	$relations
	 * 
	 * @return boolean
	 */
	protected function getNestedRelations($key, $relations)
	{
		$nestedRelations = [];

		foreach ($relations as $prop)
		{
			if(0 === strpos($prop, $key.'.'))
			{
				$newRelation = substr($prop, strlen($key));
				if( strlen($newRelation) !== 0)
				{
					$nestedRelations[] = $newRelation;
				}
			}
		}

		$nestedRelations = array_unique($nestedRelations);

		return $nestedRelations;
	}

	// ArrayAccess functions
	

	/**
	 * Set data at specific offset
	 * 
	 * @param  string $offset
	 * @param  string $value
	 * @return mixed
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			if( ! $this->isCollection)
			{
				throw new Exception('You need to specify a valid key for model.');
			}

			$this->data[] = $value;
		} 
		else 
		{
			if($this->isCollection)
			{
				$this->data[$offset] = $value;
				return;
			}

			$this->data->{$offset} = $value;
		}
	}

	/**
	 * Check if provided offset exists
	 * 
	 * @param  string $offset
	 * 
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return ($this->isCollection)?
				 isset($this->data[$offset])
				:isset($this->data->{$offset});
	}

	/**
	 * Unset the provided offset
	 * 
	 * @param  string $offset
	 * 
	 * @return boolean
	 */
	public function offsetUnset($offset) {
		if($this->isCollection)
		{
			unset($this->data[$offset]);
		}
		else
		{
			unset($this->data->{$offset});
		}
	}

	/**
	 * Get the provided offset
	 * 
	 * @param  string $offset
	 * 
	 * @return boolean
	 */
	public function offsetGet($offset)
	{
		if($this->isCollection)
		{
			return isset($this->data[$offset]) ? $this->data[$offset] : null;
		}
		else
		{
			return isset($this->data->{$offset}) ? $this->data->{$offset} : null;
		}
		
	}

	// Arrayable functions
	
	/**
	 * Get the instance as an array.
	 *
	 * @param  boolean $includeMetaData
	 *
	 * @return array
	 */
	public function toArray($includeMetaData = false, $nestedInclude = true)
	{
		$data = $this->transformToArray($this->data, $nestedInclude);
		
		if( ! $includeMetaData )
		{
			return $data;
		}

		$result = [];
		$result['data'] = $data;
		
		if( ! empty($this->included) && ! $nestedInclude && $includeMetaData)
		{
			$result['included'] = $this->transformToArray($this->getIncludes(), false);
		}

		if( $includeMetaData && ! empty($this->meta) )
		{
			$result['meta'] = $this->meta;
		}

		

		return $result;
	}

	// Jsonable functions
	
	/**
	 * Get the instance as an json string.
	 *
	 * @param  int $optinos json options
	 * @param  boolean $includeMetaData
	 *
	 * @return string
	 */
	public function toJson($options = 0, $includeMetaData = false, $nestedInclude = true)
	{
		$data = $this->toArray($includeMetaData, $nestedInclude);
		return json_encode($data, $options);
	}


	// JsonSerializable functions

	/**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
	
	/**
	 * Convert Object to array deep
	 * 
	 * @param  object $data
	 * 
	 * @return array
	 */
	protected function transformToArray($data, $nestedInclude = true)
	{
		if (is_object($data))
		{
			if( $nestedInclude && ! $this->resolved($data) )
			{
				$data = $this->getIncluded($data);
			}

			if($data instanceof DataTransferObject)
			{
				$data->addIncludes($this->included);
				$data = $data->toArray(false, $nestedInclude);
			}
			else
			{
				$data = get_object_vars($data);
			}
		}
		
		if ( is_array($data) && ! empty($data) )
		{
			foreach ($data as $key => &$value)
			{
				$value = $this->transformToArray($value, $nestedInclude);
			}
			
		}
		return $data;
	}

	/**
	 * Check if object is resolved
	 * @return object $obj
	 */
	protected function resolved($obj)
	{
		if( ! is_object($obj) )
		{
			return true;
		}

		$data = get_object_vars($obj);
		if( count($data) == 2
			&& array_key_exists('id', $data)
			&& array_key_exists('type', $data)
		)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get resolved object form trunk if possible
	 * 
	 * @param  object $obj
	 * 
	 * @return object
	 */
	protected function resolve($obj)
	{
		if(Trunk::has($obj->id, $obj->type))
		{
			return $data = Trunk::get($obj->id, $obj->type);
		}

		return $obj;
	}

	/**
	 * Get resolved object from included
	 * 
	 * @param  object $obj
	 * 
	 * @return object
	 */
	public function getIncluded($obj)
	{
		$includes = $this->getIncludes(true);
		$objectKey = $this->objectKey($obj);
		
		if(array_key_exists($objectKey, $includes))
		{
			return $includes[$objectKey];
		}

		return $obj;
	}

	public function clearIncluded()
	{
		$this->included = [];
	}

	/**
	 * Make base64 key from object
	 * 
	 * @param  object $object
	 * 
	 * @return string
	 */
	protected function objectKey($object)
	{
		return base64_encode(json_encode(['id' => $object->id, 'type' => $object->type]));
	}
}