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

	public function __construct($messages = [], $code = 500)
	{
		$this->setErrors($messages);

		$this->code = $code;
	}

	public function toArray()
	{
		return $this->compileErrors();
	}

	protected function compileErrors()
	{
		$flatErrors = $this->getFlatErrors();

		$compiledErrors = [];

		foreach ($flatErrors as $errorKey => $errorMessages) 
		{
			foreach ($errorMessages as $message)
			{
				$compiledError = [];
				$compiledError['code'] = $this->code;
				$compiledError['status'] = intval(substr(strval($this->code), 0, 3));
				$compiledError['message'] = $message;
				$compiledError['pointer'] = $errorKey;

				$compiledErrors[] = $compiledError;
			}
		}
		return $compiledErrors;
	}

	protected function getFlatErrors()
	{
		$errors = $this->getErrors();
		return $this->loopErrors($errors);
		
	}

	protected function loopErrors($errors, $key = null)
	{

		if(is_assoc($errors))
		{
			$flatErrors = [];
			foreach ($errors as $errorKey => $error) 
			{
				$newKey = (empty($key))? '/' . $errorKey : $key . '/' . $errorKey;
				$flatErrors = array_merge_recursive($flatErrors, $this->loopErrors($error, $newKey));
			}
			return $flatErrors;
		}

		if( ! is_array($errors) )
		{
			$errors = [$errors];
		}

		$newKey = (empty($key))? '/' : $key;
		return [$newKey => $errors];
		
	}
}