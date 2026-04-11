<?php

use Congraph\Contracts\Core\RepositoryContract;
use Congraph\Core\Bus\RepositoryCommand;

final class RepositoryContractStub implements RepositoryContract
{
	public function create($model)
	{
		return null;
	}

	public function update($id, $model)
	{
		return null;
	}

	public function delete($id)
	{
		return null;
	}

	public function fetch($id, $include = [])
	{
		return null;
	}

	public function get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{
		return null;
	}
}

final class RepositoryCommandTest extends \PHPUnit\Framework\TestCase
{
	public function testConstructorInjectsRepositoryAndHandleCanUseIt(): void
	{
		$repository = new RepositoryContractStub();

		$command = new class($repository) extends RepositoryCommand {
			public function handle(): mixed
			{
				return $this->repository;
			}
		};

		$this->assertSame($repository, $command->handle());
	}

	public function testSettersPopulatePublicCommandState(): void
	{
		$repository = new RepositoryContractStub();

		$command = new class($repository) extends RepositoryCommand {
			public function handle(): mixed
			{
				return null;
			}
		};

		$command->setId(15);
		$command->setParams(['status' => 'draft']);

		$this->assertSame(15, $command->id);
		$this->assertSame(['status' => 'draft'], $command->params);
	}
}
