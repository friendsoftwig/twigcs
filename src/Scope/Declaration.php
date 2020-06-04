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

    public function __construct(string $name, Token $token)
    {
        $this->name = $name;
        $this->token = $token;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function __toString()
    {
        return sprintf('declaration of %s', $this->name);
    }
}
