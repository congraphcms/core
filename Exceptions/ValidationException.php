<?php
/*
 * This file is part of the congraph/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Core\Exceptions;

/**
 * ValidationException class
 * 
 * Congraph validation exception
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class ValidationException extends BadRequestException
{
	public function __construct($messages = [], $code = 422, $previous = [])
	{
		$this->setErrors($messages);

		$this->code = $code;

		$this->previous = $previous;
	}
}