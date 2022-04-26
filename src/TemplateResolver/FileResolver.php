<?php

namespace FriendsOfTwig\Twigcs\TemplateResolver;

use FriendsOfTwig\Twigcs\TwigPort\Source;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class FileResolver implements TemplateResolverInterface
{
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    public function exists(string $path): bool
    {
        return file_exists(sprintf('%s/%s', $this->basePath, $path));
    }

    public function load(string $path): Source
    {
        $realPath = sprintf('%s/%s', $this->basePath, $path);

        $content = @file_get_contents($realPath);

        return new Source($content, $realPath, $realPath);
    }
}
