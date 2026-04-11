<?php

use Congraph\Core\Exceptions\BadRequestException;
use Congraph\Core\Exceptions\Exception;
use Congraph\Core\Exceptions\NotFoundException;
use Congraph\Core\Exceptions\ValidationException;

final class ExceptionTest extends \PHPUnit\Framework\TestCase
{
	public function testToArrayCompilesFlatErrorsForNestedMessages(): void
	{
		$exception = new Exception([
			'data' => [
				'attributes' => [
					'title' => ['Title is required'],
				],
			],
		], 422);

		$this->assertSame([
			[
				'code' => 422,
				'status' => 422,
				'message' => 'Title is required',
				'pointer' => '/data/attributes/title',
			],
		], $exception->toArray());
	}

	public function testCompileErrorsSortsByCodeAcrossPreviousExceptions(): void
	{
		$current = new Exception([
			'email' => ['Email is invalid'],
		], 422, [
			new NotFoundException([
				'id' => ['Entity not found'],
			]),
			new BadRequestException([
				'payload' => ['Payload is malformed'],
			]),
		]);

		$this->assertSame([
			[
				'code' => 400,
				'status' => 400,
				'message' => 'Payload is malformed',
				'pointer' => '/payload',
			],
			[
				'code' => 404,
				'status' => 404,
				'message' => 'Entity not found',
				'pointer' => '/id',
			],
			[
				'code' => 422,
				'status' => 422,
				'message' => 'Email is invalid',
				'pointer' => '/email',
			],
		], $current->compileErrors());
	}

	public function testSubclassConstructorsUseExpectedStatusCodes(): void
	{
		$badRequest = new BadRequestException(['request' => ['Bad request']]);
		$notFound = new NotFoundException(['resource' => ['Missing']]);
		$validation = new ValidationException(['field' => ['Required']]);

		$this->assertSame(400, $badRequest->toArray()[0]['code']);
		$this->assertSame(404, $notFound->toArray()[0]['code']);
		$this->assertSame(422, $validation->toArray()[0]['code']);
	}
}
