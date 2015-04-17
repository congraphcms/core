<?php

class ValidatorTraitTest extends PHPUnit_Framework_TestCase
{

	protected function setUp()
	{
		// create mock for trait
		$this->validatorTraitMock = $this->getMockForTrait('Cookbook\Core\Traits\ValidatorTrait');

		// set errors message bag in validator trait
		$this->validatorTraitMock->setErrors();

		// create mock for validator factory
		$this->validatorFactoryMock = $this->getMockBuilder('Illuminate\Validation\Factory')
										   ->disableOriginalConstructor()
										   ->setMethods(array('make'))
										   ->getMock();


		// create mock for validator
		$this->validatorMock = $this->getMockBuilder('Illuminate\Validation\Validator')
									->disableOriginalConstructor()
									->setMethods(array('fails', 'messages'))
									->getMock();
		
		// factory make method will return our validator mock
		$this->validatorFactoryMock	->method('make')
									->willReturn($this->validatorMock);

		// default error message if validation fails
		$this->errorMessage = array('error' => 'error message');
		
		// factory make method will return our validator mock
		$this->validatorMock->method('messages')
							->willReturn($this->errorMessage);

		// set validator factory in trait mock
		$this->validatorTraitMock->setValidatorFactory($this->validatorFactoryMock);
	}

	public function testValidParameters()
	{
		$params = array(
			'name' => 'Nikola',
			'surname' => 'Plavsic',
			'email' => 'nikola@email.com'
		);

		$rules = array(
			'name' => 'required',
			'surname' => 'required',
			'email' => 'required|email'
		);

		// validator fails method will false (validation will be passed)
		$this->validatorMock->method('fails')
							->willReturn(false);

		// Set up the expectation for the make() method
		// to be called only once and with $params and $rules
		// as its parameters.
		$this->validatorFactoryMock->expects($this->once())
								   ->method('make')
								   ->with(
								   	$this->equalTo($params), 
								   	$this->equalTo($rules)
								   );
		
		// Set up the expectation for the fails() method
		// to be called.
		$this->validatorMock->expects($this->once())
							->method('fails');

		$valid = $this->validatorTraitMock->validateParams($params, $rules);

		// check if validation has passed
		$this->assertTrue($valid);
	}

	public function testInvalidParameters()
	{
		$params = array(
			'name' => 'Nikola',
			'email' => 'nikola@email.com'
		);

		$rules = array(
			'name' => 'required',
			'surname' => 'required',
			'email' => 'required|email'
		);

		// validator fails method will true (validation will not be passed)
		$this->validatorMock->method('fails')
							->willReturn(true);

		// Set up the expectation for the make() method
		// to be called only once and with $params and $rules
		// as its parameters.
		$this->validatorFactoryMock->expects($this->once())
								   ->method('make')
								   ->with(
								   	$this->equalTo($params), 
								   	$this->equalTo($rules)
								   );
		
		// Set up the expectation for the fails() method
		// to be called.
		$this->validatorMock->expects($this->once())
							->method('fails');

		$valid = $this->validatorTraitMock->validateParams($params, $rules);

		// check if validation has passed
		$this->assertFalse($valid);
	}

	public function testNoErrorKey()
	{
		$params = array(
			'name' => 'Nikola'
		);

		$rules = array(
			'name' => 'required',
			'surname' => 'required'
		);
		
		// validator fails method will true (validation will not be passed)
		$this->validatorMock->method('fails')
							->willReturn(true);

		$valid = $this->validatorTraitMock->validateParams($params, $rules);

		// check if validation has passed
		$this->assertFalse($valid);

		// get errors
		$errors = $this->validatorTraitMock->getErrors();

		// check if they are same as errors returned from validator
		$this->assertEquals($this->errorMessage, $errors);
	}

	public function testErrorKey()
	{
		$params = array(
			'name' => 'Nikola'
		);

		$rules = array(
			'name' => 'required',
			'surname' => 'required'
		);
		
		// validator fails method will true (validation will not be passed)
		$this->validatorMock->method('fails')
							->willReturn(true);
		
		$errorKey = 'test';
		$this->validatorTraitMock->setErrorKey($errorKey);

		$valid = $this->validatorTraitMock->validateParams($params, $rules);

		// check if validation has passed
		$this->assertFalse($valid);

		// get errors
		$errors = $this->validatorTraitMock->getErrors();

		// check if they are same as errors returned from validator
		// but keyed by error key
		$this->assertEquals(array( $errorKey => $this->errorMessage), $errors);
	}

	public function testDontCleanParams()
	{
		$params = array(
			'name' => 'Nikola',
			'surname' => 'Plavsic'
		);

		$rules = array(
			'name' => 'required'
		);
		
		// validator fails method will false (validation will be passed)
		$this->validatorMock->method('fails')
							->willReturn(false);

		$valid = $this->validatorTraitMock->validateParams($params, $rules, false);

		// check if validation has passed
		$this->assertTrue($valid);

		// check if params are cleaned (there should stil be surname item)
		$this->assertEquals( array( 'name' => 'Nikola', 'surname' => 'Plavsic' ), $params );
	}

	public function testCleanParams()
	{
		$params = array(
			'name' => 'Nikola',
			'surname' => 'Plavsic'
		);

		$rules = array(
			'name' => 'required'
		);
		
		// validator fails method will false (validation will be passed)
		$this->validatorMock->method('fails')
							->willReturn(false);

		$valid = $this->validatorTraitMock->validateParams($params, $rules, true);

		// check if validation has passed
		$this->assertTrue($valid);

		// check if params are cleaned (no surname item)
		

		$this->assertEquals(array( 'name' => 'Nikola' ), $params);
	}

	public function testUniqueExceptionRule()
	{
		$params = array(
			'id' => 1,
			'name' => 'Nikola',
			'surname' => 'Plavsic'
		);

		$rules = array(
			'id' => 'required',
			'name' => 'required',
			'surname' => 'unique:surnames'
		);
		
		// validator fails method will false (validation will be passed)
		$this->validatorMock->method('fails')
							->willReturn(false);


		
		// changed rules
		$newRules = array(
			'id' => 'required',
			'name' => 'required',
			'surname' => 'unique:surnames,1,id'
		);

		// Set up the expectation for the make() method
		// to be called only once and with $params and changed $newRules
		// as its parameters.
		$this->validatorFactoryMock->expects($this->once())
								   ->method('make')
								   ->with(
								   	$this->equalTo($params), 
								   	$this->equalTo($newRules)
								   );
		

		$valid = $this->validatorTraitMock->validateParams($params, $rules);

		// check if validation has passed
		$this->assertTrue($valid);
	}
}