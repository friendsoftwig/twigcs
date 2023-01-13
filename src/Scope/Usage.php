<?php

namespace FriendsOfTwig\Twigcs\Scope;

class Usage
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return sprintf('usage of %s', $this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
