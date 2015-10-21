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
use Cookbook\Contracts\Core\TrunkContract;
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
	 * Provider of resolved objects
	 * mapped by id and type
	 * 
	 * @var Cookbook\Contracts\Core\TrunkContract
	 */
	protected $trunk;

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
	 * Creates new DataTransferObject
	 * 
	 * @param stdClass|array $data
	 */
	public function __construct(TrunkContract $trunk, $data = null)
	{
		$this->trunk = $trunk;
		$this->setData($data);
	}

	/**
	 * Set transfer data
	 * 
	 * @param mixed $data object or collection of objects
	 */
	abstract public function setData($data);

	/**
	 * Set meta data
	 * 
	 * @param string|array 	$key
	 * @param mixed 		$value
	 */
	public function setMeta($key, $value)
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
	public function setParams($key, $value)
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

	// /**
	//  * Map object to trunk
	//  * 
	//  */
	// protected function mapToTrunk($object)
	// {
	// 	$query = ['id' => $object->id, 'type' => $object->type];
	// 	$queryKey = base64_encode(json_encode($query));

	// 	if( ! array_key_exists($queryKey, $this->trunk) )
	// 	{
	// 		$this->trunk[$queryKey] = $object;
	// 	}

	// 	foreach ($this->include as $queryKey => $object)
	// 	{
	// 		if( ! array_key_exists($queryKey, $this->trunk) )
	// 		{
	// 			$this->trunk[$queryKey] = $object;
	// 		}
	// 	}
	// }


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
		$result = [];
		
		if( ! empty($this->include) && ! $nestedInclude)
		{
			$result['include'] = $this->transformToArray(array_values($this->include));
		}

		if( $includeMetaData && ! empty($this->meta) )
		{
			$result['meta'] = $this->transformToArray($this->meta);
		}

		if( $includeMetaData || ! $nestedInclude )
		{
			$result['data'] = $data;
		}
		else
		{
			$result = $data;
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
				$data = $this->resolve($data);
			}

			if($data instanceof Arrayable)
			{
				$data = $data->toArray(false, $nestedInclude);
			}
			else
			{
				$data = get_object_vars($data);
			}
		}
	
		if (is_array($data))
		{
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map([$this, __FUNCTION__], $data);
		}
		else
		{
			// Return array
			return $data;
		}
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
		if($this->trunk->has($obj->id, $obj->type))
		{
			return $data = $this->trunk->get($obj->id, $obj->type);
		}

		return $obj;
	}

	/**
	 * Get included object from trunk if it's included,
	 * otherwise return unresolved object
	 * 
	 * @param  object	$obj
	 * 
	 * @return object
	 */
	protected function resolveIncluded($obj)
	{
		if($this->trunk->includes($obj->id, $obj->type))
		{
			return $data = $this->trunk->get($obj->id, $obj->type);
		}

		return $obj;
	}
}