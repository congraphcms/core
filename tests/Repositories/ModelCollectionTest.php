<?php

require_once __DIR__ . '/../Fixtures/Repositories/GuardedModel.php';

use Congraph\Core\Facades\Trunk;
use Congraph\Core\Repositories\Collection;
use Congraph\Core\Repositories\Model;

final class ModelCollectionTest extends \Orchestra\Testbench\TestCase
{
	protected function getPackageProviders($app)
	{
		return [\Congraph\Core\CoreServiceProvider::class];
	}

	public function testModelExposesBasicIdentityAndAttributes(): void
	{
		$model = new Model([
			'id' => 10,
			'type' => 'article',
			'title' => 'Hello',
		]);

		$this->assertSame(10, $model->getId());
		$this->assertSame('article', $model->getType());
		$this->assertSame('Hello', $model->title);
		$this->assertTrue(isset($model->title));
	}

	public function testModelResolvesUnresolvedRelationsFromTrunk(): void
	{
		$author = new Model([
			'id' => 5,
			'type' => 'user',
			'name' => 'Nikola',
		]);
		$author->setParams([5]);
		Trunk::put($author);

		$article = new Model([
			'id' => 10,
			'type' => 'article',
			'author' => (object) ['id' => 5, 'type' => 'user'],
		]);

		$this->assertSame($author, $article->author);
		$this->assertSame('Nikola', $article->author->name);
	}

	public function testModelCanStoreModelAndCollectionAsUnresolvedReferences(): void
	{
		$author = new Model([
			'id' => 5,
			'type' => 'user',
			'name' => 'Nikola',
		]);
		$categoryA = new Model(['id' => 1, 'type' => 'category', 'title' => 'News']);
		$categoryB = new Model(['id' => 2, 'type' => 'category', 'title' => 'Tech']);
		$categories = new Collection([$categoryA, $categoryB]);

		$article = new Model([
			'id' => 10,
			'type' => 'article',
		]);

		$article->author = $author;
		$article->categories = $categories;

		$rawData = $article->getData();

		$this->assertSame(5, $rawData->author->id);
		$this->assertSame('user', $rawData->author->type);
		$this->assertCount(2, $rawData->categories);
		$this->assertSame(1, $rawData->categories[0]->id);
		$this->assertSame('category', $rawData->categories[0]->type);
	}

	public function testModelSupportsGuardedProperties(): void
	{
		$model = new GuardedModel([
			'id' => 10,
			'type' => 'article',
			'secret' => 'top-secret',
			'title' => 'Visible',
		]);

		$this->assertSame('top-secret', $model->getGuarded('secret'));
		$this->assertSame('Visible', $model->title);
		$this->assertFalse(isset($model->secret));
	}

	public function testModelUnsetRemovesAttributesAndUndefinedPropertyThrows(): void
	{
		$model = new Model([
			'id' => 10,
			'type' => 'article',
			'title' => 'Visible',
		]);

		unset($model->title);

		$this->assertFalse(isset($model->title));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Undefined property: Congraph\Core\Repositories\Model::$missing');

		$model->missing;
	}

	public function testCollectionWrapsRawItemsAndTracksType(): void
	{
		$collection = new Collection([
			['id' => 1, 'type' => 'article', 'title' => 'One'],
			['id' => 2, 'type' => 'article', 'title' => 'Two'],
		]);

		$this->assertCount(2, $collection);
		$this->assertSame('article', $collection->getType());
		$this->assertInstanceOf(Model::class, $collection[0]);
		$this->assertSame('One', $collection[0]->title);
	}

	public function testCollectionAddItemAndAddItemsNormalizeModels(): void
	{
		$collection = new Collection([]);

		$collection->addItem(['id' => 1, 'type' => 'article', 'title' => 'One']);
		$collection->addItems([
			['id' => 2, 'type' => 'article', 'title' => 'Two'],
		]);

		$this->assertCount(2, $collection);
		$this->assertInstanceOf(Model::class, $collection[0]);
		$this->assertInstanceOf(Model::class, $collection[1]);
	}

	public function testCollectionSupportsIteration(): void
	{
		$collection = new Collection([
			['id' => 1, 'type' => 'article', 'title' => 'One'],
			['id' => 2, 'type' => 'article', 'title' => 'Two'],
		]);

		$titles = [];

		foreach ($collection as $item) {
			$titles[] = $item->title;
		}

		$this->assertSame(['One', 'Two'], $titles);
	}

	public function testCollectionCanUseCustomDefaultModel(): void
	{
		$collection = new Collection([], GuardedModel::class);
		$collection->addItem([
			'id' => 10,
			'type' => 'article',
			'secret' => 'top-secret',
			'title' => 'Visible',
		]);

		$this->assertInstanceOf(GuardedModel::class, $collection[0]);
		$this->assertSame('top-secret', $collection[0]->getGuarded('secret'));
	}

	public function testCollectionRejectsInvalidDefaultModel(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Collection default model must be instance of Congraph\Core\Repositories\Model');

		new Collection([], \stdClass::class);
	}
}
