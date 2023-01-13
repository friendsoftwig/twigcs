<?php

namespace FriendsOfTwig\Twigcs\RegEngine;

class ScopedExpression
{
    private array $content;

    /**
     * @var string|self
     */
    private $head;

    private bool $open;

    private string $kind;

    public function __construct(string $kind = 'EXPR')
    {
        $this->content = [];
        $this->open = true;
        $this->kind = $kind;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function enqueueString($string)
    {
        $array = str_split($string);

        for ($i = 0; $i < count($array); ++$i) {
            $this->enqueue($array[$i], $array[$i - 1] ?? null, $array[$i + 1] ?? null);
        }
    }

    public function enqueue($char, $prev = null, $next = null)
    {
        if ('(' === $char) {
            $this->push(new self('__PARENTHESES__'));
            $this->push('(');
        } elseif ('{' === $char && ('%' !== $next && '{' !== $next) && ('{' !== $prev)) {
            $this->push(new self('__HASH__'));
            $this->push('{');
        } elseif ('[' === $char) {
            $this->push(new self('__ARRAY__'));
            $this->push('[');
        } elseif ('?' === $char && !in_array($next, [':', '?'], true) && !in_array($prev, ['?'], true)) { // don't collect ?: and ??
            $this->push(new self('__TERNARY__'));
            $this->push('?');
        } else {
            if ($this->shouldRollback($char)) {
                $this->rollback($char);
            }

            if ($this->shouldClose($char, $prev, $next)) {
                $this->push($char);
                $this->close();
            } else {
                $this->push($char);
            }
        }
    }

    public function shouldRollback($char)
    {
        $head = $this->getHead();

        return ($head instanceof self) && ('__TERNARY__' === $head->kind) && in_array($char, [',', '}', ')', ']'], true);
    }

    public function shouldClose(string $char, $prev = null, $next = null)
    {
        $head = $this->getHead();

        if (($head instanceof self) && ('__PARENTHESES__' === $head->kind) && ')' === $char) {
            return true;
        }

        if (($head instanceof self) && ('__HASH__' === $head->kind) && '}' === $char) {
            return '%' !== $prev;
        }

        if (($head instanceof self) && ('__ARRAY__' === $head->kind) && ']' === $char) {
            return true;
        }

        if (($head instanceof self) && ('__TERNARY__' === $head->kind) && ':' === $char) {
            return true;
        }

        return false;
    }

    public function getHead()
    {
        if ($this->head instanceof self && $this->head->open) {
            return $this->head->getHead();
        }

        return $this;
    }

    public function close()
    {
        if ($this->head instanceof self) {
            if ($this->head->open) {
                $this->head->close();
            } else {
                $this->open = false;
            }
        } else {
            $this->open = false;
        }
    }

    public function push($item)
    {
        if (($this->head instanceof self) && $this->head->open) {
            $this->head->push($item);
        } else {
            $this->head = $item;
            $this->content[] = $item;
        }
    }

    /**
     * Finds the current active head and its parent, then destroy the active head
     * and put its content inside the parent.
     */
    public function rollback($char)
    {
        $head = $this;
        $previousHead = null;

        while ($head->head instanceof self && $head->head->open) {
            $previousHead = $head;
            $head = $head->head;
        }

        if (null === $previousHead) {
            throw new \RuntimeException('Cannot rollback : no parent scope.');
        }

        // remove the rollbacked item
        array_pop($previousHead->content);

        foreach ($head->content as $item) {
            $previousHead->content[] = $item;
            $previousHead->head = $item;
        }
    }

    public function strlen()
    {
        $i = 0;

        foreach ($this->content as $item) {
            $i += is_string($item) ? 1 : ($item->strlen());
        }

        return $i;
    }

    public function debug()
    {
        $result = '';

        foreach ($this->content as $content) {
            if (is_string($content)) {
                $result .= $content;
            } else {
                $result .= '['.$content->debug().']';
            }
        }

        return $result;
    }
}
