<?php

namespace Allocine\Twigcs\Validator;

use Allocine\Twigcs\Ruleset\RulesetInterface;

class Validator
{
    /**
     * @param RulesetInterface  $ruleset
     *
     * @return Violation[]
     */
    public function validate(RulesetInterface $ruleset)
    {
        $violations = [];
        foreach ($ruleset->getRules() as $rule) {
            $violations = array_merge($violations, $rule->getViolations());
        }

        usort($violations, function (Violation $a, Violation $b) {
            return $a->getLine() > $b->getLine();
        });

        return $violations;
    }

    /**
     * @param RulesetInterface  $ruleset
     * @param \Twig_TokenStream $tokens
     */
    public function check(RulesetInterface $ruleset, \Twig_TokenStream $tokens)
    {
        foreach ($ruleset->getRules() as $rule) {
            $rule->check(clone $tokens);
        }
    }
}
