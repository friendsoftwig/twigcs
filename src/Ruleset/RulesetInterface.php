<?php

namespace FriendsOfTwig\Twigcs\Ruleset;

use FriendsOfTwig\Twigcs\Rule\RuleInterface;

/**
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
interface RulesetInterface
{
    public function __construct(int $twigMajorVersion);

    /**
     * @return RuleInterface[]
     */
    public function getRules();
}
