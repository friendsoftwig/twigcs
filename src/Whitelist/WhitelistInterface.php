<?php

namespace Allocine\Twigcs\Whitelist;

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
