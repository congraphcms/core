<?php
include_once(realpath(__DIR__ . '/../helpers.php'));

class HelpersTest extends PHPUnit_Framework_TestCase
{

	

	

	/**
	 * @test
	 */
	public function testCbParseUrlParams()
	{
		$params = [
			'include' => 'attributes.sets.options,attributes.relations,sets'
		];
		$params = cbParseUrlParams($params);

		var_dump($params);
	}

}