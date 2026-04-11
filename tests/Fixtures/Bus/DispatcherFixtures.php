<?php

use Congraph\Core\Bus\SelfValidating;

class CreateArticleCommand
{
	public bool $validated = false;

	public function handle(): string
	{
		return 'handled';
	}
}

class CreateArticleValidator
{
	public function validate(CreateArticleCommand $command): void
	{
		$command->validated = true;
	}
}

class PublishArticleCommand implements SelfValidating
{
	public static bool $validated = false;

	public static function validate(): void
	{
		self::$validated = true;
	}

	public function handle(): string
	{
		return 'published';
	}
}
