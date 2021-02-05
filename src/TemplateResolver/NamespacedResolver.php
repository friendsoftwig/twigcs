<?php

namespace FriendsOfTwig\Twigcs\TemplateResolver;

use FriendsOfTwig\Twigcs\TwigPort\Source;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class NamespacedResolver implements TemplateResolverInterface
{
    private array $namespaces;

    public function __construct(array $namespaces = [])
    {
        $this->namespaces = $namespaces;
    }

    public function load(string $path): Source
    {
        $namespace = $this->getNamespace($path);
        $subPath = substr($path, strlen($namespace) + 1);

        if ($namespace && ($this->namespaces[$namespace] ?? false) && $this->namespaces[$namespace]->exists($subPath)) {
            return $this->namespaces[$namespace]->load($subPath);
        }

        throw new \RuntimeException(sprintf('Template "%s" could not be resolved.', $path));
    }

    public function exists(string $path): bool
    {
        $namespace = $this->getNamespace($path);

        return array_key_exists($namespace, $this->namespaces);
    }

    private function getNamespace($path): ?string
    {
        $namespace = explode('/', $path)[0];

        return '@' === ($namespace[0] ?? null) ? substr($namespace, 1) : null;
    }
}
