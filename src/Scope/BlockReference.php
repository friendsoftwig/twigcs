<?php

namespace FriendsOfTwig\Twigcs\Scope;

class BlockReference
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return sprintf('reference for block "%s"', $this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
