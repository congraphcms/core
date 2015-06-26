<?php
/*
 * This file is part of the cookbook/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cookbook\Core\Event;

use Illuminate\Queue\SerializesModels;

/**
 * CommandEvent class
 * 
 * Cookbook Command Event
 * 
 * @uses  		Illuminate\Queue\SerializesModels
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	cookbook/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
abstract class CommandEvent
{
	/**
	 * Command that called this event
	 * 
	 * @var mixed
	 */
	public $command;


	/**
	 * CommandEvent constructor
	 * 
	 * @param mixed $command
	 */
	public function __construct($command)
	{
		$this->command = $command;
	}
}