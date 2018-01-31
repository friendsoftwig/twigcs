<?php

namespace Allocine\Twigcs\Ruleset;

use Allocine\Twigcs\Rule\RuleInterface;

interface RulesetInterface
{
    /**
     * @return RulesetInterface
     */
    public function initRules(): RulesetInterface;

    /**
     * @return RuleInterface[]
     */
    public function getRules(): array;
}
