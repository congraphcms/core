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
use Illuminate\Bus\Dispatcher;
use Cookbook\Contracts\Core\ValidationCommandDispatcher;
use Cookbook\Contracts\Core\SelfValidating;

/**
 * CommandDispatcher class
 * 
 * Cookbook Command Dispatcher
 * 
 * @uses  		Illuminate\Bus\Dispatcher
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CommandDispatcher extends Dispatcher implements ValidationCommandDispatcher
{

	/**
	 * All of the command-to-validator mappings.
	 *
	 * @var array
	 */
	protected $validatorMappings = [];

	/**
	 * The fallback validator mapping Closure.
	 *
	 * @var \Closure
	 */
	protected $validatorMapper;


	/**
	 * Dispatch a command to its appropriate handler.
	 *
	 * @param  mixed  $command
	 * @param  \Closure|null  $afterResolving
	 * 
	 * @return mixed
	 */
	public function dispatch($command, Closure $afterResolving = null)
	{
		$this->validate($command);
		
		parent::dispatch($command, $afterResolving);
		
	}

	/**
	 * Validate a command with its appropriate validator.
	 *
	 * @param  mixed  $command
	 * 
	 * @return mixed
	 */
	public function validate($command)
	{
		$validator = $this->getValidatorClass($command);
		$method = $this->getValidatorMethod($command);

		call_user_func([$validator, $method], $command);
	}

	/**
	 * Get the validator class for the given command.
	 *
	 * @param  mixed  $command
	 * @return string
	 */
	public function getValidatorClass($command)
	{
		if ($command instanceof SelfValidating)
		{
			return get_class($command);
		}

		return $this->inflectValidatorSegment($command, 0);
	}

	/**
	 * Get the validator method for the given command.
	 *
	 * @param  mixed  $command
	 * @return string
	 */
	public function getValidatorMethod($command)
	{
		if ($command instanceof SelfValidating) {
			return 'validate';
		}

		return $this->inflectValidatorSegment($command, 1);
	}

	/**
	 * Get the given handler segment for the given command.
	 *
	 * @param  mixed  $command
	 * @param  int  $segment
	 * @return string
	 */
	protected function inflectValidatorSegment($command, $segment)
	{
		$className = get_class($command);

		if (isset($this->validatorMappings[$className]))
		{
			return $this->getValidatorMappingSegment($className, $segment);
		}
		elseif ($this->validatorMapper)
		{
			return $this->getValidatorMapperSegment($command, $segment);
		}

		throw new InvalidArgumentException("No validator registered for command [{$className}]");
	}

	/**
	 * Get the given segment from a given class validator.
	 *
	 * @param  string  $className
	 * @param  int  $segment
	 * @return string
	 */
	protected function getValidatorMappingSegment($className, $segment)
	{
		return explode('@', $this->validatorMappings[$className])[$segment];
	}

	/**
	 * Get the given segment from a given class validator using the custom mapper.
	 *
	 * @param  mixed  $command
	 * @param  int  $segment
	 * @return string
	 */
	protected function getValidatorMapperSegment($command, $segment)
	{
		return explode('@', call_user_func($this->validatorMapper, $command))[$segment];
	}

	/**
	 * Register command-to-validator mappings.
	 *
	 * @param  array  $validators
	 * @return void
	 */
	public function mapValidators(array $validators)
	{
		$this->validatorMappings = array_merge($this->validatorMappings, $validators);
	}

	/**
	 * Register a fallback validatorMapper callback.
	 *
	 * @param  \Closure  $mapper
	 * @return void
	 */
	public function mapValidatorsUsing(Closure $mapper)
	{
		$this->validatorMapper = $mapper;
	}
}