<?php

namespace FriendsOfTwig\Twigcs\Scope;

class FlattenedScope
{
    private array $isolatedScopes;

    private array $queue;

    private array $blocks;

    public function __construct(Scope $scope, array $blocks = [])
    {
        $queue = [];
        $this->isolatedScopes = [];
        $this->blocks = $blocks;

        foreach ($scope->getQueue() as $item) {
            if ($item instanceof Scope && 'block' === $item->getType()) {
                $this->blocks[$item->getName()] = $item;
            }
        }

        foreach ($scope->getQueue() as $item) {
            if ($item instanceof Scope) {
                $sub = new self($item, $this->blocks);

                if (!$item->isIsolated()) {
                    $queue = array_merge($queue, $sub->getQueue());
                    $this->isolatedScopes = array_merge($this->isolatedScopes, $sub->getIsolatedScopes());
                    $this->blocks = array_merge($sub->getBlocks(), $this->blocks);
                } else {
                    $this->isolatedScopes[] = $sub;
                }
            } else {
                $queue[] = $item;
            }
        }

        $this->queue = [];

        foreach ($queue as $item) {
            if ($item instanceof BlockReference) {
                $block = $this->blocks[$item->getName()] ?? null;

                if ($block) {
                    $this->queue = array_merge($this->queue, $block->flatten()->getQueue());
                }
            } else {
                $this->queue[] = $item;
            }
        }
    }

    public function getIsolatedScopes(): array
    {
        return $this->isolatedScopes;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function getRootUnusedDeclarations(): array
    {
        $unused = $this->getUnusedDeclarations();

        $unused = array_filter($unused, function (Declaration $declaration) {
            $scope = $declaration->getOrigin();

            while ('file' !== $scope->getType() && $scope->getParent()) {
                $scope = $scope->getParent();
            }

            return 'root' === $scope->getName();
        });

        return $unused;
    }

    public function getUnusedDeclarations(): array
    {
        $unused = [];

        foreach ($this->queue as $item) {
            if ($item instanceof Declaration) {
                $unused[] = $item;
            }

            if ($item instanceof Usage) {
                $unused = array_filter($unused, function ($declaration) use ($item) {
                    return $declaration->getName() !== $item->getName();
                });
            }

            if ($item instanceof BlockReference) {
                // try to resolve;
            }
        }

        return $unused;
    }

    public function dump(int $tab = 0): string
    {
        $result = '';

        foreach ($this->queue as $item) {
            if ($item) {
                $result .= $item."\n";
            }
        }

        return $result;
    }
}
