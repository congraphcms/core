<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Exceptions;

use Exception as PHPException;
use Cookbook\Core\Traits\ErrorManagerTrait;
use Cookbook\Contracts\Core\ErrorManagementContract;

/**
 * Exception class
 * 
 * Cookbook base exception, implements ErrorManagementContract
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class Exception extends PHPException implements ErrorManagementContract
{
	use ErrorManagerTrait;

	public function __construct($messages = [])
	{
		$this->setErrors($messages);
	}
}