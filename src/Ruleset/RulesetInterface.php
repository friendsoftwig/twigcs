<?php

namespace FriendsOfTwig\Twigcs\Ruleset;

use FriendsOfTwig\Twigcs\Rule\RuleInterface;

interface RulesetInterface
{
    /**
     * @return RuleInterface[]
     */
    public function getRules();
}
