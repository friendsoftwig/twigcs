<?php

namespace FriendsOfTwig\Twigcs\Scope;

use FriendsOfTwig\Twigcs\TwigPort\Token;

class Scope
{
    /**
     * @var Scope|null
     */
    private $parent;

    /**
     * @var array
     */
    private $queue;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isolated;

    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
        $this->queue = [];
        $this->isolated = false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * When isolated, a scope won't be explored when looking for name usages.
     */
    public function isolate()
    {
        $this->isolated = true;
    }

    public function isIsolated(): bool
    {
        return $this->isolated;
    }

    public function spawn(string $type, string $name): self
    {
        $scope = new self($type, $name);
        $scope->parent = $this;
        $this->queue[] = $scope;

        return $scope;
    }

    public function leave(): self
    {
        return $this->parent ?? $this;
    }

    public function declare(string $name, Token $token)
    {
        $this->queue[] = new Declaration($name, $token);
    }

    public function use(string $name)
    {
        $this->queue[] = new Usage($name);
    }

    public function referenceBlock(string $blockName)
    {
        $this->queue[] = new BlockReference($blockName);
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function flatten(): FlattenedScope
    {
        return new FlattenedScope($this);
    }
}
