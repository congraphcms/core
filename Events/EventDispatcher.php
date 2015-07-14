<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Events;

use Illuminate\Events\Dispatcher;

/**
 * EventDispatcher class
 * 
 * Cookbook Event Dispatcher
 * 
 * @uses  		Illuminate\Events\Dispatcher
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class EventDispatcher extends Dispatcher
{
	/**
	 * All ValidationExceptions that occured
	 *
	 * @var array
	 */
	protected $validationExceptions;

	/**
     * Fire an event and call the listeners.
     *
     * @param  string|object  $event
     * @param  mixed  $payload
     * @param  bool  $halt
     * @return array|null
     */
    public function fire($event, &$payload = [], $halt = false)
    {
        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.
        if ( is_object($event) )
        {
            list($payload, $event, $eventName) = [ [$event], $event, get_class($event) ];
        }
        else
        {
            $eventName = $event;
        }

        $responses = [];

        // If an array is not given to us as the payload, we will turn it into one so
        // we can easily use call_user_func_array on the listeners, passing in the
        // payload to each of them so that they receive each of these arguments.
        if ( ! is_array($payload) )
        {
            $payload = [$payload];
        }

        $this->firing[] = $eventName;

        if ( isset($payload[0]) && $payload[0] instanceof ShouldBroadcast )
        {
            $this->broadcastEvent($payload[0]);
        }

        foreach ($this->getListeners($event) as $listener)
        {
            dd($listener());
        	
        	try
        	{
        		$response = call_user_func_array($listener, $payload);
        	}
            catch(ValidationException $e)
            {
            	$this->validationExceptions[] = $e;
            	$response = 0;
            }

            // If a response is returned from the listener and event halting is enabled
            // we will just return this response, and not call the rest of the event
            // listeners. Otherwise we will add the response on the response list.
            if (!is_null($response) && $halt)
            {
                array_pop($this->firing);

                return $response;
            }

            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($response === false)
            {
                break;
            }

            $responses[] = $response;
        }

        array_pop($this->firing);

        if( ! empty($this->validationExceptions) )
        {
        	throw new ValidationException([], 422, $this->validationExceptions);
        	
        }

        return $halt ? null : $responses;
    }



    /**
     * Get all of the listeners for a given event.
     *
     * @param  mixed  $event
     * @return array
     */
    public function getListeners($event)
    {
        $parentListeners = [];
        $wildcards = [];

        if( is_object($event) )
        {
            $eventName = get_class($event);
            $parentListeners = $this->getParentListeners($event);
        }
        else
        {
            $eventName = $event;
            $wildcards = $this->getWildcardListeners($event);
        }

        if (!isset($this->sorted[$eventName])) {
            $this->sortListeners($eventName);
        }

        return array_merge($this->sorted[$eventName], $wildcards, $parentListeners);
    }

    /**
     * Get the parent class or interface listeners for the event.
     *
     * @param  object  $event
     * @return array
     */
    protected function getParentListeners($event)
    {
        $parentListeners = [];

        foreach ($this->listeners as $key => $listeners)
        {
            if ($event instanceof $key)
            {
                $parentListeners = array_merge($parentListeners, $listeners);
            }
        }

        return $parentListeners;
    }
}