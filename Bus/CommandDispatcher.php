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
use Cookbook\Contracts\Core\ValidationCommandDispatcherContract;
use Cookbook\Contracts\Core\SelfValidating;
use Illuminate\Support\Facades\Event;

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
class CommandDispatcher extends Dispatcher implements ValidationCommandDispatcherContract
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
	 * All of the command-to-event mappings.
	 *
	 * @var array
	 */
	protected $eventMappings = [];

	/**
	 * The fallback event mapping Closure.
	 *
	 * @var \Closure
	 */
	protected $eventMapper;


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
		// fire any registered events before command
		$this->fireBeforeCommandEvents($command);

		// validate command with registered validator
		$this->validate($command);
		
		// dispatch the command
		$result = parent::dispatch($command, $afterResolving);
		
		// fire any registered events after command
		$this->fireAfterCommandEvents($command, $result);

		// return the handler result
		return $result;
	}

	/**
	 * Fire registered before command events
	 *
	 * @param  mixed  $command
	 * 
	 * @return void
	 */
	protected function fireBeforeCommandEvents($command)
	{
		// get events classes
		$events = $this->getBeforeEvents($command);

		// if no registered events return
		if( $events === false )
		{
			return;
		}

		if( ! is_array($events) )
		{
			$events = [$events];
		}


		foreach ($events as $event) {
			// fire the events
			Event::fire( new $event($command) );
		}
		
	}

	/**
	 * Fire registered after command events
	 *
	 * @param  mixed  $command
	 * 
	 * @return void
	 */
	protected function fireAfterCommandEvents($command, &$result)
	{
		// get event class
		$events = $this->getAfterEvents($command);

		// if no registered events return
		if( $events === false )
		{
			return;
		}

		if( ! is_array($events) )
		{
			$events = [$events];
		}


		foreach ($events as $event) {
			// fire the events
			Event::fire( new $event($command, $result) );
		}
	}

	/**
	 * Get the events classes for fireing before the given command.
	 *
	 * @param  mixed  $command
	 * @return string
	 */
	public function getBeforeEvents($command)
	{
		return $this->getCommandEvents($command, 'before');
	}

	/**
	 * Get the events classes for fireing after the given command.
	 *
	 * @param  mixed  $command
	 * @return string
	 */
	public function getAfterEvents($command)
	{
		return $this->getCommandEvents($command, 'after');
	}

	/**
	 * Get the events classes for the given command.
	 *
	 * @param  mixed  	$command
	 * @param  string  	$beforeOrAfter
	 * @return string
	 */
	protected function getCommandEvents($command, $beforeOrAfter)
	{
		// get command class name
		$commandName = get_class($command);

		// if there is registered event
		if ( isset($this->eventMappings[$commandName]) && isset($this->eventMappings[$commandName][$beforeOrAfter]) )
		{
			return $this->eventMappings[$commandName][$beforeOrAfter];
		}
		elseif ($this->eventMapper)
		{
			return call_user_func($this->eventMapper, $command);
		}

		return false;
	}

	/**
	 * Register command-to-event mappings.
	 *
	 * @param  array  $events
	 * @return void
	 */
	public function mapEvents(array $events)
	{
		$this->eventMappings = array_merge_recursive($this->eventMappings, $events);
	}

	/**
	 * Register a fallback eventMapper callback.
	 *
	 * @param  \Closure  $mapper
	 * @return void
	 */
	public function mapEventsUsing(Closure $mapper)
	{
		$this->eventMapper = $mapper;
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
		$validator = $this->container->make($this->getValidatorClass($command));
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

		return false;
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