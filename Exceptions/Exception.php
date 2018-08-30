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

use Exception as PHPException;
use Congraph\Core\Traits\ErrorManagerTrait;
use Congraph\Contracts\Core\ErrorManagementContract;

/**
 * Exception class
 * 
 * Congraph base exception, implements ErrorManagementContract
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class Exception extends PHPException implements ErrorManagementContract
{
	use ErrorManagerTrait;

	/**
	 * Array of previous exceptions
	 * 
	 * @var Array
	 */
	protected $previous;

	public function __construct($messages = [], $code = 500, $previous = [])
	{
		$this->setErrors($messages);

		$this->code = $code;

		$this->previous = $previous;
	}

	public function toArray()
	{
		return $this->compileErrors();
	}

	public function compileErrors()
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

		if( ! empty($this->previous) && is_array($this->previous) )
		{
			foreach ($previous as $exception) {
				if($exception instanceOf Exception)
				{
					$compiledErrors = array_merge_recursive($compiledErrors, $exception->compileErrors());
				}
			}
		}
		
		$compiledErrors = $this->sortErrors($compiledErrors);
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

	protected function sortErrors($errors)
	{
		usort($errors, function($a, $b){
			if($a['code'] > $b['code'])
			{
				return 1;
			}
			if($a['code'] < $b['code'])
			{
				return -1;
			}

			return 0;
		});

		return $errors;
	}
}