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

    public function testExecuteWithIgnoredErrors()
    {
        $this->commandTester->execute([
            '--severity' => 'ignore',
            'paths' => ['tests/data/exclusion'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertContains('ERROR', $output);
    }

    public function testExecuteWithIgnoredWarnings()
    {
        $this->commandTester->execute([
            '--severity' => 'error',
            'paths' => ['tests/data/exclusion/bad/warning.html.twig'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertContains('WARNING', $output);

        $this->commandTester->execute([
            '--severity' => 'error',
            'paths' => ['tests/data/exclusion/bad'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertContains('WARNING', $output);
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

    public function testErrorsOnlyDisplayBlocking()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion/bad/mixed.html.twig'],
            '--severity' => 'error',
            '--display' => LintCommand::DISPLAY_BLOCKING,
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertNotContains('l.1 c.7 : WARNING Unused variable "foo".', $output);
        $this->assertContains('l.2 c.2 : ERROR A print statement should start with 1 space.', $output);
        $this->assertContains('l.2 c.13 : ERROR There should be 0 space between the closing parenthese and its content.', $output);
        $this->assertContains('2 violation(s) found', $output);
    }

    public function testErrorsDisplayAll()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion/bad/mixed.html.twig'],
            '--severity' => 'error',
            '--display' => LintCommand::DISPLAY_ALL,
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertContains('l.1 c.7 : WARNING Unused variable "foo".', $output);
        $this->assertContains('l.2 c.2 : ERROR A print statement should start with 1 space.', $output);
        $this->assertContains('l.2 c.13 : ERROR There should be 0 space between the closing parenthese and its content.', $output);
        $this->assertContains('3 violation(s) found', $output);
    }
}
