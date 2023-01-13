<?php

namespace FriendsOfTwig\Twigcs\Scope;

use FriendsOfTwig\Twigcs\TwigPort\Token;

class Declaration
{
    private string $name;

    private Token $token;

    private Scope $origin;

    public function __construct(string $name, Token $token, Scope $origin)
    {
        $this->name = $name;
        $this->token = $token;
        $this->origin = $origin;
    }

    public function __toString()
    {
        return sprintf('declaration of %s', $this->name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getOrigin(): Scope
    {
        return $this->origin;
    }
}
