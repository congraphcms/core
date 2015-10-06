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

use Cookbook\Contracts\Core\ObjectResolverContract;
use Cookbook\Core\Traits\MapperTrait;
use Illuminate\Contracts\Container\Container;

/**
 * Object resolver
 * 
 * Uses repository mapping to resolve objects by their type
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	Cookbook/Core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ObjectResolver implements ObjectResolverContract
{

	use MapperTrait;

	/**
	 * Application container
	 * 
	 * @var Illuminate\Contracts\Container\Container
	 */
	protected $container;

	/**
	 * ObjectResolver constructor
	 * 
	 * @param Illuminate\ $db
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	public function resolve($type, $ids)
	{
		$multiple = false;
		$method = 'fetch';
		$params = [$ids];

		if(is_array($ids))
		{
			$multiple = true;
			$method = 'get';
			$params = [
				[
					'id' => [
						'in' => $ids
					]
				]
			];
		}

		return $this->resolveMapping($type, $params, 'default', $method);
	}

	public function resolveWithParams($type, $filter = [], $offset = 0, $limit = 0, $sort = [])
	{
		return $this->resolveMapping($type, [$filter, $offset, $limit, $sort], 'default', 'get');
	}


}