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
 * @author  Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package Cookbook/Core
 * @since v0.4
 * @copyright  Vizioart PR Velimir Matic
 * @version  v0.4
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
	 * Add messages to error message bag
	 * 
	 * @param array $messages (optional)
	 * 
	 * @return boolean
	 */      
	public function addErrors($messages = array())
	{
		return $this->errors->merge($messages);
	}

	/**
	 * Set error messages - deletes previous messages
	 * 
	 * @param MessageProviderInterface | array $messages (optional)
	 * 
	 * @return void
	 */      
	public function setErrors($messages = array())
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
