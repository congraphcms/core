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

/**
 * Collection class used for data transfer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class Collection extends DataTransferObject implements Iterator
{
	/**
	 * Collection type
	 * 
	 * @var mixed
	 */
	protected $type;

	/**
	 * Creates new Collection
	 * 
	 * @param stdClass|array $data
	 */
	public function __construct($data = null)
	{
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
			$item = new Model($item);
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
				$item = new Model($item);
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
}