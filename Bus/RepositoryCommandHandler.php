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

use Congraph\Contracts\Core\RepositoryContract;


/**
 * Base Repository Command Handler class
 * Handling repository commands
 * 
 * @uses  		Congraph\Contracts\Core\RepositoryContract
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class RepositoryCommandHandler
{
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
	 * Handle RepositoryCommand
	 * 
	 * @param Congraph\Core\Bus\RepositoryCommand $command
	 * 
	 * @return void
	 */
	abstract public function handle(RepositoryCommand $command);
}