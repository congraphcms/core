<?php

require_once __DIR__ . '/../Fixtures/Bus/DispatcherFixtures.php';

use Congraph\Core\Bus\CommandDispatcher;
use Illuminate\Contracts\Bus\Dispatcher as DispatcherContract;
use Illuminate\Support\Facades\Event;

final class DispatcherTest extends \Orchestra\Testbench\TestCase
{

	protected function getPackageProviders($app)
	{
		return [\Congraph\Core\CoreServiceProvider::class];
	}

	public function testLoadingOfDispatcher()
	{
		$bus = $this->app->make(CommandDispatcher::class);

		$this->assertInstanceOf(CommandDispatcher::class, $bus);
		$this->assertInstanceOf(DispatcherContract::class, $bus);
		$this->assertTrue(method_exists($bus, 'dispatchAfterResponse'));
	}

	public function testDispatchRunsMappedValidatorBeforeHandlingCommand(): void
	{
		$bus = $this->app->make(CommandDispatcher::class);
		$command = new CreateArticleCommand();

		$bus->mapValidators([
			CreateArticleCommand::class => CreateArticleValidator::class . '@validate',
		]);

		$result = $bus->dispatch($command);

		$this->assertTrue($command->validated);
		$this->assertSame('handled', $result);
	}

	public function testDispatchRunsSelfValidatingCommands(): void
	{
		$bus = $this->app->make(CommandDispatcher::class);
		PublishArticleCommand::$validated = false;
		$command = new PublishArticleCommand();

		$result = $bus->dispatch($command);

		$this->assertTrue(PublishArticleCommand::$validated);
		$this->assertSame('published', $result);
	}

	public function testFallbackValidatorMapperIsUsedWhenNoDirectMappingExists(): void
	{
		$bus = $this->app->make(CommandDispatcher::class);
		$command = new CreateArticleCommand();

		$bus->mapValidatorsUsing(function ($mappedCommand) {
			return CreateArticleValidator::class . '@validate';
		});

		$result = $bus->dispatch($command);

		$this->assertTrue($command->validated);
		$this->assertSame('handled', $result);
	}

	public function testDispatchFiresBeforeAndAfterEventsUsingNormalizedCommandName(): void
	{
		Event::fake();

		$bus = $this->app->make(CommandDispatcher::class);
		$command = new CreateArticleCommand();

		$bus->dispatch($command);

		Event::assertDispatched('cb.before.create.article');
		Event::assertDispatched('cb.after.create.article');
	}
}
