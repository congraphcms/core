<?php

class ErrorManagerTraitTest extends PHPUnit_Framework_TestCase
{

	public function testSetEmptyErrors()
	{
		// create mock for trait
		$mock = $this->getMockForTrait('Congraph\Core\Traits\ErrorManagerTrait');

		// set errors message bag
		$mock->setErrors();

		// get error bag
		$bag = $mock->getErrorBag();

		// assert if bag is instance of MessageBag
		$this->assertInstanceOf('Illuminate\Support\MessageBag', $bag);

		$hasErrors = $mock->hasErrors();
		// assert if bag is empty
		$this->assertFalse($hasErrors);
	}

	public function testSetErrorsWithMessages()
	{
		// create mock for trait
		$mock = $this->getMockForTrait('Congraph\Core\Traits\ErrorManagerTrait');

		// create errors
		$messages = array(
			'error1' => array('Error 1'),
			'error2' => array('Error 2')
		);
		// add them on errors bag setup
		$mock->setErrors($messages);

		// assert if error bag is still instance of MessageBag
		$bag = $mock->getErrorBag();
		$this->assertInstanceOf('Illuminate\Support\MessageBag', $bag);

		// check errors that are returned
		$errorMessages = $mock->getErrors();
		$this->assertEquals($messages, $errorMessages);

		$hasErrors = $mock->hasErrors();
		// assert bag is not empty
		$this->assertTrue($hasErrors);
	}

	public function testEmptyErrors()
	{
		// create mock for trait
		$mock = $this->getMockForTrait('Congraph\Core\Traits\ErrorManagerTrait');

		// create errors
		$messages = array(
			'error1' => array('Error 1'),
			'error2' => array('Error 2')
		);
		// add them on errors bag setup
		$mock->setErrors($messages);

		// check errors that are returned
		$errorMessages = $mock->getErrors();
		$this->assertEquals($messages, $errorMessages);

		$hasErrors = $mock->hasErrors();
		// assert bag is not empty
		$this->assertTrue($hasErrors);

		// empty error message bag
		$mock->setErrors();

		$hasErrors = $mock->hasErrors();
		// assert bag is empty
		$this->assertFalse($hasErrors);
	}
		
	public function testAddingErrorMessages()
	{
		// create mock for trait
		$mock = $this->getMockForTrait('Congraph\Core\Traits\ErrorManagerTrait');

		// create different errors
		$messages = array(
			'error3' => 'Error 3',
			'error4' => 'Error 4',
			'error5' => array('Error 5')
		);

		// add them on errors bag setup
		$mock->setErrors($messages);

		// assert if error bag is still instance of MessageBag
		$bag = $mock->getErrorBag();
		$this->assertInstanceOf('Illuminate\Support\MessageBag', $bag);

		// check errors that are returned
		$errorMessages = $mock->getErrors();

		// assert errors are returned correctly
		$this->assertEquals($messages, $errorMessages);

		// add some new and update some old errors
		$newMessages = array(
			'error4' => 'Error 4.5',
			'error5' => array('error6'),
			'error7' => 'error7',
			'error8' => 8
		);

		// add errors to existing errors bag
		$mock->addErrors($newMessages);

		// merge messages
		$messages = array_merge_recursive($messages, $newMessages);

		$errorMessages = $mock->getErrors();

		// assert errors are returned correctly
		$this->assertEquals($messages, $errorMessages);
	}

}
?>