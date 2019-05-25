<?php

namespace Allocine\Twigcs\Experimental;

class ExpressionNode
{
    public $expr;
    public $children;
    public $offset;

    public function __construct(string $expr, int $offset)
    {
        $this->expr = $expr;
        $this->offset = $offset;
        $this->children = [];
    }

    public function replaceExpr(string $expr)
    {
        $this->expr = $expr;
    }

    public function addChild(ExpressionNode $child)
    {
        $this->children[]= $child;
    }

    public function flatten(): array
    {
        $result = [$this];

        foreach ($this->children as $children) {
            foreach ($children->flatten() as $expressions) {
                $result[]= $expressions;
            }
        }

        $this->children = [];

        return $result;
    }
}
