<?php

namespace Allocine\Twigcs\Validator;

use Allocine\Twigcs\Ruleset\Official;
use Allocine\Twigcs\Ruleset\RulesetInterface;

class Validator
{
    /**
     * @var RulesetInterface
     */
    private $ruleset;

    /**
     * @var \Twig_TokenStream[]
     */
    private $files = [];

    /**
     * Validator constructor.
     */
    public function __construct()
    {
        $this->ruleset = new Official();
    }

    /**
     * @param RulesetInterface $ruleset
     *
     * @return self
     */
    public function setRuleset(RulesetInterface $ruleset): Validator
    {
        $this->ruleset = $ruleset;

        return $this;
    }

    /**
     * @param \Twig_TokenStream $file
     *
     * @return self
     */
    public function addFile(\Twig_TokenStream $file): Validator
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * @return self
     */
    public function check(): Validator
    {
        array_map(function ($file) {
            foreach ($this->ruleset->getRules() as $rule) {
                $rule->prepare(clone $file);
            }
        }, $this->files);

        array_map(function ($file) {
            foreach ($this->ruleset->getRules() as $rule) {
                $rule->check(clone $file);
            }
        }, $this->files);

        return $this;
    }

    /**
     * @return Violation[]
     */
    public function validate(): array
    {
        $violations = [];
        foreach ($this->ruleset->getRules() as $rule) {
            $violations = array_merge($violations, $rule->getViolations());
        }

        usort($violations, function (Violation $a, Violation $b) {
            return $a->getLine() > $b->getLine();
        });

        return $violations;
    }
}
