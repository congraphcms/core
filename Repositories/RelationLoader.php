<?php 
/*
 * This file is part of the Congraph package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Core\Repositories;

use stdClass;
use Exception;
use Closure;
use Congraph\Core\Facades\Resolver;
use Congraph\Core\Facades\Trunk;
use Carbon\Carbon;

/**
 * Loader for dynamic relations in DataTransferObjects
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.2.0-beta
 * @version  	0.2.0-beta
 */
class RelationLoader
{
	/**
	 * Load queue
	 * 
	 * @var array
	 */
	protected static $loadQueue = [];


	/**
	 * Preload relationships
	 * 
	 * @param  array $relations
	 * @return void
	 */
	public function load($data, $relations = [])
	{
		$this->clearQueue();

		$relations = $this->parseRelations($relations);	

		$this->queueUnresolvedObjects($data, $relations);

		$this->loadQueue();
	}

	/**
	 * Empty loadQueue
	 * 
	 * @return void
	 */
	protected function clearQueue()
	{
		self::$loadQueue = [];
	}

	/**
	 * Add relation properties
	 * 
	 * @param array | string | number $relations
	 * 
	 * @return void
	 */
	public function parseRelations($relations)
	{
		if(is_numeric($relations))
		{
			return intval($relations);
		}
		
		$relations = ( is_array($relations) ) ? $relations : explode(',', strval($relations));
		return $relations;
	}

	/**
	 * Add unresovled objects to load queue
	 * 
	 * @param mixed 			$data 		data to be parsed
	 * @param array | integer 	$relations 	relations to be loaded
	 * 
	 * @return void
	 */
	public function queueUnresolvedObjects($data, $relations)
	{
		if( is_object($data) )
		{

			if( $data instanceof Model )
			{
				$data = $data->getData();
			}
			if( !$this->resolved($data) )
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
				if(is_int($key))
				{
					$this->queueUnresolvedObjects($value, $relations);
				}
				if($this->hasRelation($key, $relations))
				{
					$nestedRelations = $this->getNestedRelations($key, $relations);
					$this->queueUnresolvedObjects($value, $nestedRelations);
				}
				
			}
		}
	}

	/**
	 * Check if object is resolved
	 *
	 * @param stdClass $obj object to check
	 * 
	 * @return boolean
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
	 * Add object to loadQueue if it's specified in relations
	 * 
	 * @param stdClass 		$object 	object to add
	 * @param array | int 	$relations 	relations to check
	 * 
	 * @return void
	 */
	protected function addToQueue($object, $relations)
	{
		if( ! array_key_exists($object->type, self::$loadQueue) )
		{
			self::$loadQueue[$object->type] = [];
		}
		$relationsKey = base64_encode(json_encode($relations));
		if( ! array_key_exists($relationsKey, self::$loadQueue[$object->type]) )
		{
			self::$loadQueue[$object->type][$relationsKey] = [ 'type' => $object->type, 'ids' => [], 'relations' => $relations ];
		}
		self::$loadQueue[$object->type][$relationsKey]['ids'][] = $object->id;

	}
}