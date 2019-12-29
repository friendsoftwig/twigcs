<?php

namespace FriendsOfTwig\Twigcs\Tests\Ruleset;

use FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder;
use FriendsOfTwig\Twigcs\RegEngine\RulesetConfigurator;
use FriendsOfTwig\Twigcs\Rule;
use FriendsOfTwig\Twigcs\Validator\Violation;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;

/**
 * The official twigcs ruleset, based on http://twig.sensiolabs.org/doc/coding_standards.html.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class SuppressWarningForUnusedVariable implements RulesetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        $builder = new RulesetBuilder(new RulesetConfigurator());

        return [
            new Rule\LowerCaseVariable(Violation::SEVERITY_ERROR),
            new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
            new Rule\TrailingSpace(Violation::SEVERITY_ERROR),
            new Rule\UnusedMacro(Violation::SEVERITY_WARNING),
        ];
    }
}
