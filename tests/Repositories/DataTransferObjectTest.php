<?php

use Illuminate\Support\Facades\Cache;
use Congraph\Core\Facades\Trunk;
use Illuminate\Support\Debug\Dumper;


class DataTransferObjectTest extends Orchestra\Testbench\TestCase
{

	public function setUp()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		parent::setUp();
		
		$this->d = new Dumper();

	}

	public function tearDown()
	{
		// fwrite(STDOUT, __METHOD__ . "\n");
		// parent::tearDown();
		Trunk::forgetAll();
		// $this->artisan('db:seed', [
		// 	'--class' => 'ClearDB'
		// ]);

		DB::disconnect();
		
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
			'username'  => 'homestead',
			'password'  => 'secret',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		]);

		$app['config']->set('cache.default', 'file');

		$app['config']->set('cache.stores.file', [
			'driver'	=> 'file',
			'path'   	=> realpath(__DIR__ . '/../storage/cache/'),
		]);

		// $config = require(realpath(__DIR__.'/../../config/eav.php'));

		// $app['config']->set(
		// 	'Congraph::eav', $config
		// );

		// var_dump('CONFIG SETTED');
	}

	protected function getPackageProviders($app)
	{
		return [
			'Congraph\Core\CoreServiceProvider'
		];
	}


	public function testGetUnresolvedObjects()
	{
		$obj1 = new stdClass();
		$obj1->id = 10;
		$obj1->type = 'article';
		$obj1->fields = new stdClass();
		$obj1->fields->title = 'New Title';
		$obj1->fields->desc = 'Description';
		$obj1->fields->author = new stdClass();
		$obj1->fields->author->id = 15;
		$obj1->fields->author->type = 'user';
		$obj1->categories = [];
		$cat1 = new stdClass();
		$cat1->id = 20;
		$cat1->type = 'category';
		$cat2 = new stdClass();
		$cat2->id = 21;
		$cat2->type = 'category';
		$obj1->categories[] = $cat1;
		$obj1->categories[] = $cat2;

		$obj2 = new stdClass();
		$obj2->id = 11;
		$obj2->type = 'article';
		$obj2->fields = new stdClass();
		$obj2->fields->title = 'Totaly New Title';
		$obj2->fields->desc = 'Awesome Description';
		$obj2->fields->author = new stdClass();
		$obj2->fields->author->id = 16;
		$obj2->fields->author->type = 'user';
		$obj2->categories = [];
		$cat3 = new stdClass();
		$cat3->id = 22;
		$cat3->type = 'category';
		$obj2->categories[] = $cat2;
		$obj2->categories[] = $cat3;
		$obj2->parent = new stdClass();
		$obj2->parent->id = 30;
		$obj2->parent->type = 'page';

		$data = [$obj1, $obj2];

		// $include = 'categories, fields.author.data, fields.author.test.new_data, parent';

		$collection = new Congraph\Core\Repositories\Collection($data);
		$result = $collection->toArray();

		$this->d->dump($result);
	}
}
?>