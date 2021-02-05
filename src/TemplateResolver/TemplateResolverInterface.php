<?php

namespace FriendsOfTwig\Twigcs\TemplateResolver;

use FriendsOfTwig\Twigcs\TwigPort\Source;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
interface TemplateResolverInterface
{
    public function exists(string $path): bool;

    public function load(string $path): Source;
}
