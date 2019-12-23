<?php

namespace FriendsOfTwig\Twigcs\Ruleset;

use FriendsOfTwig\Twigcs\Rule\RuleInterface;

interface RulesetInterface
{
    public function __construct(int $twigMajorVersion = 3);

    /**
     * @return RuleInterface[]
     */
    public function getRules();
}
