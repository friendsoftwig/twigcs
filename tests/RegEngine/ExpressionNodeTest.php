<?php

namespace FriendsOfTwig\Twigcs\Tests\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\ExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ExpressionNodeTest extends TestCase
{
    public function testOffsetMapping()
    {
        $expr = ExpressionNode::fromString('{% func({a: ["b", "B"]}) + {a: do(c + (d - 1))} %}');
        $this->assertSame(0, $expr->getOffsetAt(0));
        $this->assertSame(1, $expr->getOffsetAt(1));
        $this->assertSame(2, $expr->getOffsetAt(2));
        $this->assertSame(7, $expr->getOffsetAt(7));
        $this->assertSame(7, $expr->getOffsetAt(8));
        $this->assertSame(7, $expr->getOffsetAt(21));
        $this->assertSame(24, $expr->getOffsetAt(22));
        $this->assertSame(49, $expr->getOffsetAt(35));
        $this->assertNull($expr->getOffsetAt(36));

        $this->assertSame(7, $expr->getChildren()[0]->getOffsetAt(0));
        $this->assertSame(23, $expr->getChildren()[0]->getOffsetAt(9));
    }
}
