<?php

require_once __DIR__ . '/../Fixtures/Repositories/ObjectResolverRepositoryStub.php';

use Congraph\Core\Repositories\ObjectResolver;
use Illuminate\Container\Container;

final class ObjectResolverTest extends \PHPUnit\Framework\TestCase
{
	private Container $container;

	private ObjectResolverRepositoryStub $repository;

	private ObjectResolver $resolver;

	protected function setUp(): void
	{
		$this->resetMappings();

		$this->container = new Container();
		$this->repository = new ObjectResolverRepositoryStub();
		$this->container->instance(ObjectResolverRepositoryStub::class, $this->repository);
		$this->resolver = new ObjectResolver($this->container);
	}

	protected function tearDown(): void
	{
		$this->resetMappings();
	}

	public function testResolveUsesFetchForSingleIdentifier(): void
	{
		ObjectResolver::maps([
			'article' => ObjectResolverRepositoryStub::class,
		]);

		$result = $this->resolver->resolve('article', 5, ['children'], 'en_US', 'published');

		$this->assertSame('fetch', $result['method']);
		$this->assertSame([5, ['children'], 'en_US', 'published'], $result['arguments']);
	}

	public function testResolveUsesGetForMultipleIdentifiers(): void
	{
		ObjectResolver::maps([
			'article' => ObjectResolverRepositoryStub::class,
		]);

		$result = $this->resolver->resolve('article', [5, 8], ['children'], 'en_US', 'published');

		$this->assertSame('get', $result['method']);
		$this->assertSame([
			['id' => ['in' => [5, 8]]],
			0,
			0,
			[],
			['children'],
			'en_US',
			'published',
		], $result['arguments']);
	}

	public function testResolveWithParamsPassesGetArgumentsThrough(): void
	{
		ObjectResolver::maps([
			'article' => ObjectResolverRepositoryStub::class,
		]);

		$result = $this->resolver->resolveWithParams(
			'article',
			['status' => 'published'],
			10,
			25,
			['created_at' => 'desc'],
			['children'],
			'en_US',
			'published'
		);

		$this->assertSame('get', $result['method']);
		$this->assertSame([
			['status' => 'published'],
			10,
			25,
			['created_at' => 'desc'],
			['children'],
			'en_US',
			'published',
		], $result['arguments']);
	}

	public function testResolveThrowsWhenNoMappingExists(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No resolvers mapped for resource: missing.');

		$this->resolver->resolve('missing', 1);
	}

	private function resetMappings(): void
	{
		$reflection = new \ReflectionClass(ObjectResolver::class);

		foreach (['mappings', 'mappers', 'defaultResolveMethods'] as $propertyName) {
			$property = $reflection->getProperty($propertyName);
			$property->setValue(null, []);
		}
	}
}
