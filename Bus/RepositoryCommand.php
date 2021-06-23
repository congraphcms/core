<?php
/*
 * This file is part of the congraph/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Core\Bus;

/**
 * Base Repository Command class
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class RepositoryCommand extends Command
{

	/**
	 * Object ID
	 * 
	 * @var int
	 */
	public $id;

	/**
	 * Command params
	 * 
	 * @var array
	 */
	public $params;

	/**
	 * Repository for DB operations
	 * 
	 * @var Congraph\Contracts\Core\RepositoryContract
	 */
	protected $repository;

	/**
	 * Create new RepositoryCommandHandler
	 * 
	 * @param Congraph\Contracts\Core\RepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(RepositoryContract $repository)
	{
		// inject dependencies
		$this->repository = $repository;
	}

	/**
	 * Set command params
	 *
	 * @param array 					$params
	 * 
	 * @return void
	 */
	public function setParams(array $params, $id = null)
	{
		$this->params = $params;
		$this->id = $id;
	}

	/**
	 * Set command entity ID
	 *
	 * @param array 					$params
	 * 
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Handle command (Self handling)
	 * 
	 * @return mixed
	 */
	public abstract function handle();
}
