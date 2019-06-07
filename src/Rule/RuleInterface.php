<?php

namespace Allocine\Twigcs\Rule;

use Allocine\Twigcs\Validator\Violation;

interface RuleInterface
{
    /**
     * @return Violation[]
     */
    public function check(\Twig_TokenStream $tokens);

    public function collect(): array;
}
