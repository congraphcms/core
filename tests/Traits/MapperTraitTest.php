<?php

require_once __DIR__ . '/../Fixtures/Traits/MapperFixtures.php';

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Bus;

final class MapperTraitTest extends \Orchestra\Testbench\TestCase
{
	private MapperTraitHarness $harness;

	protected function setUp(): void
	{
		parent::setUp();

		$this->resetMapperState();

		$container = new Container();
		$container->instance(MapperFixtureResolver::class, new MapperFixtureResolver());
		$this->harness = new MapperTraitHarness($container);
	}

	protected function tearDown(): void
	{
		$this->resetMapperState();

		parent::tearDown();
	}

	public function testResolveMappingUsesClosureResolvers(): void
	{
		MapperTraitHarness::maps([
			'resource' => function (string $value): string {
				return 'closure:' . $value;
			},
		]);

		$result = $this->harness->resolveMapping('resource', ['value']);

		$this->assertSame('closure:value', $result);
	}

	public function testResolveMappingUsesMappedClassMethod(): void
	{
		MapperTraitHarness::maps([
			'resource' => MapperFixtureResolver::class . '@handle',
		]);

		$result = $this->harness->resolveMapping('resource', ['value']);

		$this->assertSame('handled:value', $result);
	}

	public function testResolveMappingSupportsFallbackMapperCallbacks(): void
	{
		MapperTraitHarness::mapUsing(function (string $resource): array {
			return [MapperFixtureResolver::class . '@handle'];
		});

		$result = $this->harness->resolveMapping('resource', ['value']);

		$this->assertSame('handled:value', $result);
	}

	public function testResolveMappingAcceptsObjectsWhenLookingUpMappings(): void
	{
		MapperTraitHarness::maps([
			MapperFixtureResource::class => MapperFixtureResolver::class . '@handle',
		]);

		$result = $this->harness->resolveMapping(new MapperFixtureResource(), ['value']);

		$this->assertSame('handled:value', $result);
	}

	public function testRunResolverDispatchesCommandInstancesThroughBus(): void
	{
		$this->app->instance(MapperFixtureCommand::class, new MapperFixtureCommand('value'));
		$this->harness->container = $this->app;

		Bus::fake();

		$this->harness->runResolver(MapperFixtureCommand::class, ['value']);

		Bus::assertDispatched(MapperFixtureCommand::class, function (MapperFixtureCommand $command) {
			return $command->value === 'value';
		});
	}

	public function testResolveMappingThrowsForMissingMappings(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No resolvers mapped for resource: missing.');

		$this->harness->resolveMapping('missing');
	}

	public function testRunResolverThrowsForInvalidMethodMappings(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Invalid method mapped on object: ' . MapperFixtureInvalidResolver::class . '.');

		$this->harness->runResolver(MapperFixtureInvalidResolver::class . '@handle', ['value']);
	}

	public function testRunResolverThrowsForInvalidResolvers(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Invalid resolver: definitely-not-a-resolver.');

		$this->harness->runResolver('definitely-not-a-resolver');
	}

	private function resetMapperState(): void
	{
		$reflection = new \ReflectionClass(MapperTraitHarness::class);

		foreach (['mappings', 'mappers', 'defaultResolveMethods'] as $propertyName) {
			$property = $reflection->getProperty($propertyName);
			$property->setValue(null, []);
		}
	}
}
