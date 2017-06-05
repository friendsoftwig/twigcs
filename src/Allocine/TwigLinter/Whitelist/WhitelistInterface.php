<?php

namespace Allocine\TwigLinter\Whitelist;

interface WhitelistInterface
{
    /**
     * @param \Twig_TokenStream $tokens
     * @param integer           $orientation
     *
     * @return boolean
     */
    public function pass(\Twig_TokenStream $tokens, $orientation);
}
