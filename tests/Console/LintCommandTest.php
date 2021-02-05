<?php

namespace FriendsOfTwig\Twigcs\Tests\Console;

use FriendsOfTwig\Twigcs\Config\ConfigInterface;
use FriendsOfTwig\Twigcs\Console\LintCommand;
use FriendsOfTwig\Twigcs\Container;
use FriendsOfTwig\Twigcs\TwigPort\SyntaxError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LintCommandTest extends TestCase
{
    /** @var CommandTester */
    private $commandTester;

    protected function setUp(): void
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
        $this->assertStringContainsString('No violation found.', $output);
    }

    public function testMultipleBasePaths()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/basepaths/a', 'tests/data/basepaths/b'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringStartsWith('tests/data/basepaths/a/bad.html.twig', $output);
        $this->assertStringContainsString("\ntests/data/basepaths/b/bad.html.twig", $output);
    }

    public function testExecuteWithError()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('ERROR', $output);
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
        $this->assertStringContainsString('ERROR', $output);
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
        $this->assertStringContainsString('WARNING', $output);

        $this->commandTester->execute([
            '--severity' => 'error',
            'paths' => ['tests/data/exclusion/bad'],
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('WARNING', $output);
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
        $this->assertStringContainsString('No violation found.', $output);
    }

    public function testErrorsOnlyDisplayBlocking()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion/bad/mixed.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_BLOCKING,
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringNotContainsString('l.1 c.7 : WARNING Unused variable "foo".', $output);
        $this->assertStringContainsString('l.2 c.2 : ERROR A print statement should start with 1 space.', $output);
        $this->assertStringContainsString('l.2 c.13 : ERROR There should be 0 space between the closing parenthese and its content.', $output);
        $this->assertStringContainsString('2 violation(s) found', $output);
    }

    public function testErrorsDisplayAll()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/exclusion/bad/mixed.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('l.1 c.7 : WARNING Unused variable "foo".', $output);
        $this->assertStringContainsString('l.2 c.2 : ERROR A print statement should start with 1 space.', $output);
        $this->assertStringContainsString('l.2 c.13 : ERROR There should be 0 space between the closing parenthese and its content.', $output);
        $this->assertStringContainsString('3 violation(s) found', $output);
    }

    public function testSyntaxErrorThrow()
    {
        $this->expectException(SyntaxError::class);
        $this->commandTester->execute([
            'paths' => ['tests/data/syntax_error/syntax_errors.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
            '--throw-syntax-error' => true,
        ]);

        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
    }

    public function testSyntaxErrorNotThrow()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/syntax_error/syntax_errors.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
            '--throw-syntax-error' => false,
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('1 violation(s) found', $output);
        $this->assertStringContainsString('l.1 c.17 : ERROR Unexpected "}"', $output);
    }

    public function testSyntaxErrorNotThrowOmitArgument()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/syntax_error/syntax_errors.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('1 violation(s) found', $output);
        $this->assertStringContainsString('l.1 c.17 : ERROR Unexpected "}"', $output);
    }

    public function testConfigFileWithoutCliPath()
    {
        $this->commandTester->execute([
            'paths' => null,
            '--config' => 'tests/data/config/external/.twig_cs.dist',
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/data/basepaths/a/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
tests/data/basepaths/b/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
2 violation(s) found', $output);
    }

    public function testConfigFileWithCliPath()
    {
        $this->commandTester->execute([
            'paths' => ['tests/data/syntax_error'],
            '--config' => 'tests/data/config/external/.twig_cs.dist',
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/data/basepaths/a/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
tests/data/basepaths/b/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
tests/data/syntax_error/syntax_errors.html.twig
l.1 c.17 : ERROR Unexpected "}".
3 violation(s) found', $output);
    }

    public function testConfigFileWithDisplayAndSeverity()
    {
        $this->commandTester->execute([
            '--config' => 'tests/data/config/external/.twig_cs_with_display_blocking.dist',
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/data/syntax_error/syntax_errors.html.twig
l.1 c.17 : ERROR Unexpected "}".
1 violation(s) found', $output);
    }

    public function testConfigFileSamePathWithRulesetOverrides()
    {
        chdir(__DIR__.'/../data/config/local');
        $this->commandTester->execute([
            'paths' => null,
        ]);
        chdir(__DIR__.'/../..');

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('{
    "failures": 1,
    "files": [
        {
            "file": "a.html.twig",
            "violations": [
                {
                    "line": 1,
                    "column": 8,
                    "severity": 2,
                    "type": "warning",
                    "message": "Unused variable \"foo\"."
                }
            ]
        }
    ]
}', $output);
    }

    public function testUnusedWithFileLoader()
    {
        $this->commandTester->execute([
            '--config' => 'tests/data/config/loaders/.twig_cs.dist',
        ]);

        $output = $this->commandTester->getDisplay();
        $statusCode = $this->commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/data/config/loaders/src/embed/child.html.twig
l.3 c.7 : WARNING Unused variable "unused_child".
tests/data/config/loaders/src/embed/parent.html.twig
l.2 c.7 : WARNING Unused variable "unused_parent".
tests/data/config/loaders/src/extends/child.html.twig
l.5 c.7 : WARNING Unused variable "unused_child".
tests/data/config/loaders/src/extends/parent.html.twig
l.7 c.7 : WARNING Unused variable "unused_parent".
tests/data/config/loaders/src/include/child.html.twig
l.3 c.7 : WARNING Unused variable "unused_child".
tests/data/config/loaders/src/include/parent.html.twig
l.2 c.7 : WARNING Unused variable "unused_parent".
6 violation(s) found', $output);
    }
}
