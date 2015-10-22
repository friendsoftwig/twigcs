<?php

namespace Allocine\TwigLinter\Rule;

use Allocine\TwigLinter\Validator\Violation;

interface RuleInterface
{
    /**
     * @param \Twig_TokenStream $tokens
     *
     * @return Violation[]
     */
    public function check(\Twig_TokenStream $tokens);
}
