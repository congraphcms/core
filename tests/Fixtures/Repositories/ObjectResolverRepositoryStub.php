<?php

class ObjectResolverRepositoryStub
{
	public array $calls = [];

	public function fetch(...$arguments)
	{
		$this->calls[] = ['fetch', $arguments];

		return ['method' => 'fetch', 'arguments' => $arguments];
	}

	public function get(...$arguments)
	{
		$this->calls[] = ['get', $arguments];

		return ['method' => 'get', 'arguments' => $arguments];
	}
}
