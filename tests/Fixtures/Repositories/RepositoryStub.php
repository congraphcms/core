<?php

use Congraph\Core\Repositories\AbstractRepository;

class RepositoryStub extends AbstractRepository
{
	public function _create($model)
	{
		return true;
	}

	public function _update($id, $model)
	{
		return true;
	}

	public function _delete($id)
	{
		return true;
	}

	public function _fetch($id, $include = [])
	{
		return true;
	}

	public function _get($filter = [], $offset = 0, $limit = 0, $sort = [], $include = [])
	{
		return true;
	}
}
