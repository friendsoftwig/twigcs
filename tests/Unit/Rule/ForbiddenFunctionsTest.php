<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\Rule;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\Rule\ForbiddenFunctions;
use FriendsOfTwig\Twigcs\TwigPort\Source;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;
use FriendsOfTwig\Twigcs\Validator\Violation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ForbiddenFunctionsTest extends TestCase
{
    public function testCheckWithoutFunctions(): void
    {
        $rule = new ForbiddenFunctions(Violation::SEVERITY_WARNING);
        $violations = $rule->check(self::tokenStream());

        $this->assertCount(0, $violations);
    }

    public function testCheckWithFunctions(): void
    {
        $rule = (new ForbiddenFunctions(Violation::SEVERITY_WARNING))->setFunctions(['dump']);
        $violations = $rule->check(self::tokenStream());

        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertSame(1, $violation->getLine());
        $this->assertSame(3, $violation->getColumn());
        $this->assertSame('The function "dump" is forbidden.', $violation->getReason());
        $this->assertSame('my/path/file.html.twig', $violation->getFilename());
        $this->assertSame(Violation::SEVERITY_WARNING, $violation->getSeverity());
        $this->assertSame('FriendsOfTwig\Twigcs\Rule\ForbiddenFunctions', $violation->getSource());
    }

    public function testCheckWithFunctionsNoEquals(): void
    {
        $rule = (new ForbiddenFunctions(Violation::SEVERITY_WARNING))->setFunctions(['dum']);
        $violations = $rule->check(self::tokenStream());

        $this->assertCount(0, $violations);
    }

    private static function tokenStream(): TokenStream
    {
        $lexer = new Lexer();
        $source = new Source(
            '{{ dump(test) }}{{ dump.test }}',
            'my/path/file.html.twig',
            ltrim(str_replace(getcwd(), '', 'my/path/file.html.twig'), '/')
        );

        return $lexer->tokenize($source);
    }
}
