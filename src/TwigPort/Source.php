<?php

/*
 * This file was taken and MODIFIED from Twig : https://github.com/twigphp/twig
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the same folder as this piece of code.
 */

namespace FriendsOfTwig\Twigcs\TwigPort;

final class Source
{
    private string $code;
    private string $name;
    private string $path;

    public function __construct(string $code, string $name, string $path = '')
    {
        $this->code = $code;
        $this->name = $name;
        $this->path = $path;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
