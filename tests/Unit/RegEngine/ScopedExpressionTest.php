<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\ScopedExpression;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ScopedExpressionTest extends TestCase
{
    /**
     * @dataProvider provideStringAndDebug
     */
    public function testEnqueue(
        string $string,
        string $debug,
    ): void {
        $scopedExpression = new ScopedExpression();

        $scopedExpression->enqueueString($string);

        self::assertSame($debug, $scopedExpression->debug());
    }

    /**
     * @return array<string, array<{0: string, 1: string}>
     */
    public function provideStringAndDebug(): array
    {
        return [
            [
                '{% set a = func({a: ["b", "B"]}) + {a: do(c + (d - 1))} %}',
                '{% set a = func[([{a: [["b", "B"]]}])] + [{a: do[(c + [(d - 1)])]}] %}',
            ],
            [
                '{{ a ? b : c }}',
                '{{ a [? b :] c }}',
            ],
            [
                '{{ {foo: a ? b, c}|sum }}',
                '{{ [{foo: a ? b, c}]|sum }}',
            ],
            [
                '{{ [a ? b] }}',
                '{{ [[a ? b]] }}',
            ],
            [
                '{{ (a ? b) }}',
                '{{ [(a ? b)] }}',
            ],
            [
                '{{ (a ? b) + (c ? d : 1) }}',
                '{{ [(a ? b)] + [(c [? d :] 1)] }}',
            ],
            [
                '{{ (a ? foo()) }}',
                '{{ [(a ? foo[()])] }}',
            ],
        ];
    }
}
