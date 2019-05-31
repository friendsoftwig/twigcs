<?php

namespace Allocine\Twigcs\RegEngine;

class ExpressionNode
{
    /**
     * @var string
     */
    private $expr;

    /**
     * @var array
     */
    private $children;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $expr, int $offset, string $type = 'expr')
    {
        $this->expr = $expr;
        $this->offset = $offset;
        $this->children = [];
        $this->type = $type;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getExpr(): string
    {
        return $this->expr;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function replaceExpr(string $expr): self
    {
        $this->expr = $expr;

        return $this;
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
