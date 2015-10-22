<?php

namespace Allocine\TwigLinter\Ruleset;

use Allocine\TwigLinter\Rule\RuleInterface;

interface RulesetInterface
{
    /**
     * @return RuleInterface[]
     */
    public function getRules();
}
