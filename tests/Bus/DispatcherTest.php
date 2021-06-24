<?php

use Illuminate\Support\Facades\Cache;
use Symfony\Component\VarDumper\VarDumper as Dumper;

class DispatcherTest extends \Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		parent::setUp();

		// code here
		$this->d = new Dumper();
	}

	public function tearDown()
	{
		// code here

		parent::tearDown();
	}

		/**
	 * Define environment setup.
	 *
	 * @param  \Illuminate\Foundation\Application  $app
	 *
	 * @return void
	 */
	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   	=> 'mysql',
			'host'      => '127.0.0.1',
			'port'		=> '3306',
			'database'	=> 'congraph_testbench',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		]);

	}

	protected function getPackageProviders($app)
	{
		return ['Congraph\Core\CoreServiceProvider'];
	}

	public function testLoadingOfDispatcher()
	{
		fwrite(STDOUT, __METHOD__ . "\n");

		$bus = $this->app->make('Congraph\Core\Bus\CommandDispatcher');
		$this->assertEquals('Congraph\Core\Bus\CommandDispatcher', get_class($bus));
	}

}