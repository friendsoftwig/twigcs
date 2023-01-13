<?php

namespace FriendsOfTwig\Twigcs\Tests\Functional\Console;

use FriendsOfTwig\Twigcs\Config\ConfigInterface;
use FriendsOfTwig\Twigcs\Console\LintCommand;
use FriendsOfTwig\Twigcs\Container;
use FriendsOfTwig\Twigcs\TwigPort\SyntaxError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
final class LintCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/exclusion/good'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertStringContainsString('No violation found.', $output);
    }

    public function testMultipleBasePaths(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/basepaths/a', 'tests/Fixture/basepaths/b'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringStartsWith('tests/Fixture/basepaths/a/bad.html.twig', $output);
        $this->assertStringContainsString("\ntests/Fixture/basepaths/b/bad.html.twig", $output);
    }

    public function testExecuteWithError(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/exclusion'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('ERROR', $output);
    }

    public function testExecuteWithIgnoredErrors(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            '--severity' => 'ignore',
            'paths' => ['tests/Fixture/exclusion'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertStringContainsString('ERROR', $output);
    }

    public function testExecuteWithIgnoredWarnings(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            '--severity' => 'error',
            'paths' => ['tests/Fixture/exclusion/bad/warning.html.twig'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertStringContainsString('WARNING', $output);

        $commandTester->execute([
            '--severity' => 'error',
            'paths' => ['tests/Fixture/exclusion/bad'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('WARNING', $output);
    }

    public function testExecuteWithExclude(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/exclusion'],
            '--exclude' => ['bad'],
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 0);
        $this->assertStringContainsString('No violation found.', $output);
    }

    public function testErrorsOnlyDisplayBlocking(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/exclusion/bad/mixed.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_BLOCKING,
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringNotContainsString('l.1 c.7 : WARNING Unused variable "foo".', $output);
        $this->assertStringContainsString('l.2 c.2 : ERROR A print statement should start with 1 space.', $output);
        $this->assertStringContainsString('l.2 c.13 : ERROR There should be 0 space between the closing parenthese and its content.', $output);
        $this->assertStringContainsString('2 violation(s) found', $output);
    }

    public function testErrorsDisplayAll(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/exclusion/bad/mixed.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('l.1 c.7 : WARNING Unused variable "foo".', $output);
        $this->assertStringContainsString('l.2 c.2 : ERROR A print statement should start with 1 space.', $output);
        $this->assertStringContainsString('l.2 c.13 : ERROR There should be 0 space between the closing parenthese and its content.', $output);
        $this->assertStringContainsString('3 violation(s) found', $output);
    }

    public function testSyntaxErrorThrow(): void
    {
        $commandTester = self::commandTester();

        $this->expectException(SyntaxError::class);

        $commandTester->execute([
            'paths' => ['tests/Fixture/syntax_error/syntax_errors.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
            '--throw-syntax-error' => true,
        ]);

        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
    }

    public function testSyntaxErrorNotThrow(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/syntax_error/syntax_errors.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
            '--throw-syntax-error' => false,
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('1 violation(s) found', $output);
        $this->assertStringContainsString('l.1 c.17 : ERROR Unexpected "}"', $output);
    }

    public function testSyntaxErrorNotThrowOmitArgument(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/syntax_error/syntax_errors.html.twig'],
            '--severity' => 'error',
            '--display' => ConfigInterface::DISPLAY_ALL,
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('1 violation(s) found', $output);
        $this->assertStringContainsString('l.1 c.17 : ERROR Unexpected "}"', $output);
    }

    public function testConfigFileWithoutCliPath(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => null,
            '--config' => 'tests/Fixture/config/external/.twig_cs.dist.php',
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/Fixture/basepaths/a/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
tests/Fixture/basepaths/b/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
2 violation(s) found', $output);
    }

    public function testConfigFileWithCliPath(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => ['tests/Fixture/syntax_error'],
            '--config' => 'tests/Fixture/config/external/.twig_cs.dist.php',
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/Fixture/basepaths/a/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
tests/Fixture/basepaths/b/bad.html.twig
l.1 c.8 : WARNING Unused variable "foo".
tests/Fixture/syntax_error/syntax_errors.html.twig
l.1 c.17 : ERROR Unexpected "}".
3 violation(s) found', $output);
    }

    public function testConfigFileWithDisplayAndSeverity(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            '--config' => 'tests/Fixture/config/external/.twig_cs_with_display_blocking.dist.php',
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/Fixture/syntax_error/syntax_errors.html.twig
l.1 c.17 : ERROR Unexpected "}".
1 violation(s) found', $output);
    }

    public function testConfigFileSamePathWithRulesetOverrides(): void
    {
        chdir(__DIR__.'/../../Fixture/config/local');

        $commandTester = self::commandTester();

        $commandTester->execute([
            'paths' => null,
        ]);
        chdir(__DIR__.'/../../..');

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
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

    public function testUnusedWithFileLoader(): void
    {
        $commandTester = self::commandTester();

        $commandTester->execute([
            '--config' => 'tests/Fixture/config/loaders/.twig_cs.dist.php',
        ]);

        $output = $commandTester->getDisplay();
        $statusCode = $commandTester->getStatusCode();
        $this->assertSame($statusCode, 1);
        $this->assertStringContainsString('tests/Fixture/config/loaders/src/embed/child.html.twig
l.3 c.7 : WARNING Unused variable "unused_child".
tests/Fixture/config/loaders/src/embed/parent.html.twig
l.2 c.7 : WARNING Unused variable "unused_parent".
tests/Fixture/config/loaders/src/extends/child.html.twig
l.5 c.7 : WARNING Unused variable "unused_child".
tests/Fixture/config/loaders/src/extends/parent.html.twig
l.7 c.7 : WARNING Unused variable "unused_parent".
tests/Fixture/config/loaders/src/include/child.html.twig
l.3 c.7 : WARNING Unused variable "unused_child".
tests/Fixture/config/loaders/src/include/parent.html.twig
l.2 c.7 : WARNING Unused variable "unused_parent".
6 violation(s) found', $output);
    }

    private static function commandTester(): CommandTester
    {
        $container = new Container();
        $command = new LintCommand();
        $command->setContainer($container);

        return new CommandTester($command);
    }
}
