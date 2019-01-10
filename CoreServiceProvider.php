<?php
/*
 * This file is part of the congraph/core package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Congraph\Core;

use Illuminate\Support\ServiceProvider;

/**
 * CoreServiceProvider service provider
 * 
 * @uses   		Illuminate\Support\ServiceProvider
 * 
 * @author  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @copyright  	Nikola Plavšić <nikolaplavsic@gmail.com>
 * @package 	congraph/core
 * @since 		0.1.0-alpha
 * @version  	0.1.0-alpha
 */
class CoreServiceProvider extends ServiceProvider
{

	/**
	* Register
	* 
	* @return void
	*/
	public function register() 
	{
		$this->registerServiceProviders();
	}

	/**
	 * Boot
	 * 
	 * @return void
	 */
	public function boot() 
	{
		include __DIR__ . '/helpers.php';
	}

	/**
	 * Register Service Providers for this package
	 * 
	 * @return void
	 */
	protected function registerServiceProviders()
	{
		// Core Bus
		// -----------------------------------------------------------------------------
		$this->app->register('Congraph\Core\Bus\BusServiceProvider');

		// Core Event
		// -----------------------------------------------------------------------------
		$this->app->register('Congraph\Core\Events\EventsServiceProvider');

		// Repositories
		// -----------------------------------------------------------------------------
		$this->app->register('Congraph\Core\Repositories\RepositoriesServiceProvider');
	}

}