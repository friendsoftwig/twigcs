<?php

namespace FriendsOfTwig\Twigcs\Tests\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\ScopedExpression;
use PHPUnit\Framework\TestCase;

class ScopedExpressionTest extends TestCase
{
    public function testEnqueue()
    {
        $expr = new ScopedExpression();
        $expr->enqueueString('{% set a = func({a: ["b", "B"]}) + {a: do(c + (d - 1))} %}');
        $this->assertSame('{% set a = func[([{a: [["b", "B"]]}])] + [{a: do[(c + [(d - 1)])]}] %}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ a ? b : c }}');
        $this->assertSame('{{ a [? b :] c }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ {foo: a ? b, c}|sum }}');
        $this->assertSame('{{ [{foo: a ? b, c}]|sum }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ [a ? b] }}');
        $this->assertSame('{{ [[a ? b]] }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ (a ? b) }}');
        $this->assertSame('{{ [(a ? b)] }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ (a ? b) + (c ? d : 1) }}');
        $this->assertSame('{{ [(a ? b)] + [(c [? d :] 1)] }}', $expr->debug());

        $expr = new ScopedExpression();
        $expr->enqueueString('{{ (a ? foo()) }}');
        $this->assertSame('{{ [(a ? foo[()])] }}', $expr->debug());
    }
}
