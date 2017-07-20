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

/**
 * Model class used for data transfer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class Model extends DataTransferObject
{
	

	/**
	 * List of guarded model properties
	 * 
	 * @var array
	 */ 
	protected $guarded = [];

	/**
	 * Values of guarded model properties
	 * 
	 * @var array
	 */ 
	protected $protected = [];

	/**
	 * Creates new Model
	 * 
	 * @param stdClass|array $data
	 */
	public function __construct($data = null)
	{
		parent::__construct($data);
		$this->isCollection = false;
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
			$data = new stdClass();
		}

		if( $data instanceof stdClass )
		{
			$this->data = $data;
		}
		else
		{
			$this->data = (object) $data;
		}
		
		$this->id = $this->data->{$this->idKey};
		$this->type = $this->data->{$this->typeKey};

		$dataVars = get_object_vars($this->data);
		foreach ($this->guarded as $guardedProperty)
		{
			if( array_key_exists($guardedProperty, $dataVars) )
			{
				$this->protected[$guardedProperty] = $this->data->$guardedProperty;
				unset($this->data->$guardedProperty);
			}
		}
	}

	/**
	 * Get guarded property
	 * 
	 * @return mixed
	 */
	public function getGuarded($prop)
	{
		if(array_key_exists($prop, $this->protected))
		{
			return $this->protected[$prop];
		}
		return null;
	}

	/**
	 * Set guarded property
	 * 
	 * @return mixed
	 */
	public function setGuarded($prop, $value)
	{
		$this->protected[$prop] = $value;
	}

	/**
	 * Get model ID
	 * 
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get model ID key
	 * 
	 * @return mixed
	 */
	public function getIdKey()
	{
		return $this->idKey;
	}

	/**
	 * Set model ID key
	 * 
	 * @return mixed
	 */
	public function setIdKey($key)
	{
		$this->idKey = $key;
	}

	public function __get($name)
	{
		$vars = get_object_vars($this->data);

		if( array_key_exists($name, $vars) )
		{
			if( $this->data->{$name} instanceof ModelIdentifier )
			{
				if($this->data->{$name}->resolved) {
					return $this->data{$name}->resolver;
				}

				return $this->data->{$name};
			}

			return $this->data->{$name};
		}
		
		throw new Exception('Undefined property: ' . get_class($this) . '::$' . $name);
	}

	public function __set($name, $value)
	{
		if($value instanceof Model)
		{
			$identifier = new ModelIdentifier($value);
			$this->data->{$name} = $identifier;
			return;
		}

		if($value instanceof Collection)
		{
			$unresolvedCollection = [];
			foreach ($value as $item)
			{
				$identifier = new ModelIdentifier($item);
				$unresolvedCollection[] = $identifier;
			}

			$this->data->{$name} = $unresolvedCollection;
			
			return;
		}

		if($name == $this->idKey)
		{
			$this->id = $value;
		}

		if($name == $this->typeKey)
		{
			$this->type = $value;
		}

		$this->data->{$name} = $value;
	}

	public function __isset($name)
	{
		return isset($this->data->{$name});
	}

	public function __unset($name)
	{
		unset($this->data->{$name});
	}
}