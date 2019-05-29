<?php

namespace Allocine\Twigcs\Experimental;

class ExpressionNode
{
    public $expr;
    public $children;
    public $offset;
    public $type;

    public function __construct(string $expr, int $offset, string $type = 'expr')
    {
        $this->expr = $expr;
        $this->offset = $offset;
        $this->children = [];
        $this->type = $type;
    }

    public function replaceExpr(string $expr)
    {
        $this->expr = $expr;
    }

    public function addChild(self $child)
    {
        $this->children[] = $child;
    }

    public function getTrace(): string
    {
        $lines = [];

        $lines[] = ' ↪ '.$this->expr.' (offset: '.$this->offset.')';

        foreach ($this->children as $child) {
            $childrenLines = explode("\n", $child->getTrace());
            $childrenLines = array_map(function (string $line) {
                return '    '.$line;
            }, $childrenLines);

            $lines[] = implode("\n", $childrenLines);
        }

        return implode("\n", $lines);
    }

    public function flatten(): array
    {
        $result = [$this];

        foreach ($this->children as $children) {
            foreach ($children->flatten() as $expressions) {
                $result[] = $expressions;
            }
        }

        $this->children = [];

        return $result;
    }
}
