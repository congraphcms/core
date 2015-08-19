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

use Illuminate\Database\Connection;
use Cookbook\Contracts\Core\RepositoryContract;
use Illuminate\Support\Facades\Cache;

/**
 * Abstract repository
 * 
 * Implementing logic for calling methods through a proxy.
 * 
 * Allowing extra logic to be bound to any number of repository (domain) methods.
 * This logic should be implemented inside of proxy method
 * 
 * @uses   		Cookbook\Contracts\Core\RepositoryContract
 * @uses   		Cookbook\Core\Traits\ValidatorTrait
 * @uses   		Illuminate\Database\Connection
 * @uses   		Illuminate\Contracts\Validation\Factory
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	Cookbook/Core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class AbstractRepository implements RepositoryContract
{
	/**
	 * Object type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The database connection to use.
	 *
	 * @var Illuminate\Database\Connection
	 */
	protected $db;

	/**
	 * Array of private|protected repository methods that should be called 
	 * through proxy() method - domain methods.
	 * @var array
	 */
	protected $domainMethods = [];

	/**
	 * Array of repository methods that should be  
	 * placed inside transaction - transaction methods.
	 * 
	 * If this property isn't populated repository will treat all
	 * domain methods as transaction methods, and if there are some
	 * method names in this array only they will be treated as transaction
	 * methods.
	 * 
	 * Transaction methods need to be also domain methods for any effect.
	 * 
	 * @var array | null
	 */
	protected $transactionMethods = null;

	/**
	 * Number of minutes to keep cache
	 *
	 * @var int
	 */
	protected $cacheDuration;

	/**
	 * Repository constructor
	 * 
	 * @param \Illuminate\Database\Connection $db
	 */
	public function __construct(Connection $db)
	{
		$this->setConnection($db);
	}

	/**
	 * Override __call() magic method so we can catch calls 
	 * to our domain methods and put them through proxy
	 * 
	 * @param $method 	string 		- method name
	 * @param $arg 		array 		- array of arguments for method
	 * 
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		// check if domain method with this name exists on this class
		if(!in_array($method, $this->domainMethods))
		{
			throw new \BadMethodCallException('Unkonown method ' . $method . '.');
		}

		// call the proxy method
		return $this->proxy($method, $args);
	}

	/**
	 * Set the connection to run queries on.
	 *
	 * @param \Illuminate\Database\Connection $db
	 *
	 * @return $this
	 */
	public function setConnection(Connection $db)
	{
		$this->db = $db;
		return $this;
	}

	/**
	 * Set transaction method.
	 *
	 * @param string $method - method name
	 *
	 * @return $this
	 */
	public function setTransactionMethod($method)
	{
		// check if is array
		if(!is_array($this->transactionMethods))
		{
			$this->transactionMethods = [$method];

			return $this;
		}

		// check if method is already in array
		if(!in_array($method, $this->transactionMethods)){
			$this->transactionMethods[] = $method;
		}

		return $this;
	}

	/**
	 * Remove transaction method.
	 *
	 * @param string $method - method name
	 *
	 * @return $this
	 */
	public function removeTransactionMethod($method)
	{
		// if it's not an array, there are no transaction methods
		if(!is_array($this->transactionMethods))
		{
			return $this;
		}
		
		// find method in transactionMethods
		$index = array_search($method, $this->transactionMethods);
		
		// remove the method if it exists in array
		if($index !== false){
			array_splice($this->transactionMethods, $index, 1);
		}


		// if transaction methods are empty set them to null
		if(empty($this->transactionMethods)){
			$this->transactionMethods = null;
		}

		return $this;
	}

	/**
	 * Get transaction methods.
	 *
	 * @return array|null
	 */
	public function getTransactionMethods()
	{
		return $this->transactionMethods;
	}

	/**
	 * Proxy function through which domain methods will be called
	 * This method should be overridden in extending classes
	 * 
	 * @param $method 	string 		- domain method name
	 * @param $arg 		array 		- array of arguments for domain method
	 * 
	 * @return mixed
	 */
	protected function proxy($method, $args)
	{

		// logic before executing the method
		// 
		$this->beforeProxy($method, $args);
		
		// executing the domain method
		$result = call_user_func_array(array($this, $method), $args);


		// logic after executin the method
		// 
		$this->afterProxy($method, $args, $result);

		// return the result of domain method
		return $result;
	}

	/**
	 * Logic before proxy call to domain method
	 * 
	 * @param $method 	string 		- domain method name
	 * @param $arg 		array 		- array of arguments for domain method
	 * 
	 * @return void
	 */
	protected function beforeProxy($method, $args)
	{
		// if method is defined as transaction method
		if
		(	empty($this->transactionMethods) || 
			(	
				is_array($this->transactionMethods) && 
				in_array($method, $this->transactionMethods)
			)
		)
		{
			// begin transaction
			$this->db->beginTransaction();
		}
	}

	/**
	 * Logic after proxy call to domain method
	 * 
	 * @param $method 	string 		- domain method name
	 * @param $arg 		array 		- array of arguments for domain method
	 * 
	 * @return void
	 */
	protected function afterProxy($method, $args, $result)
	{
		// if method is defined as transaction method
		if
		(	empty($this->transactionMethods) || 
			(	
				is_array($this->transactionMethods) && 
				in_array($method, $this->transactionMethods)
			)
		)
		{
			// commit transaction
			$this->db->commit();
		}
	}

	/**
	 * DB INSERT of object
	 * 
	 * @param $model 	string 		- data for object creation
	 * 
	 * @return mixed
	 */
	public function create($model)
	{
		// arguments for private method 
		$args = func_get_args();

		// proxy call
		$result = $this->proxy('_create', $args);

		if($this->shouldUseCache())
		{
			// Cache::put($this->type . ':' . $result->id, $result, $this->cacheDuration);
			Cache::forget($this->type);
		}

		return $result;
	}

	/**
	 * DB UPDATE of object
	 * 
	 * @param $model 	string 		- data for object update
	 * 
	 * @return mixed
	 */
	public function update($id, $model)
	{
		// arguments for private method 
		$args = func_get_args();

		// proxy call
		$result = $this->proxy('_update', $args);

		if($this->shouldUseCache())
		{
			// Cache::put($this->type . ':' . $result->id, $result, $this->cacheDuration);
			Cache::forget($this->type);
		}

		return $result;
	}

	/**
	 * DB DELETE of object
	 * 
	 * @param $id 		int 		- ID of object for delete 
	 * 
	 * @return mixed
	 */
	public function delete($id)
	{
		// arguments for private method 
		$args = func_get_args();

		// proxy call
		$result = $this->proxy('_delete', $args);

		if($this->shouldUseCache())
		{
			// Cache::forget($this->type . ':' . $result);
			Cache::forget($this->type);
		}

		return $result;
	}

	public function fetch($id, $refresh = false)
	{
		// arguments for private method 
		$args = func_get_args();
		$key = $this->type . ':' . $id;

		if( $refresh || ! $this->shouldUseCache() )
		{
			// proxy call
			$result = $this->proxy('_fetch', $args);

			if($this->shouldUseCache())
			{
				Cache::put($key, $result, $this->cacheDuration);
			}

			return $result;
		}

		return Cache::remember($key, $this->cacheDuration, function() use ($args){
			return $this->proxy('_fetch', $args);
		});
	}

	public function get($filter = [], $offset = 0, $limit = 0, $sort = [], $refresh = false)
	{
		// arguments for private method 
		$args = func_get_args();
		
		$cacheArgs = [
			'filter' => $filter,
			'offset' => $offset, 
			'limit' => $limit, 
			'sort' => $sort
		];

		$key = $this->type . ':' . base64_encode( json_encode($cacheArgs) );

		if( $refresh || ! $this->shouldUseCache() )
		{
			// proxy call
			$result = $this->proxy('_get', $args);

			if($this->shouldUseCache())
			{
				Cache::put($key, $result, $this->cacheDuration);
			}

			return $result;
		}

		return Cache::remember($key, $this->cacheDuration, function() use ($args){
			return $this->proxy('_get', $args);
		});
	}

	protected function shouldUseCache()
	{
		if ($this instanceof UsesCache) {
            return true;
        }

        return false;
	}

	protected function parseFilters($query, $filters)
	{

		foreach ($filters as $key => $filter)
		{
			if( ! is_array($filter) )
			{
				$query = $query->where($key, '=', $filter);
				continue;
			}

			$query = $this->parseFilter($query, $key, $filter);
		}
		return $query;
	}

	protected function parseFilter($query, $key, $filter)
	{
		foreach ($filter as $operator => $value) {
			switch ($operator) 
			{
				case 'ne':
					$query = $query->where($key, '!=', $value);
					break;
				case 'lt':
					$query = $query->where($key, '<', $value);
					break;
				case 'lte':
					$query = $query->where($key, '<=', $value);
					break;
				case 'gt':
					$query = $query->where($key, '>', $value);
					break;
				case 'gte':
					$query = $query->where($key, '>=', $value);
					break;
				case 'in':
					$query = $query->whereIn($key, $value);
					break;
				case 'nin':
					$query = $query->whereNotIn($key, $value);
					break;
				
				default:
					throw new BadRequestException(['Filter operator not supported.']);
					break;
			}
		}

		return $query;
	}



	protected function parsePaging($query, $offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		if( ! empty($offset) )
		{
			$query->skip($offset);
		}

		if( ! empty($limit) )
		{
			$query->take($limit);
		}

		return $query;
	}

	protected function parseSorting($query, $sort)
	{
		if( ! empty($sort) )
		{
			
			$sort = (is_array($sort))? $sort: [$sort];

			foreach ($sort as $sortCriteria) {
				$sortDirection = 'asc';

				if($sortCriteria[0] === '-')
				{
					$sortCriteria = substr($sortCriteria, 1);
					$sortDirection = 'desc';
				}

				$query = $query->orderBy($sortCriteria, $sortDirection);
			}
		}

		return $query;
	}


	/**
	 * Abstract definition of RepositoryInterface methods
	 */
	abstract protected function _create($model);

	abstract protected function _update($id, $model);

	abstract protected function _delete($id);

	abstract protected function _fetch($id);

	abstract protected function _get($filter = [], $offset = 0, $limit = 0, $sort = []);

}