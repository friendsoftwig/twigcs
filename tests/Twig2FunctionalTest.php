<?php

namespace FriendsOfTwig\Twigcs\Tests;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\Rule\RegEngineRule;
use FriendsOfTwig\Twigcs\Ruleset\Official;
use FriendsOfTwig\Twigcs\TwigPort\Source;
use FriendsOfTwig\Twigcs\Validator\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
final class Twig2FunctionalTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testExpressions($expression, $expectedViolation, array $expectedViolationPosition = null): void
    {
        $lexer = new Lexer();
        $validator = new Validator();

        $violations = $validator->validate(new Official(2), $lexer->tokenize(new Source($expression, 'src', 'src.html.twig')));
        $this->assertCount(0, $validator->getCollectedData()[RegEngineRule::class]['unrecognized_expressions'] ?? []);

        if ($expectedViolation) {
            $this->assertCount(1, $violations, sprintf("There should be one violation in:\n %s", $expression));
            $this->assertSame($expectedViolation, $violations[0]->getReason());
            if ($expectedViolationPosition) {
                $this->assertSame($expectedViolationPosition[0], $violations[0]->getColumn());
                $this->assertSame($expectedViolationPosition[1], $violations[0]->getLine());
            }
        } else {
            $this->assertCount(0, $violations, sprintf("There should be no violations in:\n %s", $expression));
        }
    }

    public function getData()
    {
        return [
            ['{% for i in (some_array) if  foo %}{{ i }}', 'There should be 1 space between the if and its expression.'],
            ['{% for i in (some_array)  if foo %}{{ i }}', 'There should be 1 space before the if part of the loop.'],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/56
            ["{% for item in ['one', 'two'] if attribute(_context, item) is not empty %}\n{% endfor %}", null],

            // Regressions from the Prestashop corpus
            ['{% for module in hook[\'modules\'] if modules[module[\'id_module\']] is defined %}', null],
        ];
    }
}
