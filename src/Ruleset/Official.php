<?php

namespace Allocine\Twigcs\Ruleset;

use Allocine\Twigcs\Rule;
use Allocine\Twigcs\Validator\Violation;

/**
 * The official twigcs ruleset, based on http://twig.sensiolabs.org/doc/coding_standards.html.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class Official implements RulesetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return [
            new Rule\LowerCaseVariable(Violation::SEVERITY_ERROR),
            new Rule\UnusedVariable(Violation::SEVERITY_WARNING),
            new Rule\UnusedMacro(Violation::SEVERITY_WARNING),
            new Rule\RegEngineRule(Violation::SEVERITY_ERROR),
            new Rule\TrailingSpace(Violation::SEVERITY_ERROR),
        ];
    }
}
