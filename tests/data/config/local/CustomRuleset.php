<?php

namespace FriendsOfTwig\Twigcs\Tests\data\config\local;

use FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder;
use FriendsOfTwig\Twigcs\RegEngine\RulesetConfigurator;
use FriendsOfTwig\Twigcs\Rule\UnusedVariable;
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;
use FriendsOfTwig\Twigcs\Validator\Violation;

class CustomRuleset implements RulesetInterface
{
    private $twigMajorVersion;

    public function __construct(int $twigMajorVersion)
    {
        $this->twigMajorVersion = $twigMajorVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        $configurator = new RulesetConfigurator();
        $configurator->setTwigMajorVersion($this->twigMajorVersion);
        $builder = new RulesetBuilder($configurator);

        return [
            new UnusedVariable(Violation::SEVERITY_WARNING),
        ];
    }
}
