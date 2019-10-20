<?php

namespace FriendsOfTwig\Twigcs\Tests;

use FriendsOfTwig\Twigcs\Console\LintCommand;
use FriendsOfTwig\Twigcs\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CorpusTest extends TestCase
{
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
            'paths' => ['tests/data/valid_corpus'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertContains('No violation found.', $output);
    }
}
