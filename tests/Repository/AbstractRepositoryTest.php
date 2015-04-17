<?php

class AbstractRepositoryTest extends PHPUnit_Framework_TestCase
{

	protected function setUp()
	{
		// create mock for DB
		$this->db = $this->mockConnection();

		// create stub for Abstract Repository
		$this->stub = $this	->getMockBuilder('Cookbook\Core\Repository\AbstractRepository')
							->setConstructorArgs(array($this->db))
							->getMockForAbstractClass();

		
	}

	/**
	 * @expectedException BadMethodCallException
	 * @expectedExceptionMessage Unkonown method abcdefg.
	 */
	public function testCallToUnknownMethod()
	{
		$this->stub->abcdefg();
	}

	/**
	 * @test
	 */
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

	/**
	 * @test
	 */
	public function testCallToCreateMethod()
	{
		$this->db 	->expects($this->once())
					->method('beginTransaction')
					->willReturn(true);

		$this->db 	->expects($this->once())
					->method('commit')
					->willReturn(true);

		$this->stub ->expects($this->once())
			 		->method('_create')
			 		->willReturn(true);

		// set create as transaction method
		$this->stub->setTransactionMethod('_create');

		$this->assertTrue($this->stub->create(false));

		$this->stub->removeTransactionMethod('_create');
	}

	/**
	 * @test
	 */
	public function testCallToUpdateMethod()
	{
		$this->db 	->expects($this->once())
					->method('beginTransaction')
					->willReturn(true);

		$this->db 	->expects($this->once())
					->method('commit')
					->willReturn(true);

		$this->stub ->expects($this->once())
			 		->method('_update')
			 		->willReturn(true);

		// set update as transaction method
		$this->stub->setTransactionMethod('_update');

		$this->assertTrue($this->stub->update(false));

		$this->stub->removeTransactionMethod('_update');
	}

	/**
	 * @test
	 */
	public function testCallToDeleteMethod()
	{
		$this->db 	->expects($this->once())
					->method('beginTransaction')
					->willReturn(true);

		$this->db 	->expects($this->once())
					->method('commit')
					->willReturn(true);

		$this->stub ->expects($this->once())
			 		->method('_delete')
			 		->willReturn(true);

		// set delete as transaction method
		$this->stub->setTransactionMethod('_delete');

		$this->assertTrue($this->stub->delete(false));

		$this->stub->removeTransactionMethod('_delete');
	}

	/**
	 * Get mock for Illuminate\Database\Connection
	 */
	public function mockConnection()
	{
		return $this->getMockBuilder('Illuminate\Database\Connection')
					->setMethods(array('beginTransaction', 'commit', 'rollback'))
					->getMock();
	}
}
?>