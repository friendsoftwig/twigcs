<?php

declare(strict_types=1);

namespace FriendsOfTwig\Twigcs\Tests\Unit\Console;

use FriendsOfTwig\Twigcs\Console\Application;
use FriendsOfTwig\Twigcs\Console\ContainerAwareCommand;
use FriendsOfTwig\Twigcs\Console\LintCommand;
use FriendsOfTwig\Twigcs\Console\RegDebugCommand;
use FriendsOfTwig\Twigcs\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 *
 * @covers \FriendsOfTwig\Twigcs\Console\Application
 */
final class ApplicationTest extends TestCase
{
    public function testAddCommandWithRegularCommand(): void
    {
        $application = new Application(false);
        $command = new Command('test:command');

        $result = $application->addCommand($command);

        self::assertSame($command, $result);
        self::assertTrue($application->has('test:command'));
    }

    public function testAddCommandWithContainerAwareCommand(): void
    {
        $application = new Application(false);
        $command = new RegDebugCommand();

        $result = $application->addCommand($command);

        self::assertSame($command, $result);
        self::assertNotNull($command->getContainer());
        self::assertTrue($application->has('reg:debug'));
    }

    public function testAddCommandWithLintCommand(): void
    {
        $application = new Application(false);
        $command = new LintCommand();

        $result = $application->addCommand($command);

        self::assertSame($command, $result);
        self::assertNotNull($command->getContainer());
        self::assertTrue($application->has('lint'));
    }

    public function testAddCommandReplacesExistingCommandWithSameName(): void
    {
        $application = new Application(false);
        $command1 = new Command('test:command');
        $command2 = new Command('test:command');

        $result1 = $application->addCommand($command1);
        $result2 = $application->addCommand($command2);

        // The first command should be returned
        self::assertSame($command1, $result1);
        // The second command should replace the first one
        self::assertSame($command2, $result2);
        // The application should have the second command registered
        self::assertSame($command2, $application->get('test:command'));
    }

    public function testAddCommandSetsContainerForContainerAwareCommand(): void
    {
        $application = new Application(false);
        $command = new RegDebugCommand();

        // Before adding, container should be null
        self::assertNull($command->getContainer());

        $application->addCommand($command);

        // After adding, container should be set
        self::assertNotNull($command->getContainer());
    }

    public function testAddCommandDoesNotSetContainerForRegularCommand(): void
    {
        $application = new Application(false);
        $command = new Command('regular:command');

        // Regular commands don't have getContainer method, so we just verify it doesn't crash
        $result = $application->addCommand($command);

        self::assertSame($command, $result);
        self::assertTrue($application->has('regular:command'));
    }

    public function testAddCommandWithSymfony80Plus(): void
    {
        // Skip this test if we're not on Symfony 8.0+
        if (!method_exists(BaseApplication::class, 'addCommand')) {
            self::markTestSkipped('This test requires Symfony Console 8.0+');
        }

        $application = new Application(false);
        $command = new Command('test:command');

        $result = $application->addCommand($command);

        self::assertSame($command, $result);
        self::assertTrue($application->has('test:command'));

        // Test that ContainerAwareCommand gets its container set
        $containerAwareCommand = new RegDebugCommand();
        self::assertNull($containerAwareCommand->getContainer());

        $application->addCommand($containerAwareCommand);
        self::assertNotNull($containerAwareCommand->getContainer());
    }

    public function testAddCommandFallsBackToAddWithSymfonyBefore80(): void
    {
        // Skip this test if we're on Symfony 8.0+
        if (method_exists(BaseApplication::class, 'addCommand')) {
            self::markTestSkipped('This test requires Symfony Console < 8.0');
        }

        $application = new Application(false);
        $command = new Command('test:command');

        $result = $application->addCommand($command);

        self::assertSame($command, $result);
        self::assertTrue($application->has('test:command'));

        // Test that ContainerAwareCommand gets its container set
        $containerAwareCommand = new RegDebugCommand();
        self::assertNull($containerAwareCommand->getContainer());

        $application->addCommand($containerAwareCommand);
        self::assertNotNull($containerAwareCommand->getContainer());

        // Test that callable throws exception on old versions
        $callable = function () {
            return new Command('callable:command');
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Command must be an instance of');
        $application->addCommand($callable);
    }
}
