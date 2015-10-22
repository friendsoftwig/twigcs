<?php

namespace Allocine\TwigLinter\Whistelist;

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
