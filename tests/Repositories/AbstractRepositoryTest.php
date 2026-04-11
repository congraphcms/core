<?php

require_once __DIR__ . '/../Fixtures/Repositories/RepositoryStub.php';

use Illuminate\Database\Connection;

class AbstractRepositoryTest extends \PHPUnit\Framework\TestCase
{
	protected Connection $db;

	protected RepositoryStub $stub;

	protected function setUp(): void
	{
		$this->db = $this->makeConnectionStub();
		$this->stub = new RepositoryStub($this->db);
	}

	public function testCallToUnknownMethod()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Unkonown method abcdefg.');
		$this->stub->abcdefg();
	}

	public function testTransactionMethods()
	{
		// defaults to null
		$transactionMethods = $this->stub->getTransactionMethods();
		$this->assertNull($transactionMethods);


		// set transaction method
		$this->stub->setTransactionMethod('test');
		$transactionMethods = $this->stub->getTransactionMethods();
		$this->assertEquals(['test'], $transactionMethods);

		// remove transaction method
		$this->stub->removeTransactionMethod('test');
		$transactionMethods = $this->stub->getTransactionMethods();
		$this->assertNull($transactionMethods);
	}

	public function testCallToCreateMethod()
	{
		$db = $this->mockConnection();

		$db 	->expects($this->once())
					->method('beginTransaction')
					->willReturn(true);

		$db 	->expects($this->once())
					->method('commit')
					->willReturn(true);

		$stub = $this->getMockBuilder(RepositoryStub::class)
			->setConstructorArgs([$db])
			->onlyMethods(['_create'])
			->getMock();

		$stub ->expects($this->once())
			 		->method('_create')
			 		->willReturn(true);

		// set create as transaction method
		$stub->setTransactionMethod('_create');

		$this->assertTrue($stub->create(false));

		$stub->removeTransactionMethod('_create');
	}

	public function testCallToUpdateMethod()
	{
		$db = $this->mockConnection();

		$db 	->expects($this->once())
					->method('beginTransaction')
					->willReturn(true);

		$db 	->expects($this->once())
					->method('commit')
					->willReturn(true);

		$stub = $this->getMockBuilder(RepositoryStub::class)
			->setConstructorArgs([$db])
			->onlyMethods(['_update'])
			->getMock();

		$stub ->expects($this->once())
			 		->method('_update')
			 		->willReturn(true);

		// set update as transaction method
		$stub->setTransactionMethod('_update');

		$this->assertTrue($stub->update(1, false));

		$stub->removeTransactionMethod('_update');
	}

	public function testCallToDeleteMethod()
	{
		$db = $this->mockConnection();

		$db 	->expects($this->once())
					->method('beginTransaction')
					->willReturn(true);

		$db 	->expects($this->once())
					->method('commit')
					->willReturn(true);

		$stub = $this->getMockBuilder(RepositoryStub::class)
			->setConstructorArgs([$db])
			->onlyMethods(['_delete'])
			->getMock();

		$stub ->expects($this->once())
			 		->method('_delete')
			 		->willReturn(true);

		// set delete as transaction method
		$stub->setTransactionMethod('_delete');

		$this->assertTrue($stub->delete(false));

		$stub->removeTransactionMethod('_delete');
	}


	// public function testIncludeSorting()
	// {
	// 	$include = 'attributes.data, attributes.test.data, fields.author';

	// 	$sorted = $this->stub->sortInclude($include);
	// 	$shouldBe = [
	// 		'attributes' => [
	// 			'data',
	// 			'test.data'
	// 		],
	// 		'fields' => ['author']
	// 	];
	// 	$this->assertEquals($shouldBe, $sorted);
	// }

	// public function testGetUnresolvedObjects()
	// {
	// 	$obj1 = new stdClass();
	// 	$obj1->id = 10;
	// 	$obj1->type = 'article';
	// 	$obj1->fields = new stdClass();
	// 	$obj1->fields->title = 'New Title';
	// 	$obj1->fields->desc = 'Description';
	// 	$obj1->fields->author = new stdClass();
	// 	$obj1->fields->author->id = 15;
	// 	$obj1->fields->author->type = 'user';
	// 	$obj1->categories = [];
	// 	$cat1 = new stdClass();
	// 	$cat1->id = 20;
	// 	$cat1->type = 'category';
	// 	$cat2 = new stdClass();
	// 	$cat2->id = 21;
	// 	$cat2->type = 'category';
	// 	$obj1->categories[] = $cat1;
	// 	$obj1->categories[] = $cat2;

	// 	$obj2 = new stdClass();
	// 	$obj2->id = 11;
	// 	$obj2->type = 'article';
	// 	$obj2->fields = new stdClass();
	// 	$obj2->fields->title = 'Totaly New Title';
	// 	$obj2->fields->desc = 'Awesome Description';
	// 	$obj2->fields->author = new stdClass();
	// 	$obj2->fields->author->id = 16;
	// 	$obj2->fields->author->type = 'user';
	// 	$obj2->categories = [];
	// 	$cat3 = new stdClass();
	// 	$cat3->id = 22;
	// 	$cat3->type = 'category';
	// 	$obj2->categories[] = $cat2;
	// 	$obj2->categories[] = $cat3;
	// 	$obj2->parent = new stdClass();
	// 	$obj2->parent->id = 30;
	// 	$obj2->parent->type = 'page';

	// 	$data = [$obj1, $obj2];

	// 	$include = 'categories, fields.author.data, fields.author.test.new_data, parent';

	// 	$unresolvedObjects = $this->stub->getUnresolvedObjects($data, $include);

	// 	$shouldBe = [
  	// 		'eyJ0eXBlIjoidXNlciIsImluY2x1ZGUiOlsiZGF0YSIsInRlc3QubmV3X2RhdGEiXX0=' => [
    // 			'type' => "user",
    // 			'include' => [ "data", "test.new_data" ],
    // 			'ids' => [ 15, 16 ]
    // 		],
    // 		'eyJ0eXBlIjoiY2F0ZWdvcnkiLCJpbmNsdWRlIjpbXX0=' => [
    // 			'type' => "category",
    // 			'include' => [],
    // 			'ids' => [ 20, 21, 22 ]
    // 		],
    // 		'eyJ0eXBlIjoicGFnZSIsImluY2x1ZGUiOltdfQ==' => [
    // 			'type' => "page",
    // 			'include' => [],
    // 			'ids' => [ 30 ]
    // 		]
    // 	];

    // 	$this->assertEquals($shouldBe, $unresolvedObjects);
	// }

	// public function testPopulateResults()
	// {
	// 	$obj1 = new stdClass();
	// 	$obj1->id = 10;
	// 	$obj1->type = 'article';
	// 	$obj1->fields = new stdClass();
	// 	$obj1->fields->title = 'New Title';
	// 	$obj1->fields->desc = 'Description';
	// 	$obj1->fields->author = new stdClass();
	// 	$obj1->fields->author->id = 15;
	// 	$obj1->fields->author->type = 'user';
	// 	$obj1->categories = [];
	// 	$cat1 = new stdClass();
	// 	$cat1->id = 20;
	// 	$cat1->type = 'category';
	// 	$cat2 = new stdClass();
	// 	$cat2->id = 21;
	// 	$cat2->type = 'category';
	// 	$obj1->categories[] = $cat1;
	// 	$obj1->categories[] = $cat2;

	// 	$obj2 = new stdClass();
	// 	$obj2->id = 11;
	// 	$obj2->type = 'article';
	// 	$obj2->fields = new stdClass();
	// 	$obj2->fields->title = 'Totaly New Title';
	// 	$obj2->fields->desc = 'Awesome Description';
	// 	$obj2->fields->author = new stdClass();
	// 	$obj2->fields->author->id = 16;
	// 	$obj2->fields->author->type = 'user';
	// 	$obj2->categories = [];
	// 	$cat3 = new stdClass();
	// 	$cat3->id = 22;
	// 	$cat3->type = 'category';
	// 	$obj2->categories[] = $cat2;
	// 	$obj2->categories[] = $cat3;
	// 	$obj2->parent = new stdClass();
	// 	$obj2->parent->id = 30;
	// 	$obj2->parent->type = 'page';

	// 	$data = [$obj1, $obj2];

	// 	$include = 'categories, fields.author.data, fields.author.test.new_data, parent';

	// 	$unresolvedObjects = $this->stub->getUnresolvedObjects($data, $include);

	// 	$shouldBe = [
  	// 		'eyJ0eXBlIjoidXNlciIsImluY2x1ZGUiOlsiZGF0YSIsInRlc3QubmV3X2RhdGEiXX0=' => [
    // 			'type' => "user",
    // 			'include' => [ "data", "test.new_data" ],
    // 			'ids' => [ 15, 16 ]
    // 		],
    // 		'eyJ0eXBlIjoiY2F0ZWdvcnkiLCJpbmNsdWRlIjpbXX0=' => [
    // 			'type' => "category",
    // 			'include' => [],
    // 			'ids' => [ 20, 21, 22 ]
    // 		],
    // 		'eyJ0eXBlIjoicGFnZSIsImluY2x1ZGUiOltdfQ==' => [
    // 			'type' => "page",
    // 			'include' => [],
    // 			'ids' => [ 30 ]
    // 		]
    // 	];

    // 	$this->assertEquals($shouldBe, $unresolvedObjects);
	// }

	/**
	 * Get mock for Illuminate\Database\Connection
	 */
	public function mockConnection()
	{
		return $this->getMockBuilder(Connection::class)
					->disableOriginalConstructor()
					->onlyMethods(['beginTransaction', 'commit', 'rollback'])
					->getMock();
	}

	private function makeConnectionStub(): Connection
	{
		return new class extends Connection {
			public function __construct()
			{
			}
		};
	}
}
