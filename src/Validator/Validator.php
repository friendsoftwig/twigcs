<?php

namespace FriendsOfTwig\Twigcs\Validator;

use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\TwigPort\TokenStream;

class Validator
{
    private array $collectedData;

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
    public function validate(RulesetInterface $ruleset, TokenStream $tokens)
    {
        $violations = [];

        foreach ($ruleset->getRules() as $rule) {
            $violations = array_merge($violations, $rule->check(clone $tokens));

            $this->collectedData[get_class($rule)] = $rule->collect();
        }

        usort($violations, function (Violation $a, Violation $b) {
            return $a->getLine() - $b->getLine();
        });

        return $violations;
    }
}
