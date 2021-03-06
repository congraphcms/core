<?php
/*
 * This file is part of the congraph/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Core\Events;

/**
 * AfterCommandEvent class
 * 
 * Congraph After Command Event
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class AfterCommandEvent extends CommandEvent
{
	/**
	 * Command result
	 * 
	 * @var mixed
	 */
	public $result;


	/**
	 * AfterCommandEvent constructor
	 * 
	 * @param mixed $command
	 * @param mixed &$result
	 */
	public function __construct($command, &$result)
	{
		$this->command = $command;
		$this->result = &$result;
	}
}