<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Bus;

use Cookbook\Contracts\Core\RepositoryContract;


/**
 * Base Repository Command Handler class
 * Handling repository commands
 * 
 * @uses  		Cookbook\Contracts\Core\RepositoryContract
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class RepositoryCommandHandler
{
	/**
	 * Repository for DB operations
	 * 
	 * @var Cookbook\Contracts\Core\RepositoryContract
	 */
	protected $repository;

	/**
	 * Create new RepositoryCommandHandler
	 * 
	 * @param Cookbook\Contracts\Core\RepositoryContract $repository
	 * 
	 * @return void
	 */
	public function __construct(RepositoryContract $repository)
	{
		// inject dependencies
		$this->repository = $repository;
	}


	/**
	 * Handle RepositoryCommand
	 * 
	 * @param Cookbook\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	abstract public function handle(RepositoryCommand $command);
}