<?php

namespace Allocine\Twigcs\Ruleset;

use Allocine\Twigcs\Rule\RuleInterface;

interface RulesetInterface
{
    /**
     * @return RuleInterface[]
     */
    public function getRules();
}
