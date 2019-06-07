<?php

namespace Allocine\Twigcs\Validator;

use Allocine\Twigcs\Ruleset\RulesetInterface;

class Validator
{
    private $collectedData;

    public function __construct()
    {
        $this->collectedData = [];
    }

    public function getCollectedData(): array
    {
        return $this->collectedData;
    }

    /**
     * @return Violation[]
     */
    public function validate(RulesetInterface $ruleset, \Twig_TokenStream $tokens)
    {
        $violations = [];
        foreach ($ruleset->getRules() as $rule) {
            $violations = array_merge($violations, $rule->check(clone $tokens));

            $this->collectedData[get_class($rule)] = $rule->collect();
        }

        usort($violations, function (Violation $a, Violation $b) {
            return $a->getLine() > $b->getLine();
        });

        return $violations;
    }
}
