<?php

require_once __DIR__ . '/../Fixtures/Repositories/FakeDataTransferObject.php';

use Congraph\Core\Repositories\TrunkCache;

final class TrunkCacheTest extends \PHPUnit\Framework\TestCase
{
	private TrunkCache $cache;

	protected function setUp(): void
	{
		$this->cache = new TrunkCache();
	}

	public function testPutRejectsInvalidObjects(): void
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('You are trying to put invalid object to trunk.');

		$this->cache->put(new \stdClass());
	}

	public function testPutAndGetWorkWithIntegerLookupKeys(): void
	{
		$dto = new FakeDataTransferObject('article', [10]);

		$this->cache->put($dto);

		$this->assertTrue($this->cache->has(10, 'article'));
		$this->assertSame($dto, $this->cache->get(10, 'article'));
	}

	public function testPutAndGetWorkWithArrayLookupKeys(): void
	{
		$dto = new FakeDataTransferObject('article', ['status' => 'published']);

		$this->cache->put($dto);

		$this->assertTrue($this->cache->has(['status' => 'published'], 'article'));
		$this->assertSame($dto, $this->cache->get(['status' => 'published'], 'article'));
	}

	public function testPutAndGetWorkWithEncodedStringKeys(): void
	{
		$params = ['status' => 'published'];
		$key = base64_encode(json_encode($params));
		$dto = new FakeDataTransferObject('article', $params);

		$this->cache->put($dto);

		$this->assertTrue($this->cache->has($key, 'article'));
		$this->assertSame($dto, $this->cache->get($key, 'article'));
	}

	public function testForgetRemovesSingleItems(): void
	{
		$dto = new FakeDataTransferObject('article', [10]);

		$this->cache->put($dto);
		$this->cache->forget(10, 'article');

		$this->assertFalse($this->cache->has(10, 'article'));
		$this->assertNull($this->cache->get(10, 'article'));
	}

	public function testForgetTypeRemovesAllItemsForAType(): void
	{
		$article = new FakeDataTransferObject('article', [10]);
		$page = new FakeDataTransferObject('page', [20]);

		$this->cache->put($article);
		$this->cache->put($page);
		$this->cache->forgetType('article');

		$this->assertFalse($this->cache->has(10, 'article'));
		$this->assertTrue($this->cache->has(20, 'page'));
	}

	public function testForgetAllClearsEveryCachedItem(): void
	{
		$this->cache->put(new FakeDataTransferObject('article', [10]));
		$this->cache->put(new FakeDataTransferObject('page', [20]));

		$this->cache->forgetAll();

		$this->assertFalse($this->cache->has(10, 'article'));
		$this->assertFalse($this->cache->has(20, 'page'));
	}
}
