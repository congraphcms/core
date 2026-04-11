<?php

use Congraph\Core\Repositories\DataTransferObject;

class FakeDataTransferObject extends DataTransferObject
{
	protected string $type = 'article';

	public function __construct(string $type = 'article', array $params = [], mixed $data = null)
	{
		$this->type = $type;
		$this->data = $data;
		$this->params = $params;
		$this->meta = [];
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getType()
	{
		return $this->type;
	}
}
