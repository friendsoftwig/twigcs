<?php

namespace FriendsOfTwig\Twigcs\TemplateResolver;

use FriendsOfTwig\Twigcs\TwigPort\Source;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class ChainResolver implements TemplateResolverInterface
{
    private array $chain;

    public function __construct(array $chain = [])
    {
        $this->chain = $chain;
    }

    public function load(string $path): Source
    {
        foreach ($this->chain as $loader) {
            if ($loader->exists($path)) {
                return $loader->load($path);
            }
        }

        throw new \RuntimeException(sprintf('Template "%s" could not be resolved.', $path));
    }

    public function exists(string $path): bool
    {
        foreach ($this->chain as $loader) {
            if ($loader->exists($path)) {
                return true;
            }
        }

        return false;
    }
}
