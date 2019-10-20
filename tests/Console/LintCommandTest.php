<?php

namespace FriendsOfTwig\Twigcs\Tests\Console;

use FriendsOfTwig\Twigcs\Console\LintCommand;
use FriendsOfTwig\Twigcs\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LintCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    public function setUp()
    {
        $container = new Container();
        $command = new LintCommand();
        $command->setContainer($container);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecute()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion/good'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertContains('No violation found.', $output);
    }

    public function testExecuteWithError()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertContains('ERROR', $output);
    }

    public function testExecuteWithExclude()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion'],
            '--exclude' => ['bad'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertContains('No violation found.', $output);
    }
}
