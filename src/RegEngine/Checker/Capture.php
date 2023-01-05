<?php

namespace FriendsOfTwig\Twigcs\RegEngine\Checker;

class Capture
{
    private string $type;

    private string $text;

    private int $offset;

    private Regex $source;

    private array $offsetsMap;

    public function __construct(string $type, string $text, int $offset, Regex $source)
    {
        $this->type = $type;
        $this->text = $text;
        $this->offset = $offset;
        $this->source = $source;
        $this->offsetsMap = [];
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

    public function getRealOffset()
    {
        return $this->offsetsMap[$this->offset] ?? 0;
    }

    public function getOffsetsMap(): array
    {
        return $this->offsetsMap;
    }

    public function setOffsetsMap(array $offsetsMap): self
    {
        $this->offsetsMap = $offsetsMap;

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
