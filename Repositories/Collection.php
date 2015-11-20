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
use Iterator;
use Countable;
use ReflectionClass;

/**
 * Collection class used for data transfer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class Collection extends DataTransferObject implements Iterator, Countable
{
	/**
	 * Collection type
	 * 
	 * @var mixed
	 */
	protected $type;

	/**
	 * Default model class name
	 * 
	 * @var string
	 */
	protected $defaultModel = Model::class;

	/**
	 * Creates new Collection
	 * 
	 * @param stdClass|array $data
	 */
	public function __construct($data = null, $defaultModel = null)
	{
		if( ! is_null($defaultModel) )
		{
			$this->setDefaultModel($defaultModel);
		}

		parent::__construct($data);
		$this->isCollection = true;
	}

	/**
	 * Set transfer data
	 * 
	 * @param mixed $data object or collection of objects
	 */
	public function setData($data)
	{
		if( is_null($data) )
		{
			$data = [];
		}

		if( $data instanceof stdClass )
		{
			$data = [$data];
		}

		if( is_array($data) )
		{
			$data = $this->checkItems($data);
			$this->data = $data;
		}

		$this->data = (array) $data;

		if(empty($this->type) && ! empty($this->data[0]))
		{
			$this->type = $this->data[0]->getType();
		}
	}

	/**
	 * Set default model class
	 * 
	 * @param Cookbook\Core\Repositories\Model|string $model
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function setDefaultModel($model)
	{
		if(is_string($model))
		{
			$model = new ReflectionClass($model);
			$model = $model->newInstanceWithoutConstructor();
		}

		if( $model instanceof Model)
		{
			$this->defaultModel = get_class($model);
			return;
		}

		throw new \InvalidArgumentException('Collection default model must be instance of ' . Model::class);
	}

	/**
	 * Set collection type
	 * 
	 * @param mixed $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * Get model type
	 * 
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Add item to data
	 * 
	 * @param mixed $item
	 */
	public function addItem($item)
	{
		if( ! $item instanceof Model )
		{
			$item = new $this->defaultModel($item);
		}

		$this->data[] = $item;
	}

	/**
	 * Add item to data
	 * 
	 * @param mixed $item
	 */
	public function addItems($data)
	{
		if( is_null($data) )
		{
			return;
		}

		if( $data instanceof stdClass )
		{
			$data = [$data];
		}

		if( is_array($data) )
		{
			$data = $this->checkItems($data);
			$this->data = array_merge($this->data, $data);
		}
	}

	/**
	 * Check if all items are instance of Model
	 * if not, create new Model for each item
	 * 
	 * @param  array  $data
	 * 
	 * @return array
	 */
	protected function checkItems(array $data)
	{
		foreach ($data as &$item)
		{
			if( ! $item instanceof Model )
			{
				$item = new $this->defaultModel($item);
			}
		}

		return $data;
	}


	// Iterator functions
	
	function rewind()
	{
		reset($this->data);
		$this->position = key($this->data);
	}

	function current()
	{
		return $this->data[$this->position];
	}

	function key()
	{
		return $this->position;
	}

	function next()
	{
		next($this->data);
		$this->position = key($this->data);
	}

	function valid()
	{
		return isset($this->data[$this->position]);
	}

	// Countable functions
	
	function count()
	{
		return count($this->data);
	}
}