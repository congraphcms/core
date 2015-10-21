<?php

class TrunkTest extends PHPUnit_Framework_TestCase
{

	public function testPut()
	{
		$trunk = new Cookbook\Core\Repositories\Trunk();

		$post = new stdClass();
		$post->id = 1;
		$post->type = 'post';
		$post->title = 'Post Title';
		$post->body = 'Post body...';

		$trunk->put($post, true);

		$post2 = new stdClass();
		$post2->id = 2;
		$post2->type = 'post';
		$post2->title = 'Post Title 2';
		$post2->body = 'Post body second time...';
		$post2->parent = new stdClass();
		$post2->parent->id = 1;
		$post2->parent->type = 'post';

		$collection = [$post2];

		$trunk->put($collection, true);

		$result = $trunk->get(2, 'post');

		var_dump($trunk->includes(1, 'post'));

		var_dump($result->toArray(true, true));
	}
}
?>