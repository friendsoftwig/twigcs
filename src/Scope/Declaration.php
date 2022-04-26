<?php

namespace FriendsOfTwig\Twigcs\Scope;

use FriendsOfTwig\Twigcs\TwigPort\Token;

class Declaration
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var Scope
     */
    private $origin;

    public function __construct(string $name, Token $token, Scope $origin)
    {
        $this->name = $name;
        $this->token = $token;
        $this->origin = $origin;
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

    public function __toString()
    {
        return sprintf('declaration of %s', $this->name);
    }
}
