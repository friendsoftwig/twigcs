<?php

namespace FriendsOfTwig\Twigcs\TemplateResolver;

use FriendsOfTwig\Twigcs\TwigPort\Source;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class NullResolver implements TemplateResolverInterface
{
    public function exists(string $path): bool
    {
        return true;
    }

    public function load(string $path): Source
    {
        return new Source('', $path, $path);
    }
}
