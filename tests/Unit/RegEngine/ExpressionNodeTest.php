<?php

namespace FriendsOfTwig\Twigcs\Tests\Unit\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\ExpressionNode;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ExpressionNodeTest extends TestCase
{
    public function testOffsetMapping(): void
    {
        $expr = ExpressionNode::fromString('{% func({a: ["b", "B"]}) + {a: do(c + (d - 1))} %}');

        self::assertSame(0, $expr->getOffsetAt(0));
        self::assertSame(1, $expr->getOffsetAt(1));
        self::assertSame(2, $expr->getOffsetAt(2));
        self::assertSame(7, $expr->getOffsetAt(7));
        self::assertSame(7, $expr->getOffsetAt(8));
        self::assertSame(7, $expr->getOffsetAt(21));
        self::assertSame(24, $expr->getOffsetAt(22));
        self::assertSame(49, $expr->getOffsetAt(35));
        self::assertNull($expr->getOffsetAt(36));

        self::assertSame(7, $expr->getChildren()[0]->getOffsetAt(0));
        self::assertSame(23, $expr->getChildren()[0]->getOffsetAt(9));
    }
}
