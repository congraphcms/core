<?php 
/*
 * This file is part of the Cookbook package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Traits;

use Illuminate\Support\MessageBag;

/**
 * Trait for handling errors
 * 
 * Gives class ability to add, remove and pass errors
 * with use of Illuminate\Support\MessageBag as error messages holder.
 * 
 * @uses 		Illuminate\Support\MessageBag
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	Cookbook/Core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
trait ErrorManagerTrait
{

	/**
	 * Array of errors.
	 *
	 * @var MessageBag
	 */
	public $errors;

	/**
	 * Error key
	 * 
	 * array key for error messages
	 *
	 * @var string
	 */
	protected $errorKey;

	/**
	 * Set error key
	 * 
	 * @param string $key
	 * 
	 * @return void
	 */ 
	public function setErrorKey($key = null)
	{
		$this->errorKey = $key;
	}

	/**
	 * Nest messages according to error key
	 * 
	 * @param array $messages
	 * 
	 * @return array
	 */ 
	protected function resolveErrorKey($messages = [])
	{
		// if there is no error key defined leave messages as they were
		if(empty($this->errorKey))
		{
			return $messages;
		}

		// keys are divided on every .
		$keys = explode('.', $this->errorKey);

		// we need to reverse this array for sorting
		$keys = array_reverse($keys);

		foreach ($keys as $errorKey) {
			$messages = array($errorKey => $messages);
		}

		return $messages;
	}

	/**
	 * Add messages to error message bag
	 * 
	 * @param array $messages (optional)
	 * 
	 * @return boolean
	 */      
	public function addErrors($messages = [])
	{
		// check if messages are an array
		if(! is_array($messages) )
		{
			$messages = [$messages];
		}

		return $this->errors->merge($this->resolveErrorKey($messages));
	}

	/**
	 * Set error messages - deletes previous messages
	 * 
	 * @param MessageProviderInterface | array $messages (optional)
	 * 
	 * @return void
	 */      
	public function setErrors($messages = [])
	{
		$this->errors = new MessageBag();
		$this->addErrors($messages);
	}

	/**
	 * Check if error message bag has eny messages
	 * 
	 * @return boolean
	 */
	public function hasErrors()
	{
		$messages = $this->errors->getMessages();
		return !empty($messages);
	}

	/**
	 * Get all error messages
	 * 
	 * @return boolean
	 */
	public function getErrors()
	{
		return $this->errors->getMessages();
	}

	/**
	 * Get error bag
	 * return whole errors object
	 * 
	 * @return boolean
	 */
	public function getErrorBag()
	{
		return $this->errors;
	}
}
