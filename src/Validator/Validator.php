<?php

namespace Allocine\Twigcs\Validator;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Ruleset\RulesetInterface;

class Validator
{
    /**
     * @param \Twig_TokenStream $tokens
     *
     * @return Violation[]
     */
    public function validate(RulesetInterface $ruleset, \Twig_TokenStream $tokens)
    {
        $violations = [];
        foreach ($ruleset->getRules() as $rule) {
            $violations = array_merge($violations, $rule->check(clone $tokens));
        }

        usort($violations, function (Violation $a, Violation $b) {
            return $a->getLine() > $b->getLine();
        });

        return $violations;
    }
}
