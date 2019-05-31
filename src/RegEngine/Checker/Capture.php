<?php

namespace Allocine\Twigcs\RegEngine\Checker;

class Capture
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var Regex
     */
    private $source;

    public function __construct(string $type, string $text, int $offset, Regex $source)
    {
        $this->type = $type;
        $this->text = $text;
        $this->offset = $offset;
        $this->source = $source;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function increaseOffset(int $offset): self
    {
        $this->offset += $offset;

        return $this;
    }

    public function getSource(): Regex
    {
        return $this->source;
    }

    public function setSource(Regex $source): self
    {
        $this->source = $source;

        return $this;
    }
}
