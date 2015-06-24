<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Routing\ResponseFactory;

/**
 * AttributeController class
 * 
 * RESTful Controller for attribute resource
 * 
 * @uses  		Illuminate\Routing\Controller
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/eav
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class BaseManagementController extends Controller
{
	public $bus;

	public $response;
	
	public function __construct(Dispatcher $bus, ResponseFactory $response)
	{
		$this->bus = $bus;
		$this->response = $response;
	}
}