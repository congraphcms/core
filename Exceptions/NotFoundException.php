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

/**
 * NotFoundException class
 * 
 * Cookbook 404 not found exception
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class NotFoundException extends BadRequestException
{
	public function __construct($messages = [], $code = 404, $previous = [])
	{
		$this->setErrors($messages);

		$this->code = $code;

		$this->previous = $previous;
	}
}