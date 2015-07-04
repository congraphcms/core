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

use Closure;
use Exception;
use Illuminate\Http\Response;
use Cookbook\Core\Exceptions\Exception as CookbookException;

/**
 * ApiCommandDispatcher class
 * 
 * Cookbook API Command Dispatcher
 * 
 * @uses  		Cookbook\Core\Bus\CommandDispatcher
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ApiCommandDispatcher
{

	protected $dispatcher;


	public function __construct(CommandDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	

}