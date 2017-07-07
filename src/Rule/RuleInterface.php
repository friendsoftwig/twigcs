<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Validator\Violation;

interface RuleInterface
{
    /**
     * @param \Twig_TokenStream $tokens
     *
     * @return Violation[]
     */
    public function check(\Twig_TokenStream $tokens);
}
