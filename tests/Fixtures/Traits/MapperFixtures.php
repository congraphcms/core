<?php

use Congraph\Core\Bus\Command;
use Congraph\Core\Traits\MapperTrait;
use Illuminate\Container\Container;

class MapperTraitHarness
{
	use MapperTrait;

	public Container $container;

	public function __construct(?Container $container = null)
	{
		$this->container = $container ?? new Container();
	}
}

class MapperFixtureResource
{
}

class MapperFixtureResolver
{
	public function handle(string $value): string
	{
		return 'handled:' . $value;
	}
}

class MapperFixtureInvalidResolver
{
}

class MapperFixtureCommand extends Command
{
	public function __construct(public string $value)
	{
	}
}
