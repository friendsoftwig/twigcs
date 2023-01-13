<?php

namespace FriendsOfTwig\Twigcs\RegEngine;

class ExpressionNode
{
    private string $expr;

    private array $children;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var string
     */
    private $type;

    private array $offsetsMap;

    public function __construct(ScopedExpression $scoped, $offset = 0)
    {
        $expr = '';
        $offsets = [];
        $offsetCounter = $offset;

        $this->children = [];

        foreach ($scoped->getContent() as $item) {
            if (is_string($item)) {
                $expr .= $item;
                $offsets[] = $offsetCounter;
                ++$offsetCounter;
            } else {
                $kind = $item->getKind();

                for ($i = 0; $i < strlen($kind); ++$i) {
                    $offsets[] = $offsetCounter;
                }
                $this->addChild(new self($item, $offsetCounter));
                $expr .= $kind;
                $offsetCounter += $item->strlen();
            }
        }

        $this->offsetsMap = $offsets;
        $this->expr = $expr;
        $this->offset = $offset;

        switch ($scoped->getKind()) {
            case '__ARRAY__':
                $this->type = 'arrayOrSlice';

                break;

            case '__PARENTHESES__':
            case '__HASH__':
            default:
                $this->type = 'expr';
        }
    }

    public static function fromString($expr)
    {
        $scoped = new ScopedExpression();
        $scoped->enqueueString($expr);

        return new self($scoped);
    }

    public function getOffsetAt($i)
    {
        return $this->offsetsMap[$i] ?? null;
    }

    public function getOffsetsMap()
    {
        return $this->offsetsMap;
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

    public function addChild(self $child, $delegateChildren = true): self
    {
        $this->children[] = $child;

        return $this;
    }

    public function getTrace(): string
    {
        $lines = [];

        $lines[] = ' ↪ '.$this->expr.' (offset: '.$this->offset.', type: '.$this->type.')';

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
