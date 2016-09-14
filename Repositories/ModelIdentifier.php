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
 * ModelIdentifier class used for data transfer
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ModelIdentifier extends DataTransferObject
{
	/**
	 * Flag if this identifier is resolved
	 * 
	 * @var boolean
	 */
	public $resolved;

	/**
	 * Model for this identifier
	 * 
	 * @var Model
	 */
	public $resolver;

	/**
	 * Creates new ModelIdentifier
	 * 
	 * @param stdClass|array $data
	 */
	public function __construct($data = null)
	{
		$this->setData($data);
		$this->isCollection = false;
		$this->resolved = false;
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
			return;
		}

		if( $data instanceof stdClass )
		{
			$this->data = $data;
		}
		else
		{
			$this->data = (object) $data;
		}

		if($data instanceof Model)
		{
			$this->data = new stdClass();
			$this->data->{$data->getIdKey()} = $data->getId();
			$this->data->{$data->getTypeKey()} = $data->getType();
			$this->idKey = $data->getIdKey();
			$this->typeKey = $data->getTypeKey();
			$this->resolved = true;
			$this->resolver = $data;
		}
		
		$this->id = $this->data->{$this->idKey};
		$this->type = $this->data->{$this->typeKey};
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
			return $this->data->{$name};
		}
		
		throw new Exception('Undefined property: ' . get_class($this) . '::$' . $name);
	}

	public function __set($name, $value)
	{
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