<?php

namespace Allocine\Twigcs\Whitelist;

interface WhitelistInterface
{
    /**
     * @param \Twig\TokenStream $tokens
     * @param integer           $orientation
     *
     * @return boolean
     */
    public function pass(\Twig\TokenStream $tokens, $orientation);
}
