<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\ScopedExpression;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ScopedExpressionTest extends TestCase
{
    public function testEnqueue(): void
    {
        $expr = new ScopedExpression();
        $expr->enqueueString('{% set a = func({a: ["b", "B"]}) + {a: do(c + (d - 1))} %}');

        self::assertSame('{% set a = func[([{a: [["b", "B"]]}])] + [{a: do[(c + [(d - 1)])]}] %}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ a ? b : c }}');

        self::assertSame('{{ a [? b :] c }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ {foo: a ? b, c}|sum }}');

        self::assertSame('{{ [{foo: a ? b, c}]|sum }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ [a ? b] }}');

        self::assertSame('{{ [[a ? b]] }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ (a ? b) }}');

        self::assertSame('{{ [(a ? b)] }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ (a ? b) + (c ? d : 1) }}');

        self::assertSame('{{ [(a ? b)] + [(c [? d :] 1)] }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ (a ? foo()) }}');

        self::assertSame('{{ [(a ? foo[()])] }}', $expr->debug());
    }
}
