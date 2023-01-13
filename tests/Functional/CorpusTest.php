<?php

namespace FriendsOfTwig\Twigcs\Tests\Functional;

use FriendsOfTwig\Twigcs\Console\LintCommand;
use FriendsOfTwig\Twigcs\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
final class CorpusTest extends TestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $container = new Container();
        $command = new LintCommand();
        $command->setContainer($container);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->commandTester->execute([
            'paths' => ['tests/Fixture/valid_corpus'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertStringContainsString('No violation found.', $output);
    }
}
