<?php

namespace Allocine\TwigLinter\Whitelist;

class TokenWhitelist implements WhitelistInterface
{
    private $tokens;

    private $offsets;

    /**
     * @param mixed[]   $tokens
     * @param integer[] $offsets
     */
    public function __construct(array $tokens, array $offsets)
    {
        $this->tokens = $tokens;
        $this->offsets = $offsets;
    }

    /**
     * {@inheritdoc}
     */
    public function pass(\Twig_TokenStream $tokens, $orientation)
    {
        foreach ($this->offsets as $offset) {
            $token = $tokens->look($offset*$orientation);

            if (in_array($token->getValue(), $this->tokens)) {
                return true;
            }

            if (in_array($token->getType(), $this->tokens)) {
                return true;
            }
        }

        return false;
    }
}
