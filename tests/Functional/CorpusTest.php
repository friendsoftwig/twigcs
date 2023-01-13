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
    public function testExecute(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => [
                'tests/Fixture/valid_corpus',
            ],
        ]);

        $output = $commandTester->getDisplay();

        self::assertSame(0, $commandTester->getStatusCode());
        self::assertStringContainsString('No violation found.', $output);
    }

    private static function commandTester(): CommandTester
    {
        $container = new Container();
        $command = new LintCommand();
        $command->setContainer($container);

        return new CommandTester($command);
    }
}
